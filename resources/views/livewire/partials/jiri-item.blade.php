<?php

use App\Models\Attendance;
use App\Models\Duties;
use App\Models\Jiri;
use function Livewire\Volt\{state, mount, on};
use Masmerise\Toaster\Toaster;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\JiriLaunched;
use App\Notifications\JiriLaunchedNotification;
use App\Jobs\SendJiriLaunchedEmails;
use Illuminate\Support\Facades\Auth;

state([
    'jiri',
    'user',
]);

mount(function (Jiri $jiri) {
    Carbon::setLocale('fr');
    $this->user = Auth::user();

    $this->jiri = $jiri;
    $this->jiri->starting_at = Carbon::parse($this->jiri->starting_at)->translatedFormat('j F Y');

    $this->jiri->errors = collect([]);

    if ($this->jiri->evaluators->isEmpty()) {
        $this->jiri->errors->push('Le jiri n\'a pas d\'évaluateurs');
    }

    if ($this->jiri->students->isEmpty()) {
        $this->jiri->errors->push('Le jiri n\'a pas d\'élève');
    }

    if ($this->jiri->duties) {
        $sum = 0;
        foreach ($this->jiri->duties as $duty) {
            $sum += $duty->weighting;
        }
    } else {
        $this->jiri->errors->push('Le jiri n\'a pas de projet');
    }

    if ($this->jiri->duties->isEmpty()) {
        $this->jiri->errors->push('Le jiri n\'a pas de projet');
    }

    if ($sum !== 100) {
        $this->jiri->errors->push('La somme des pondérations des projets doit être égale à 100');
    }

    foreach ($this->jiri->duties as $duty) {
        if ($duty['weighting'] === null || $duty['weighting'] === '') {
            $this->jiri->errors->push('Un projet ou plusieurs n\'ont pas de pondération.');
            break;
        }
    }
});

$delete = function (Jiri $jiri) {
    $this->dispatch('openDeleteModal', modelId: $jiri->id, modelName: 'App\Models\Jiri')->to('partials.delete-modal');
    $this->mount($this->jiri);
};

on([
    'refreshDashboardItems' => function () {
        $this->mount($this->jiri);
    }, 'refreshJiriItem' => function () {
        $this->mount($this->jiri);
    }]);
?>
<li class="flex items-center justify-between gap-x-6 py-5 p-4"
    x-data="{tooltip: false}">
    <div class="min-w-0 flex gap-x-2">
        <div
            class="relative">
            <div x-cloak
                 x-show="tooltip"
                 class="absolute z-50 flex gap-2 left-full -top-1/2 ml-12 mb-4 bg-white p-4 rounded-xl border border-gray-200 text-sm shadow
                                     w-48
                                     {{ !count($jiri->errors) ? 'border-2 border-green-500' : 'border-2 border-red-500' }}
                                     ">
                @if(!count($jiri->errors))
                    <small class="text-sm ">
                        Le jiri est prêt à être lancé.
                    </small>
                @else
                    <ul class="text-sm list-disc ms-4">
                        @foreach($jiri->errors as $errors)
                            <li>{{$errors}}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
        @if(!count($jiri->errors))
            <svg @click="tooltip = !tooltip;"
                 @click.outside="tooltip = false;" @mouseenter="tooltip = true;"
                 @mouseleave="tooltip = false;"
                 class="text-green-600 sm:size-6 cursor-pointer" xmlns="http://www.w3.org/2000/svg"
                 viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd"/>
            </svg>
        @else
            <svg @click="tooltip = !tooltip;"
                 @click.outside="tooltip = false;" @mouseenter="tooltip = true;"
                 @mouseleave="tooltip = false;"
                 class="text-red-500 size-6 cursor-pointer" viewBox="0 0 16 16"
                 fill="currentColor"
                 aria-hidden="true"
                 data-slot="icon">
                <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14ZM8 4a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-1.5 0v-3A.75.75 0 0 1 8 4Zm0 8a1 1 0 1 0d 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
            </svg>
        @endif
        <div class="flex-col items-start gap-x-3">

            <div class="flex gap-x-2 items-center">
                @if(session('currentJiri') && session('currentJiri')->id === $jiri->id && session('currentJiri')->status === Jiri::STATUS_IN_PROGRESS)
                    <p class="text-sm/6 font-semibold text-gray-900">{{$jiri->name}} (Jiri en cours)</p>
                    <div class="w-4 h-4 bg-red-500 rounded-full animate-pulse"></div>
                @elseif(session('currentJiri') && session('currentJiri')->id === $jiri->id && session('currentJiri')->status === Jiri::STATUS_ON_PAUSE)
                    <p class="text-sm/6 font-semibold text-gray-900">{{$jiri->name}} (Jiri en pause)</p>
                    <div class="w-4 h-4 bg-yellow-500 rounded-full animate-pulse"></div>
                @else
                    <p class="text-sm/6 font-semibold text-gray-900">{{$jiri->name}}</p>
                @endif
            </div>

            <p class="text-sm/6 text-gray-500">{{ $jiri->starting_at }}</p>
        </div>
    </div>

    <div class="flex flex-none items-center gap-x-4">
        @if(session('currentJiri') && $jiri->id === session('currentJiri')->id && session('currentJiri')->canBeStopped())
            <livewire:partials.stopjiri :$jiri/>
        @else
            <livewire:partials.startjiri :$jiri/>
        @endif
        <a wire:navigate href="{{route('pages.jiris.edit', $jiri)}}"
           class="rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:block">
            Voir le Jiri<span class="sr-only">{{$jiri->name}}</span>
        </a>
        <button wire:click="delete({{$jiri}})"
                type="button"
                class="hidden rounded-md bg-indigo-600 px-2.5 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 sm:block">
            Supprimer
        </button>
    </div>
</li>
