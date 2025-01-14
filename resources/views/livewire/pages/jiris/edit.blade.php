<?php

use App\Models\Attendance;
use App\Models\Contact;
use App\Models\Jiri;
use App\Models\Duties;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Masmerise\Toaster\Toaster;
use App\Exceptions\StartJiriException;
use App\Jobs\SendJiriLaunchedEmails;
use function Livewire\Volt\{layout, mount, rules, state, computed, usesFileUploads, on};

usesFileUploads();
layout('layouts.app');

state([
    'drawer',
    'jiri',
    'students',
    'evaluators',
    'user',
    'date',
    'name' => '',
    'id',
    'deleteModal',
    'contacts',
    'contact',
    'id',
]);

rules(fn() => [
    'name' => 'required',
    'date' => 'required|date',
])->messages([
    'name.required' => 'Le nom est obligatoire.',
    'date.required' => 'La date est obligatoire.',
])->attributes([
]);

mount(function (Jiri $jiri) {
    $this->students = $jiri->students;
    $this->evaluators = $jiri->evaluators;
    $this->user = Auth::user();
    $this->jiri = $jiri;
    $this->id = $jiri->id;
    $this->date = Carbon::parse($jiri->starting_at)->format('Y-m-d');
    $this->name = $jiri->name;
});

$cancel = function () {
    $this->resetValidation();
    $this->mount($this->jiri);
};


$save = function () {
    try {
        $this->validate();
    } catch (\Illuminate\Validation\ValidationException $e) {
        throw $e;
    }


    Jiri::updateOrCreate([
        'user_id' => Auth::id(),
        'id' => $this->jiri->id,
    ],
        [
            'name' => $this->name,
            'starting_at' => $this->date,
        ]);

    $this->jiri->starting_at = Carbon::parse($this->date)->format('Y-m-d');
    $this->jiri->name = $this->name;

    if ($this->id === 0) {
        Toaster::success('Jiri ajouté avec succès');
    }

    if ($this->id !== 0) {
        Toaster::success('Jiri modifiée avec succès');
    }
    $this->mount($this->jiri);
};


$openDeleteModal = function (Jiri $jiri) {
    $this->dispatch('openDeleteModal', modelId: $jiri->id, modelName: 'App\Models\Jiri')->to('partials.delete-modal');
};

on(['refreshComponent' => function () {
    $this->mount($this->jiri);
}]);
?>

<div class="py-10"
     x-data="{
    open: $wire.entangle('drawer'),
    deleteModal: $wire.entangle('deleteModal'),
    }"
>
    <x-slot name="h1">
        @if(session('currentJiri') && session('currentJiri')->id === $jiri->id && session('currentJiri')->status === Jiri::STATUS_IN_PROGRESS)
            {{$jiri->name}} (Jiri en cours)
        @elseif(session('currentJiri') && session('currentJiri')->id === $jiri->id && session('currentJiri')->status === Jiri::STATUS_ON_PAUSE)
            {{$jiri->name}} (Jiri en cours)
        @else
            {{$jiri->name}}
        @endif
    </x-slot>
    <div class="flex gap-x-2 items-center mb-2 mt-2">
        @if(session('currentJiri') && session('currentJiri')->id === $jiri->id && session('currentJiri')->status === Jiri::STATUS_IN_PROGRESS)
            <p class="text-3xl font-bold leading-tight tracking-tight text-gray-900">{{$jiri->name}} (Jiri en
                cours)</p>
            <div class="w-4 h-4 bg-red-500 rounded-full animate-pulse"></div>
        @elseif(session('currentJiri') && session('currentJiri')->id === $jiri->id && session('currentJiri')->status === Jiri::STATUS_ON_PAUSE)
            <p class="text-3xl font-bold leading-tight tracking-tight text-gray-900">{{$jiri->name}} (Jiri en
                cours)</p>
            <div class="w-4 h-4 bg-yellow-500 rounded-full animate-pulse"></div>
        @else
            <p class="text-3xl font-bold leading-tight tracking-tight text-gray-900">{{$jiri->name}}</p>
        @endif
    </div>
    <div>
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <p class="mt-2 text-sm text-gray-700">Toutes les informations sur {{$jiri->name}}</p>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none flex gap-x-2">
                @if(session('currentJiri') && session('currentJiri')->id === $jiri->id)
                    @if(session('currentJiri')->canBePaused())
                        <livewire:partials.pausejiri :jiri="session('currentJiri')"/>
                    @endif
                    @if(session('currentJiri')->canBeRelaunched())
                        <livewire:partials.restartjiri :jiri="session('currentJiri')"/>
                    @endif
                    @if(session('currentJiri')->canBeStopped())
                        <livewire:partials.stopjiri :jiri="session('currentJiri')"/>
                    @endif
                @endif
                @if(!session('currentJiri') || session('currentJiri')->id !== $jiri->id)
                    <livewire:partials.startjiri :jiri="$jiri"/>
                    <button wire:click="openDeleteModal({{ $jiri }})" type="button"
                            class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Supprimer le jiri
                    </button>
                @endif
            </div>
        </div>

        <form wire:submit.prevent="save" class=" bg-white border mt-4 shadow-sm ring-1 ring-gray-900/5 p-4">
            <h2 class="text-base/7 font-semibold text-gray-900">Information général</h2>
            <p class="mt-1 text-sm/6 text-gray-500">Nom et date du début du jiri</p>

            <fieldset class="mt-6 pt-2 border-t border-gray-200 text-sm/6">
                <label for="name"
                       class="mt-2 block text-sm/6 font-medium text-gray-900 sm:pt-1.5">Nom</label>
                <input type="text"
                       x-init="$el.focus()"
                       autofocus
                       name="name"
                       id="name"
                       autocomplete="given-name"
                       wire:model="name"
                       class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm/6">
                @if ($messages = $errors->get('name'))
                    <div class="text-sm text-red-600 space-y-1 mt-2">
                        <p>{{$messages[0]}}</p>
                    </div>
                @endif
            </fieldset>
            <fieldset class="mt-6 pt-2 divide-y divide-gray-100 border-t border-gray-200 text-sm/6">
                <label for="date"
                       class="mt-2 block text-sm/6 font-medium text-gray-900 sm:pt-1.5">Date</label>
                <input type="date"
                       name="date"
                       id="date"
                       wire:model="date"
                       autocomplete="given-name"
                       class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm/6">
                @if ($messages = $errors->get('date'))
                    <div class="text-sm text-red-600 space-y-1 mt-2">
                        <p>{{$messages[0]}}</p>
                    </div>
                @endif
            </fieldset>
            <div class="flex justify-end mt-6 pt-4 divide-y divide-gray-100 border-t border-gray-200 text-sm/6">
                <div class="mt-4 sm:ml-16 sm:mt-0 flex">
                    <button type="button"
                            wire:click="cancel"
                            class="rounded-md bg-white px-3 py-2 mr-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="submit"
                            class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Enregistrer
                    </button>
                </div>
            </div>
        </form>
        <livewire:partials.add-contacts-to-jiri :jiri="$jiri" :role="'student'"/>
        <livewire:partials.add-contacts-to-jiri :jiri="$jiri" :role="'evaluator'"/>
        <livewire:partials.add-projects-to-jiri :jiri="$jiri"/>
    </div>
    <livewire:partials.contacts-drawers/>
    <livewire:partials.delete-modal/>
    <livewire:partials.projects-drawers/>
</div>
