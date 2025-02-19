<?php

use App\Http\Controllers\API\V1\WhoisController;
use Illuminate\Support\Facades\Route;

Route::group(
    ['as' => 'api.', 'prefix' => 'v1'],
    static function () {
        Route::post('/whois', [WhoisController::class, 'lookup'])->name('lookup');
    }
);
