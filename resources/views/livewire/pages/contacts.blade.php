<?php
use App\Models\User;
use \App\Models\Contact
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{
    layout,
    mount,
    state,
};

state([
    'drawer',
    'contacts',
    'user',
    'name',
    'email',
    'phone'
]);

mount(function () {
    $this->drawer = false;
    $this->user = Auth::user()->load('contacts');
    $this->contacts = $this->user->contacts()->get();

    $this->name = '' ;
    $this->email = '';
    $this->phone = '' ;
});

$openCreateContactDrawer = function () {
    $this->drawer = !$this->drawer;
};

Contact::updateOrCreate([
    'user_id' => Auth::id(),
    'id' => $this->id
],
    [
        'name' => $this->name,
        'email' => $this->email,
        'phone' => $this->phone,
    ]);

$this->openSinglePlayerExperienceModal = false;
if ($this->id === 0) {
    Toaster::success('Expérience ajouté avec succès');
}

if ($this->id !== 0) {
    Toaster::success('Expérience modifiée avec succès');
}
};

layout('layouts.app');
?>

<div class="py-10"
     x-data="{
    open: $wire.entangle('drawer'),
    }"
>

    <h1 class="text-3xl font-bold leading-tight tracking-tight text-gray-900">Liste des contacts</h1>
    <div>
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <p class="mt-2 text-sm text-gray-700">La liste de tout vos contacts</p>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                <button wire:click="openCreateContactDrawer" type="button" class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
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
                                        <img class="h-11 w-11 rounded-full" src="{{'https://ui-avatars.com/api/?length=1&name='. $contact->name}}" alt="Photo de {{$contact->name}}">
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
                                <a href="#" class="text-indigo-600 hover:text-indigo-900">Edit<span class="sr-only">,
                                        Lindsay Walton</span></a>
                            </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td class="px-5 py-5 pl-4 pr-3 text-sm">
                                <div class="">
                                    <div class="h-11 w-11 flex-shrink-0">
                                        <img class="h-11 w-11 rounded-full" src="https://images.unsplash.com/photo-1517841905240-472988babdf9?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
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
    <div :class="open ? 'relative z-10' : 'hidden'" class="" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
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
                        <form @click.away="open = false" class="flex h-full flex-col divide-y divide-gray-200 bg-white shadow-xl">
                            <div class="h-0 flex-1 overflow-y-auto">
                                <div class="bg-gray-900 px-4 py-6 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <h2 class="text-base font-semibold leading-6 text-white" id="slide-over-title">
                                            Nouveau contact</h2>
                                        <div class="ml-3 flex h-7 items-center">
                                            <button wire:click="openCreateContactDrawer" type="button" class="relative rounded-md bg-gray-900 text-gray-400 hover:text-white focus:outline-none focus:ring-2 focus:ring-white">
                                                <span class="absolute -inset-2.5"></span>
                                                <span class="sr-only">Fermer la modal</span>
                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
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
                                            <div>
                                                <label for="name" class="block text-sm font-medium leading-6 text-gray-900">Nom</label>
                                                <div class="mt-2">
                                                    <input wire:model="name" type="text" name="name" id="name" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" autocomplete="on">
                                                </div>
                                            </div>

                                            <div>
                                                <label  for="email" class="block text-sm font-medium leading-6 text-gray-900">Email</label>
                                                <div class="mt-2">
                                                    <input wire:model="email" type="text" name="email" id="email" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" autocomplete="on">
                                                </div>
                                            </div>

                                            <div>
                                                <label for="phone" class="block text-sm font-medium leading-6 text-gray-900">Téléphone</label>
                                                <div class="mt-2">
                                                    <input wire:model="phone" type="text" name="phone" id="phone" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" autocomplete="on">
                                                </div>
                                            </div>

                                            <div class="">
                                                <label for="picture" class="block text-sm font-medium leading-6 text-gray-900">Upload
                                                    file</label>
                                                <input type="file"
                                                       id="picture"
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
                                                <p class="text-xs text-gray-400 mt-2">PNG and JPG are allowed. Max
                                                    4mo.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-shrink-0 justify-end px-4 py-4">
                                <button type="button" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button wire:submit.prevent="saveContact" type="submit" class="ml-4 inline-flex justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
