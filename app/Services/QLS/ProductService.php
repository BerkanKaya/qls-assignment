<?php

namespace App\Services\QLS;

use App\DTOs\Product\ProductCombinationDTO;
use Illuminate\Support\Facades\Cache;

class ProductService
{
    public function __construct(
        private readonly QLSApiService $api,
        private readonly string $companyId
    ) {}

    public function listCombinations(): array
    {
        $rows = Cache::remember(
            $this->cacheKey(),
            now()->addMinutes(10),
            fn () => $this->fetchCombinationRows()
        );

        return array_values(array_map(
            static fn (array $row) => new ProductCombinationDTO((int) $row['id'], (string) $row['name']),
            $rows
        ));
    }

    private function cacheKey(): string
    {
        return "qls.product_combinations.{$this->companyId}";
    }

    private function fetchCombinationRows(): array
    {
        $payload = $this->api->get("/companies/{$this->companyId}/products");
        $products = $this->items($payload);

        $seen = [];
        $rows = [];

        foreach ($products as $product) {
            if (!$this->isShipmentProduct($product)) {
                continue;
            }

            $productName = is_string($product['name'] ?? null) ? $product['name'] : null;
            $combinations = $this->extractCombinations($product);

            foreach ($combinations as $combo) {
                $dto = $this->buildCombinationDto($combo, $productName);
                if ($dto === null) {
                    continue;
                }

                $key = (string) $dto->id;
                if (isset($seen[$key])) {
                    continue;
                }

                $seen[$key] = true;

                $rows[] = [
                    'id' => $dto->id,
                    'name' => $dto->name,
                ];
            }
        }

        return $rows;
    }

    private function isShipmentProduct(mixed $product): bool
    {
        if (!is_array($product)) {
            return false;
        }

        return ($product['category'] ?? null) === 'shipment'
            && ($product['has_label'] ?? false) === true;
    }

    private function extractCombinations(array $product): array
    {
        $combinations = $product['combinations'] ?? [];

        return is_array($combinations) ? $combinations : [];
    }

    private function buildCombinationDto(array $combo, ?string $productName): ?ProductCombinationDTO
    {
        $id = $combo['id'] ?? null;
        $name = $combo['name'] ?? null;

        if (!is_scalar($id) || !is_string($name) || $name === '') {
            return null;
        }

        $label = $this->buildLabel($name, $productName);

        return new ProductCombinationDTO((int) $id, $label);
    }

    private function buildLabel(string $comboName, ?string $productName): string
    {
        if (!$productName || stripos($comboName, $productName) !== false) {
            return $comboName;
        }

        return "{$productName} - {$comboName}";
    }

    private function items(mixed $payload): array
    {
        if (!is_array($payload)) {
            return [];
        }

        $data = $payload['data'] ?? null;

        return is_array($data) ? $data : $payload;
    }
}
