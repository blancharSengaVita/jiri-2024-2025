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
    $this->jiris = $this->user->jiris()->orderBy('updated_at')->get();
});

$openCreateDrawer = function () {
    $this->mount();
    $this->dispatch('openCreateJiriDrawer');
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
    on([
        'refreshComponent' => function (string $role = null, Jiri|null $jiri = null) {
            $this->mount();
        }
    ]);
};

$openDeleteModal = function (Jiri $jiri) {
    $this->dispatch('openDeleteModal', modelId: $jiri->id, modelName: 'App\Models\Jiri')->to('partials.delete-modal');
};

on(['refreshComponent' => function () {
    $this->mount();
}]);

on(['openDrawer' => function () {
    $this->openCreateDrawer();
}]);
?>

<div class="py-10 h-auto"
     x-data="{
    open: $wire.entangle('drawer'),
    deleteModal: $wire.entangle('deleteModal'),
    }"
>
    <x-slot name="h1">
        Liste des jiris
    </x-slot>
    <div class="h-auto">
        <p class="text-3xl font-bold leading-tight tracking-tight text-gray-900">Liste des jiris</p>
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <p class="mt-2 text-sm text-gray-700">La liste de tout vos jiris</p>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                <button wire:click="openCreateDrawer" type="button"
                        class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    Ajouter un jiri
                </button>
            </div>
        </div>
        @if(count($jiris))
            <ul role="list" class="divide-y divide-gray-100 bg-white border mt-4 shadow-sm ring-1 ring-gray-900/5">
                @foreach($jiris as $jiri)
                    <livewire:partials.jiri-item :$jiri :key="$jiri->id"/>
                @endforeach
            </ul>
        @else
            <div class="mt-10 flex items-center text-center justify-center">
                <div class="">
                    <svg class="mx-auto size-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/>
                    </svg>
                    <p class="mt-2 text-sm font-semibold text-gray-900">Aucun jiri créé</p>
                    <p class="mt-1 text-sm text-gray-500">Vous pouvez créer un jiri ici</p>
                    <div class="mt-6">
                        <button wire:click="openCreateDrawer" type="button" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            <svg class="-ml-0.5 mr-1.5 size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/>
                            </svg>
                            Nouveau jiri
                        </button>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        </svg>

                    </div>
                </div>
            </div>
        @endif
    </div>
    <livewire:partials.delete-modal/>
    <livewire:partials.jiris-drawers/>
</div>
