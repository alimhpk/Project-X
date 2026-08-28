<?php

namespace Tests\Feature;

use App\Models\Destination;
use Database\Seeders\DestinationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DestinationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_database_destinations_with_pagination_metadata(): void
    {
        $this->seed(DestinationSeeder::class);

        $this->getJson('/api/destinations')
            ->assertOk()
            ->assertJsonCount(15, 'data')
            ->assertJsonPath('meta.total', 15)
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.last_page', 1)
            ->assertJsonStructure([
                'data' => [['id', 'name', 'country', 'region', 'costLevel', 'activities', 'averageDailyBudget', 'annualVisitors']],
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'from', 'last_page', 'per_page', 'to', 'total'],
            ]);
    }

    public function test_it_keeps_the_public_field_names_and_types(): void
    {
        $destination = Destination::factory()->create([
            'name' => 'Kyoto',
            'country' => 'Japan',
            'region' => 'Asia',
            'cost_level' => 'Moderate',
            'activities' => ['Cultural Tours', 'Photography'],
            'average_daily_budget' => 180,
            'annual_visitors' => 5300000,
        ]);

        $this->getJson('/api/destinations')
            ->assertOk()
            ->assertJsonPath('data.0', [
                'id' => $destination->id,
                'name' => 'Kyoto',
                'country' => 'Japan',
                'region' => 'Asia',
                'costLevel' => 'Moderate',
                'activities' => ['Cultural Tours', 'Photography'],
                'averageDailyBudget' => 180,
                'annualVisitors' => 5300000,
            ]);
    }

    public function test_it_paginates_with_the_requested_page_size(): void
    {
        Destination::factory()->count(12)->create();

        $this->getJson('/api/destinations?perPage=5&page=3')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.current_page', 3)
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonPath('meta.last_page', 3)
            ->assertJsonPath('meta.total', 12);
    }

    public function test_it_searches_by_name(): void
    {
        Destination::factory()->create(['name' => 'Cusco', 'country' => 'Peru', 'region' => 'South America']);
        Destination::factory()->create(['name' => 'Kyoto', 'country' => 'Japan', 'region' => 'Asia']);

        $this->getJson('/api/destinations?search=cus')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Cusco');
    }

    public function test_it_searches_by_country(): void
    {
        Destination::factory()->create(['name' => 'Cusco', 'country' => 'Peru', 'region' => 'South America']);
        Destination::factory()->create(['name' => 'Kyoto', 'country' => 'Japan', 'region' => 'Asia']);

        $this->getJson('/api/destinations?search=japan')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Kyoto');
    }

    public function test_it_searches_by_region(): void
    {
        Destination::factory()->create(['name' => 'Cusco', 'country' => 'Peru', 'region' => 'South America']);
        Destination::factory()->create(['name' => 'Kyoto', 'country' => 'Japan', 'region' => 'Asia']);

        $this->getJson('/api/destinations?search=south')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Cusco');
    }

    public function test_it_filters_by_region(): void
    {
        Destination::factory()->create(['name' => 'Bali', 'region' => 'Asia']);
        Destination::factory()->create(['name' => 'Phuket', 'region' => 'Asia']);
        Destination::factory()->create(['name' => 'Santorini', 'region' => 'Europe']);

        $this->getJson('/api/destinations?region=Asia')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.*.name', ['Bali', 'Phuket']);
    }

    public function test_it_filters_by_cost_level(): void
    {
        Destination::factory()->create(['name' => 'Bali', 'cost_level' => 'Budget']);
        Destination::factory()->create(['name' => 'Santorini', 'cost_level' => 'Premium']);
        Destination::factory()->create(['name' => 'Phuket', 'cost_level' => 'Premium']);

        $this->getJson('/api/destinations?costLevel=Premium')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.*.name', ['Phuket', 'Santorini']);
    }

    public function test_it_sorts_by_name_ascending_by_default(): void
    {
        Destination::factory()->create(['name' => 'Kyoto']);
        Destination::factory()->create(['name' => 'Bali']);
        Destination::factory()->create(['name' => 'Zurich']);

        $this->getJson('/api/destinations')
            ->assertOk()
            ->assertJsonPath('data.*.name', ['Bali', 'Kyoto', 'Zurich']);
    }

    #[DataProvider('sorts')]
    public function test_it_sorts_by_every_supported_key_in_both_directions(string $sort, array $ascending): void
    {
        Destination::factory()->create([
            'name' => 'Cusco',
            'country' => 'Peru',
            'region' => 'South America',
            'cost_level' => 'Moderate',
            'average_daily_budget' => 60,
            'annual_visitors' => 2700000,
        ]);
        Destination::factory()->create([
            'name' => 'Kyoto',
            'country' => 'Japan',
            'region' => 'Asia',
            'cost_level' => 'Premium',
            'average_daily_budget' => 180,
            'annual_visitors' => 5300000,
        ]);
        Destination::factory()->create([
            'name' => 'Zurich',
            'country' => 'Switzerland',
            'region' => 'Europe',
            'cost_level' => 'Luxury',
            'average_daily_budget' => 400,
            'annual_visitors' => 1200000,
        ]);

        $this->getJson("/api/destinations?sort={$sort}&direction=asc")
            ->assertOk()
            ->assertJsonPath('data.*.name', $ascending);

        $this->getJson("/api/destinations?sort={$sort}&direction=desc")
            ->assertOk()
            ->assertJsonPath('data.*.name', array_reverse($ascending));
    }

    public static function sorts(): array
    {
        return [
            'name' => ['name', ['Cusco', 'Kyoto', 'Zurich']],
            'country' => ['country', ['Kyoto', 'Cusco', 'Zurich']],
            'region' => ['region', ['Kyoto', 'Zurich', 'Cusco']],
            'costLevel' => ['costLevel', ['Zurich', 'Cusco', 'Kyoto']],
            'averageDailyBudget' => ['averageDailyBudget', ['Cusco', 'Kyoto', 'Zurich']],
            'annualVisitors' => ['annualVisitors', ['Zurich', 'Cusco', 'Kyoto']],
        ];
    }

    public function test_it_keeps_a_stable_order_across_pages_when_sort_values_are_equal(): void
    {
        Destination::factory()->count(12)->create(['region' => 'Asia']);

        $ids = collect([1, 2, 3])->flatMap(
            fn (int $page) => $this->getJson("/api/destinations?sort=region&perPage=5&page={$page}")->json('data.*.id')
        );

        $this->assertSame(Destination::orderBy('id')->pluck('id')->all(), $ids->all());
    }

    public function test_pagination_links_keep_only_validated_parameters(): void
    {
        Destination::factory()->count(6)->create(['region' => 'Asia']);

        $next = $this->getJson('/api/destinations?region=Asia&perPage=5&sort=name&direction=asc&bogus=1')
            ->assertOk()
            ->json('links.next');

        $this->assertStringContainsString('region=Asia', $next);
        $this->assertStringContainsString('perPage=5', $next);
        $this->assertStringContainsString('sort=name', $next);
        $this->assertStringContainsString('direction=asc', $next);
        $this->assertStringContainsString('page=2', $next);
        $this->assertStringNotContainsString('bogus', $next);
    }

    #[DataProvider('invalidQueries')]
    public function test_it_rejects_invalid_query_values(string $query, string $field): void
    {
        $this->getJson("/api/destinations?{$query}")
            ->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors'])
            ->assertJsonValidationErrors($field);
    }

    public static function invalidQueries(): array
    {
        return [
            'unknown sort' => ['sort=password', 'sort'],
            'unknown direction' => ['direction=sideways', 'direction'],
            'unknown cost level' => ['costLevel=Free', 'costLevel'],
            'page size below one' => ['perPage=0', 'perPage'],
            'page size above fifty' => ['perPage=51', 'perPage'],
            'page size not numeric' => ['perPage=ten', 'perPage'],
            'page below one' => ['page=0', 'page'],
            'search too long' => ['search='.str_repeat('a', 101), 'search'],
        ];
    }

    public function test_it_returns_an_empty_collection_when_there_are_no_destinations(): void
    {
        $this->getJson('/api/destinations')
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.total', 0);
    }

    public function test_the_seed_endpoint_no_longer_exists(): void
    {
        $this->postJson('/api/seed')->assertNotFound();
    }

    public function test_api_errors_do_not_expose_exception_details_when_debug_is_off(): void
    {
        config()->set('app.debug', false);

        $this->postJson('/api/seed')
            ->assertNotFound()
            ->assertExactJson(['message' => 'The route api/seed could not be found.']);
    }

    public function test_it_returns_429_once_the_rate_limit_is_exceeded(): void
    {
        for ($request = 1; $request <= 60; $request++) {
            $this->getJson('/api/destinations')->assertOk();
        }

        $this->getJson('/api/destinations')
            ->assertStatus(429)
            ->assertHeader('Retry-After');
    }
}
