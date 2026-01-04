<?php

namespace App\DTOs\Common;

final readonly class ContactDetailsDTO
{
    public function __construct(
        public ?string $companyName,
        public string $name,
        public string $street,
        public string $houseNumber,
        public ?string $addressLine2,
        public string $postalCode,
        public string $city,
        public string $country,
        public ?string $email,
        public ?string $phone
    ) {}

    public static function fromFormArray(array $data): self
    {
        return new self(
            $data['companyname'] ?? null,
            $data['name'],
            $data['street'],
            $data['housenumber'],
            $data['address2'] ?? null,
            $data['postalcode'],
            $data['city'],
            $data['country'],
            $data['email'] ?? null,
            $data['phone'] ?? null
        );
    }

    public function toQlsArray(bool $filterNull = true): array
    {
        $payload = [
            'companyname' => $this->companyName,
            'name' => $this->name,
            'street' => $this->street,
            'housenumber' => $this->houseNumber,
            'address2' => $this->addressLine2,
            'postalcode' => $this->postalCode,
            'locality' => $this->city,
            'country' => $this->country,
            'email' => $this->email,
            'phone' => $this->phone,
        ];

        return $filterNull
            ? array_filter($payload, static fn($value) => $value !== null)
            : $payload;
    }

    public function toFormArray(bool $filterNull = false): array
    {
        $payload = [
            'companyname' => $this->companyName,
            'name' => $this->name,
            'street' => $this->street,
            'housenumber' => $this->houseNumber,
            'address2' => $this->addressLine2,
            'postalcode' => $this->postalCode,
            'city' => $this->city,
            'country' => $this->country,
            'email' => $this->email,
            'phone' => $this->phone,
        ];

        return $filterNull
            ? array_filter($payload, static fn($value) => $value !== null)
            : $payload;
    }
}
