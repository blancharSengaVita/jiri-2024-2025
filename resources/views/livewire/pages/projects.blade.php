<?php

use App\Models\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Masmerise\Toaster\Toaster;
use function Livewire\Volt\{layout, mount, rules, state, on, with, usesPagination};

usesPagination();

layout('layouts.app');

state([
    'drawer',
//    'projects',
    'user',
    'deleteModal',
]);

mount(function () {
    $this->drawer = false;
    $this->user = Auth::user()->load('projects');
//    $this->projects = $this->user->projects()->orderBy('name')->get();
});

with(fn() => ['projects' => $this->user->projects()->orderBy('updated_at', 'desc')->paginate(10)]);

$create = function () {
    $this->dispatch('openCreateProjectDrawer')->to('partials.projects-drawers');
};

$edit = function (Project $project) {
    $this->dispatch('openEditProjectDrawer', project: $project)->to('partials.projects-drawers');
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
                <button wire:click="create" type="button"
                        class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    Ajouter un projet
                </button>
            </div>
        </div>
        @if(count($projects))
            <ul role="list" class="divide-y divide-gray-100 bg-white border mt-4 shadow-sm ring-1 ring-gray-900/5">
                @foreach($projects as $project)
                    <livewire:partials.project-item :$project :key="'project'.$project->id"/>
                @endforeach
            </ul>
            {{ $projects->links() }}
        @else
            <div class="mt-10 flex items-center text-center justify-center">
                <div class="">
                    <svg class="mx-auto size-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">Aucun projet créé</h3>
                    <p class="mt-1 text-sm text-gray-500">Vous pouvez créer un projet ici</p>
                    <div class="mt-6">
                        <button wire:click="create" type="button" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            <svg class="-ml-0.5 mr-1.5 size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/>
                            </svg>
                            Nouveau projet
                        </button>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        </svg>

                    </div>
                </div>
            </div>
        @endif()
    </div>
    <livewire:partials.projects-drawers/>
    <livewire:partials.delete-modal/>
</div>
