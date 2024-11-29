<?php

use App\Models\Attendance;
use App\Models\Contact;
use App\Models\Jiri;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Masmerise\Toaster\Toaster;
use function Livewire\Volt\{layout, mount, rules, state, computed, usesFileUploads};
usesFileUploads();
layout('layouts.app');

state([
    'drawer',
    'jiri',
    'model',
    'students',
    'evaluators',
    'user',
    'date',
    'name' => '',
    'id',
    'deleteModal',
    'search' => '',
    'contacts',
    'contact',
    'contact_id',
    'contact_name',
    'email',
    'phone',
    'photo',
    'path',
    'id',
]);

rules(fn() => [
    'name' => 'required',
    'date' => 'required|date',
    'contact_name' => 'required',
    'email' => 'required|email',
    'phone' => [
        'required',
        'regex:/^[\d\s()+-]+$/',
    ],
    'photo' => 'sometimes|nullable|image|max:5120',
])->messages([
    'name.required' => 'Le champ est obligatoire.',
    'date.required' => 'Le champ est obligatoire.',
    'date.date' => 'Le champ doit être une date.',
    'contact_name.required' => 'Le champ est obligatoire.',
    'email.required' => 'Le champ est obligatoire.',
    'email.email' => 'Le champ doit être un email.',
    'phone.required' => 'Le champ est obligatoire.',
    'phone.numeric' => 'Le champ doit être composé de chiffre',
    'phone.regex' => 'Le champ doit contenir uniquement des chiffres, des espaces, des parenthèses, et le signe "+".',
    'photo.image' => 'Le fichier doit être une photo en JPEG, JPG ou PNG',
    'photo.max' => 'La taille de l\'image doit être inferieur à 5mo',
])->attributes([
]);

mount(function (Jiri $jiri) {
    $this->students = $jiri->students;
    $this->evaluators = $jiri->evaluators;
    $this->drawer = false;
    $this->user = Auth::user();
    $this->jiri = $jiri;
    $this->id = $jiri->id;
    $this->date = Carbon::parse($jiri->starting_at)->format('Y-m-d');
    $this->name = $jiri->name;

    $this->contact_id = 0;
    $this->contact_name = '';
    $this->email = '';
    $this->phone = '';
    $this->photo = null;
});

$openCreateContactDrawer = function () {
    $this->resetValidation();
    $this->contact_id = 0;
    $this->drawer = true;
    $this->contact_name = '';
    $this->email = '';
    $this->phone = '';
    $this->photo = null;
};

$closeCreateContactDrawer = function () {
    $this->drawer = false;
};

$editContact = function (Contact $contact) {
    $this->contact_id = $contact->id;
    $this->contact_name = $contact->name;
    $this->email = $contact->email;
    $this->phone = $contact->phone;
    $this->path = $contact->photo;
    $this->photo = null;
    $this->drawer = true;
};


$saveContact = function () {
    try {
        $this->validate();
    } catch (\Illuminate\Validation\ValidationException $e) {
        throw $e;
    }

    if ($this->photo) {
        $this->path = $this->photo->store('contacts/' . $this->user->id . '/originals');
        $myExplodedArray = explode('.', $this->path);
        $extension = $myExplodedArray[array_key_last($myExplodedArray)];
        $myExplodedArray = explode('/', $myExplodedArray[0]);
        $hashname = $myExplodedArray[array_key_last($myExplodedArray)];

        $sizes = Config::get('photos.sizes');
        foreach ($sizes as $name => $size) {
            if (!is_int($size)) {
                continue;
            }
            $i = Image::read($this->photo);
            $i->cover($size, $size);
            $i->save(storage_path('app/public/contacts/' . $this->user->id . '/' . $hashname . '_' . $name . '.' . $extension));
        }
    }

    $contact = Contact::updateOrCreate([
        'user_id' => Auth::id(),
        'id' => $this->contact_id,
    ],
        [
            'name' => $this->contact_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'photo' => $this->path,
        ]);

    $this->drawer = false;
    if ($this->id === 0) {
        Toaster::success('Contact ajouté avec succès');
    }

    if ($this->id !== 0) {
        Toaster::success('Contact modifiée avec succès');
    };

    $this->addToJiri($contact);

    $this->mount($this->jiri);
};

$cancel = function () {
    $this->mount($this->jiri);
};

$filteredContact = computed(function () {
    $result = auth()
        ->user()
        ?->contacts()
//        ->where('name', 'like', '%' . $this->search . '%')
        ->whereDoesntHave('attendances', function ($query) {
            $query->where('jiri_id', $this->jiri->id);
        })
        ->get();
    return $result;
});

