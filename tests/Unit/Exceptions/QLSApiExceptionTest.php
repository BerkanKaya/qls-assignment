<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\QLSApiException;
use PHPUnit\Framework\TestCase;

class QLSApiExceptionTest extends TestCase
{
    public function test_it_maps_nested_errors_to_form_fields(): void
    {
        $payload = [
            'errors' => [
                'sender_contact' => [
                    'name' => ['_required' => 'Verplicht'],
                ],
                'receiver_contact' => [
                    'locality' => ['_required' => 'Verplicht'],
                ],
            ],
        ];

        $e = QLSApiException::fromPayload($payload);

        $errors = $e->toFormErrors();

        $this->assertSame('Vul de verplichte velden in.', $errors['billing.name']);
        $this->assertSame('Vul de verplichte velden in.', $errors['delivery.city']);
    }

    public function test_it_falls_back_to_generic_api_message_when_no_field_errors(): void
    {
        $e = new QLSApiException('Nope', payload: [], code: 401);

        $this->assertSame(['api' => 'QLS authentication failed.'], $e->toFormErrors());
    }
}
