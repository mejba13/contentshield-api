<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

// Root redirect
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('/login', function (Request $request) {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    });
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

// Protected Dashboard Routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('pages.dashboard');
    })->name('dashboard');

    // Protected Content
    Route::get('/content', function () {
        return view('pages.content.index');
    })->name('content.index');

    Route::get('/content/{id}', function ($id) {
        return view('pages.content.show', ['id' => $id]);
    })->name('content.show');

    // Scan Results
    Route::get('/scans', function () {
        return view('pages.scans.index');
    })->name('scans.index');

    // Plagiarism Alerts
    Route::get('/alerts', function () {
        return view('pages.alerts.index');
    })->name('alerts.index');

    Route::get('/alerts/{id}', function ($id) {
        return view('pages.alerts.show', ['id' => $id]);
    })->name('alerts.show');

    // Analytics
    Route::get('/analytics', function () {
        return view('pages.analytics.index');
    })->name('analytics.index');

    // DMCA Requests
    Route::get('/dmca', function () {
        return view('pages.dmca.index');
    })->name('dmca.index');

    Route::get('/dmca/{id}', function ($id) {
        return view('pages.dmca.show', ['id' => $id]);
    })->name('dmca.show');

    // Schedule Scans
    Route::get('/schedule', function () {
        return view('pages.schedule.index');
    })->name('schedule.index');

    // Settings
    Route::get('/settings', function () {
        return view('pages.settings.index');
    })->name('settings.index');

    // API Keys
    Route::get('/api-keys', function () {
        return view('pages.api-keys.index');
    })->name('api-keys.index');
});
