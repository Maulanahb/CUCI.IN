<?php

namespace App\Services\Reports;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Illuminate\Support\Carbon;

class ServiceTurnaroundEvaluation
{
    /**
     * Hitung evaluasi durasi penyelesaian cucian per jenis layanan (FR-31).
     *
     * @return array{
     *     evaluations: array<int, array{
     *         service_id: int,
     *         service_name: string,
     *         is_express: bool,
     *         target_hours: int,
     *         completed_count: int,
     *         avg_duration_hours: float,
     *         on_time_count: int,
     *         on_time_percentage: float
     *     }>,
     *     overall_avg_hours: float,
     *     overall_on_time_percentage: float
     * }
     */
    public function evaluate(Carbon $startDate, Carbon $endDate): array
    {
        $completedStatuses = [
            TransactionStatus::Selesai,
            TransactionStatus::Diambil,
        ];

        $transactions = Transaction::query()
            ->with(['details.service', 'statusHistories'])
            ->whereIn('status', $completedStatuses)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $grouped = [];

        foreach ($transactions as $transaction) {
            $completedHistory = $transaction->statusHistories
                ->filter(function ($history) {
                    $status = $history->status instanceof TransactionStatus
                        ? $history->status
                        : TransactionStatus::tryFrom((string) $history->status);

                    return $status === TransactionStatus::Selesai;
                })
                ->sortBy('changed_at')
                ->first();

            $completedAt = $completedHistory && $completedHistory->changed_at
                ? Carbon::parse($completedHistory->changed_at)
                : Carbon::parse($transaction->updated_at);

            $createdAt = Carbon::parse($transaction->created_at);
            $actualHours = round(max(0, abs($completedAt->diffInMinutes($createdAt, false)) / 60), 2);

            foreach ($transaction->details as $detail) {
                $service = $detail->service;
                if (! $service) {
                    continue;
                }

                $serviceId = (int) $service->id;

                if (! isset($grouped[$serviceId])) {
                    $grouped[$serviceId] = [
                        'service_id' => $serviceId,
                        'service_name' => (string) $service->name,
                        'is_express' => (bool) $service->is_express,
                        'target_hours' => (int) ($service->estimated_duration ?? 24),
                        'total_duration_hours' => 0.0,
                        'completed_count' => 0,
                        'on_time_count' => 0,
                    ];
                }

                $grouped[$serviceId]['completed_count']++;
                $grouped[$serviceId]['total_duration_hours'] += $actualHours;

                if ($actualHours <= $grouped[$serviceId]['target_hours']) {
                    $grouped[$serviceId]['on_time_count']++;
                }
            }
        }

        $evaluations = [];
        $totalCompleted = 0;
        $totalHoursAll = 0.0;
        $totalOnTimeAll = 0;

        foreach ($grouped as $item) {
            $count = $item['completed_count'];
            $avgHours = $count > 0 ? round($item['total_duration_hours'] / $count, 1) : 0.0;
            $onTimePct = $count > 0 ? round(($item['on_time_count'] / $count) * 100, 1) : 0.0;

            $evaluations[] = [
                'service_id' => $item['service_id'],
                'service_name' => $item['service_name'],
                'is_express' => $item['is_express'],
                'target_hours' => $item['target_hours'],
                'completed_count' => $count,
                'avg_duration_hours' => $avgHours,
                'on_time_count' => $item['on_time_count'],
                'on_time_percentage' => $onTimePct,
            ];

            $totalCompleted += $count;
            $totalHoursAll += $item['total_duration_hours'];
            $totalOnTimeAll += $item['on_time_count'];
        }

        return [
            'evaluations' => $evaluations,
            'overall_avg_hours' => $totalCompleted > 0 ? round($totalHoursAll / $totalCompleted, 1) : 0.0,
            'overall_on_time_percentage' => $totalCompleted > 0 ? round(($totalOnTimeAll / $totalCompleted) * 100, 1) : 0.0,
        ];
    }
}
