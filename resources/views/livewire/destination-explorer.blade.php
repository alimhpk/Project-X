@php
    $sortLabels = [
        'name' => 'Name',
        'country' => 'Country',
        'region' => 'Region',
        'costLevel' => 'Cost level',
        'averageDailyBudget' => 'Daily budget',
        'annualVisitors' => 'Annual visitors',
    ];
    $isList = $viewMode === 'list';
    $fieldClasses = 'mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-[#f05f40] focus:outline-none focus:ring-2 focus:ring-[#f05f40]/30';
    $buttonClasses = 'inline-flex items-center justify-center gap-1 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:border-[#f05f40] hover:bg-[#f05f40]/5 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#f05f40] disabled:cursor-not-allowed disabled:opacity-60';
    $secondaryButtonClasses = 'inline-flex items-center justify-center rounded-lg border border-[#f05f40] bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-[#f05f40]/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#f05f40] disabled:cursor-not-allowed disabled:opacity-60';
@endphp

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">Project Expedition Destinations</h1>
        <p class="mt-2 max-w-2xl text-slate-600">Compare regions, daily budgets and activities across our destinations to plan your next trip.</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-[14rem_minmax(0,1fr)] lg:items-start">
        <section aria-labelledby="filters-heading" class="rounded-xl border border-slate-200 bg-white p-3 shadow-xs sm:p-4">
            <h2 id="filters-heading" class="text-base font-semibold text-slate-900">Filters</h2>

            <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-[minmax(0,2fr)_1fr_1fr_auto] md:items-end lg:grid-cols-1">
                <div>
                    <label for="search-term" class="block text-sm font-medium text-slate-700">Search</label>
                    <input id="search-term" type="search" wire:model.live.debounce.300ms="searchTerm" placeholder="Name, country or region" autocomplete="off" class="{{ $fieldClasses }} placeholder:text-slate-400">
                </div>

                <div>
                    <label for="region" class="block text-sm font-medium text-slate-700">Region</label>
                    <select id="region" wire:model.live="region" class="{{ $fieldClasses }}">
                        <option value="">All regions</option>
                        @foreach ($regions as $regionName)
                            <option value="{{ $regionName }}">{{ $regionName }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="cost-level" class="block text-sm font-medium text-slate-700">Cost level</label>
                    <select id="cost-level" wire:model.live="costLevel" class="{{ $fieldClasses }}">
                        <option value="">All cost levels</option>
                        @foreach ($costLevels as $level)
                            <option value="{{ $level }}">{{ $level }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <button type="button" wire:click="resetFilters" wire:loading.attr="disabled" class="{{ $secondaryButtonClasses }} w-full md:w-auto lg:w-full">Reset filters</button>
                </div>
            </div>
        </section>

        <section id="destination-results" aria-labelledby="results-heading" class="scroll-mt-6 space-y-4">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <h2 id="results-heading" class="text-lg font-semibold text-slate-900">Destinations</h2>
                    <p role="status" aria-live="polite" class="text-sm text-slate-600">
                        {{ $destinations->total() }} {{ Str::plural('destination', $destinations->total()) }}{{ $hasActiveFilters ? ' match your filters' : '' }}
                        <span wire:loading class="font-medium text-slate-700">Loading…</span>
                    </p>
                </div>

                <div class="flex flex-wrap items-end gap-3">
                    <div class="min-w-0 flex-1 sm:w-48 sm:flex-none">
                        <label for="sort-field" class="block text-sm font-medium text-slate-700">Sort by</label>
                        <select id="sort-field" wire:model.live="sortField" class="{{ $fieldClasses }}">
                            @foreach ($sortLabels as $key => $label)
                                <option value="{{ $key }}" @selected($activeSort === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="button" wire:click="sort('{{ $activeSort }}')" wire:loading.attr="disabled" class="{{ $buttonClasses }}">
                        <span aria-hidden="true">{{ $activeDirection === 'asc' ? '↑' : '↓' }}</span>
                        {{ $activeDirection === 'asc' ? 'Ascending' : 'Descending' }}
                        <span class="sr-only">, activate to sort {{ $activeDirection === 'asc' ? 'descending' : 'ascending' }}</span>
                    </button>

                    <div role="group" aria-label="Display mode" class="inline-flex rounded-lg border border-slate-300 bg-white p-0.5">
                        <button type="button" wire:click="setViewMode('grid')" aria-label="Grid view" aria-pressed="{{ $isList ? 'false' : 'true' }}" title="Grid view" @class([
                            'inline-flex h-9 w-9 items-center justify-center rounded-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#f05f40]',
                            'bg-[#f05f40]/10 text-slate-900 ring-1 ring-[#f05f40]' => ! $isList,
                            'text-slate-500 hover:bg-slate-50 hover:text-slate-900' => $isList,
                        ])>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" aria-hidden="true">
                                <rect x="3" y="3" width="7" height="7" rx="1" />
                                <rect x="14" y="3" width="7" height="7" rx="1" />
                                <rect x="3" y="14" width="7" height="7" rx="1" />
                                <rect x="14" y="14" width="7" height="7" rx="1" />
                            </svg>
                        </button>
                        <button type="button" wire:click="setViewMode('list')" aria-label="List view" aria-pressed="{{ $isList ? 'true' : 'false' }}" title="List view" @class([
                            'inline-flex h-9 w-9 items-center justify-center rounded-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#f05f40]',
                            'bg-[#f05f40]/10 text-slate-900 ring-1 ring-[#f05f40]' => $isList,
                            'text-slate-500 hover:bg-slate-50 hover:text-slate-900' => ! $isList,
                        ])>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" aria-hidden="true">
                                <path d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            @if ($destinations->isEmpty())
                <div class="rounded-xl border border-slate-200 bg-white px-6 py-12 text-center shadow-sm">
                    @if ($hasActiveFilters)
                        <p class="text-base font-medium text-slate-900">No destinations match your search.</p>
                        <p class="mt-1 text-sm text-slate-600">Try a different search term or clear the filters.</p>
                        <button type="button" wire:click="resetFilters" wire:loading.attr="disabled" class="{{ $secondaryButtonClasses }} mt-4">Reset filters</button>
                    @else
                        <p class="text-base font-medium text-slate-900">No destinations have been added yet.</p>
                        <p class="mt-1 text-sm text-slate-600">Destinations will appear here once they are available.</p>
                    @endif
                </div>
            @else
                <div wire:loading.class="opacity-60" class="transition-opacity">
                    <ul @class([
                        'grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3' => ! $isList,
                        'flex flex-col gap-3' => $isList,
                    ])>
                        @foreach ($destinations as $destination)
                            @php
                                $badgeClasses = match ($destination->cost_level) {
                                    'Budget' => 'bg-[#f05f40]/15 text-orange-900',
                                    'Moderate' => 'bg-sky-50 text-sky-800',
                                    'Premium' => 'bg-amber-50 text-amber-800',
                                    'Luxury' => 'bg-violet-50 text-violet-800',
                                    default => 'bg-slate-100 text-slate-700',
                                };
                            @endphp
                            <li wire:key="destination-{{ $destination->id }}" class="relative flex flex-col overflow-hidden rounded-xl border border-slate-200 border-t-4 border-t-[#f05f40] bg-white shadow-sm transition hover:border-[#f05f40] hover:shadow-md">
                                <span class="absolute top-0 right-0 rounded-bl-lg px-2.5 py-1 text-xs font-medium {{ $badgeClasses }}">{{ $destination->cost_level }}</span>

                                <div @class([
                                    'flex flex-1 flex-col',
                                    'gap-4 p-4 pt-8' => ! $isList,
                                    'gap-2.5 px-4 pt-7 pb-3 sm:px-5' => $isList,
                                ])>
                                    <div class="flex items-start gap-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#f05f40]/10 text-[#f05f40]" aria-hidden="true">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                                                <path d="M12 21s-6-5.7-6-11a6 6 0 0 1 12 0c0 5.3-6 11-6 11z" />
                                                <circle cx="12" cy="10" r="2.5" />
                                            </svg>
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <h3 class="text-base font-semibold text-balance break-words text-slate-900">{{ $destination->name }}</h3>
                                            <p class="mt-0.5 text-sm text-slate-600">{{ $destination->country }} · {{ $destination->region }}</p>
                                        </div>
                                    </div>

                                    <dl @class([
                                        'grid grid-cols-2 gap-3 text-sm',
                                        'rounded-lg bg-[#f05f40]/5 px-3 py-2.5' => ! $isList,
                                        'sm:flex sm:gap-8' => $isList,
                                    ])>
                                        <div>
                                            <dt class="text-xs text-slate-500">Daily budget</dt>
                                            <dd class="mt-0.5 font-semibold tabular-nums text-slate-900">{{ Number::currency($destination->average_daily_budget, 'USD', precision: 0) }}/day</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs text-slate-500">Annual visitors</dt>
                                            <dd class="mt-0.5 font-semibold tabular-nums text-slate-900">{{ Number::format($destination->annual_visitors) }}</dd>
                                        </div>
                                    </dl>

                                    <ul @class(['flex flex-wrap gap-1.5', 'mt-auto' => ! $isList]) aria-label="Activities">
                                        @foreach ($destination->activities as $activity)
                                            <li class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700">{{ $activity }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{ $destinations->onEachSide(1)->links('pagination.destinations', ['scrollTo' => '#destination-results']) }}
            @endif
        </section>
    </div>
</div>
