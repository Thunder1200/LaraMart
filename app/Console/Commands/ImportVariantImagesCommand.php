<?php

namespace App\Console\Commands;

use App\Models\ProductVariant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportVariantImagesCommand extends Command
{
    protected $signature = 'laramart:import-variant-images
        {source? : Görsellerin bulunduğu klasör (varsayılan: storage/app/flow-downloads)}
        {--batch= : Dosyalar isimsiz/sıralı indiyse hangi 24lük pakete (1, 2, 3...) eşleneceği}
        {--batch-size=24 : Paket boyutu}';

    protected $description = 'Google Flow ile indirilen benzersiz ürün-renk görsellerini public/images/products/variants/ klasörüne aktarır ve durumu raporlar.';

    public function handle(): int
    {
        $sourceDir = (string) ($this->argument('source') ?: storage_path('app/flow-downloads'));
        $targetDir = public_path('images/products/variants');
        $manifestPath = base_path('docs/google-flow-prompts/manifest.json');

        File::ensureDirectoryExists($targetDir);

        if (! File::isDirectory($sourceDir)) {
            $this->warn("Kaynak klasör bulunamadı: {$sourceDir}");
            $this->reportCoverage();

            return self::SUCCESS;
        }

        $files = collect(File::files($sourceDir))
            ->filter(fn (\SplFileInfo $f): bool => in_array(strtolower($f->getExtension()), ['jpg', 'jpeg', 'png', 'webp'], true))
            ->sortBy(fn (\SplFileInfo $f): int => $f->getMTime())
            ->values();

        if ($files->isEmpty()) {
            $this->warn("Kaynak klasörde aktarılacak görsel bulunamadı: {$sourceDir}");
            $this->reportCoverage();

            return self::SUCCESS;
        }

        $manifest = File::exists($manifestPath)
            ? (json_decode(File::get($manifestPath), true) ?? [])
            : [];

        $imported = 0;
        $batchOption = $this->option('batch');

        if ($batchOption !== null && is_numeric($batchOption)) {
            $batchNum = max(1, (int) $batchOption);
            $batchSize = max(1, (int) $this->option('batch-size'));
            $offset = ($batchNum - 1) * $batchSize;
            $slice = array_slice($manifest, $offset, $batchSize);

            foreach ($files as $idx => $file) {
                if (! isset($slice[$idx])) {
                    break;
                }

                $dest = public_path($slice[$idx]['image_path']);
                if ($this->saveAsJpg($file->getRealPath(), $dest)) {
                    $imported++;
                }
            }
        } else {
            $expectedByStem = [];
            foreach ($manifest as $entry) {
                $stem = pathinfo((string) $entry['file_name'], PATHINFO_FILENAME);
                $expectedByStem[strtolower($stem)] = (string) $entry['image_path'];
            }

            foreach ($files as $file) {
                $stem = strtolower(pathinfo($file->getFilename(), PATHINFO_FILENAME));
                foreach ($expectedByStem as $expectedStem => $targetRelative) {
                    if ($stem === $expectedStem || str_contains($stem, $expectedStem)) {
                        if ($this->saveAsJpg($file->getRealPath(), public_path($targetRelative))) {
                            $imported++;
                        }
                        break;
                    }
                }
            }
        }

        $this->info("{$imported} benzersiz varyant görseli başarıyla aktarıldı.");
        $this->reportCoverage();

        return self::SUCCESS;
    }

    private function saveAsJpg(string $sourcePath, string $destPath): bool
    {
        $ext = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        $image = match ($ext) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($sourcePath),
            'png' => @imagecreatefrompng($sourcePath),
            'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : false,
            default => false,
        };

        if (! $image) {
            return File::copy($sourcePath, $destPath);
        }

        $saved = imagejpeg($image, $destPath, 90);
        imagedestroy($image);

        return $saved;
    }

    private function reportCoverage(): void
    {
        $uniquePaths = ProductVariant::query()
            ->whereNotNull('image_path')
            ->distinct()
            ->pluck('image_path');

        $total = $uniquePaths->count();
        $ready = $uniquePaths->filter(fn (string $p): bool => is_file(public_path($p)))->count();

        $this->line("Benzersiz varyant görsel durumu: {$ready} / {$total} hazır.");
    }
}
