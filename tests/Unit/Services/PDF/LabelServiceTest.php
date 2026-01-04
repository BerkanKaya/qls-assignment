<?php

namespace Tests\Unit\Services\PDF;

use App\Exceptions\PDFGenerationException;
use App\Services\PDF\LabelService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LabelServiceTest extends TestCase
{
    public function test_fetch_decodes_base64_pdf_from_json_data(): void
    {
        $pdf = "%PDF-1.4\nhello";
        $base64 = base64_encode($pdf);

        Http::fake([
            'https://labels.test/*' => Http::response(['data' => $base64], 200),
        ]);

        $service = new LabelService('u', 'p', 10);

        $out = $service->fetch('https://labels.test/labels/pdf');

        $this->assertSame($pdf, $out);
    }

    public function test_fetch_throws_when_json_has_no_data_field(): void
    {
        Http::fake([
            'https://labels.test/*' => Http::response(['nope' => true], 200),
        ]);

        $service = new LabelService('u', 'p', 10);

        $this->expectException(PDFGenerationException::class);

        $service->fetch('https://labels.test/labels/pdf');
    }

    public function test_fetch_wraps_http_errors(): void
    {
        Http::fake([
            'https://labels.test/*' => Http::response([], 500),
        ]);

        $service = new LabelService('u', 'p', 10);

        $this->expectException(PDFGenerationException::class);

        $service->fetch('https://labels.test/labels/pdf');
    }
}
