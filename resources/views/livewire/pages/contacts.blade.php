<?php

use App\Models\Contact;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Masmerise\Toaster\Toaster;
use Intervention\Image\Laravel\Facades\Image;
use function Livewire\Volt\{layout, mount, rules, state, usesFileUploads,};

usesFileUploads();
layout('layouts.app');

state([
    'drawer',
    'contacts',
    'contact',
    'user',
    'name',
    'email',
    'phone',
    'photo',
    'path',
    'id',
    'deleteModal',
]);

rules(fn() => [
    'name' => 'required',
    'email' => 'required|email',
    'phone' => [
        'required',
        'regex:/^[\d\s()+-]+$/',
    ],
    'photo' => 'sometimes|nullable|image|max:5120',
])->messages([
    'name.required' => 'Le champ est obligatoire.',
    'email.required' => 'Le champ est obligatoire.',
    'email.email' => 'Le champ doit être un email.',
    'phone.required' => 'Le champ est obligatoire.',
    'phone.numeric' => 'Le champ doit être composé de chiffre',
    'phone.regex' => 'Le champ doit contenir uniquement des chiffres, des espaces, des parenthèses, et le signe "+".',
    'photo.image' => 'Le fichier doit être une photo en JPEG, JPG ou PNG',
    'photo.max' => 'La taille de l\'image doit être inferieur à 5mo',
])->attributes([
]);

mount(function () {
    $this->drawer = false;
    $this->user = Auth::user()->load('contacts');
    $this->contacts = $this->user->contacts()->orderBy('name')->get();

    $this->id = 0;
    $this->name = '';
    $this->email = '';
    $this->phone = '';
    $this->photo = null;
});

$openCreateContactDrawer = function () {
    $this->resetValidation();
    $this->drawer = true;
    $this->id = 0;
    $this->name = '';
    $this->email = '';
    $this->phone = '';
    $this->photo = null;
    $this->drawer = true;
};

$closeCreateContactDrawer = function () {
    $this->drawer = false;
};

$editContact = function (Contact $contact) {
    $this->id = $contact->id;
    $this->name = $contact->name;
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


//    dd($path);

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

    Contact::updateOrCreate([
        'user_id' => Auth::id(),
        'id' => $this->id,
    ],
        [
            'name' => $this->name,
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

    $this->mount();
};



$deleteContact = function () {
    $this->contact->delete();
    $this->deleteModal = false;
    Toaster::success('Contact Supprimé avec succès');
    $this->mount();
};

$openDeleteModal = function (Contact $contact) {
    $this->deleteModal = true;
    $this->contact = $contact;
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

    <h1 class="text-3xl font-bold leading-tight tracking-tight text-gray-900">Liste des contacts</h1>
    <div>
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <p class="mt-2 text-sm text-gray-700">La liste de tout vos contacts</p>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                <button wire:click="openCreateContactDrawer" type="button"
                        class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    Ajouter un contact
                </button>
            </div>
        </div>
        <div class="mt-8 flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead>
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">
                                Photo
                            </th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Nom</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Email</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Téléphone
                            </th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                <span class="">Action</span>
                            </th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($contacts as $contact)
                            <tr>
                                <td class="px-5 py-5 pl-4 pr-3 text-sm">
                                    <div class="">
                                        <div class="h-11 w-11 flex-shrink-0">
                                            <img class="h-11 w-11 rounded-full"
                                                 src="{{$contact->photo ? asset($contact->photo) : 'https://ui-avatars.com/api/?length=1&name='. $contact->name}}"
                                                 alt="Photo de {{$contact->name}}">
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $contact->name }}</div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500">
                                    <div class="font-medium text-gray-900">{{ $contact->email }}</div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500">
                                    <div class="font-medium text-gray-900">{{ $contact->phone }}</div>
                                </td>
                                <td class="relative whitespace-nowrap py-5 pl-3 pr-4 text-sm font-medium">
                                    <button wire:click="editContact({{$contact}})" type="button"
                                            class="text-gray-700 group rounded-md p-2 text-sm leading-6 font-semibold ">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                             stroke-width="1.5" stroke="currentColor"
                                             class="size-6 h-6 w-6 shrink-0 text-gray-400 group-hover:text-indigo-600">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/>
                                        </svg>
                                        <span class="sr-only">Modifier {{ $contact->name }}</span>
                                    </button>
                                    <button wire:click="openDeleteModal({{$contact}})" type="button"
                                            class="text-gray-700 group rounded-md p-2 text-sm leading-6 font-semibold">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                             stroke-width="1.5" stroke="currentColor"
                                             class="size-6 h-6 w-6 shrink-0 text-gray-400 group-hover:text-indigo-600">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                        </svg>
                                    </button>
                                </td>

                            </tr>
                        @endforeach
                        <tr>
                            <td class="px-5 py-5 pl-4 pr-3 text-sm">
                                <div class="">
                                    <div class="h-11 w-11 flex-shrink-0">
                                        <img class="h-11 w-11 rounded-full"
                                             src="https://images.unsplash.com/photo-1517841905240-472988babdf9?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
                                             alt="">
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500">
                                <div>
                                    <div class="font-medium text-gray-900">Lindsay Walton</div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500">
                                <div class="font-medium text-gray-900">lindsay.walton@example.com</div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500">
                                <div class="font-medium text-gray-900">0123456789</div>
                            </td>
                            <td class="relative whitespace-nowrap py-5 pl-3 pr-4 text-sm font-medium">
                                <a href="#" class="text-indigo-600 hover:text-indigo-900">Edit<span class="sr-only">,
                                        Lindsay Walton</span></a>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div
        x-trap.inert="open"
        x-on:keydown.escape="open = false"
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

        <div class="fixed inset-0 overflow-hidden">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10 sm:pl-16"
                     x-show="open"
{{--                     x-transition--}}
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
                        <form wire:submit.prevent="saveContact"
                              @click.down.outside="open = false "
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
                                                <label for="name"
                                                       class="block text-sm font-medium leading-6 text-gray-900">Nom<span class="text-red-500">*</span></label>
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
                                                <label for="email"
                                                       class="block text-sm font-medium leading-6 text-gray-900">Email<span class="text-red-500">*</span></label>
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
                                                       class="block text-sm font-medium leading-6 text-gray-900">Téléphone<span class="text-red-500">*</span></label>
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
                                                @if($id !== 0)
                                                    <div class="mt-2 h-24 w-24 flex-shrink-0">
                                                        <img class="h-24 w-24 rounded-full"
                                                             src="{{ $path ?  asset($path) : 'https://ui-avatars.com/api/?length=1&name='. $name}}"
                                                             alt="Photo de {{$name}}">
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
{{--                                                               focus:ring-2--}}
{{--                                                               focus:ring-indigo-600--}}
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
                                <p class="text-sm text-gray-500">Êtes-vous sûr de vouloir supprimer ce contact ?
                                    Le contact sera définitivement supprimée de nos serveurs. Cette action ne peut
                                    être annulée.</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <button wire:click="deleteContact" type="button"
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
