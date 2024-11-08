<?php

use App\Models\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Masmerise\Toaster\Toaster;
use function Livewire\Volt\{layout, mount, rules, state};

layout('layouts.app');

state([
    'drawer',
    'projects',
    'project',
    'user',
    'name',
    'description',
    'linkInputs',
    'tasks',
    'id',
    'deleteModal',
]);

rules(fn() => [
    'name' => 'required',
    'description' => 'required',
    'linkInputs.*' => 'required',
    'tasks.*' => 'required',
])->messages([
    'name.required' => 'Le champ est obligatoire.',
    'description.required' => 'Le champ est obligatoire.',
    'linkInputs.*.required' => 'Le champ est obligatoire.',
    'tasks.*.required' => 'Le champ est obligatoire.',
])->attributes([
]);

mount(function () {
    $this->drawer = false;
    $this->user = Auth::user()->load('projects');
    $this->projects = $this->user->projects()->orderBy('name')->get();

    $this->id = 0;
    $this->name = '';
    $this->description = '';
    $this->linkInputs = new Collection();
    $this->tasks = new Collection();
});

$addLinkInput = function () {
    $this->linkInputs->push('');
};

$removeLinkInput = function ($key) {
    $this->linkInputs->pull($key);
};

$addTasks = function () {
    $this->tasks->push('');
};

$removeTasks = function ($key) {
    $this->tasks->pull($key);
};

$openCreateDrawer = function () {
    $this->name = '';
    $this->description = '';
    $this->linkInputs = new Collection();
    $this->tasks = new Collection();
    $this->drawer = true;
};

$closeCreateDrawer = function () {
    $this->drawer = false;
};

$edit = function (Project $project) {
    $this->name = $project->name;
    $this->description = $project->description;
    $this->linkInputs = collect(json_decode($project->links));
    $this->tasks = collect(json_decode($project->tasks));
    $this->drawer = true;
};

$save = function () {
    try {
        $this->validate();
    } catch (\Illuminate\Validation\ValidationException $e) {
        throw $e;
    }

    Project::updateOrCreate([
        'user_id' => Auth::id(),
        'id' => $this->id,
    ],
        [
            'name' => $this->name,
            'description' => $this->description,
            'links' => json_encode($this->linkInputs),
            'tasks' => json_encode($this->tasks),
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

$delete = function () {
    $this->project->delete();
    $this->deleteModal = false;
    Toaster::success('Projet supprimé avec succès');
    $this->mount();
};

$openDeleteModal = function (Project $project) {
    $this->deleteModal = true;
    $this->project = $project;
    $this->mount();
};

$closeDeleteModal = function () {
    $this->deleteModal = false;
    $this->mount();
};
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
    <div :class="open ? 'relative z-10' : 'hidden'" class="" aria-labelledby="slide-over-title" role="dialog"
         aria-modal="true">
        <!-- Background backdrop, show/hide based on slide-over state. -->
        <div class="fixed inset-0"></div>

        <div class="fixed inset-0 overflow-hidden">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full w-auto pl-10 sm:pl-16">
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
                                            Nouveau projet</h2>
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
                                        <p class="text-sm text-gray-400">Ajouter des informations pour votre projet</p>
                                    </div>
                                </div>
                                <div class="flex flex-1 flex-col justify-between">
                                    <div class="divide-y divide-gray-200 px-4 sm:px-6">
                                        <div class="space-y-6 pb-5 pt-6">
                                            <div>
                                                <label for="name"
                                                       class="block text-sm font-medium leading-6 text-gray-900">Nom du
                                                    projet</label>
                                                <div class="mt-2">
                                                    <input wire:model="name" type="text" name="name" id="name"
                                                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                                           autocomplete="on">
                                                    @if ($messages = $errors->get('name'))
                                                        <div class="text-sm text-red-600 space-y-1 mt-2">
                                                            <p>{{$messages[0]}}</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div>
                                                <label for="description"
                                                       class="block text-sm font-medium leading-6 text-gray-900">Description</label>
                                                <div class="mt-2">
                                                    <textarea wire:model="description" type="text" name="description"
                                                              id="description"
                                                              rows="10"
                                                              cols="40"
                                                              class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                                              autocomplete="on"></textarea>
                                                </div>
                                                @if ($messages = $errors->get('description'))
                                                    <div class="text-sm text-red-600 space-y-1 mt-2">
                                                        <p>{{$messages[0]}}</p>
                                                    </div>
                                                @endif
                                            </div>


                                            <div>
                                                {{ $linkInputs }}
                                                <label for="addLinks"
                                                       class="text-sm font-medium leading-6 text-gray-900 flex gap-1 ">Le
                                                    nom
                                                    des liens qui seront attribués aux projets
                                                    <button class="display-none create-project__input" id="addLinks"
                                                            type="button"
                                                            value="le nom des liens qui seront attribués aux projets"
                                                            wire:click="addLinkInput">
                                                        <svg class="create-project__svg addLink button__icon"
                                                             xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                             viewBox="0 0 16 16">
                                                            <path fill="currentColor" d="M14 7v1H8v6H7V8H1V7h6V1h1v6z"/>
                                                        </svg>
                                                    </button>
                                                </label>
                                                <div class="mt-2">
                                                    <ul>

                                                    </ul>
                                                    @foreach($linkInputs as $key => $input)
                                                        <div>
                                                            <input wire:model.live="linkInputs.{{$key}}" type="text"
                                                                   name="addLinks"
                                                                   id="addLinks"
                                                                   class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                                                   autocomplete="on">
                                                            <svg class=""
                                                                 wire:click="removeLinkInput({{$key}})"
                                                                 xmlns="http://www.w3.org/2000/svg" width="16"
                                                                 height="16" viewBox="0 0 16 16">
                                                                <path fill="currentColor" fill-rule="evenodd"
                                                                      d="m8 8.707l3.646 3.647l.708-.707L8.707 8l3.647-3.646l-.707-.708L8 7.293L4.354 3.646l-.707.708L7.293 8l-3.646 3.646l.707.708z"
                                                                      clip-rule="evenodd"/>
                                                            </svg>
                                                        </div>
                                                        @if ($messages = $errors->get('linkInputs.' .$key))
                                                            <div class="text-sm text-red-600 space-y-1 mt-2">
                                                                <p>{{$messages[0]}}</p>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
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
    <div x-cloak x-show="deleteModal" class="relative z-50" aria-labelledby="modal-title" role="dialog"
         aria-modal="true">
        <!--
          Background backdrop, show/hide based on modal state.

          Entering: "ease-out duration-300"
            From: "opacity-0"
            To: "opacity-100"
          Leaving: "ease-in duration-200"
            From: "opacity-100"
            To: "opacity-0"
        -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-40 transition-opacity"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <!--
                  Modal panel, show/hide based on modal state.

                  Entering: "ease-out duration-300"
                    From: "opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    To: "opacity-100 translate-y-0 sm:scale-100"
                  Leaving: "ease-in duration-200"
                    From: "opacity-100 translate-y-0 sm:scale-100"
                    To: "opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                -->
                <div @click.away="deleteModal = false"
                     class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                 stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">Supprimer une
                                langue</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Êtes-vous sûr de vouloir supprimer ce projet ?
                                    Le projet sera définitivement supprimée de nos serveurs. Cette action ne peut
                                    être annulée.</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <button wire:click="delete" type="button"
                                class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">
                            Supprimer
                        </button>
                        <button wire:click="closeDeleteModal" type="button"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                            Annuler
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
