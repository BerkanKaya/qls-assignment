<?php

namespace App\DTOs\Shipment;

use App\DTOs\Common\ContactDetailsDTO;

final readonly class CreateShipmentDTO
{
    public function __construct(
        public int $productCombinationId,
        public string $brandId,
        public string $reference,
        public int $weight,
        public ContactDetailsDTO $senderContact,
        public ContactDetailsDTO $receiverContact,
        public ?ContactDetailsDTO $returnContact,
        public array $products,
        public bool $zplDirect = false
    ) {}

    public function toPayload(): array
    {
        return [
            'product_combination_id' => $this->productCombinationId,
            'brand_id' => $this->brandId,
            'reference' => $this->reference,
            'weight' => $this->weight,
            'return_contact' => $this->returnContact?->toQlsArray(),
            'sender_contact' => $this->senderContact->toQlsArray(),
            'receiver_contact' => $this->receiverContact->toQlsArray(),
            'shipment_products' => array_map(
                static fn (ShipmentProductDTO $p) => $p->toPayload(),
                $this->products
            ),
            'zpl_direct' => $this->zplDirect,
        ];
    }
}
