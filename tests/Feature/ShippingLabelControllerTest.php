<?php

namespace Tests\Feature;

use App\Exceptions\PDFGenerationException;
use App\Exceptions\QLSApiException;
use App\Services\Document\ShippingDocumentService;
use App\Services\QLS\ShipmentService;
use App\DTOs\Shipment\ShipmentResponseDTO;
use Mockery;
use Tests\TestCase;

class ShippingLabelControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['qls.brand_id' => 'brand']);
    }

    public function test_it_streams_a_pdf_download_on_success(): void
    {
        $shipmentService = Mockery::mock(ShipmentService::class);
        $shipmentService->shouldReceive('create')->once()->andReturn(
            new ShipmentResponseDTO('id', 'https://labels.test/pdf', null, null)
        );
        $this->instance(ShipmentService::class, $shipmentService);

        $docService = Mockery::mock(ShippingDocumentService::class);
        $docService->shouldReceive('build')->once()->andReturn("%PDF-1.4\nmerged");
        $this->instance(ShippingDocumentService::class, $docService);

        $response = $this->post('/shipping', $this->validPayload());

        $response->assertOk();

        $disposition = (string) $response->headers->get('content-disposition');
        $this->assertStringContainsString('attachment', $disposition);
        $this->assertStringContainsString('shipment-998201.pdf', $disposition);

        $content = $response->streamedContent();
        $this->assertStringStartsWith('%PDF-', $content);
    }

    public function test_it_redirects_back_with_validation_errors(): void
    {
        $payload = $this->validPayload();
        unset($payload['order_number']);

        $response = $this->from('/shipping')->post('/shipping', $payload);

        $response->assertRedirect('/shipping');
        $response->assertSessionHasErrors(['order_number']);
    }

    public function test_it_maps_qls_validation_errors_back_to_form_fields(): void
    {
        $shipmentService = Mockery::mock(ShipmentService::class);
        $shipmentService->shouldReceive('create')->once()->andThrow(
            QLSApiException::fromPayload([
                'errors' => [
                    'sender_contact' => [
                        'name' => ['_required' => 'Verplicht'],
                    ],
                    'receiver_contact' => [
                        'locality' => ['_required' => 'Verplicht'],
                    ],
                ],
            ])
        );
        $this->instance(ShipmentService::class, $shipmentService);

        $response = $this->from('/shipping')->post('/shipping', $this->validPayload());

        $response->assertRedirect('/shipping');
        $response->assertSessionHasErrors(['billing.name', 'delivery.city']);
    }

    public function test_it_redirects_back_with_pdf_error(): void
    {
        $shipmentService = Mockery::mock(ShipmentService::class);
        $shipmentService->shouldReceive('create')->once()->andReturn(
            new ShipmentResponseDTO('id', 'https://labels.test/pdf', null, null)
        );
        $this->instance(ShipmentService::class, $shipmentService);

        $docService = Mockery::mock(ShippingDocumentService::class);
        $docService->shouldReceive('build')->once()->andThrow(new PDFGenerationException('merge failed'));
        $this->instance(ShippingDocumentService::class, $docService);

        $response = $this->from('/shipping')->post('/shipping', $this->validPayload());

        $response->assertRedirect('/shipping');
        $response->assertSessionHasErrors(['pdf']);
    }

    private function validPayload(): array
    {
        return [
            'order_number' => '998201',
            'weight' => 1200,
            'product_combination_id' => 3,
            'billing' => [
                'companyname' => null,
                'name' => 'John Doe',
                'street' => 'Daltonstraat',
                'housenumber' => '36',
                'address2' => '1e verdieping',
                'postalcode' => '3316GD',
                'city' => 'Dordrecht',
                'country' => 'NL',
                'email' => 'email@example.com',
                'phone' => '0011234567',
            ],
            'delivery' => [
                'companyname' => null,
                'name' => 'John Doe',
                'street' => 'Daltonstraat',
                'housenumber' => '36',
                'address2' => '1e verdieping',
                'postalcode' => '3316GD',
                'city' => 'Dordrecht',
                'country' => 'NL',
                'email' => 'email@example.com',
                'phone' => '0011234567',
            ],
            'lines' => [
                ['name' => 'Jeans - Black 36', 'sku' => '62905', 'quantity' => 2, 'ean' => '8710525229528'],
                ['name' => '590ml Red Orange', 'sku' => '25920', 'quantity' => 1, 'ean' => '3509943009097'],
            ],
        ];
    }
}
