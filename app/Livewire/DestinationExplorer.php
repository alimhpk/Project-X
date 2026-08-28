<?php

namespace App\Livewire;

use Livewire\Component;

class DestinationExplorer extends Component
{
    public $destinations = [];
    public $filteredDestinations = [];
    public $searchTerm = '';
    public $sortField = '';
    public $sortDirection = 'asc';

    public function mount()
    {
        // Uncomment this to use the database
        // $this->destinations = \App\Models\Destination::all()->toArray();

        $this->destinations = $this->getHardcodedDestinations();
        $this->filteredDestinations = $this->destinations;
    }

    public function search()
    {
        $searchTerm = $this->searchTerm;

        if (empty($searchTerm)) {
            $this->filteredDestinations = $this->destinations;
            return;
        }

        $this->filteredDestinations = array_filter($this->destinations, function ($destination) use ($searchTerm) {
            return str_contains($destination['name'], $searchTerm) ||
                str_contains($destination['country'], $searchTerm) ||
                str_contains($destination['region'], $searchTerm) ||
                str_contains($destination['cost_level'], $searchTerm) ||
                str_contains(json_encode($destination['activities']), $searchTerm) ||
                str_contains($destination['average_daily_budget'], $searchTerm);
        });
    }

    public function resetSearch()
    {
        $this->searchTerm = '';
        $this->filteredDestinations = $this->destinations;
    }

    public function sort($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $sorted = $this->filteredDestinations;
        usort($sorted, function ($a, $b) use ($field) {
            if ($this->sortDirection === 'asc') {
                return $a[$field] > $b[$field] ? 1 : -1;
            } else {
                return $a[$field] < $b[$field] ? 1 : -1;
            }
        });
        $this->filteredDestinations = $sorted;
    }

    public function render()
    {
        return view('livewire.destination-explorer');
    }

    private function getHardcodedDestinations()
    {
        return [
            [
                'name' => 'Machu Picchu',
                'country' => 'Peru',
                'region' => 'South America',
                'cost_level' => 'Moderate',
                'activities' => ['Hiking & Trekking', 'Cultural Tours', 'Photography', 'Historical Sightseeing'],
                'average_daily_budget' => 150,
                'annual_visitors' => 1500000,
            ],
            [
                'name' => 'Santorini',
                'country' => 'Greece',
                'region' => 'Europe',
                'cost_level' => 'Premium',
                'activities' => ['Beach & Relaxation', 'Food & Wine Tasting', 'Photography', 'Sailing & Boating'],
                'average_daily_budget' => 250,
                'annual_visitors' => 2000000,
            ],
            [
                'name' => 'Kyoto',
                'country' => 'Japan',
                'region' => 'Asia',
                'cost_level' => 'Moderate',
                'activities' => ['Cultural Tours', 'Historical Sightseeing', 'Food & Wine Tasting', 'Photography'],
                'average_daily_budget' => 180,
                'annual_visitors' => 5300000,
            ],
            [
                'name' => 'Serengeti National Park',
                'country' => 'Tanzania',
                'region' => 'Africa',
                'cost_level' => 'Premium',
                'activities' => ['Wildlife Safari', 'Photography', 'Hot Air Ballooning', 'Camping'],
                'average_daily_budget' => 300,
                'annual_visitors' => 350000,
            ],
            [
                'name' => 'Queenstown',
                'country' => 'New Zealand',
                'region' => 'Oceania',
                'cost_level' => 'Premium',
                'activities' => ['Adventure Sports', 'Skiing & Snowboarding', 'Hiking & Trekking', 'Paragliding'],
                'average_daily_budget' => 200,
                'annual_visitors' => 3000000,
            ],
            [
                'name' => 'Reykjavik',
                'country' => 'Iceland',
                'region' => 'Europe',
                'cost_level' => 'Premium',
                'activities' => ['Hiking & Trekking', 'Hot Air Ballooning', 'Photography', 'Volcano Tours'],
                'average_daily_budget' => 280,
                'annual_visitors' => 2300000,
            ],
            [
                'name' => 'Cusco',
                'country' => 'Peru',
                'region' => 'South America',
                'cost_level' => 'Budget',
                'activities' => ['Hiking & Trekking', 'Cultural Tours', 'Historical Sightseeing', 'Food & Wine Tasting'],
                'average_daily_budget' => 60,
                'annual_visitors' => 2700000,
            ],
            [
                'name' => 'Bali',
                'country' => 'Indonesia',
                'region' => 'Asia',
                'cost_level' => 'Budget',
                'activities' => ['Beach & Relaxation', 'Surfing', 'Yoga & Wellness Retreats', 'Cultural Tours'],
                'average_daily_budget' => 70,
                'annual_visitors' => 6200000,
            ],
            [
                'name' => 'Banff National Park',
                'country' => 'Canada',
                'region' => 'North America',
                'cost_level' => 'Moderate',
                'activities' => ['Hiking & Trekking', 'Skiing & Snowboarding', 'Kayaking & Canoeing', 'Wildlife Safari'],
                'average_daily_budget' => 180,
                'annual_visitors' => 4000000,
            ],
            [
                'name' => 'Patagonia',
                'country' => 'Argentina',
                'region' => 'South America',
                'cost_level' => 'Premium',
                'activities' => ['Hiking & Trekking', 'Rock Climbing', 'Photography', 'Camping'],
                'average_daily_budget' => 220,
                'annual_visitors' => 400000,
            ],
            [
                'name' => 'Marrakech',
                'country' => 'Morocco',
                'region' => 'Africa',
                'cost_level' => 'Budget',
                'activities' => ['Cultural Tours', 'Food & Wine Tasting', 'Historical Sightseeing', 'Nightlife & Entertainment'],
                'average_daily_budget' => 55,
                'annual_visitors' => 3000000,
            ],
            [
                'name' => 'Dubrovnik',
                'country' => 'Croatia',
                'region' => 'Europe',
                'cost_level' => 'Moderate',
                'activities' => ['Historical Sightseeing', 'Beach & Relaxation', 'Kayaking & Canoeing', 'Food & Wine Tasting'],
                'average_daily_budget' => 160,
                'annual_visitors' => 1400000,
            ],
            [
                'name' => 'Cancun',
                'country' => 'Mexico',
                'region' => 'North America',
                'cost_level' => 'Moderate',
                'activities' => ['Beach & Relaxation', 'Scuba Diving & Snorkeling', 'Nightlife & Entertainment', 'Cultural Tours'],
                'average_daily_budget' => 140,
                'annual_visitors' => 8000000,
            ],
            [
                'name' => 'Phuket',
                'country' => 'Thailand',
                'region' => 'Asia',
                'cost_level' => 'Budget',
                'activities' => ['Beach & Relaxation', 'Scuba Diving & Snorkeling', 'Food & Wine Tasting', 'Nightlife & Entertainment'],
                'average_daily_budget' => 80,
                'annual_visitors' => 9500000,
            ],
            [
                'name' => 'Swiss Alps',
                'country' => 'Switzerland',
                'region' => 'Europe',
                'cost_level' => 'Luxury',
                'activities' => ['Skiing & Snowboarding', 'Hiking & Trekking', 'Rock Climbing', 'Photography'],
                'average_daily_budget' => 400,
                'annual_visitors' => 1200000,
            ],
        ];
    }
}
