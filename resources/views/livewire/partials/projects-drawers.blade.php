<?php

use App\Models\Project;
use App\Models\Duties;
use App\Models\Jiri;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Masmerise\Toaster\Toaster;
use function Livewire\Volt\{layout, mount, rules, state, on};

layout('layouts.app');

state([
	'drawer',
	'project',
	'user',
	'name',
	'description',
	'linkInputs',
	'linkInputId',
	'tasks',
	'taskId',
	'id',
	'deleteModal',
    'jiri' => null,
]);

rules(fn() => [
	'name' => 'required',
	'description' => 'required',
	'linkInputs.*' => 'sometimes',
	'tasks.*' => 'sometimes',
])->messages([
	'name.required' => 'Le champ est obligatoire.',
	'description.required' => 'Le champ est obligatoire.',
    'linkInputs.*.required' => 'Le champ est obligatoire.',
    'tasks.*.required' => 'Le champ est obligatoire.',
    'linkInputs.*.sometimes' => 'Le champ est obligatoire.',
    'tasks.*.sometimes' => 'Le champ est obligatoire.',
])->attributes([
]);

mount(function () {
	$this->drawer = false;
	$this->user = Auth::user();
	$this->id = 0;
	$this->name = '';
	$this->description = '';
	$this->linkInputs = new Collection();
	$this->tasks = new Collection();
	$this->linkInputId = 0;
});

$addLinkInput = function () {
    $this->linkInputId++;
	$this->linkInputs->put($this->linkInputId, '');
    $this->dispatch('scroll-to-bottom')->self();
};

$removeLinkInput = function ($key) {
	$this->linkInputs->pull($key);
};

$addTasks = function () {
    $this->taskId++;
	$this->tasks->put($this->taskId,'');
    $this->dispatch('scroll-to-bottom-2')->self();
};

$removeTasks = function ($key) {
	$this->tasks->pull($key);
};

$create = function () {
	$this->resetValidation();
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
	$this->id = $project->id;
	$this->name = $project->name;
	$this->description = $project->description;
	$this->linkInputs = collect(json_decode($project->links));
	$this->tasks = collect(json_decode($project->tasks));
	$this->drawer = true;
};

$deleteNullValueFromCollection = function (Collection $myCollection){
    return $myCollection->filter(function ($value) {
        return !empty($value); // Vérifie si la valeur n'est pas vide
    });
};

$save = function () {
	try {
		$this->validate();
	} catch (\Illuminate\Validation\ValidationException $e) {
		throw $e;
	}

    $this->linkInputs = $this->deleteNullValueFromCollection($this->linkInputs);
    $this->tasks = $this->deleteNullValueFromCollection($this->tasks);

	$newProject = Project::updateOrCreate([
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
		Toaster::success('Donnée ajouté avec succès');
	}

	if ($this->id !== 0) {
		Toaster::success('Donnée modifiée avec succès');
	}

	if ($this->jiri !== null) {
		Duties::updateOrInsert([
			'jiri_id' => $this->jiri->id,
			'project_id' => $newProject->id,
		]);
    }

	$this->dispatch('refreshComponent');
};

on([
	'openCreateProjectDrawer' => function (Jiri $jiri = null) {
		$this->create();
        if ($jiri->id) {
            $this->jiri = $jiri;
        }
	},
	'openEditProjectDrawer' => function (Project $project) {
        $this->edit($project);
	},
]);
?>

<div class="py-10"
     x-data="{
    open: $wire.entangle('drawer'),
    }"
