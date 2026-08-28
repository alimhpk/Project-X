<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class SeedController extends Controller
{
    public function store()
    {
        DB::table('destinations')->truncate();

        Artisan::call('db:seed', ['--class' => 'DestinationSeeder']);

        $destinations = DB::table('destinations')->get();

        return response()->json(['destinations' => $destinations]);
    }
}
