<?php

use App\Models\Attendance;
use App\Models\Contact;
use App\Models\Jiri;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, computed, mount, on};
use \App\Models\Project;
use \App\Models\Duties;
use Masmerise\Toaster\Toaster;

state([
    'jiri',
    'id',
    'attendances',
    'search' => '',
    'user',
    'modelName',
    'duties',
]);

mount(function (Jiri $jiri) {
    $this->user = Auth::user();
//	$this->projects = $this->user->projects()->get();
    $this->modelName = 'projet';
    $this->jiri = $jiri;
    $this->id = $jiri->id;
    $this->duties = new Collection();
    foreach ($this->jiri->duties()->get() as $duty) {
        $this->duties->push(collect(['name' => $duty->project->name, 'weighting' => $duty->weighting, 'project_id' => $duty->project->id]));
    }
});

$weightingSum = computed(function () {
	$sum = 0;
    foreach ($this->duties as $duty) {
        $sum += (int)$duty['weighting'];
    }
	return $sum;
});

$weightingIsEmpty = computed(function () {
    foreach ($this->duties as $duty) {
        if ($duty['weighting'] === null | $duty['weighting'] === ''){
			return true;
        }
    }
    return false;
});

$filteredProjects = computed(function () {
    return auth()
        ->user()
        ?->projects()
        ->where('name', 'like', '%' . $this->search . '%')
        ->whereDoesntHave('duties', function ($query) {
            $query->where('jiri_id', $this->jiri->id);
        })
        ->get();
});


$projects = computed(function () {
    return Project::whereHas('duties', function ($query) {
        $query->where('jiri_id', $this->jiri->id);
    })
        ->get();
});

$addToJiri = function (Project $project) {
    $duty = Duties::updateOrInsert([
        'jiri_id' => $this->jiri->id,
        'project_id' => $project->id,
    ]);
    unset($this->projects);
    $this->mount($this->jiri);
};

$deleteFromJiri = function (Project $project) {
    Duties::where('jiri_id', $this->jiri->id)
        ->where('project_id', $project->id)
        ->delete();
    $this->mount($this->jiri);
};

$create = function () {
    $this->dispatch('openCreateProjectDrawer', jiri: $this->jiri)->to('partials.projects-drawers');
};

$edit = function (Project $project) {
    $this->dispatch('openEditProjectDrawer', jiri: $this->jiri, project: $project)->to('partials.projects-drawers');
};

$save = function () {
    foreach ($this->duties as $duty) {
        $currentDuty = Duties::where('jiri_id', $this->jiri->id)
            ->where('project_id', $duty['project_id'])->first();
        $currentDuty->weighting = $duty['weighting'] ?: null;
        $currentDuty->save();
    }

    Toaster::success('Changement enregistré');

    $this->mount($this->jiri);
};

$cancel = function (){
    $this->mount($this->jiri);
};

