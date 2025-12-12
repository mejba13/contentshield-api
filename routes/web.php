<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

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