$addToJiri = function (Contact $contact) {
    Attendance::updateOrInsert([
        'jiri_id' => $this->jiri->id,
        'contact_id' => $contact->id,
    ],
        [
            'role' => 'student',
        ]);
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

    if ($this->id === 0) {
        Toaster::success('Jiri ajouté avec succès');
    }

    if ($this->id !== 0) {
        Toaster::success('Jiri modifiée avec succès');
    }

    $this->mount($this->jiri);
};

$delete = function () {
    $this->model->delete();
    $this->deleteModal = false;
    Toaster::success('Supprimé avec succès');
    if ($this->model instanceof Jiri) {
        $this->redirectIntended(default: route('pages.jiris', absolute: false), navigate: true);
    }
};

$deleteFromJiri = function (Contact $contact) {
    Attendance::where('jiri_id', $this->jiri->id)
        ->where('contact_id', $contact->id)
        ->delete();
    $this->mount($this->jiri);
};

$openDeleteModal = function ($model) {
    $this->model = $model;
    $this->deleteModal = true;
};

$closeDeleteModal = function () {
    $this->deleteModal = false;
    $this->mount($this->jiri);
};
?>

<div class="py-10"
     x-data="{
    open: $wire.entangle('drawer'),
    deleteModal: $wire.entangle('deleteModal'),
    }"
