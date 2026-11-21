
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TrackingController;

// API Publik untuk Pelacakan Nota Cucian
Route::prefix('v1')->group(function () {
    Route::get(
        '/track/{transaction_code}',
        [TrackingController::class, 'show']
    )->name('api.tracking.show');
});
