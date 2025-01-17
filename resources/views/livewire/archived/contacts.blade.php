<?php

use App\Models\Contact;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Masmerise\Toaster\Toaster;
use Intervention\Image\Laravel\Facades\Image;
use function Livewire\Volt\{layout, mount, rules, state, usesFileUploads,on};

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
});

$openDrawer = function () {
    $this->dispatch('openDrawer')->to('pages.contacts-drawers');
};

$editContact = function(Contact $contact) {
    $this->dispatch('editThisContact',  contact: $contact)->to('pages.contacts-drawers');
};

$openDeleteModal = function (Contact $contact) {
    $this->dispatch('openDeleteModal', modelId: $contact->id, modelName: 'App\Models\Contact')->to('pages.delete-modal');
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

    <h1 class="text-3xl font-bold leading-tight tracking-tight text-gray-900">Liste des contacts</h1>
    <div>
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <p class="mt-2 text-sm text-gray-700">La liste de tout vos contacts</p>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                <button wire:click="openDrawer" type="button"
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
                                                 src="{{$contact->photo ? asset('storage/'.$contact->photo) : 'https://ui-avatars.com/api/?length=1&name='. $contact->name}}"
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
                                    <div class="font-medium text-gray-900">{{ $contact->phone ?: 'Pas de numéro' }}</div>
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
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <livewire:pages.contacts-drawers/>
    <livewire:pages.delete-modal/>
</div>