>
    <a class="w-auto font-semibold mb-12 text-indigo-600 hover:text-indigo-500" href="{{url()->previous()}}">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
             class="size-12">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
        </svg>
    </a>
    <h1 class="text-3xl mt-2 font-bold leading-tight tracking-tight text-gray-900">{{$jiri->name}}</h1>
    <div>
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <p class="mt-2 text-sm text-gray-700">Toutes les informations sur {{$jiri->name}}</p>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                <button wire:click="openDeleteModal({{$jiri}})" type="button"
                        class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    Supprimer le jiri
                </button>
            </div>
        </div>

        <form wire:submit.prevent="save" class=" bg-white border mt-4 shadow-sm ring-1 ring-gray-900/5 p-4">
            <h2 class="text-base/7 font-semibold text-gray-900">Information général</h2>
            <p class="mt-1 text-sm/6 text-gray-500">Nom et date du début du jiri</p>

            <fieldset class="mt-6 pt-2 divide-y divide-gray-100 border-t border-gray-200 text-sm/6">
                <label for="name"
                       class="mt-2 block text-sm/6 font-medium text-gray-900 sm:pt-1.5">Nom</label>
                <input type="text"
                       name="name"
                       id="name"
                       autocomplete="given-name"
                       wire:model.live="name"
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
        <div class=" bg-white border mt-4 shadow-sm ring-1 ring-gray-900/5 p-4">
            <h2 class="text-base/7 font-semibold text-gray-900">Etudiants</h2>
            <p class="mt-1 text-sm/6 text-gray-500">Ajouter ou retirer des étudiants aux jiris.</p>
            <div
                class="flex gap-x-4 items-center mb-4">
                <div
                    class="w-auto">
                    <label
                        for="contact_search"
                        class="sr-only block text-sm font-medium leading-6 text-gray-900">
                        Recherche de contacts</label>
                    <div class="relative"
                         x-data="{
                                                                            isFocused: false,
                                                                            blurTimeout: null
                                                                            }"
                    >
                        <input
                            autocomplete="off"
                            @focus="clearTimeout(blurTimeout); isFocused = true"
                            @blur="blurTimeout = setTimeout(() => { isFocused = false }, 200)"
                            wire:model.live="search"
                            id="contact_search" type="search"
                            class="w-80 rounded-md border-0 bg-white py-1.5 pl-8 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                            role="combobox" aria-controls="options" aria-expanded="false">
                        <button type="button"
                                class="absolute inset-y-0 left-0 flex items-center rounded-r-md px-2 focus:outline-none">
                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                                 aria-hidden="true">
                                <path fill-rule="evenodd"
                                      d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z"
                                      clip-rule="evenodd"/>
                            </svg>
                        </button>
{{--                        {{$this->filteredContact}}--}}
                        <ul
                            x-data="{
                                                                                searchValue: $wire.entangle('search'),
                                                                                }"

                            x-show="isFocused"
                            class="absolute z-10 mt-1 max-h-56 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
                            id="options" role="listbox">
                            {{--                            <!----}}
                            {{--                              Combobox option, manage highlight styles based on mouseenter/mouseleave and keyboard navigation.--}}

                            {{--                              Active: "text-white bg-indigo-600", Not Active: "text-gray-900"--}}
                            {{--                            -->--}}
                            @if(!count($this->filteredContact))
                                <li class="relative cursor-default select-none py-2 pl-3 pr-9 text-gray-900"
                                    id="option-0" role="option" tabindex="-1">
                                    <p>Aucun résultat</p>
                                </li>
                            @endif
                                @foreach($this->filteredContact as $contact)
                                    <li
                                        wire:key="contact_search-{{$contact->id}}"
                                        wire:click="addToJiri({{$contact->id}})"
                                        x-data="{ isHovered: false }"
                                        @mouseenter="isHovered = true"
                                        @mouseleave="isHovered = false"
                                        :class="isHovered ? 'text-white bg-indigo-600' : 'text-gray-900'"
                                        class="relative cursor-default select-none py-2 pl-3 pr-9 text-gray-900"
                                        id="option-0" role="option" tabindex="-1"
                                    >
                                        <div class="flex items-center">
                                            {{--                                                                                                                                    <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="" class="h-6 w-6 flex-shrink-0 rounded-full">--}}
                                            <!-- Selected: "font-semibold" -->

                                            <img class="h-8 w-8 rounded-full"
                                                 src="{{$contact->photo ? asset($contact->photo) : 'https://ui-avatars.com/api/?length=1&name='. $contact->name}}"
                                                 alt="Photo de {{$contact->name}}">
                                            <span class="ml-3 truncate">{{ $contact->name }}</span>

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
                                                                                                  d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z"/>
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
                @foreach($students as $student)
                    <div class="flex items-center justify-between gap-x-4 py-6 px-4 rounded-xl border border-gray-200">
                        <img class="h-8 w-8 rounded-full"
                             src="{{$student->photo ? asset($student->photo) : 'https://ui-avatars.com/api/?length=1&name='. $student->name}}"
                             alt="Photo de {{$contact->name}}">
                        <div class="font-medium text-gray-900">{{$student->name}}</div>
                        <button wire:click="deleteFromJiri({{$student}})" type="button"
                                class="font-semibold text-indigo-600 hover:text-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                 stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                            </svg>
                        </button>
                    </div>
                @endforeach
            </div>

            <div class="flex border-t border-gray-100 pt-6">
                <button wire:click="openCreateContactDrawer" type="button"
                        class="text-sm/6 font-semibold text-indigo-600 hover:text-indigo-500"><span
                        aria-hidden="true">+</span> Ajouter un étudiant qui n'existe pas dans les contacts
                </button>
            </div>
        </div>


        <div class=" bg-white border mt-4 shadow-sm ring-1 ring-gray-900/5 p-4">
            {{$jiri->name}}
            <h2 class="text-base/7 font-semibold text-gray-900">Etudiants</h2>
            <p class="mt-1 text-sm/6 text-gray-500">Ajouter ou retirer des étudiants aux jiris.</p>

            <ul role="list" class="mt-6 divide-y divide-gray-100 border-t border-gray-200 text-sm/6">
                @foreach($evaluators as $evaluator)
                    <li class="flex justify-between gap-x-6 py-6">
                        <div class="font-medium text-gray-900">{{$evaluator->name}}</div>
                        <button wire:click="deleteFromJiri({{$evaluator}})" type="button"
                                class="font-semibold text-indigo-600 hover:text-indigo-500">Supprimer
                        </button>
                    </li>
                @endforeach
            </ul>

            <div class="flex border-t border-gray-100 pt-6">
                <button type="button" class="text-sm/6 font-semibold text-indigo-600 hover:text-indigo-500"><span
                        aria-hidden="true">+</span> Add another bank
                </button>
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
                            <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">Supprimer un
                                Jiri</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Êtes-vous sûr de vouloir supprimer ce jiri ?
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
    <div :class="open ? 'relative z-10' : 'hidden'" class="z-50" aria-labelledby="slide-over-title" role="dialog"
         aria-modal="true">
        <!-- Background backdrop, show/hide based on slide-over state. -->
        <div class="fixed inset-0"></div>

        <div class="fixed inset-0 overflow-hidden">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10 sm:pl-16">
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
                        <form wire:submit.prevent="saveContact"
                              @click.away="open = false "
                              enctype="multipart/form-data"
                              class="flex h-full flex-col divide-y divide-gray-200 bg-white shadow-xl"
                        >
                            <div class="h-0 flex-1 overflow-y-auto">
                                <div class="bg-gray-900 px-4 py-6 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <h2 class="text-base font-semibold leading-6 text-white" id="slide-over-title">
                                            Nouveau contact</h2>
                                        <div class="ml-3 flex h-7 items-center">
                                            <button wire:click="closeCreateContactDrawer" type="button"
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
                                        <p class="text-sm text-gray-400">Ajouter des informations pour votre contact</p>
                                    </div>
                                </div>
                                <div class="flex flex-1 flex-col justify-between">
                                    <div class="divide-y divide-gray-200 px-4 sm:px-6">
                                        <div class="space-y-6 pb-5 pt-6">
                                            <fieldset>
                                                <label for="contact_name"
                                                       class="block text-sm font-medium leading-6 text-gray-900">Nom<span
                                                        class="text-red-500">*</span></label>
                                                <div class="mt-2">
                                                    <input wire:model="contact_name" type="text" name="contact_name" id="contact_name"
                                                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                                           autocomplete="on">
                                                    <p class="text-xs text-gray-400 mt-2">Ce champ est obligatoire*</p>
                                                </div>
                                                @if ($messages = $errors->get('contact_name'))
                                                    <div class="text-sm text-red-600 space-y-1 mt-2">
                                                        <p>{{$messages[0]}}</p>
                                                    </div>
                                                @endif
                                            </fieldset>

                                            <fieldset>
                                                <label for="email"
                                                       class="block text-sm font-medium leading-6 text-gray-900">Email<span
                                                        class="text-red-500">*</span></label>
                                                <div class="mt-2">
                                                    <input wire:model="email" type="text" name="email" id="email"
                                                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                                           autocomplete="on">
                                                    <p class="text-xs text-gray-400 mt-2">Ce champ est obligatoire*</p>
                                                </div>

                                                @if ($messages = $errors->get('email'))
                                                    <div class="text-sm text-red-600 space-y-1 mt-2">
                                                        <p>{{$messages[0]}}</p>
                                                    </div>
                                                @endif
                                            </fieldset>

                                            <fieldset>
                                                <label for="phone"
                                                       class="block text-sm font-medium leading-6 text-gray-900">Téléphone<span
                                                        class="text-red-500">*</span></label>
                                                <div class="mt-2">
                                                    <input wire:model="phone" type="text" name="phone" id="phone"
                                                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                                           autocomplete="on">
                                                    <p class="text-xs text-gray-400 mt-2">Ce champ est obligatoire*</p>
                                                </div>
                                                @if ($messages = $errors->get('phone'))
                                                    <div class="text-sm text-red-600 space-y-1 mt-2">
                                                        <p>{{$messages[0]}}</p>
                                                    </div>
                                                @endif
                                            </fieldset>

                                            <fieldset class="">
                                                <label
                                                    for="photo"
                                                    class="block text-sm font-medium leading-6 text-gray-900">
                                                    Photo de profil
                                                </label>
                                                {{$name}}
                                                @if($contact_id !== 0)
                                                    <div class="mt-2 h-24 w-24 flex-shrink-0">
                                                        <img class="h-24 w-24 rounded-full"
                                                             src="{{ $path ?  asset($path) : 'https://ui-avatars.com/api/?length=1&name='. $contact_name}}"
                                                             alt="Photo de {{$contact_name}}">
                                                    </div>
                                                @endif
                                                <input type="file"
                                                       wire:model="photo"
                                                       id="photo"
                                                       name="photo"
                                                       class="
                                                                                                           mt-2
                                                                                                           block
                                                                                                           w-full
                                                                                                           rounded-md
                                                                                                           border-0
                                                                                                           text-gray-900
                                                                                                           shadow-sm
                                                                                                           ring-1
                                                                                                           ring-inset
                                                                                                           ring-gray-300
                                                                                                           sm:text-sm sm:leading-6
                                                                                                           text-base bg-white
                                                                                                           focus:ring-2
                                                                                                           focus:ring-indigo-600
                                                                                                           file:rounded-md
                                                                                                           file:focus:ring-0
                                                                                                           file:border-0
                                                                                                           file:cursor-pointer
                                                                                                           cursor-pointer
                                                                                                           file:py-2
                                                                                                           file:px-4
                                                                                                           file:hover:bg-indigo-400
                                                                                                           file:mr-4
                                                                                                           file:bg-gray-300
                                                                                                           file:text-gray-900
                                                                                                           file:text-sm
                                                                                                            "/>
                                                <p class="text-xs text-gray-400 mt-2">Seules les PNG et JPG sont. Max
                                                    4mo.</p>
                                                @if ($messages = $errors->get('photo'))
                                                    <div class="text-sm text-red-600 space-y-1 mt-2">
                                                        <p>{{$messages[0]}}</p>
                                                    </div>
                                                @endif
                                            </fieldset>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-shrink-0 justify-end px-4 py-4">
                                <button type="button"
                                        wire:click="closeCreateContactDrawer"
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
