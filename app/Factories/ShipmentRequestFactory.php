<?php

namespace App\Factories;

use App\DTOs\Order\OrderDTO;
use App\DTOs\Shipment\CreateShipmentDTO;
use App\DTOs\Shipment\ShipmentProductDTO;

class ShipmentRequestFactory
{
    public function build(
        OrderDTO $order,
        int $productCombinationId,
        string $brandId,
        int $weight
    ): CreateShipmentDTO {
        return new CreateShipmentDTO(
            $productCombinationId,
            $brandId,
            $order->number,
            $weight,
            $order->billingAddress,
            $order->deliveryAddress,
            $order->billingAddress,
            $this->products($order)
        );
    }

    private function products(OrderDTO $order): array
    {
        return array_map(
            fn($line) => new ShipmentProductDTO(
                productId: null,
                amount: $line->amountOrdered,
                ean: $line->ean,
                name: $line->name,
                sku: $line->sku
            ),
            $order->orderLines
        );
    }
}
