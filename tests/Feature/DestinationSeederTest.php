<?php

namespace Tests\Feature;

use App\Models\Destination;
use Database\Seeders\DestinationSeeder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestinationSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_running_the_seeder_twice_does_not_duplicate_destinations(): void
    {
        $this->seed(DestinationSeeder::class);
        $this->seed(DestinationSeeder::class);

        $this->assertDatabaseCount('destinations', 15);
    }

    public function test_reseeding_restores_changed_values(): void
    {
        $this->seed(DestinationSeeder::class);
        Destination::where('name', 'Kyoto')->update(['average_daily_budget' => 1]);

        $this->seed(DestinationSeeder::class);

        $this->assertSame(180, Destination::where('name', 'Kyoto')->firstOrFail()->average_daily_budget);
    }

    public function test_activities_are_stored_and_read_as_an_array(): void
    {
        $this->seed(DestinationSeeder::class);

        $this->assertSame(
            ['Hiking & Trekking', 'Cultural Tours', 'Photography', 'Historical Sightseeing'],
            Destination::where('name', 'Machu Picchu')->firstOrFail()->activities
        );
    }

    public function test_the_same_name_in_a_different_country_is_allowed(): void
    {
        Destination::factory()->create(['name' => 'Patagonia', 'country' => 'Argentina']);
        Destination::factory()->create(['name' => 'Patagonia', 'country' => 'Chile']);

        $this->assertDatabaseCount('destinations', 2);
    }

    public function test_the_same_name_and_country_cannot_be_stored_twice(): void
    {
        Destination::factory()->create(['name' => 'Patagonia', 'country' => 'Argentina']);

        $this->expectException(UniqueConstraintViolationException::class);

        Destination::factory()->create(['name' => 'Patagonia', 'country' => 'Argentina']);
    }
}
