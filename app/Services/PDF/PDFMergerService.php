<?php

namespace App\Services\PDF;

use App\Exceptions\PDFGenerationException;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\PdfParserException;

class PDFMergerService
{
    private const A4_WIDTH = 210;
    private const A4_HEIGHT = 297;
    private const MARGIN = 10;
    private const GAP = 5;

    public function merge(string $packingSlipPdf, string $labelPdf): string
    {
        $packingPath = $this->writeTempFile($packingSlipPdf);
        $labelPath = $this->writeTempFile($labelPdf);

        try {
            return $this->mergePdfs($packingPath, $labelPath);
        } catch (PdfParserException $e) {
            throw new PDFGenerationException('Failed to merge PDFs: ' . $e->getMessage(), 0, $e);
        } finally {
            if (file_exists($packingPath)) unlink($packingPath);
            if (file_exists($labelPath)) unlink($labelPath);
        }
    }

    private function mergePdfs(string $packingPath, string $labelPath): string
    {
        $pdf = new Fpdi();
        $pdf->AddPage('P', 'A4');

        $packing = $this->importTemplate($pdf, $packingPath);
        $label = $this->importTemplate($pdf, $labelPath);

        $this->scaleToFit($packing, $label);
        $this->placeTemplates($pdf, $packing, $label);

        return $pdf->Output('S');
    }

    private function importTemplate(Fpdi $pdf, string $path): array
    {
        $pdf->setSourceFile($path);
        $templateId = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($templateId);

        $availableWidth = self::A4_WIDTH - (self::MARGIN * 2);
        $scale = $availableWidth / $size['width'];

        return [
            'id' => $templateId,
            'scale' => $scale,
            'height' => $size['height'] * $scale,
            'width' => $size['width'],
        ];
    }

    private function scaleToFit(array &$packing, array &$label): void
    {
        $availableHeight = self::A4_HEIGHT - (self::MARGIN * 2);
        $totalHeight = $packing['height'] + $label['height'] + self::GAP;

        if ($totalHeight > $availableHeight) {
            $scaleFactor = $availableHeight / $totalHeight;
            $packing['scale'] *= $scaleFactor;
            $packing['height'] *= $scaleFactor;
            $label['scale'] *= $scaleFactor;
            $label['height'] *= $scaleFactor;
        }
    }

    private function placeTemplates(Fpdi $pdf, array $packing, array $label): void
    {
        $pdf->useTemplate(
            $packing['id'],
            self::MARGIN,
            self::MARGIN,
            $packing['width'] * $packing['scale'],
            $packing['height']
        );

        $pdf->useTemplate(
            $label['id'],
            self::MARGIN,
            self::MARGIN + $packing['height'] + self::GAP,
            $label['width'] * $label['scale'],
            $label['height']
        );
    }

    private function writeTempFile(string $content): string
    {
        $path = tempnam(sys_get_temp_dir(), 'qls_pdf_');
        file_put_contents($path, $content);

        return $path;
    }
}
