<?php

use App\Http\Controllers\Api\DestinationController;
use Illuminate\Support\Facades\Route;

Route::get('/destinations', [DestinationController::class, 'index']);
