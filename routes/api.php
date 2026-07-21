<?php

use Illuminate\Support\Facades\Route;
use Khudayberdiyev\UzbekistanRegions\Http\Controllers\DistrictController;
use Khudayberdiyev\UzbekistanRegions\Http\Controllers\QuarterController;
use Khudayberdiyev\UzbekistanRegions\Http\Controllers\RegionController;

Route::get('/regions', [RegionController::class, 'index'])->name('uzbekistan-regions.regions.index');
Route::get('/regions/{id}', [RegionController::class, 'show'])->name('uzbekistan-regions.regions.show');

Route::get('/districts', [DistrictController::class, 'index'])->name('uzbekistan-regions.districts.index');
Route::get('/districts/{id}', [DistrictController::class, 'show'])->name('uzbekistan-regions.districts.show');

Route::get('/quarters', [QuarterController::class, 'index'])->name('uzbekistan-regions.quarters.index');
Route::get('/quarters/{id}', [QuarterController::class, 'show'])->name('uzbekistan-regions.quarters.show');
