<?php

namespace App\Services\Document;

use App\DTOs\Order\OrderDTO;
use App\DTOs\Shipment\ShipmentResponseDTO;
use App\Services\PDF\LabelService;
use App\Services\PDF\PackingSlipService;
use App\Services\PDF\PDFMergerService;

class ShippingDocumentService
{
    public function __construct(
        private readonly PackingSlipService $packingSlipService,
        private readonly LabelService $labelService,
        private readonly PDFMergerService $pdfMergerService
    ) {
    }

    public function build(OrderDTO $order, ShipmentResponseDTO $shipment): string
    {
        $packingSlip = $this->packingSlipService->generate($order);
        $label = $this->labelService->fetch($shipment->labelPdfUrl);

        return $this->pdfMergerService->merge($packingSlip, $label);
    }
}
