<?php

use App\Models\Attendance;
use App\Models\Contact;
use App\Models\Jiri;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Masmerise\Toaster\Toaster;
use Intervention\Image\Laravel\Facades\Image;
use function Livewire\Volt\{layout, mount, rules, state, usesFileUploads, on};

usesFileUploads();
layout('layouts.app');

state([
    'drawer',
    'contacts',
    'contact',
    'jiri',
    'role',
    'user',
    'name',
    'email',
    'phone',
    'photo',
    'path',
    'id',
    'deleteModal',
    'modelName',
    'stringNew',
]);

rules(fn() => [
    'name' => 'required',
    'email' => 'required|email',
    'phone' => [
        'regex:/^[\d\s()+-]+$/',
    ],
    'photo' => 'sometimes|nullable|image|max:5120',
])->messages([
    'name.required' => 'Le champ est obligatoire.',
    'email.required' => 'Le champ est obligatoire.',
    'email.email' => 'Le champ doit être un email.',
    'phone.numeric' => 'Le champ doit être composé de chiffre',
    'phone.regex' => 'Le champ doit contenir uniquement des chiffres, des espaces, des parenthèses, et le signe "+".',
    'photo.image' => 'Le fichier doit être une photo en JPEG, JPG ou PNG',
    'photo.max' => 'La taille de l\'image doit être inferieur à 5mo',
])->attributes([
]);

mount(function () {
    $this->drawer = false;
    $this->user = Auth::user();
    $this->modelName = 'contact';
    $this->stringNew = 'Nouveau';


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

    $newContact = Contact::updateOrCreate([
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
        Toaster::success('Donnée ajouté avec succès');
    }

    if ($this->id !== 0) {
        Toaster::success('Donnée modifiée avec succès');
    };

    if ($this->jiri !== null) {
        Attendance::updateOrInsert([
            'jiri_id' => $this->jiri->id,
            'contact_id' => $newContact->id,
        ],
            [
                'role' => 'student',
            ]);

    }

    $this->dispatch('refreshComponent');
};

on([
    'openDrawer' => function (string $role = null, Jiri $jiri = null, Contact $contact) {
        $this->openCreateContactDrawer();
        $this->jiri = $jiri;
        $this->role = $role;
        $this->editContact($contact);
        $this->modelName = $this->role === null ? 'contact' : ($this->role === 'student' ? 'étudiant' : 'évaluateur');
    },
    'editThisContact' => function (Contact $contact) {
        $this->openCreateContactDrawer();
        $this->editContact($contact);
    },
]);
?>

<div class="py-10"
     x-data="{
    open: $wire.entangle('drawer'),
    }"
>
    <div
        x-trap.inert="open"
        @keydown.escape="open = false"
        x-cloak
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
                              @mousedown.outside="open = false"
                              enctype="multipart/form-data"
                              class="flex h-full flex-col divide-y divide-gray-200 bg-white shadow-xl"
                        >
                            <div class="h-0 flex-1 overflow-y-auto">
                                <div class="bg-gray-900 px-4 py-6 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <h2 class="text-base font-semibold leading-6 text-white" id="slide-over-title">
                                            Nouveau {{$modelName}}</h2>
                                        <div class="ml-3 flex h-7 items-center">
                                            <button wire:click="closeCreateContactDrawer" type="button"
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
                                        <p class="text-sm text-gray-400">Ajouter des informations pour votre {{$modelName}}</p>
                                    </div>
                                </div>
                                <div class="flex flex-1 flex-col justify-between">
                                    <div class="divide-y divide-gray-200 px-4 sm:px-6">
                                        <div class="space-y-6 pb-5 pt-6">
                                            <fieldset>
                                                <label for="name"
                                                       class="block text-sm font-medium leading-6 text-gray-900">Nom<span
                                                        class="text-red-500">*</span></label>
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
                                                       class="block text-sm font-medium leading-6 text-gray-900">Téléphone
                                                </label>
                                                <div class="mt-2">
                                                    <input wire:model="phone" type="text" name="phone" id="phone"
                                                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                                           autocomplete="on">
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
</div>
