<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Penjaga drift desain: memindai resources/views terhadap aturan DESIGN.md.
 */
class DesignSystemGuardTest extends TestCase
{
    /**
     * Dokumen ekspor dan nota thermal punya aturan visual sendiri.
     *
     * @var array<int, string>
     */
    private const EXCLUDED_VIEWS = [
        'admin/reports/excel.blade.php',
        'admin/reports/pdf.blade.php',
        'staff/transactions/print.blade.php',
    ];

    /**
     * @return array<string, string> path relatif view => isi file
     */
    private function viewFiles(): array
    {
        $root = resource_path('views');
        $files = [];

        foreach (File::allFiles($root) as $file) {
            $relative = str_replace('\\', '/', $file->getRelativePathname());

            if (! str_ends_with($relative, '.blade.php') || in_array($relative, self::EXCLUDED_VIEWS, true)) {
                continue;
            }

            $files[$relative] = $file->getContents();
        }

        return $files;
    }

    /**
     * Pemindaian per baris, atau per berkas utuh ($wholeFile) agar tag multi-baris ikut terperiksa.
     *
     * @param  array<int, string>  $allowedPaths
     */
    private function assertNoMatches(string $pattern, string $message, array $allowedPaths = [], bool $wholeFile = false): void
    {
        $violations = [];

        foreach ($this->viewFiles() as $path => $contents) {
            if (in_array($path, $allowedPaths, true)) {
                continue;
            }

            if ($wholeFile) {
                preg_match_all($pattern, $contents, $matches, PREG_OFFSET_CAPTURE);

                foreach ($matches[0] as [, $offset]) {
                    $violations[] = $path.':'.(substr_count($contents, "\n", 0, $offset) + 1);
                }

                continue;
            }

            foreach (explode("\n", $contents) as $index => $line) {
                if (preg_match($pattern, $line) === 1) {
                    $violations[] = $path.':'.($index + 1);
                }
            }
        }

        $this->assertSame([], $violations, $message."\n".implode("\n", $violations));
    }

    public function test_views_have_no_gradients_or_decorative_blur(): void
    {
        $this->assertNoMatches('/bg-gradient-|bg-linear-|bg-clip-text|\bblur-(?:xl|2xl|3xl)\b/', 'Gradien dan blur dekoratif dilarang DESIGN.md.');

        $this->assertNoMatches('/^(?!.*\bsticky\b).*backdrop-blur/', 'Blur hanya untuk header lengket, modal, dan sheet.', [
            'components/ui/modal.blade.php',
            'components/ui/sheet.blade.php',
        ]);
    }

    public function test_views_have_no_hover_lift_effects(): void
    {
        $this->assertNoMatches('/hover(?:\/[\w-]+)?:(?:shadow-|scale-)|active:scale-|\bshadow-(?:xl|2xl)\b/', 'Flat Counter Rule: tanpa efek angkat atau bayangan besar.');
    }

    public function test_views_use_only_design_system_palettes(): void
    {
        $this->assertNoMatches(
            '/\b(?:bg|text|border|ring|from|via|to|fill|stroke)-(?:purple|indigo|blue|violet|fuchsia|pink|cyan|red|green|gray|zinc|neutral|stone|lime|teal|orange|yellow)-\d/',
            'Pakai brand-*, slate, dan warna status resmi.'
        );
    }

    public function test_views_have_no_emoji_icons(): void
    {
        $this->assertNoMatches('/⚡/u', 'Pakai ikon Lucide, bukan emoji.');
    }

    public function test_table_headers_declare_scope(): void
    {
        $this->assertNoMatches('/<th(?![^>]*\bscope=)[\s>]/', 'Setiap <th> wajib punya scope="col" atau scope="row".', [], true);
    }

    public function test_small_touch_targets_grow_on_coarse_pointers(): void
    {
        $this->assertNoMatches('/^(?!.*pointer-coarse).*(?:min-h-\[3\dpx\]|\bmin-h-8\b)/', 'Kontrol dengan tinggi minimum kecil wajib punya pointer-coarse:min-h-11.');
        $this->assertNoMatches('/^(?!.*pointer-coarse)(?=.*\bcursor-pointer\b).*\b(?:size-8|h-8)\b/', 'Kontrol interaktif size-8/h-8 wajib punya varian pointer-coarse.');
    }

    public function test_views_have_no_tiny_text(): void
    {
        $this->assertNoMatches('/text-\[(?:9|10|11)px\]/', 'Teks minimal text-xs (12px).');
    }

    public function test_views_do_not_use_low_contrast_slate_for_text(): void
    {
        $this->assertNoMatches(
            '/^(?!.*(?:<svg|x-lucide|placeholder|<kbd|disabled)).*(?=.*\btext-slate-400\b)(?=.*(?:\btext-(?:xs|sm|2xs)\b|\btext-\[|\bblock\b|\bitalic\b|\bfont-(?:medium|normal|semibold|bold|mono|sans)\b|\btext-slate-400[^>]*>[^<]*[A-Za-z{]))/',
            'text-slate-400 kontrasnya kurang dari 4,5:1; pakai text-slate-500.'
        );
    }
}
