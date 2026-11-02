<?php

namespace App\Http\Middleware;

use App\Models\IdempotencyKey;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class EnsureIdempotency
{
    /**
     * Handle an incoming request with idempotency protection.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->extractKey($request);

        // 1. Pass-through if key is empty/null (backward compatibility)
        if (blank($key)) {
            return $next($request);
        }

        $currentHash = $this->computePayloadHash($request);
        $userId = $request->user()?->id;
        $route = $request->route()?->getName() ?? $request->path();

        // 2. Atomic insert or lookup
        try {
            $record = IdempotencyKey::create([
                'key' => $key,
                'user_id' => $userId,
                'route' => $route,
                'request_hash' => $currentHash,
                'status' => 'processing',
                'expires_at' => now()->addHours(24),
            ]);
            $isNewRecord = true;
        } catch (UniqueConstraintViolationException|QueryException) {
            $record = IdempotencyKey::where('key', $key)->first();
            $isNewRecord = false;
        }

        // Unlikely fallback if record was deleted concurrently
        if (! $record) {
            return $this->handle($request, $next);
        }

        // 3. Handle existing key (concurrency or replay)
        if (! $isNewRecord) {
            // Prune expired record and process anew
            if ($record->expires_at && $record->expires_at->isPast()) {
                $record->delete();

                return $this->handle($request, $next);
            }

            // In-flight concurrent request returns 409 Conflict
            if ($record->status === 'processing') {
                return response()->json([
                    'message' => 'Permintaan sedang diproses. Mohon tunggu sesaat.',
                ], 409);
            }

            // Reject payload mismatch with 422 Unprocessable Content
            if ($record->request_hash !== $currentHash) {
                return response()->json([
                    'message' => 'Idempotency key payload mismatch.',
                ], 422);
            }

            // Completed request: replay cached response
            if ($record->status === 'completed') {
                return $this->buildReplayResponse($record, $request);
            }

            // Fallback for any invalid or unrecognized status: delete and reprocess
            $record->delete();

            return $this->handle($request, $next);
        }

        // 4. Forwarding to next middleware/controller
        try {
            $response = $next($request);
        } catch (\Throwable $e) {
            $record->delete();

            throw $e;
        }

        // 5. Error handling on response (status >= 400 or newly flashed session errors)
        $hasErrors = false;

        if ($response->getStatusCode() >= 400) {
            $hasErrors = true;
        }

        if (! $hasErrors && $response->isRedirection() && $request->hasSession()) {
            $session = $request->session();
            $newFlashes = (array) $session->get('_flash.new', []);
            if (in_array('errors', $newFlashes, true) || in_array('error', $newFlashes, true)) {
                $hasErrors = true;
            }
        }

        if ($hasErrors) {
            $record->delete();

            return $response;
        }

        // 6. Success finalization: update record to 'completed'
        $headers = $this->extractSafeHeaders($response);

        if ($response->isRedirection()) {
            $flash = [];
            if ($request->hasSession()) {
                $session = $request->session();
                foreach (['success', 'status', 'message', 'warning', 'info'] as $flashKey) {
                    if ($session->has($flashKey)) {
                        $flash[$flashKey] = $session->get($flashKey);
                    }
                }
            }

            $responseBody = json_encode([
                'target_url' => $response->getTargetUrl(),
                'flash' => $flash,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } else {
            $responseBody = $response->getContent();
        }

        $record->update([
            'status' => 'completed',
            'response_code' => $response->getStatusCode(),
            'response_body' => $responseBody,
            'response_headers' => $headers,
            'expires_at' => now()->addHours(24),
        ]);

        return $response;
    }

    /**
     * Extract idempotency key from headers or form input.
     */
    protected function extractKey(Request $request): ?string
    {
        $key = $request->header('Idempotency-Key')
            ?? $request->header('X-Idempotency-Key')
            ?? $request->input('idempotency_key');

        if (is_array($key)) {
            $key = reset($key);
        }

        if (! is_string($key)) {
            return null;
        }

        $key = trim($key);

        return $key !== '' ? substr($key, 0, 64) : null;
    }

    /**
     * Compute a deterministic SHA-256 hash of the request payload.
     */
    protected function computePayloadHash(Request $request): string
    {
        $payload = $request->except(['_token', '_method', 'idempotency_key']);

        $normalized = $this->normalizePayload($payload);

        return hash('sha256', (string) json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Recursively normalize payload values and sort keys for canonical hashing.
     */
    protected function normalizePayload(mixed $value): mixed
    {
        if ($value instanceof UploadedFile) {
            return [
                '__file_name' => $value->getClientOriginalName(),
                '__file_size' => $value->getSize(),
                '__file_mime' => $value->getClientMimeType(),
            ];
        }

        if (! is_array($value)) {
            return $value;
        }

        foreach ($value as $k => $v) {
            $value[$k] = $this->normalizePayload($v);
        }

        if (! array_is_list($value)) {
            ksort($value);
        }

        return $value;
    }

    /**
     * Reconstruct and replay a cached response with the Idempotent-Replayed header.
     */
    protected function buildReplayResponse(IdempotencyKey $record, Request $request): Response
    {
        $savedCode = $record->response_code ?? 200;
        $savedHeaders = $record->response_headers ?? [];
        $savedBody = $record->response_body ?? '';

        if ($savedCode >= 300 && $savedCode < 400) {
            $data = json_decode($savedBody, true) ?? [];
            $targetUrl = $data['target_url'] ?? url('/');
            $replayResponse = redirect()->to($targetUrl, $savedCode);

            if (isset($data['flash']) && is_array($data['flash']) && $request->hasSession()) {
                $session = $request->session();
                foreach ($data['flash'] as $key => $message) {
                    $session->flash($key, $message);
                }
            }
        } else {
            $replayResponse = response($savedBody, $savedCode);
        }

        foreach ($savedHeaders as $name => $values) {
            if (in_array(strtolower($name), ['set-cookie', 'location'], true)) {
                continue;
            }
            $replayResponse->headers->set($name, $values);
        }

        $replayResponse->headers->set('Idempotent-Replayed', 'true');

        return $replayResponse;
    }

    /**
     * Extract transport-safe headers from response, excluding session cookies.
     *
     * @return array<string, array<int, string|null>>
     */
    protected function extractSafeHeaders(Response $response): array
    {
        $headers = [];
        $strip = ['set-cookie', 'date', 'transfer-encoding', 'connection', 'x-powered-by', 'keep-alive'];

        foreach ($response->headers->all() as $name => $values) {
            if (in_array(strtolower($name), $strip, true)) {
                continue;
            }
            $headers[$name] = $values;
        }

        return $headers;
    }
}
