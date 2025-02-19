<?php

use App\Http\Controllers\API\V1\WhoisController;
use Illuminate\Support\Facades\Route;

Route::group(
    ['as' => 'api.', 'prefix' => 'v1', 'middleware' => ['throttle:10,1']],
    static function () {
        Route::post('/whois', [WhoisController::class, 'lookup'])->name('lookup');
    }
);
