<div>
    <h1 style="font-size: 24px; font-weight: bold;">Project Expedition Destinations</h1>
    <br>
    <br>
    <div>
        <p>Search</p>
        <p>Searching for: <span id="search-term">{{ $searchTerm }}</span></p>
        <input type="text" wire:model="searchTerm" wire:keydown="search" style="border: 1px solid black; padding: 4px;">
        <button wire:click="resetSearch">Reset Search</button>
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
                    <th wire:click="sort('cost_level')" style="cursor: pointer;">Cost Level</th>
                    <th>Activities</th>
                    <th wire:click="sort('average_daily_budget')" style="cursor: pointer;">Avg. Daily Budget</th>
                    <th wire:click="sort('annual_visitors')" style="cursor: pointer;">Annual Visitors</th>
                </tr>
            </thead>
            <tbody>
                @foreach($filteredDestinations as $destination)
                <tr x-on:mouseenter="highlightedRow = {{ $loop->index }}" x-on:mouseleave="highlightedRow = null" x-bind:style="highlightedRow === {{ $loop->index }} ? 'background-color: #f0f0f0' : ''">
                    <td>{{ $destination['name'] }}</td>
                    <td>{{ $destination['country'] }}</td>
                    <td>{{ $destination['region'] }}</td>
                    <td>{{ $destination['cost_level'] }}</td>
                    <td>{{ implode(', ', $destination['activities']) }}</td>
                    <td>{{ $destination['average_daily_budget'] }}</td>
                    <td>{{ $destination['annual_visitors'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</div>
