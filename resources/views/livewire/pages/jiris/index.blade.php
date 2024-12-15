<?php

use App\Models\Jiri;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Masmerise\Toaster\Toaster;
use function Livewire\Volt\{layout, mount, rules, state, on};
use Carbon\Carbon;
use App\Models\Duties;

layout('layouts.app');

state([
    'drawer',
    'jiris',
    'jiri',
    'user',
    'name',
    'date',
    'id',
    'deleteModal',
]);

rules(fn() => [
    'name' => 'required',
    'date' => 'required|date',
])->messages([
    'name.required' => 'Le champ est obligatoire.',
    'date.required' => 'Le champ est obligatoire.',
    'date.date' => 'Le champ doit être une date.',
])->attributes([
]);

mount(function () {
    $this->drawer = false;
    $this->user = Auth::user()->load('jiris');
    $this->jiris = $this->user->jiris()->orderBy('name')->get();


    //Je devrai faire ça dans le model
    foreach ($this->jiris as $jiri) {
        $jiri->starting_at = Carbon::parse($jiri->starting_at)->translatedFormat('j F Y');

        $jiri->status = collect([]);

        $sum = 0;
        foreach ($jiri->duties as $duty) {
            $sum += $duty->weighting;
        }

        if ($sum !== 100) {
            $jiri->status->push('La somme des pondérations des projets doit être égale à 100.');
        }

        foreach ($jiri->duties as $duty) {
            if ($duty['weighting'] === null | $duty['weighting'] === '') {
                $jiri->status->push('Un projet n\'a pas de pondération.');
                break;
            }
        }
    }

    $this->id = 0;
    $this->name = '';
    $this->email = '';
    $this->phone = '';
    $this->photo = null;
});

$openCreateDrawer = function () {
    $this->mount();
    $this->resetValidation();
    $this->drawer = true;
    $this->name = '';
    $this->date = '';
};

$closeCreateDrawer = function () {
    $this->mount();
    $this->drawer = false;
};

$edit = function (Jiri $jiri) {
    $this->mount();
    $this->drawer = true;
    $this->id = $jiri->id;
    $this->name = $jiri->name;
    $this->date = $jiri->date;
};

$save = function () {
    try {
        $this->validate();
    } catch (\Illuminate\Validation\ValidationException $e) {
        throw $e;
    }

    Jiri::updateOrCreate([
        'user_id' => Auth::id(),
        'id' => $this->id,
    ],
        [
            'name' => $this->name,
            'date' => $this->date,
        ]);

    $this->drawer = false;
    if ($this->id === 0) {
        Toaster::success('Projet ajouté avec succès');
    }

    if ($this->id !== 0) {
        Toaster::success('Projet modifiée avec succès');
    };
    $this->mount();
};

$start = function (Jiri $jiri) {
    $this->jiri = $jiri;

    $duties = Duties::where('jiri_id', $this->jiri->id)->get();
    $sum = 0;
    foreach ($duties as $duty) {
        $sum += (int)$duty->weighting;

        if ($duty['weighting'] === null | $duty['weighting'] === '') {
            Toaster::error('Un projet ou plusieurs n\'ont pas de pondération.');
            $this->mount();
            return;
//            throw new StartJiriException('Un projet n\'a pas de pondération.');
        }
    }

    if ($sum !== 100) {
        Toaster::error('La somme des pondérations des projets doit être égale à 100.');
        $this->mount();
        return;
//        throw new StartJiriException('La somme des pondérations des projets doit être égale à 100.') ;
    }

    dd('Je dois faire la suite !');
};


$openDeleteModal = function (Jiri $jiri) {
    $this->dispatch('openDeleteModal', modelId: $jiri->id, modelName: 'App\Models\Jiri')->to('partials.delete-modal');
};

on(['refreshComponent' => function () {
    $this->mount();
}]);
?>

<div class="py-10"
     x-data="{
    open: $wire.entangle('drawer'),
    deleteModal: $wire.entangle('deleteModal'),
    }"
