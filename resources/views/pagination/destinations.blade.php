@php
    $scrollIntoView = isset($scrollTo) ? 'document.querySelector('.json_encode($scrollTo).')?.scrollIntoView()' : '';
    $buttonClasses = 'inline-flex min-w-10 items-center justify-center rounded-lg px-3 py-2 font-medium text-slate-700 hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-600 disabled:cursor-not-allowed disabled:opacity-60';
@endphp

@if ($paginator->hasPages())
    <nav aria-label="Destinations pagination" class="flex flex-col gap-3 border-t border-slate-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <p class="text-sm text-slate-600">
            Showing <span class="font-medium text-slate-900">{{ $paginator->firstItem() }}</span>
            to <span class="font-medium text-slate-900">{{ $paginator->lastItem() }}</span>
            of <span class="font-medium text-slate-900">{{ $paginator->total() }}</span> results
        </p>

        <ul class="flex flex-wrap items-center gap-1 text-sm">
            <li>
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" class="inline-flex items-center rounded-lg px-3 py-2 text-slate-400">Previous</span>
                @else
                    <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" x-on:click="{{ $scrollIntoView }}" aria-label="Previous page" class="{{ $buttonClasses }}">Previous</button>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li aria-hidden="true" class="px-2 text-slate-500">{{ $element }}</li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li wire:key="paginator-page-{{ $page }}">
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="inline-flex min-w-10 items-center justify-center rounded-lg bg-[#f05f40] px-3 py-2 font-medium text-slate-900">{{ $page }}</span>
                            @else
                                <button type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" x-on:click="{{ $scrollIntoView }}" aria-label="Go to page {{ $page }}" class="{{ $buttonClasses }}">{{ $page }}</button>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            <li>
                @if ($paginator->hasMorePages())
                    <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" x-on:click="{{ $scrollIntoView }}" aria-label="Next page" class="{{ $buttonClasses }}">Next</button>
                @else
                    <span aria-disabled="true" class="inline-flex items-center rounded-lg px-3 py-2 text-slate-400">Next</span>
                @endif
            </li>
        </ul>
    </nav>
@endif
