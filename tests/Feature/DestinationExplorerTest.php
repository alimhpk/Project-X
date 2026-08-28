<?php

namespace Tests\Feature;

use App\Livewire\DestinationExplorer;
use App\Models\Destination;
use Database\Seeders\DestinationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
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

    public function test_the_development_search_echo_is_gone(): void
    {
        Destination::factory()->create(['name' => 'Lisbon']);

        Livewire::test(DestinationExplorer::class)
            ->set('searchTerm', 'lis')
            ->assertSee('Lisbon')
            ->assertDontSee('Searching for');
    }

    public function test_form_controls_are_labelled(): void
    {
        Livewire::test(DestinationExplorer::class)
            ->assertSeeHtml('for="search-term"')
            ->assertSeeHtml('id="search-term"')
            ->assertSeeHtml('for="region"')
            ->assertSeeHtml('id="region"')
            ->assertSeeHtml('for="cost-level"')
            ->assertSeeHtml('id="cost-level"')
            ->assertSeeHtml('for="sort-field"')
            ->assertSeeHtml('id="sort-field"');
    }

    public function test_an_empty_database_shows_an_empty_message(): void
    {
        Livewire::test(DestinationExplorer::class)
            ->assertSee('No destinations have been added yet.')
            ->assertDontSee('No destinations match your search.');
    }

    public function test_no_matching_destinations_shows_a_message_and_a_reset_option(): void
    {
        $this->seed(DestinationSeeder::class);

        $component = Livewire::test(DestinationExplorer::class)
            ->set('searchTerm', 'nowhere')
            ->assertSee('No destinations match your search.')
            ->assertDontSee('No destinations have been added yet.')
            ->assertSeeHtml('wire:click="resetFilters"')
            ->call('resetFilters')
            ->assertDontSee('No destinations match your search.');

        $this->assertSame(15, $component->viewData('destinations')->total());
    }

    public function test_it_formats_budget_and_visitors(): void
    {
        Destination::factory()->create(['name' => 'Kyoto', 'average_daily_budget' => 1250, 'annual_visitors' => 2700000]);

        Livewire::test(DestinationExplorer::class)
            ->assertSee('$1,250/day')
            ->assertSee('2,700,000')
            ->assertDontSee('2700000');
    }

    public function test_changing_the_sort_controls_resets_the_page_and_stays_safe(): void
    {
        $this->seed(DestinationSeeder::class);

        $component = Livewire::test(DestinationExplorer::class)->call('gotoPage', 2);

        $this->assertSame(2, $component->viewData('destinations')->currentPage());

        $component->set('sortField', 'annualVisitors');

        $this->assertSame(1, $component->viewData('destinations')->currentPage());
        $this->assertSame('annualVisitors', $component->viewData('activeSort'));

        $component->call('gotoPage', 2)->set('sortDirection', 'desc');

        $this->assertSame(1, $component->viewData('destinations')->currentPage());
        $this->assertSame('desc', $component->viewData('activeDirection'));

        DB::enableQueryLog();

        $component->set('sortField', 'password')->assertStatus(200)->set('sortDirection', 'sideways')->assertStatus(200);

        $this->assertSame('name', $component->viewData('activeSort'));
        $this->assertSame('asc', $component->viewData('activeDirection'));

        $sql = collect(DB::getQueryLog())->pluck('query')->implode(' ');

        $this->assertStringNotContainsString('password', $sql);
        $this->assertStringNotContainsString('sideways', $sql);
        $this->assertMatchesRegularExpression('/order by \W?name\W? asc/', $sql);
    }

    public function test_the_paginator_exposes_current_previous_and_next_states(): void
    {
        $this->seed(DestinationSeeder::class);

        $component = Livewire::test(DestinationExplorer::class)
            ->assertSeeInOrder(['Showing', '1', 'to', '10', 'of', '15'])
            ->assertSeeHtml('aria-current="page"')
            ->assertSeeHtml("wire:click=\"nextPage('page')\"")
            ->assertDontSeeHtml("wire:click=\"previousPage('page')\"")
            ->assertSeeHtml("gotoPage(2, 'page')")
            ->assertDontSeeHtml("gotoPage(1, 'page')")
            ->call('nextPage')
            ->assertSeeInOrder(['Showing', '11', 'to', '15', 'of', '15'])
            ->assertSeeHtml('aria-current="page"')
            ->assertSeeHtml("wire:click=\"previousPage('page')\"")
            ->assertDontSeeHtml("wire:click=\"nextPage('page')\"")
            ->assertSeeHtml("gotoPage(1, 'page')")
            ->assertDontSeeHtml("gotoPage(2, 'page')");

        $this->assertSame(2, $component->viewData('destinations')->currentPage());
    }

    public function test_the_sort_controls_expose_the_active_field_and_direction(): void
    {
        Destination::factory()->create(['name' => 'Kyoto', 'country' => 'Japan']);
        Destination::factory()->create(['name' => 'Bali', 'country' => 'Indonesia']);

        $component = Livewire::test(DestinationExplorer::class)
            ->assertSeeHtml('value="name" selected')
            ->assertSee('Ascending')
            ->assertSeeInOrder(['Bali', 'Kyoto'])
            ->call('sort', 'country')
            ->assertSeeHtml('value="country" selected')
            ->assertDontSeeHtml('value="name" selected')
            ->assertSee('Ascending')
            ->assertSeeInOrder(['Bali', 'Kyoto'])
            ->call('sort', 'country')
            ->assertSeeHtml('value="country" selected')
            ->assertSee('Descending')
            ->assertSeeInOrder(['Kyoto', 'Bali']);

        $this->assertSame(1, substr_count($component->html(), ' selected'));
        $this->assertSame('country', $component->viewData('activeSort'));
        $this->assertSame('desc', $component->viewData('activeDirection'));
    }

    public function test_grid_is_the_default_view_mode(): void
    {
        Destination::factory()->create(['name' => 'Lisbon']);

        Livewire::test(DestinationExplorer::class)
            ->assertSet('viewMode', 'grid')
            ->assertSeeHtml('aria-label="Grid view" aria-pressed="true"')
            ->assertSee('Lisbon')
            ->assertSee('Annual visitors');
    }

    public function test_the_view_mode_controls_expose_their_pressed_state(): void
    {
        Livewire::test(DestinationExplorer::class)
            ->assertSeeHtml('aria-label="Grid view" aria-pressed="true"')
            ->assertSeeHtml('aria-label="List view" aria-pressed="false"')
            ->call('setViewMode', 'list')
            ->assertSeeHtml('aria-label="Grid view" aria-pressed="false"')
            ->assertSeeHtml('aria-label="List view" aria-pressed="true"')
            ->call('setViewMode', 'grid')
            ->assertSeeHtml('aria-label="Grid view" aria-pressed="true"')
            ->assertSeeHtml('aria-label="List view" aria-pressed="false"');
    }

    public function test_switching_to_list_mode_renders_the_list_layout(): void
    {
        Destination::factory()->create(['name' => 'Lisbon', 'cost_level' => 'Budget', 'average_daily_budget' => 90]);

        Livewire::test(DestinationExplorer::class)
            ->call('setViewMode', 'list')
            ->assertSet('viewMode', 'list')
            ->assertSeeHtml('aria-label="List view" aria-pressed="true"')
            ->assertSee('Lisbon')
            ->assertSee('Annual visitors')
            ->assertSee('$90/day');
    }

    public function test_switching_views_preserves_page_filters_and_sorting(): void
    {
        $this->seed(DestinationSeeder::class);

        $component = Livewire::test(DestinationExplorer::class)
            ->call('sort', 'annualVisitors')
            ->call('sort', 'annualVisitors')
            ->call('gotoPage', 2);

        $this->assertSame(2, $component->viewData('destinations')->currentPage());

        DB::enableQueryLog();

        $component->call('setViewMode', 'list');

        $this->assertCount(3, DB::getQueryLog());
        $this->assertSame(2, $component->viewData('destinations')->currentPage());
        $component->assertSet('sortField', 'annualVisitors')->assertSet('sortDirection', 'desc');

        $component
            ->set('searchTerm', 'a')
            ->set('region', 'Asia')
            ->set('costLevel', 'Budget')
            ->call('setViewMode', 'grid')
            ->assertSet('viewMode', 'grid')
            ->assertSet('searchTerm', 'a')
            ->assertSet('region', 'Asia')
            ->assertSet('costLevel', 'Budget')
            ->assertSet('sortField', 'annualVisitors')
            ->assertSet('sortDirection', 'desc');
    }

    public function test_invalid_view_modes_are_rejected_safely(): void
    {
        $component = Livewire::test(DestinationExplorer::class)
            ->call('setViewMode', 'table')
            ->assertSet('viewMode', 'grid')
            ->call('setViewMode', 'list')
            ->assertSet('viewMode', 'list')
            ->call('setViewMode', '')
            ->assertSet('viewMode', 'list')
            ->call('setViewMode', 'LIST')
            ->assertSet('viewMode', 'list');

        $this->expectException(CannotUpdateLockedPropertyException::class);

        $component->set('viewMode', 'table');
    }

    public function test_each_destination_is_rendered_once_with_a_unique_key(): void
    {
        $this->seed(DestinationSeeder::class);
        Destination::factory()->create(['name' => 'Aardvark Cove', 'country' => 'Nowhere']);

        foreach (['grid', 'list'] as $mode) {
            $component = Livewire::test(DestinationExplorer::class)->call('setViewMode', $mode);
            $html = $component->html();

            preg_match_all('/wire:key="destination-(\d+)"/', $html, $matches);

            $this->assertCount(10, $matches[1]);
            $this->assertCount(10, array_unique($matches[1]));
            $this->assertSame(1, substr_count($html, 'Aardvark Cove'));
            $this->assertSame(10, substr_count($html, '/day'));
        }
    }
}
