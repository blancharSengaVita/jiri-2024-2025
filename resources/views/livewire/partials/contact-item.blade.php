<?php

use App\Models\Contact;
use function Livewire\Volt\{state, mount, on};
use Masmerise\Toaster\Toaster;
use Carbon\Carbon;
use App\Models\Duties;

state([
	'contact',
]);

mount(function (Contact $contact) {
	$this->contact = $contact;
});

$edit = function (Contact $contact) {
	$this->dispatch('openEditContactDrawer', contact: $contact)->to('partials.contacts-drawers');
};

$create = function () {
	$this->dispatch('openCreateContactDrawer')->to('partials.contacts-drawers');
};

$delete = function (Contact $contact) {
	$this->dispatch('openDeleteModal', modelId: $contact->id, modelName: 'App\Models\Contact')->to('partials.delete-modal');
};

on(['refreshDashboardItems' => function () {
	$this->mount($this->contact);
}]);
?>
<tr>
    <td class="hidden md:table-cell px-5 py-5 pl-4 pr-3 text-sm">
        <div class="">
            <div class="h-11 w-11 flex-shrink-0">
                <img class="h-11 w-11 rounded-full"
                     src="{{$contact->photo ? asset($contact->photo) : 'https://ui-avatars.com/api/?length=1&name='. $contact->name}}"
                     alt="Photo de {{$contact->name}}">
            </div>
        </div>
    </td>
    <td class=" whitespace-nowrap px-3 py-5 text-sm text-gray-500">
        <div>
            <div class="font-medium text-gray-900">{{ $contact->name }}</div>
        </div>
    </td>
    <td class="hidden md:table-cell whitespace-nowrap px-3 py-5 text-sm text-gray-500">
        <div class="font-medium text-gray-900">{{ $contact->email }}</div>
    </td>
    <td class="hidden md:table-cell whitespace-nowrap px-3 py-5 text-sm text-gray-500">
        <div class="font-medium text-gray-900">{{ $contact->phone ?: 'Pas de numéro' }}</div>
    </td>
    <td class="relative whitespace-nowrap py-5 pl-3 pr-4 text-sm font-medium">
        <button wire:click="edit({{$contact}})" type="button"
                class="text-gray-700 group rounded-md p-2 text-sm leading-6 font-semibold ">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                 stroke-width="1.5" stroke="currentColor"
                 class="size-6 h-6 w-6 shrink-0 text-gray-400 group-hover:text-indigo-600">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/>
            </svg>
            <span class="sr-only">Modifier {{ $contact->name }}</span>
        </button>
        <button wire:click="delete({{$contact}})" type="button"
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
