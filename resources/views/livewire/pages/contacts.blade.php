<?php

use App\Models\Contact;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Masmerise\Toaster\Toaster;
use Intervention\Image\Laravel\Facades\Image;
use function Livewire\Volt\{layout, mount, rules, state, usesFileUploads, on, with, usesPagination};


usesPagination();
usesFileUploads();

layout('layouts.app');

state([
    'drawer',
//    'contacts',
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

mount(function () {
    $this->drawer = false;
    $this->user = Auth::user()->load('contacts');
//    $this->contacts = $this->user->contacts()->orderBy('name')->get();
});

with(fn() => ['contacts' => $this->user->contacts()->orderBy('updated_at', 'desc')->paginate(10)]);

$create = function () {
    $this->dispatch('openCreateContactDrawer')->to('partials.contacts-drawers');
};

$edit = function (Contact $contact) {
    $this->dispatch('openEditContactDrawer', contact: $contact)->to('partials.contacts-drawers');
};

$openDeleteModal = function (Contact $contact) {
    $this->dispatch('openDeleteModal', modelId: $contact->id, modelName: 'App\Models\Contact')->to('partials.delete-modal');
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
                <button wire:click="create" type="button"
                        class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    Ajouter un contact
                </button>
            </div>
        </div>
        <div class="mt-8 flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle px-6 lg:px-8">
                    @if(count($contacts))
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead>
                            <tr>
                                <th scope="col" class="hidden md:table-cell py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">
                                    Photo
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Nom
                                </th>
                                <th scope="col" class="hidden md:table-cell px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    Email
                                </th>
                                <th scope="col" class="hidden md:table-cell px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    Téléphone
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    <span class="">Action</span>
                                </th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($contacts as $contact)
                                <livewire:partials.contact-item :$contact :key="'contact'.$contact->id"/>
                            @endforeach
                            </tbody>
                        </table>
                        {{ $contacts->links() }}
                    @else
                        <div class="mt-10 flex items-center text-center justify-center">
                            <div class="">
                                <svg class="mx-auto size-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-semibold text-gray-900">Aucun contact créé</h3>
                                <p class="mt-1 text-sm text-gray-500">Vous pouvez créer un contact ici</p>
                                <div class="mt-6">
                                    <button wire:click="create" type="button" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                        <svg class="-ml-0.5 mr-1.5 size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                            <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/>
                                        </svg>
                                        Nouveau contact
                                    </button>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    </svg>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <livewire:partials.contacts-drawers/>
    <livewire:partials.delete-modal/>
</div>