on(['refreshComponent' => function () {
    $this->mount($this->jiri);
}]);
?>
<div>
    <div class="bg-white border mt-4 shadow-sm ring-1 ring-gray-900/5 p-4">
        <h2 class="text-base/7 font-semibold text-gray-900"> {{Str::title($modelName)}}s</h2>
        <p class="mt-1 mb-2 text-sm/6 text-gray-500">Ajouter ou retirer des {{$modelName}}s aux jiris.</p>
        <div class="flex gap-x-4 items-center mb-2">
            <div
                class="w-auto">
                <label
                    for="contact_search"
                    class="sr-only block text-sm font-medium leading-6 text-gray-900">
                    Recherche de projets</label>
                <div class="relative"
                     x-data="{
                     isFocused: false,
                     blurTimeout: null,
                     }"
                >
                    <input
                        autocomplete="off"
                        @focus="clearTimeout(blurTimeout); isFocused = true"
                        @blur="blurTimeout = setTimeout(() => { isFocused = false }, 200)"
                        wire:model.live="search"
                        id="contact_search" type="search"
                        class="inputSearch w-80 rounded-md border-0 bg-white py-1.5 pl-8 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                        role="combobox" aria-controls="options" aria-expanded="false">
                    <div
                        class="absolute inset-y-0 left-0 flex items-center rounded-r-md px-2 focus:outline-none">
                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                             aria-hidden="true">
                            <path fill-rule="evenodd"
                                  d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z"
                                  clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <ul
                        x-data="{
                                                                                    searchValue: $wire.entangle('search'),
                                                                                    }"
                        x-show="isFocused"
                        class="absolute z-10 mt-1 max-h-56 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
                        id="options" role="listbox">
                        <!--
                          Combobox option, manage highlight styles based on mouseenter/mouseleave and keyboard navigation.

                          Active: "text-white bg-indigo-600", Not Active: "text-gray-900"
                        -->
                        @if(!count($this->filteredProjects) && $this->search !== '' )
                            <li class="inputSearch relative cursor-default select-none py-2 pl-3 pr-9 text-gray-900"
                                id="option-0" role="option" tabindex="-1">
                                <p>Aucun résultat</p>
                            </li>
                        @elseif(!count($this->filteredProjects) && $this->search === '')
                            <li class="inputSearch relative cursor-default select-none py-2 pl-3 pr-9 text-gray-900 flex gap-x-2 items-center"
                                id="option-0" role="option" tabindex="-1">
                                <svg class="text-red-500 sm:size-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon">
                                    <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14ZM8 4a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-1.5 0v-3A.75.75 0 0 1 8 4Zm0 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
                                </svg>
                                <p>Tous les projects ont déjà été ajouté</p>
                            </li>
                        @endif
                        @foreach($this->filteredProjects as $project)
                            <li
                                tabindex="0"
                                @focus="clearTimeout(blurTimeout); isFocused = true"
                                @blur="blurTimeout = setTimeout(() => { isFocused = false }, 200)"
                                wire:key="project_search-{{$project->id}}"
                                wire:click="addToJiri({{$project->id}})"
                                x-data="{ isHovered: false }"
                                @keyup.enter="$wire.addToJiri({{$project->id}})"
                                @mouseenter="isHovered = true"
                                @mouseleave="isHovered = false"
                                :class="isHovered ? 'text-white bg-indigo-600' : 'text-gray-900'"
                                class="relative cursor-default select-none py-2 pl-3 pr-9 text-gray-900"
                                id="option-0" role="option"
                            >
                                <div class="flex items-center">
                                    <!-- Selected: "font-semibold" -->
                                    <span class="ml-3 truncate">{{ $project->name }}</span>
                                </div>

                                <!--
                                  Checkmark, only display for selected option.

                                  Active: "text-white", Not Active: "text-indigo-600"
                                -->
                                <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600">
                                    <svg x-show="isHovered"
                                         class="h-5 w-5 text-indigo-500 "
                                         viewBox="0 0 24 24"
                                         stroke-width="2px"
                                         stroke="white" fill="none"
                                         aria-hidden="true">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="M18 7.5v3m0 0v3m0-3pm-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z"/>
                                    </svg>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>


        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4
                my-6 border-t border-gray-100 pt-6
                text-sm/6
                ">
            @foreach($this->projects as $project)
                <div class="flex justify-between gap-x-4 py-6 px-4 rounded-xl border border-gray-200">
                    <div class="flex items-center  gap-x-2">
                        <div class="font-medium text-gray-900">{{$project->name}}</div>
                    </div>
                    <div class="flex items-center gap-x-2">
                        <button wire:click="deleteFromJiri({{$project}})" type="button"
                                class="font-semibold text-indigo-600 hover:text-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                 stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                            </svg>
                        </button>
                        <button wire:click="edit({{$project}})" type="button"
                                class="font-semibold text-indigo-600 hover:text-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                 stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex border-t border-gray-100 pt-6">
            <button wire:click="create" type="button"
                    class="text-sm/6 font-semibold text-indigo-600 hover:text-indigo-500"><span
                    aria-hidden="true">+</span> Ajouter un {{$modelName}} qui n'existe pas dans vos projets
            </button>
        </div>
    </div>
    <form
        wire:submit.prevent="save"
        class=" bg-white border mt-4 shadow-sm ring-1 ring-gray-900/5 p-4">
        <h2 class="text-base/7 font-semibold text-gray-900">Pondérations</h2>
        <p class="mt-1 text-sm/6 text-gray-500 mb-2">Ajouter une pondération aux projets. Vous pouvez enregistrer des pondérations dont la somme n'est pas 100 ou avec des pondérations vides mais, vous ne pourrez pas lancer le jiri.</p>

        <div class="flex items-center gap-x-2">
            @if($this->weightingSum === 100)
                <svg  class="text-green-600 sm:size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                </svg>
                <p class="text-sm text-green-600" id="email-error"> La somme de la pondération est à 100</p>
            @else
                <svg class="text-red-500 sm:size-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon">
                    <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14ZM8 4a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-1.5 0v-3A.75.75 0 0 1 8 4Zm0 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm text-red-600" id="email-error"> La somme de la pondération est à {{ $this->weightingSum }} au lieu de 100 </p>
            @endif
        </div>

        <div class="flex items-center gap-x-2">
            @if($this->weightingIsEmpty)
                <svg class="text-red-500 sm:size-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon">
                    <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14ZM8 4a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-1.5 0v-3A.75.75 0 0 1 8 4Zm0 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm text-red-600" id="email-error">Tous les projets doivent avoir une pondération</p>
            @else
                <svg  class="text-green-600 sm:size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                </svg>
                <p class="text-sm text-green-600" id="email-error">Tous les projets ont une pondération</p>
            @endif
        </div>



        <div class="flex items-center gap-x-2">

        </div>

        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4 my-4 border-t border-gray-100 pt-2
                text-sm/6">
            @foreach($duties as $key => $duty)
                <fieldset class="text-sm/6">
                    <label for="name"
                           class="block text-sm/6 font-medium text-gray-900 sm:pt-1.5">{{$duty['name']}}</label>
                    <input type="number"
                           name="name"
                           id="name"
                           autocomplete="given-name"
                           wire:model.live="duties.{{$key}}.weighting"
                           class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm/6">
                    @if ($messages = $errors->get('duties'.$key.'weighting'))
                        <div class="text-sm text-red-600 space-y-1 mt-2">
                            <p>{{$messages[0]}}</p>
                        </div>
                    @endif
                </fieldset>
            @endforeach
        </div>

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

</div>
@script
<script>
    document.addEventListener('keydown', function (event) {
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            const focusableElements = Array.from(document.querySelectorAll('input, button, select, textarea, a[href], [tabindex]:not([tabindex="-1"])'));
            const currentIndex = focusableElements.indexOf(document.activeElement);

            const nextIndex = event.key === 'ArrowDown'
                ? (currentIndex + 1) % focusableElements.length
                : (currentIndex - 1 + focusableElements.length) % focusableElements.length;

            focusableElements[nextIndex].focus();
        }
    });
</script>
@endscript

@script
<script>
    const container = document.getElementById('drawerContainer');
    scrollToBottom();

    function scrollToBottom () {
        container.scrollTop = container.scrollHeight;
    }

    document.addEventListener('DOMContentLoaded', function () {
        scrollToBottom(); // Scroll initial au chargement de la page
    });

    $wire.on('message-sent', () => {
        setTimeout(() => scrollToBottom(), 1);
    });
</script>
@endscript
