<?php
// Temporary test route - add to routes/web.php

Route::post('/test-broadcast-auth', function () {
    return response()->json([
        'authenticated' => auth()->check(),
        'user_id' => auth()->id(),
        'session_id' => session()->getId(),
        'csrf_token' => csrf_token(),
        'store_access' => auth()->user()?->stores()->pluck('id')->toArray() ?? [],
    ]);
})->middleware(['web', 'auth']);