>
    <div
        x-cloak
        x-show="open"
         x-trap.inert="open"
         @keydown.escape="open = false"
         :class="open ? 'relative z-10' : 'hidden'"
         class="z-50" aria-labelledby="slide-over-title" role="dialog"
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
                     x-transition:leave-end="translate-x-full"
                >
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
                            @mousedown.outside="open = false "
                            enctype="multipart/form-data"
                            class="flex h-full flex-col divide-y divide-gray-200 bg-white shadow-xl"
                        >
                            <div class="h-0 flex-1 overflow-y-scroll overflow-hidden max-h-full" id="drawerContainer">
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
                                                     stroke="currentColor" data-slot="icon">
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
                                            <fieldset>
                                                <label for="name"
                                                       class="block text-sm font-medium leading-6 text-gray-900">Nom du
                                                    projet<span class="text-red-500">*</span></label>
                                                <div class="mt-2">
                                                    <input x-init="$el.focus()" wire:model="name" type="text" name="name" id="name" autofocus
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
                                                <label for="description"
                                                       class="block text-sm font-medium leading-6 text-gray-900">Description<span
                                                        class="text-red-500">*</span></label>
                                                <div class="mt-2">
                                                    <textarea wire:model="description" type="text" name="description"
                                                              id="description"
                                                              rows="10"
                                                              cols="40"
                                                              class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                                              autocomplete="on"></textarea>
                                                    <p class="text-xs text-gray-400 mt-2">Ce champ est obligatoire*</p>
                                                </div>
                                                @if ($messages = $errors->get('description'))
                                                    <div class="text-sm text-red-600 space-y-1 mt-2">
                                                        <p>{{$messages[0]}}</p>
                                                    </div>
                                                @endif
                                            </fieldset>


                                            <fieldset
                                                id="linkContainer"
                                                class="relative"
                                                x-data="{
                                            tooltip: false
                                            }">
                                                <div
                                                    class="relative">
                                                    <div x-cloak
                                                         x-show="tooltip"
                                                         class="absolute z-50 flex items-start gap-2 bottom-full mb-4 bg-white p-4 rounded-xl border border-gray-200 text-sm shadow">
                                                        <svg
                                                            @click="tooltip = !tooltip;"
                                                            @mouseenter="tooltip = true;"
                                                            @mouseleave="tooltip = false;"
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            class="w-24 cursor-pointer hover:bg-gray-200 rounded"
                                                            fill="none"
                                                            viewBox="0 0 24 24"
                                                            stroke-width="1.5"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round"
                                                                  stroke-linejoin="round"
                                                                  d="M6 18 18 6M6 6l12 12"/>
                                                        </svg>
                                                        <small class="text-sm">Indiquez les différents types de liens
                                                            que les étudiants devront fournir pour compléter ce projet.
                                                            Chaque lien correspond à une ressource ou un livrable
                                                            attendu (par exemple : lien du dépôt du code source
                                                            (github), lien de la
                                                            maquette, lien du site en production, etc.).</small>
                                                    </div>
                                                </div>
                                                <div class="flex">
                                                    <svg @click="tooltip = true;"
                                                         @mouseenter="tooltip = true;"
                                                         @mouseleave="tooltip = false;"
                                                         xmlns="http://www.w3.org/2000/svg"
                                                         fill="none"
                                                         viewBox="0 0 24 24"
                                                         stroke-width="1.5"
                                                         stroke="currentColor"
                                                         class="size-6 mr-1 cursor-pointer hover:bg-gray-200 rounded">
                                                        <path stroke-linecap="round"
                                                              stroke-linejoin="round"
                                                              d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
                                                    </svg>
                                                    <label for="addLinks"
                                                           class="text-sm font-medium leading-6 text-gray-900 flex gap-x-0.5 items-center">
                                                        Liens requis
                                                        <button id="addLinks"
                                                                type="button"
                                                                value="L'intitulé des liens qui seront attribués aux projets"
                                                                wire:click="addLinkInput"
                                                                class="flex items-center justify-center rounded bg-indigo-600 ml-1 px-2 py-1 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                                                        >Ajouter
                                                        </button>
                                                    </label>
                                                </div>
                                                <div>
                                                    @foreach($linkInputs as $key => $input)
                                                        <div class="flex items-center mt-4">
                                                            <input wire:model.live="linkInputs.{{$key}}" :key="{{$key}}" type="text"
                                                                   name="addLinks"
                                                                   id="addLinks-{{$key}}"
                                                                   placeholder="Lien du projet"
                                                                   class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                                                   autocomplete>
                                                            <svg width="12"
                                                                 height="12"
                                                                 wire:click="removeLinkInput({{$key}})"
                                                                 xmlns="http://www.w3.org/2000/svg"
                                                                 class="cursor-pointer hover:bg-gray-200 rounded h-6 w-6 shrink-0 ml-1"
                                                                 fill="none"
                                                                 viewBox="0 0 24 24"
                                                                 stroke-width="1.5"
                                                                 stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                      d="M6 18 18 6M6 6l12 12"/>
                                                            </svg>
                                                        </div>
                                                        <p class="text-xs text-gray-400 mt-1 mb-4">Ce champ est
                                                            obligatoire*</p>
                                                        @if ($messages = $errors->get('linkInputs.' .$key))
                                                            <div class="text-sm text-red-600 space-y-1 mt-2">
                                                                <p>{{$messages[0]}}</p>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </fieldset>
                                            <fieldset
                                                id="taskContainer"
                                                class="relative"
                                                      x-data="{
                                            tooltip: false
                                            }">
                                                <div
                                                    class="relative">
                                                    <div x-cloak
                                                         x-show="tooltip"
                                                         class="absolute z-50 flex items-start gap-2 bottom-full mb-4 bg-white p-4 rounded-xl border border-gray-200 text-sm shadow">
                                                        <svg
                                                            @click="tooltip = !tooltip;"
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            class="text-base w-32 cursor-pointer hover:bg-gray-200 rounded"
                                                            fill="none"
                                                            viewBox="0 0 24 24"
                                                            stroke-width="1.5"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round"
                                                                  stroke-linejoin="round"
                                                                  d="M6 18 18 6M6 6l12 12"/>
                                                        </svg>
                                                        <small class="text-sm">Indiquez les compétences ou tâches
                                                            spécifiques que les étudiants devront réaliser dans le cadre
                                                            de ce projet. Chaque tâche correspond à une étape ou une
                                                            compétence clé nécessaire pour répondre aux attentes du
                                                            jury, par exemple :<i>Intégration (HTML, CSS, JS), Design,
                                                                Développement côté serveur, WordPress,
                                                                Déploiement</i></small>
                                                    </div>
                                                </div>
                                                <div class="flex">
                                                    <svg @click="tooltip = !tooltip;"
                                                         @mouseenter="tooltip = true;"
                                                         @mouseleave="tooltip = false;"
                                                         xmlns="http://www.w3.org/2000/svg"
                                                         fill="none"
                                                         viewBox="0 0 24 24"
                                                         stroke-width="1.5"
                                                         stroke="currentColor"
                                                         class="size-6 mr-1 cursor-pointer hover:bg-gray-200 rounded">
                                                        <path stroke-linecap="round"
                                                              stroke-linejoin="round"
                                                              d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
                                                    </svg>
                                                    <label for="tasks"
                                                           class="text-sm font-medium leading-6 text-gray-900 flex gap-x-0.5">
                                                        Les tâches attribuées au projet
                                                        <button id="tasks"
                                                                type="button"
                                                                value="les tâches qui seront attribués aux projets"
                                                                wire:click="addTasks"
                                                                class="flex items-center justify-center">
                                                            <svg width="12"
                                                                 height="12"
                                                                 xmlns="http://www.w3.org/2000/svg"
                                                                 class="cursor-pointer hover:bg-gray-200 rounded h-6 w-6 shrink-0 ml-1 rotate-45"
                                                                 fill="none"
                                                                 viewBox="0 0 24 24"
                                                                 stroke-width="1.5"
                                                                 stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                      d="M6 18 18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                    </label>
                                                </div>
                                                <div>
                                                    @foreach($tasks as $key => $task)
                                                        <div class="flex items-center mt-4">
                                                            <input wire:model="tasks.{{$key}}" type="text"
                                                                   name="addTask"
                                                                   id="addTask.{{$key}}"
                                                                   placeholder="Intégration (HTML, CSS, JS)"
                                                                   class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                                                   autocomplete="on">
                                                            <svg
                                                                wire:click="removeTasks({{$key}})"
                                                                width="12"
                                                                height="12"
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                class="cursor-pointer hover:bg-gray-200 rounded h-6 w-6 shrink-0 ml-1"
                                                                fill="none"
                                                                viewBox="0 0 24 24"
                                                                stroke-width="1.5"
                                                                stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                      d="M6 18 18 6M6 6l12 12"/>
                                                            </svg>
                                                        </div>
                                                        <p class="text-xs text-gray-400 mt-2">Ce champ est
                                                            obligatoire*</p>
                                                        @if ($messages = $errors->get('tasks.' .$key))
                                                            <div class="text-sm text-red-600 space-y-1 mt-2">
                                                                <p>{{$messages[0]}}</p>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
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
@script
<script>
    const linkContainer = document.getElementById('linkContainer');
    const taskContainer = document.getElementById('taskContainer');
    const container = document.getElementById('drawerContainer');
    scrollToBottom(container);

    function scrollToBottom (element ,number) {
        console.log(container);
        console.log(taskContainer);
        console.log(linkContainer);
        container.scrollTop += element.clientHeight;
    }

    document.addEventListener('DOMContentLoaded', function () {
        scrollToBottom(linkContainer, linkContainer.scrollHeight); // Scroll initial au chargement de la page
        scrollToBottom(taskContainer,taskContainer.scrollHeight); // Scroll initial au chargement de la page
    });

    $wire.on('scroll-to-bottom', () => {
        setTimeout(() => scrollToBottom(linkContainer,linkContainer.scrollHeight), 1);
    });

    $wire.on('scroll-to-bottom-2', () => {
        setTimeout(() => scrollToBottom(taskContainer, taskContainer.scrollHeight), 1);
    });
</script>
@endscript
