<?php

namespace App\Livewire;

use App\Models\Destination;
use Livewire\Component;
use Livewire\WithPagination;

class DestinationExplorer extends Component
{
    use WithPagination;

    public string $searchTerm = '';

    public string $region = '';

    public string $costLevel = '';

    public string $sortField = 'name';

    public string $sortDirection = 'asc';

    public function updated(string $property): void
    {
        if (in_array($property, ['searchTerm', 'region', 'costLevel'], true)) {
            $this->resetPage();
        }
    }

    public function sort(string $field): void
    {
        if (! array_key_exists($field, Destination::SORTS)) {
            return;
        }

        $this->sortDirection = $this->sortField === $field && $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->sortField = $field;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset('searchTerm', 'region', 'costLevel', 'sortField', 'sortDirection');
        $this->resetPage();
    }

    public function render()
    {
        $column = Destination::SORTS[$this->sortField] ?? 'name';
        $direction = in_array($this->sortDirection, ['asc', 'desc'], true) ? $this->sortDirection : 'asc';

        $destinations = Destination::query()
            ->search($this->searchTerm)
            ->when($this->region, fn ($query, $region) => $query->where('region', $region))
            ->when($this->costLevel, fn ($query, $costLevel) => $query->where('cost_level', $costLevel))
            ->orderBy($column, $direction)
            ->orderBy('id')
            ->paginate(10);

        return view('livewire.destination-explorer', [
            'destinations' => $destinations,
            'regions' => Destination::query()->distinct()->orderBy('region')->pluck('region'),
            'costLevels' => Destination::COST_LEVELS,
        ]);
    }
}
