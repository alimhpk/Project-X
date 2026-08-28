<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexDestinationRequest;
use App\Http\Resources\DestinationResource;
use App\Models\Destination;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DestinationController extends Controller
{
    public function index(IndexDestinationRequest $request): AnonymousResourceCollection
    {
        $sort = $request->validated('sort') ?? 'name';
        $direction = $request->validated('direction') ?? 'asc';

        $destinations = Destination::query()
            ->select(['id', 'name', 'country', 'region', 'cost_level', 'activities', 'average_daily_budget', 'annual_visitors'])
            ->search($request->validated('search'))
            ->when($request->validated('region'), fn ($query, $region) => $query->where('region', $region))
            ->when($request->validated('costLevel'), fn ($query, $costLevel) => $query->where('cost_level', $costLevel))
            ->orderBy(Destination::SORTS[$sort], $direction)
            ->orderBy('id')
            ->paginate((int) ($request->validated('perPage') ?? 15))
            ->appends($request->safe()->only(['search', 'region', 'costLevel', 'sort', 'direction', 'perPage']));

        return DestinationResource::collection($destinations);
    }
}
