<?php

use App\Models\Jiri;
use function Livewire\Volt\{layout, mount, rules, state, on};
use Illuminate\Support\Facades\Auth;
use Masmerise\Toaster\Toaster;
use Carbon\Carbon;
use App\Models\Duties;
use Illuminate\Support\Facades\URL;

state([
    'user',
    'jiri',
]);

layout('layouts.app');

mount(function () {
    if (request()->has('token')) {
        $this->redirect(URL::current(), navigate: true);
    }
    $this->user = session('evaluator');
    $this->jiri = session('evaluator')->jiri;
});
?>

<div class="py-10"
     x-data="{
     }"
>
    <x-slot name="h1">
        Bonjour, {{$user->contact->name}}
    </x-slot>
        <div class="items-center mb-4">
            <p class="text-3xl font-bold leading-tight tracking-tight text-gray-900">
                Bonjour, {{$user->contact->name}}
                <span class="text-red-500"></span></p>
        </div>
        <div class="gap-4">
            <div class="col-span-5 mb-6">
                <h2 class="text-base/7 font-semibold text-gray-900 mb-2">Étudiant à évaluer</h2>
                <livewire:partials.student-to-evaluate :jiri="$jiri"/>
            </div>
        </div>
</div>

