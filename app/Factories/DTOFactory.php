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
        return $this->groupByProduct($this->mapToOrderLines($lines));
    }

    private function mapToOrderLines(array $lines): array
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

    private function groupByProduct(array $dtos): array
    {
        $grouped = [];

        foreach ($dtos as $dto) {
            $key = $this->productKey($dto);

            if (!isset($grouped[$key])) {
                $grouped[$key] = $dto;
                continue;
            }

            $grouped[$key] = $this->mergeQuantities($grouped[$key], $dto);
        }

        return array_values($grouped);
    }
    
    private function productKey(OrderLineDTO $dto): string
    {
        return $dto->sku . '|' . ($dto->ean ?? '') . '|' . $dto->name;
    }

    private function mergeQuantities(OrderLineDTO $existing, OrderLineDTO $incoming): OrderLineDTO
    {
        return new OrderLineDTO(
            $existing->sku,
            $existing->name,
            $existing->amountOrdered + $incoming->amountOrdered,
            $existing->ean
        );
    }
}
