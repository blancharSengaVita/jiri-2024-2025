<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Middleware\VerifyEvaluatorToken;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;


Volt::route('evaluator', 'pages.evaluator')
    ->middleware([VerifyEvaluatorToken::class])
    ->name('pages.evaluator');

Volt::route('dashboard', 'pages.dashboard')
    ->middleware(['auth', 'verified'])
    ->name('pages.dashboard');

Volt::route('contacts', 'pages.contacts')
    ->middleware(['auth', 'verified'])
    ->name('pages.contacts');

Volt::route('contacts-2', 'pages.contacts-2')
    ->middleware(['auth', 'verified'])
    ->name('pages.contacts-2');

Volt::route('projects', 'pages.projects')
    ->middleware(['auth', 'verified'])
    ->name('pages.projects');

Volt::route('jiris', 'pages.jiris.index')
    ->middleware(['auth', 'verified'])
    ->name('pages.jiris');

Volt::route('jiris/{jiri}', 'pages.jiris.edit')
    ->middleware(['auth', 'verified'])
    ->name('pages.jiris.edit');

require __DIR__ . '/auth.php';
