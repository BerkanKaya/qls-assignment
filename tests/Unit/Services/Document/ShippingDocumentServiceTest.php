<?php

namespace Tests\Unit\Services\Document;

use App\DTOs\Common\ContactDetailsDTO;
use App\DTOs\Order\OrderDTO;
use App\DTOs\Order\OrderLineDTO;
use App\DTOs\Shipment\ShipmentResponseDTO;
use App\Services\Document\ShippingDocumentService;
use App\Services\PDF\LabelService;
use App\Services\PDF\PackingSlipService;
use App\Services\PDF\PDFMergerService;
use Mockery;
use PHPUnit\Framework\TestCase;

class ShippingDocumentServiceTest extends TestCase
{
    public function test_it_builds_document_by_generating_fetching_and_merging(): void
    {
        $packing = Mockery::mock(PackingSlipService::class);
        $label = Mockery::mock(LabelService::class);
        $merger = Mockery::mock(PDFMergerService::class);

        $packing->shouldReceive('generate')->once()->andReturn('PACK');
        $label->shouldReceive('fetch')->once()->with('https://labels.test/pdf')->andReturn('LABEL');
        $merger->shouldReceive('merge')->once()->with('PACK', 'LABEL')->andReturn('MERGED');

        $service = new ShippingDocumentService($packing, $label, $merger);

        $contact = new ContactDetailsDTO(null, 'John', 'Main', '10', null, '1234AB', 'Utrecht', 'NL', null, null);
        $order = new OrderDTO('998201', $contact, $contact, [new OrderLineDTO('sku', 'name', 1, null)]);
        $shipment = new ShipmentResponseDTO('id', 'https://labels.test/pdf', null, null);

        $this->assertSame('MERGED', $service->build($order, $shipment));
    }
}
