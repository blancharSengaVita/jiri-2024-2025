<?php

use App\Models\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Masmerise\Toaster\Toaster;
use function Livewire\Volt\{layout, mount, rules, state, on};

layout('layouts.app');

state([
    'drawer',
    'projects',
    'user',
    'deleteModal',
]);

mount(function () {
    $this->drawer = false;
    $this->user = Auth::user()->load('projects');
    $this->projects = $this->user->projects()->orderBy('name')->get();
});

$openCreateDrawer = function () {
    $this->dispatch('openDrawer')->to('partials.projects-drawers');
};

$edit = function(Project $project) {
    $this->dispatch('editThis',  project: $project)->to('partials.projects-drawers');
};

$openDeleteModal = function (Project $project) {
    $this->dispatch('openDeleteModal', modelId: $project->id, modelName: 'App\Models\Project')->to('partials.delete-modal');
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

    <h1 class="text-3xl font-bold leading-tight tracking-tight text-gray-900">Liste des projets</h1>
    <div>
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <p class="mt-2 text-sm text-gray-700">La liste de tout vos projets</p>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                <button wire:click="openCreateDrawer" type="button"
                        class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    Ajouter un projet
                </button>
            </div>
        </div>
        <ul role="list" class="divide-y divide-gray-100 bg-white border mt-4 shadow-sm ring-1 ring-gray-900/5">
            @foreach($projects as $project)
                <li class="flex items-center justify-between gap-x-6 py-5 p-4">
                    <div class="min-w-0">
                        <div class="flex items-start gap-x-3">
                            <p class="text-sm/6 font-semibold text-gray-900">{{$project->name}}</p>
                        </div>
                    </div>
                    <div class="flex flex-none items-center gap-x-4">
                        <button href="#"
                                wire:click="edit({{$project}})"
                                class="hidden rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:block">
                            Voir
                            le projet<span class="sr-only">, </span>
                        </button>
                        <button wire:click="openDeleteModal({{$project}})"
                                type="button"
                                class="rounded-md bg-indigo-600 px-2.5 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            Supprimer
                        </button>
                    </div>

                </li>
            @endforeach
        </ul>
    </div>
    <livewire:partials.projects-drawers/>
    <livewire:partials.delete-modal/>
</div>
