<?php

namespace App\Support;

final class ShippingLabelFormDefaults
{
    public function defaults(): array
    {
        return [
            'order_number' => '998201',
            'weight' => 1200,
            'product_combination_id' => config('qls.product_combination_id'),
            'billing' => $this->address(),
            'delivery' => $this->address(),
            'lines' => $this->lines(),
        ];
    }

    private function address(): array
    {
        return [
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
        ];
    }

    private function lines(): array
    {
        return [
            ['name' => 'Jeans - Black 36', 'sku' => '62905', 'quantity' => 2, 'ean' => '8710525229528'],
            ['name' => '590ml Red Orange', 'sku' => '25920', 'quantity' => 1, 'ean' => '3509943009097'],
        ];
    }
}
