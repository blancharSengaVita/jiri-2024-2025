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
{{--    @if(session('currentJiri'))--}}
{{--        <div class="flex gap-x-2 items-center mb-2">--}}
{{--            @if(session('currentJiri')->status === Jiri::STATUS_IN_PROGRESS)--}}
{{--                <h1 class="text-3xl font-bold leading-tight tracking-tight text-gray-900">--}}
{{--                    Dashboard - jiri en cours : {{session('currentJiri')->name}}<span class="text-red-500"></span></h1>--}}
{{--                <div class="w-4 h-4 bg-red-500 rounded-full animate-pulse"></div>--}}
{{--            @endif--}}
{{--            @if(session('currentJiri')->status === Jiri::STATUS_ON_PAUSE)--}}
{{--                <h1 class="text-3xl font-bold leading-tight tracking-tight text-gray-900">--}}
{{--                    Dashboard - jiri en pause : {{session('currentJiri')->name}}<span class="text-red-500"></span></h1>--}}
{{--                <div class="w-4 h-4 bg-yellow-500 rounded-full animate-pulse"></div>--}}
{{--            @endif--}}
{{--        </div>--}}
{{--    @else--}}
        <div class="items-center mb-4">
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-gray-900">
                Bonjour, {{$user->contact->name}}
                <span class="text-red-500"></span></h1>
        </div>
        <div class="gap-4">
            <div class="col-span-5 mb-6">
                <h2 class="text-base/7 font-semibold text-gray-900 mb-2">Étudiant à évaluer</h2>
                <livewire:partials.student-to-evaluate :jiri="$jiri"/>
            </div>
        </div>
{{--    @endif--}}
</div>

