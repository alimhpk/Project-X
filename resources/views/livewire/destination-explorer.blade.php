<div>
    <h1 style="font-size: 24px; font-weight: bold;">Project Expedition Destinations</h1>
    <br>
    <br>
    <div>
        <p>Search</p>
        <p>Searching for: <span id="search-term">{{ $searchTerm }}</span></p>
        <input type="text" wire:model.live.debounce.300ms="searchTerm" style="border: 1px solid black; padding: 4px;">
        <select wire:model.live="region" style="border: 1px solid black; padding: 4px;">
            <option value="">All regions</option>
            @foreach ($regions as $regionName)
                <option value="{{ $regionName }}">{{ $regionName }}</option>
            @endforeach
        </select>
        <select wire:model.live="costLevel" style="border: 1px solid black; padding: 4px;">
            <option value="">All cost levels</option>
            @foreach ($costLevels as $level)
                <option value="{{ $level }}">{{ $level }}</option>
            @endforeach
        </select>
        <button wire:click="resetFilters">Reset Filters</button>
    </div>
    <br>
    <br>
    <div x-data="{ highlightedRow: null }">
        <table>
            <thead>
                <tr>
                    <th wire:click="sort('name')" style="cursor: pointer;">Name</th>
                    <th wire:click="sort('country')" style="cursor: pointer;">Country</th>
                    <th wire:click="sort('region')" style="cursor: pointer;">Region</th>
                    <th wire:click="sort('costLevel')" style="cursor: pointer;">Cost Level</th>
                    <th>Activities</th>
                    <th wire:click="sort('averageDailyBudget')" style="cursor: pointer;">Avg. Daily Budget</th>
                    <th wire:click="sort('annualVisitors')" style="cursor: pointer;">Annual Visitors</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($destinations as $destination)
                <tr wire:key="{{ $destination->id }}" x-on:mouseenter="highlightedRow = {{ $loop->index }}" x-on:mouseleave="highlightedRow = null" x-bind:style="highlightedRow === {{ $loop->index }} ? 'background-color: #f0f0f0' : ''">
                    <td>{{ $destination->name }}</td>
                    <td>{{ $destination->country }}</td>
                    <td>{{ $destination->region }}</td>
                    <td>{{ $destination->cost_level }}</td>
                    <td>{{ implode(', ', $destination->activities) }}</td>
                    <td>{{ $destination->average_daily_budget }}</td>
                    <td>{{ $destination->annual_visitors }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <br>
    {{ $destinations->links() }}

    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</div>
