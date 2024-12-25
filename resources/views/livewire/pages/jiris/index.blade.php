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
    <div class="h-auto">
        <h1 class="text-3xl font-bold leading-tight tracking-tight text-gray-900">Liste des jiris</h1>
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
{{--                @foreach($jiris as $jiri)--}}
{{--                    <li class="flex items-center justify-between gap-x-6 py-5 p-4"--}}
{{--                        x-data="{tooltip: false}">--}}
{{--                        <div class="min-w-0 flex gap-x-2">--}}
{{--                            <div--}}
{{--                                    class="relative">--}}
{{--                                <div x-cloak--}}
{{--                                     x-show="tooltip"--}}
{{--                                     class="absolute z-50 flex gap-2 left-full -top-1/2 ml-12 mb-4 bg-white p-4 rounded-xl border border-gray-200 text-sm shadow--}}
{{--                                     w-48--}}
{{--                                     {{ !count($jiri->errors) ? 'border-2 border-green-500' : 'border-2 border-red-500' }}--}}
{{--                                     ">--}}
{{--                                    @if(!count($jiri->errors))--}}
{{--                                        <small class="text-sm ">--}}
{{--                                            Le jiri est prêt à être lancé.--}}
{{--                                        </small>--}}
{{--                                    @else--}}
{{--                                        <ul class="text-sm list-disc ms-4">--}}
{{--                                            @foreach($jiri->errors as $errors)--}}
{{--                                                <li>{{$errors}}</li>--}}
{{--                                            @endforeach--}}
{{--                                        </ul>--}}
{{--                                    @endif--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            @if(!count($jiri->errors))--}}
{{--                                <svg @click="tooltip = !tooltip;"--}}
{{--                                     @click.outside="tooltip = false;" @mouseenter="tooltip = true;"--}}
{{--                                     @mouseleave="tooltip = false;"--}}
{{--                                     class="text-green-600 sm:size-6 cursor-pointer" xmlns="http://www.w3.org/2000/svg"--}}
{{--                                     viewBox="0 0 24 24" fill="currentColor">--}}
{{--                                    <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd"/>--}}
{{--                                </svg>--}}
{{--                            @else--}}
{{--                                <svg @click="tooltip = !tooltip;"--}}
{{--                                     @click.outside="tooltip = false;" @mouseenter="tooltip = true;"--}}
{{--                                     @mouseleave="tooltip = false;"--}}
{{--                                     class="text-red-500 sm:size-6 cursor-pointer" viewBox="0 0 16 16"--}}
{{--                                     fill="currentColor"--}}
{{--                                     aria-hidden="true"--}}
{{--                                     data-slot="icon">--}}
{{--                                    <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14ZM8 4a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-1.5 0v-3A.75.75 0 0 1 8 4Zm0 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>--}}
{{--                                </svg>--}}
{{--                            @endif--}}
{{--                            <div class="flex-col items-start gap-x-3">--}}
{{--                                <p class="text-sm/6 font-semibold text-gray-900">{{$jiri->name}}</p>--}}
{{--                                <p class="text-sm/6 text-gray-500">{{ $jiri->starting_at }}</p>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                        <div class="flex flex-none items-center gap-x-4">--}}
{{--                            <button type="button"--}}
{{--                                    wire:click="start({{$jiri}})"--}}
{{--                                    class="hidden rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:block">--}}
{{--                                Lancer le jiri<span class="sr-only">{{$jiri->name}}</span>--}}
{{--                            </button>--}}
{{--                            <a href="{{route('pages.jiris.edit', $jiri)}}"--}}
{{--                               class="hidden rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:block">--}}
{{--                                Voir le Jiri<span class="sr-only">{{$jiri->name}}</span>--}}
{{--                            </a>--}}
{{--                            <button wire:click="openDeleteModal({{$jiri}})"--}}
{{--                                    type="button"--}}
{{--                                    class="rounded-md bg-indigo-600 px-2.5 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">--}}
{{--                                Supprimer--}}
{{--                            </button>--}}
{{--                        </div>--}}
{{--                    </li>--}}
{{--                @endforeach--}}
            </ul>
        @else
            <div class="mt-10 flex items-center text-center justify-center">
                <div class="">
                    <svg class="mx-auto size-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">Aucun jiri créé</h3>
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
