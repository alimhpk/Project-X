<?php

namespace App\Livewire;

use App\Models\Destination;
use Livewire\Attributes\Locked;
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

    #[Locked]
    public string $viewMode = 'grid';

    public function updated(string $property): void
    {
        if (in_array($property, ['searchTerm', 'region', 'costLevel', 'sortField', 'sortDirection'], true)) {
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

    public function setViewMode(string $mode): void
    {
        if (in_array($mode, ['grid', 'list'], true)) {
            $this->viewMode = $mode;
        }
    }

    public function resetFilters(): void
    {
        $this->reset('searchTerm', 'region', 'costLevel', 'sortField', 'sortDirection');
        $this->resetPage();
    }

    public function render()
    {
        $sortField = array_key_exists($this->sortField, Destination::SORTS) ? $this->sortField : 'name';
        $sortDirection = in_array($this->sortDirection, ['asc', 'desc'], true) ? $this->sortDirection : 'asc';

        $destinations = Destination::query()
            ->search($this->searchTerm)
            ->when($this->region, fn ($query, $region) => $query->where('region', $region))
            ->when($this->costLevel, fn ($query, $costLevel) => $query->where('cost_level', $costLevel))
            ->orderBy(Destination::SORTS[$sortField], $sortDirection)
            ->orderBy('id')
            ->paginate(10);

        return view('livewire.destination-explorer', [
            'destinations' => $destinations,
            'regions' => Destination::query()->distinct()->orderBy('region')->pluck('region'),
            'costLevels' => Destination::COST_LEVELS,
            'activeSort' => $sortField,
            'activeDirection' => $sortDirection,
            'hasActiveFilters' => $this->searchTerm !== '' || $this->region !== '' || $this->costLevel !== '',
        ]);
    }
}
