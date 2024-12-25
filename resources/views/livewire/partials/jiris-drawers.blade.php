<?php

use App\Models\Attendance;
use App\Models\Contact;
use App\Models\Jiri;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Masmerise\Toaster\Toaster;
use Intervention\Image\Laravel\Facades\Image;
use function Livewire\Volt\{layout, mount, rules, state, usesFileUploads, on};

usesFileUploads();
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
});

$closeCreateDrawer = function () {
    $this->mount();
    $this->drawer = false;
};

$create = function () {
    $this->mount();
    $this->resetValidation();
    $this->drawer = true;
    $this->name = '';
    $this->date = '';
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
    $this->dispatch('refreshComponent');
};

on([
    'refreshComponent' => function (string $role = null, Jiri|null $jiri = null) {
        $this->mount();
    },
    'openCreateJiriDrawer' => function (string $role = null, Jiri|null $jiri = null) {
        $this->create();
    },
    'openEditContactDrawer' => function (Jiri $jiri) {
        $this->edit($jiri);
    },
]);
?>

<div x-cloak
     x-show="open"
     class="py-10"
     x-data="{
    open: $wire.entangle('drawer'),
    }"
>
    <div
        x-trap.inert="open"
        @keydown.escape="open = false"
        x-cloak
        x-show="open"
        {{--         :class="open ? 'relative' : 'hidden'"--}}
        class="z-50" aria-labelledby="slide-over-title" role="dialog"
        aria-modal="true">
        <!-- Background backdrop, show/hide based on slide-over state. -->
        {{--        Entering: "transform transition ease-in-out duration-500 sm:duration-700"--}}
        {{--        From: "translate-x-full"--}}
        {{--        To: "translate-x-0"--}}
        {{--        Leaving: "transform transition ease-in-out duration-500 sm:duration-700"--}}
        {{--        From: "translate-x-0"--}}
        {{--        To: "translate-x-full"--}}
        <div class="fixed inset-0"></div>

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
    </div>
</div>
