<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;


Volt::route('dashboard', 'pages.dashboard')
    ->middleware(['auth', 'verified'])
    ->name('pages.dashboard');

Volt::route('contacts', 'pages.contacts')
    ->middleware(['auth', 'verified'])
    ->name('pages.contacts');

Volt::route('projects', 'pages.projects')
    ->middleware(['auth', 'verified'])
    ->name('pages.projects');

Volt::route('jiris', 'pages.jiris')
    ->middleware(['auth', 'verified'])
    ->name('pages.jiris');


require __DIR__ . '/auth.php';
