<?php

namespace Tests\Unit\Services\QLS;

use App\DTOs\Product\ProductCombinationDTO;
use App\Services\QLS\ProductService;
use App\Services\QLS\QLSApiService;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    public function test_it_extracts_deduplicates_and_labels_combinations(): void
    {
        config(['cache.default' => 'array']);
        Cache::flush();

        $api = Mockery::mock(QLSApiService::class);
        $api->shouldReceive('get')->once()->andReturn([
            [
                'category' => 'shipment',
                'has_label' => true,
                'name' => 'DHL',
                'combinations' => [
                    ['id' => 3, 'name' => 'Pakje'],
                    ['id' => 3, 'name' => 'Pakje Duplicate'],
                    ['id' => 4, 'name' => 'Handtekening'],
                ],
            ],
            [
                'category' => 'return',
                'has_label' => true,
                'name' => 'Return',
                'combinations' => [
                    ['id' => 9, 'name' => 'Ignore'],
                ],
            ],
        ]);

        $service = new ProductService($api, 'company');

        $items = $service->listCombinations();

        $this->assertContainsOnlyInstancesOf(ProductCombinationDTO::class, $items);

        $ids = array_map(static fn(ProductCombinationDTO $dto) => $dto->id, $items);
        sort($ids);

        $this->assertSame([3, 4], $ids);

        $names = array_map(static fn(ProductCombinationDTO $dto) => $dto->name, $items);
        $this->assertTrue(in_array('DHL - Pakje', $names, true) || in_array('Pakje', $names, true));
    }
}
