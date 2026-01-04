<?php

namespace App\Factories;

use App\DTOs\Common\ContactDetailsDTO;
use App\DTOs\Order\OrderDTO;
use App\DTOs\Order\OrderLineDTO;

final class DTOFactory
{
    public function makeOrder(array $payload): OrderDTO
    {
        return new OrderDTO(
            $payload['order_number'],
            ContactDetailsDTO::fromFormArray($payload['billing']),
            ContactDetailsDTO::fromFormArray($payload['delivery']),
            $this->lines($payload['lines'])
        );
    }

    private function lines(array $lines): array
    {
        return array_map(
            fn(array $line) => new OrderLineDTO(
                $line['sku'],
                $line['name'],
                (int) $line['quantity'],
                $line['ean'] ?? null
            ),
            $lines
        );
    }
}
