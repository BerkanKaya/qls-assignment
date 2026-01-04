<?php

namespace App\Services\PDF;

use App\DTOs\Order\OrderDTO;
use App\Exceptions\PDFGenerationException;
use Dompdf\Dompdf;
use Dompdf\Options;
use Throwable;

class PackingSlipService
{
    public function generate(OrderDTO $order): string
    {
        try {
            $dompdf = $this->createPdf();
            $dompdf->loadHtml(view('pdf.packing-slip', ['order' => $order])->render());
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return $dompdf->output();
        } catch (Throwable $exception) {
            throw new PDFGenerationException('Failed to generate packing slip', 0, $exception);
        }
    }

    private function createPdf(): Dompdf
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);

        return new Dompdf($options);
    }
}
