<?php

use App\Http\Controllers\Api\DestinationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'abilities:destinations:read'])
    ->get('/destinations', [DestinationController::class, 'index']);