>
    <h1 class="text-3xl font-bold leading-tight tracking-tight text-gray-900">Liste des jiris</h1>
    <div>
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <p class="mt-2 text-sm text-gray-700">La liste de tout vos jiris</p>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                <button wire:click="openCreateDrawer" type="button"
                        class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    Ajouter un jiris
                </button>
            </div>
        </div>
        @if(count($jiris))
            <ul role="list" class="divide-y divide-gray-100 bg-white border mt-4 shadow-sm ring-1 ring-gray-900/5">
                @foreach($jiris as $jiri)
                    <li class="flex items-center justify-between gap-x-6 py-5 p-4"
                        x-data="{tooltip: false}">
                        <div class="min-w-0 flex gap-x-2">
                            <div
                                class="relative">
                                <div x-cloak
                                     x-show="tooltip"
                                     class="absolute z-50 flex gap-2 bottom-full mb-4 bg-white p-4 rounded-xl border border-gray-200 text-sm shadow
                                     w-48
                                     {{ !count($jiri->status) ? 'border-2 border-green-500' : 'border-2 border-red-500' }}
                                     ">
{{--                                    <svg--}}
{{--                                        @click="tooltip = !tooltip;"--}}
{{--                                        xmlns="http://www.w3.org/2000/svg"--}}
{{--                                        class="text-base w-16 me-2 cursor-pointer hover:bg-gray-200 rounded"--}}
{{--                                        fill="none"--}}
{{--                                        viewBox="0 0 24 24"--}}
{{--                                        stroke-width="1.5"--}}
{{--                                        stroke="currentColor">--}}
{{--                                        <path stroke-linecap="round"--}}
{{--                                              stroke-linejoin="round"--}}
{{--                                              d="M6 18 18 6M6 6l12 12"/>--}}
{{--                                    </svg>--}}
                                    @if(!count($jiri->status))
                                        <small class="text-sm ">
                                            Le jiri est prêt à être lancé.
                                        </small>
                                    @else
                                        <ul class="text-sm list-disc ms-4">
                                            @foreach($jiri->status as $status)
                                                <li>{{$status}}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                            @if(!count($jiri->status))
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
                                     class="text-red-500 sm:size-6 cursor-pointer" viewBox="0 0 16 16"
                                     fill="currentColor"
                                     aria-hidden="true"
                                     data-slot="icon">
                                    <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14ZM8 4a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-1.5 0v-3A.75.75 0 0 1 8 4Zm0 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                            <div class="flex-col items-start gap-x-3">
                                <p class="text-sm/6 font-semibold text-gray-900">{{$jiri->name}}</p>
                                <p class="text-sm/6 text-gray-500">{{ $jiri->starting_at }}</p>
                            </div>
                        </div>
                        <div class="flex flex-none items-center gap-x-4">
                            <button type="button"
                                    wire:click="start({{$jiri}})"
                                    class="hidden rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:block">
                                Lancer le jiri<span class="sr-only">{{$jiri->name}}</span>
                            </button>
                            <a href="{{route('pages.jiris.edit', $jiri)}}"
                               class="hidden rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:block">
                                Voir le Jiri<span class="sr-only">{{$jiri->name}}</span>
                            </a>
                            <button wire:click="openDeleteModal({{$jiri}})"
                                    type="button"
                                    class="rounded-md bg-indigo-600 px-2.5 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                Supprimer
                            </button>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="mt-4">Aucun jiri veuillez en crée un ici +</p>
        @endif
    </div>
    <div x-cloak x-show="open" class="z-10" aria-labelledby="slide-over-title" role="dialog"
         aria-modal="true">
        <!-- Background backdrop, show/hide based on slide-over state. -->
        <div class="fixed inset-0"></div>

        <div class="fixed inset-0 overflow-hidden">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full w-auto pl-10 sm:pl-16"

                     x-show="open"
                     x-transition:enter="transform transition ease-in-out duration-200"
                     x-transition:enter-start="translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transform transition ease-in-out duration-200"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="translate-x-full">
                    <!--
                      Slide-over panel, show/hide based on slide-over state.

                      Entering: "transform transition ease-in-out duration-500 sm:duration-700"
                        From: "translate-x-full"
                        To: "translate-x-0"
                      Leaving: "transform transition ease-in-out duration-500 sm:duration-700"
                        From: "translate-x-0"
                        To: "translate-x-full"
                    -->
                    <div class="pointer-events-auto w-screen max-w-md">
                        <form
                            wire:submit.prevent="save"
                            @click.away="open = false "
                            enctype="multipart/form-data"
                            class="flex h-full flex-col divide-y divide-gray-200 bg-white shadow-xl"
                        >
                            <div class="h-0 flex-1 overflow-y-scroll overflow-hidden max-h-full">
                                <div class="bg-gray-900 px-4 py-6 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <h2 class="text-base font-semibold leading-6 text-white" id="slide-over-title">
                                            Nouveau Jiri</h2>
                                        <div class="ml-3 flex h-7 items-center">
                                            <button wire:click="closeCreateDrawer" type="button"
                                                    class="relative rounded-md bg-gray-900 text-gray-400 hover:text-white focus:outline-none focus:ring-2 focus:ring-white">
                                                <span class="absolute -inset-2.5"></span>
                                                <span class="sr-only">Fermer la modal</span>
                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                     stroke="currentColor" aria-hidden="true" data-slot="icon">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M6 18 18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mt-1">
                                        <p class="text-sm text-gray-400">Ajouter des informations pour votre jiri</p>
                                    </div>
                                </div>
                                <div class="flex flex-1 flex-col justify-between">
                                    <div class="divide-y divide-gray-200 px-4 sm:px-6">
                                        <div class="space-y-6 pb-5 pt-6">
                                            <fieldset>
                                                <label for="name"
                                                       class="block text-sm font-medium leading-6 text-gray-900">Nom du
                                                    projet<span class="text-red-500">*</span></label>
                                                <div class="mt-2">
                                                    <input wire:model="name" type="text" name="name" id="name"
                                                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                                           autocomplete="on">
                                                    <p class="text-xs text-gray-400 mt-2">Ce champ est obligatoire*</p>
                                                </div>
                                                @if ($messages = $errors->get('name'))
                                                    <div class="text-sm text-red-600 space-y-1 mt-2">
                                                        <p>{{$messages[0]}}</p>
                                                    </div>
                                                @endif
                                            </fieldset>
                                            <fieldset>
                                                <label for="date"
                                                       class="block text-sm font-medium leading-6 text-gray-900">Date<span class="text-red-500">*</span></label>
                                                <div class="mt-2">
                                                    <input wire:model="date" type="date" name="date"
                                                           id="date"
                                                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                                           autocomplete="on">
                                                    <p class="text-xs text-gray-400 mt-2">Ce champ est obligatoire*</p>
                                                </div>
                                                @if ($messages = $errors->get('date'))
                                                    <div class="text-sm text-red-600 space-y-1 mt-2">
                                                        <p>{{$messages[0]}}</p>
                                                    </div>
                                                @endif
                                            </fieldset>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-shrink-0 justify-end px-4 py-4 bg-white">
                                <button type="button"
                                        wire:click="closeCreateDrawer"
                                        class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                    Annuler
                                </button>
                                <button type="submit"
                                        class="ml-4 inline-flex justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                    Enregistrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <livewire:partials.delete-modal/>
</div>
