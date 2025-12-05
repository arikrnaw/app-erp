<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Laravel\WorkOS\Http\Requests\AuthKitAuthenticationRequest;
use Laravel\WorkOS\Http\Requests\AuthKitLoginRequest;
use Laravel\WorkOS\Http\Requests\AuthKitLogoutRequest;

Route::get('login', function (AuthKitLoginRequest $request) {
    return $request->redirect();
})->middleware(['guest'])->name('login');

Route::middleware('web')->get('authenticate', function (AuthKitAuthenticationRequest $request) {
    try {
        // Authenticate user first to ensure tokens are saved to session
        $request->authenticate();
      
        // Ensure session is saved before redirect
        $request->session()->save();
        
        // Then redirect to dashboard
        return redirect()->route('dashboard');
    } catch (\Exception $e) {
        // Log the error for debugging
        Log::error('WorkOS Authentication Error: ' . $e->getMessage(), [
            'exception' => $e,
            'code' => $request->get('code'),
            'state' => $request->get('state'),
            'trace' => $e->getTraceAsString(),
        ]);
        
        // Redirect to login with error message
        return redirect()->route('login')->with('error', 'Authentication failed: ' . $e->getMessage());
    }
})->withoutMiddleware([\Laravel\WorkOS\Http\Middleware\ValidateSessionWithWorkOS::class]);

Route::post('logout', function (AuthKitLogoutRequest $request) {
    return $request->logout();
})->middleware(['auth'])->name('logout');
