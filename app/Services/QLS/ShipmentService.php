<?php

namespace App\Services\QLS;

use App\DTOs\Shipment\CreateShipmentDTO;
use App\DTOs\Shipment\ShipmentResponseDTO;

class ShipmentService
{
    public function __construct(
        private readonly QLSApiService $api,
        private readonly string $companyId
    ) {}

    public function create(CreateShipmentDTO $dto): ShipmentResponseDTO
    {
        $payload = $dto->toPayload();
        $data = $this->api->post("/v2/companies/{$this->companyId}/shipments", $payload);

        return ShipmentResponseDTO::fromArray($data);
    }
}
