<?php

namespace App\Http\Controllers;

use App\Exceptions\PDFGenerationException;
use App\Exceptions\QLSApiException;
use App\Factories\DTOFactory;
use App\Factories\ShipmentRequestFactory;
use App\Http\Requests\CreateShippingLabelRequest;
use App\Services\Document\ShippingDocumentService;
use App\Services\QLS\ProductService;
use App\Services\QLS\ShipmentService;
use App\Support\ShippingLabelFormDefaults;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ShippingLabelController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly ShipmentService $shipmentService,
        private readonly DTOFactory $dtoFactory,
        private readonly ShipmentRequestFactory $shipmentRequestFactory,
        private readonly ShippingDocumentService $shippingDocumentService,
        private readonly ShippingLabelFormDefaults $formDefaults
    ) {}

    public function create(): View
    {
        return view('shipping.create', [
            'products' => $this->productService->listCombinations(),
            'defaults' => $this->formDefaults->defaults(),
        ]);
    }

    public function store(CreateShippingLabelRequest $request): StreamedResponse|RedirectResponse
    {
        try {
            $data = $request->validated();
            $order = $this->dtoFactory->makeOrder($data);
            $shipmentDto = $this->shipmentRequestFactory->build(
                $order,
                (int) $data['product_combination_id'],
                config('qls.brand_id'),
                (int) $data['weight']
            );
            $shipment = $this->shipmentService->create($shipmentDto);
            $document = $this->shippingDocumentService->build($order, $shipment);

            return response()->streamDownload(function () use ($document) {
                echo $document;
            }, "shipment-{$order->number}.pdf");
        } catch (QLSApiException $e) {
            report($e);

            return back()
                ->withInput()
                ->withErrors($e->toFormErrors());
        } catch (PDFGenerationException $e) {
            return back()
                ->withInput()
                ->withErrors(['pdf' => 'PDF generation failed: ' . $e->getMessage()]);
        }
    }
}
