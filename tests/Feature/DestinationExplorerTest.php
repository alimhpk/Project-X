<?php

namespace Tests\Feature;

use App\Livewire\DestinationExplorer;
use App\Models\Destination;
use Database\Seeders\DestinationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class DestinationExplorerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_database_destinations(): void
    {
        Destination::factory()->create(['name' => 'Lisbon', 'country' => 'Portugal']);

        Livewire::test(DestinationExplorer::class)
            ->assertStatus(200)
            ->assertSee('Lisbon')
            ->assertSee('Portugal');
    }

    public function test_it_no_longer_shows_hardcoded_destinations(): void
    {
        Livewire::test(DestinationExplorer::class)
            ->assertDontSee('Machu Picchu')
            ->assertDontSee('Swiss Alps');
    }

    public function test_it_searches_by_name(): void
    {
        Destination::factory()->create(['name' => 'Cusco', 'country' => 'Peru', 'region' => 'South America']);
        Destination::factory()->create(['name' => 'Kyoto', 'country' => 'Japan', 'region' => 'Asia']);

        Livewire::test(DestinationExplorer::class)
            ->set('searchTerm', 'cus')
            ->assertSee('Cusco')
            ->assertDontSee('Kyoto');
    }

    public function test_it_searches_by_country(): void
    {
        Destination::factory()->create(['name' => 'Cusco', 'country' => 'Peru', 'region' => 'South America']);
        Destination::factory()->create(['name' => 'Kyoto', 'country' => 'Japan', 'region' => 'Asia']);

        Livewire::test(DestinationExplorer::class)
            ->set('searchTerm', 'japan')
            ->assertSee('Kyoto')
            ->assertDontSee('Cusco');
    }

    public function test_it_searches_by_region(): void
    {
        Destination::factory()->create(['name' => 'Cusco', 'country' => 'Peru', 'region' => 'South America']);
        Destination::factory()->create(['name' => 'Kyoto', 'country' => 'Japan', 'region' => 'Asia']);

        Livewire::test(DestinationExplorer::class)
            ->set('searchTerm', 'south')
            ->assertSee('Cusco')
            ->assertDontSee('Kyoto');
    }

    public function test_it_filters_by_region(): void
    {
        Destination::factory()->create(['name' => 'Bali', 'region' => 'Asia']);
        Destination::factory()->create(['name' => 'Santorini', 'region' => 'Europe']);

        Livewire::test(DestinationExplorer::class)
            ->set('region', 'Asia')
            ->assertSee('Bali')
            ->assertDontSee('Santorini');
    }

    public function test_it_filters_by_cost_level(): void
    {
        Destination::factory()->create(['name' => 'Bali', 'cost_level' => 'Budget']);
        Destination::factory()->create(['name' => 'Santorini', 'cost_level' => 'Premium']);

        Livewire::test(DestinationExplorer::class)
            ->set('costLevel', 'Premium')
            ->assertSee('Santorini')
            ->assertDontSee('Bali');
    }

    public function test_it_sorts_by_an_allowed_field_in_both_directions(): void
    {
        Destination::factory()->create(['name' => 'Bali', 'average_daily_budget' => 400]);
        Destination::factory()->create(['name' => 'Kyoto', 'average_daily_budget' => 70]);
        Destination::factory()->create(['name' => 'Zurich', 'average_daily_budget' => 180]);

        Livewire::test(DestinationExplorer::class)
            ->assertSeeInOrder(['Bali', 'Kyoto', 'Zurich'])
            ->call('sort', 'averageDailyBudget')
            ->assertSet('sortField', 'averageDailyBudget')
            ->assertSet('sortDirection', 'asc')
            ->assertSeeInOrder(['Kyoto', 'Zurich', 'Bali'])
            ->call('sort', 'averageDailyBudget')
            ->assertSet('sortDirection', 'desc')
            ->assertSeeInOrder(['Bali', 'Zurich', 'Kyoto']);
    }

    public function test_an_invalid_sort_field_never_reaches_the_query(): void
    {
        Destination::factory()->create(['name' => 'Kyoto']);
        Destination::factory()->create(['name' => 'Bali']);

        DB::enableQueryLog();

        Livewire::test(DestinationExplorer::class)
            ->call('sort', 'password')
            ->assertSet('sortField', 'name')
            ->set('sortField', 'password')
            ->assertSeeInOrder(['Bali', 'Kyoto']);

        $sql = collect(DB::getQueryLog())->pluck('query')->implode(' ');

        $this->assertStringNotContainsString('password', $sql);
        $this->assertMatchesRegularExpression('/order by \W?name\W? asc/', $sql);
    }

    public function test_an_invalid_direction_falls_back_to_ascending(): void
    {
        Destination::factory()->create(['name' => 'Kyoto']);
        Destination::factory()->create(['name' => 'Bali']);

        DB::enableQueryLog();

        Livewire::test(DestinationExplorer::class)
            ->set('sortDirection', 'sideways')
            ->assertSeeInOrder(['Bali', 'Kyoto']);

        $sql = collect(DB::getQueryLog())->pluck('query')->implode(' ');

        $this->assertStringNotContainsString('sideways', $sql);
        $this->assertMatchesRegularExpression('/order by \W?name\W? asc/', $sql);
    }

    public function test_it_shows_ten_destinations_per_page(): void
    {
        $this->seed(DestinationSeeder::class);

        $destinations = Livewire::test(DestinationExplorer::class)->viewData('destinations');

        $this->assertSame(10, $destinations->count());
        $this->assertSame(15, $destinations->total());
        $this->assertSame(2, $destinations->lastPage());
    }

    public function test_page_two_shows_the_remaining_destinations(): void
    {
        $this->seed(DestinationSeeder::class);

        $destinations = Livewire::test(DestinationExplorer::class)
            ->call('gotoPage', 2)
            ->viewData('destinations');

        $this->assertSame(2, $destinations->currentPage());
        $this->assertSame(5, $destinations->count());
    }

    public function test_searching_resets_to_the_first_page(): void
    {
        $this->seed(DestinationSeeder::class);

        $component = Livewire::test(DestinationExplorer::class)->call('gotoPage', 2);

        $this->assertSame(2, $component->viewData('destinations')->currentPage());

        $component->set('searchTerm', 'a');

        $this->assertSame(1, $component->viewData('destinations')->currentPage());
    }

    public function test_filtering_resets_to_the_first_page(): void
    {
        $this->seed(DestinationSeeder::class);

        $component = Livewire::test(DestinationExplorer::class)->call('gotoPage', 2);
        $component->set('region', 'Europe');

        $this->assertSame(1, $component->viewData('destinations')->currentPage());

        $component = Livewire::test(DestinationExplorer::class)->call('gotoPage', 2);
        $component->set('costLevel', 'Budget');

        $this->assertSame(1, $component->viewData('destinations')->currentPage());
    }

    public function test_reset_filters_restores_the_default_state(): void
    {
        $this->seed(DestinationSeeder::class);

        $component = Livewire::test(DestinationExplorer::class)
            ->set('searchTerm', 'ba')
            ->set('region', 'Asia')
            ->set('costLevel', 'Budget')
            ->call('sort', 'annualVisitors')
            ->call('resetFilters')
            ->assertSet('searchTerm', '')
            ->assertSet('region', '')
            ->assertSet('costLevel', '')
            ->assertSet('sortField', 'name')
            ->assertSet('sortDirection', 'asc');

        $this->assertSame(15, $component->viewData('destinations')->total());
        $this->assertSame(1, $component->viewData('destinations')->currentPage());
    }

    public function test_it_renders_with_an_empty_database(): void
    {
        $component = Livewire::test(DestinationExplorer::class)
            ->assertStatus(200)
            ->assertSee('Project Expedition Destinations');

        $this->assertSame(0, $component->viewData('destinations')->total());
    }
}
