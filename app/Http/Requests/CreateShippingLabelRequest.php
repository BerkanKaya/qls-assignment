<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateShippingLabelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            ...$this->orderRules(),
            ...$this->addressRules('billing'),
            ...$this->addressRules('delivery'),
            ...$this->lineRules(),
        ];
    }

    private function orderRules(): array
    {
        return [
            'order_number' => ['required', 'string', 'max:50'],
            'weight' => ['required', 'integer', 'min:1'],
            'product_combination_id' => ['required', 'integer'],
        ];
    }

    private function addressRules(string $key): array
    {
        $prefix = "{$key}.";

        return [
            "{$prefix}companyname" => ['nullable', 'string', 'max:100'],
            "{$prefix}name" => ['required', 'string', 'max:100'],
            "{$prefix}street" => ['required', 'string', 'max:120'],
            "{$prefix}housenumber" => ['required', 'string', 'max:20'],
            "{$prefix}address2" => ['nullable', 'string', 'max:120'],
            "{$prefix}postalcode" => ['required', 'string', 'max:20'],
            "{$prefix}city" => ['required', 'string', 'max:80'],
            "{$prefix}country" => ['required', 'string', 'size:2', 'alpha:2', 'uppercase'],
            "{$prefix}email" => ['nullable', 'email'],
            "{$prefix}phone" => ['nullable', 'string', 'max:20'],
        ];
    }

    private function lineRules(): array
    {
        return [
            'lines' => ['required', 'array', 'min:1'],
            'lines.*' => ['required', 'array'],
            'lines.*.name' => ['required', 'string', 'max:120'],
            'lines.*.sku' => ['required', 'string', 'max:60'],
            'lines.*.quantity' => ['required', 'integer', 'min:1'],
            'lines.*.ean' => ['nullable', 'string', 'max:32'],
        ];
    }
}
