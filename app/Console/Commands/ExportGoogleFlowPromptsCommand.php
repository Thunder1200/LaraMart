<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportGoogleFlowPromptsCommand extends Command
{
    protected $signature = 'laramart:export-flow-prompts {--batch-size=24 : Her paketteki görsel sayısı}';

    protected $description = 'Tüm benzersiz ürün ve renk varyantları için Google Flow Agent Modu 24er görsellik prompt paketleri üretir.';

    public function handle(): int
    {
        $batchSize = max(1, (int) $this->option('batch-size'));
        $outputDir = base_path('docs/google-flow-prompts');
        File::ensureDirectoryExists($outputDir);
        File::delete(File::glob($outputDir.'/paket-*.txt'));

        $products = Product::query()
            ->with(['category', 'brand', 'variants'])
            ->orderBy('id')
            ->get();

        $manifest = [];
        $seenPaths = [];

        foreach ($products as $product) {
            $specs = collect($product->formattedAttributes())
                ->map(fn (string $val, string $key): string => "{$key}: {$val}")
                ->implode(', ');

            foreach ($product->variants as $variant) {
                $imagePath = (string) $variant->image_path;
                if ($imagePath === '' || isset($seenPaths[$imagePath])) {
                    continue;
                }

                $seenPaths[$imagePath] = true;
                $fileName = basename($imagePath);
                $color = (string) ($variant->color ?? 'Standart');

                $prompt = sprintf(
                    'Studio e-commerce product photograph (1:1 square, clean seamless soft off-white background, crisp natural studio lighting, no text, no logos, no watermarks). Product: "%s" (%s category, distinct unique design #%d). Exact color variant: "%s". Visual/material details: %s. High-resolution commercial catalog photography.',
                    $product->name,
                    $product->category->name,
                    $product->id,
                    $color,
                    $specs !== '' ? $specs : 'modern premium finish'
                );

                $manifest[] = [
                    'index' => count($manifest) + 1,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'category' => $product->category->name,
                    'color' => $color,
                    'file_name' => $fileName,
                    'image_path' => $imagePath,
                    'prompt' => $prompt,
                ];
            }
        }

        File::put(
            $outputDir.'/manifest.json',
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $chunks = array_chunk($manifest, $batchSize);

        foreach ($chunks as $chunkIndex => $items) {
            $batchNumber = $chunkIndex + 1;
            $firstIdx = str_pad((string) $items[0]['index'], 3, '0', STR_PAD_LEFT);
            $lastIdx = str_pad((string) $items[count($items) - 1]['index'], 3, '0', STR_PAD_LEFT);
            $count = count($items);

            $lines = [
                "GOOGLE FLOW AGENT MODU — PAKET {$batchNumber} ({$count} BENZERSİZ ÜRÜN / RENK GÖRSELİ: #{$firstIdx}–#{$lastIdx})",
                str_repeat('=', 90),
                '',
                'Aşağıdaki listedeki her bir madde için 1:1 kare, temiz açık gri/beyaz stüdyo fonlu, metinsiz ve filigransız',
                'gerçekçi bir e-ticaret ürün fotoğrafı üret. Her ürün kendi tasarımına ve belirtilen tam renk varyantına uygun, benzersiz olsun:',
                '',
            ];

            foreach ($items as $item) {
                $num = str_pad((string) $item['index'], 3, '0', STR_PAD_LEFT);
                $lines[] = "[{$num}] DOSYA: {$item['file_name']} | ÜRÜN: {$item['product_name']} | RENK: {$item['color']}";
                $lines[] = "PROMPT: {$item['prompt']}";
                $lines[] = '';
            }

            $filePath = sprintf('%s/paket-%d-%s-%s.txt', $outputDir, $batchNumber, $firstIdx, $lastIdx);
            File::put($filePath, implode(PHP_EOL, $lines));
        }

        $this->info(sprintf(
            'Toplam %d benzersiz ürün-renk görseli için %d paket oluşturuldu -> %s',
            count($manifest),
            count($chunks),
            $outputDir
        ));

        return self::SUCCESS;
    }
}
