<?php

use App\Models\Attendance;
use App\Models\Contact;
use App\Models\Jiri;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, computed, mount};

state([
    'jiri',
    'id',
    'students',
    'evaluators',
    'search' => '',
    'user' => '',
    'contact_id',
]);

mount(function (Jiri $jiri) {
    $this->students = $jiri->students;
    $this->evaluators = $jiri->evaluators;
    $this->user = Auth::user();
    $this->jiri = $jiri;
    $this->id = $jiri->id;
    $this->contact_id = 0;
});

$filteredContact = computed(function () {
    $result = auth()
        ->user()
        ?->contacts()
        ->where('name', 'like', '%' . $this->search . '%')
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

$deleteFromJiri = function (Contact $contact) {
    Attendance::where('jiri_id', $this->jiri->id)
        ->where('contact_id', $contact->id)
        ->delete();
    $this->mount($this->jiri);
};

$createAttendance = function (string $role) {
    $this->dispatch('openDrawer', role: $role, jiri: $this->jiri
    )->to('partials.contacts-drawers');
};


$editAttendance = function (Contact $contact, string $role) {
    $this->dispatch('openDrawer', role: $role, jiri: $this->jiri, contact: $contact)->to('partials.contacts-drawers');
};
?>

<div class=" bg-white border mt-4 shadow-sm ring-1 ring-gray-900/5 p-4">
    <h2 class="text-base/7 font-semibold text-gray-900">Etudiants</h2>
    <p class="mt-1 mb-2 text-sm/6 text-gray-500">Ajouter ou retirer des étudiants aux jiris.</p>
    <div class="flex gap-x-4 items-center mb-2">
        <div
            class="w-auto">
            <label
                for="contact_search"
                class="sr-only block text-sm font-medium leading-6 text-gray-900">
                Recherche de contacts</label>
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
                    @if(!count($this->filteredContact))
                        <li class="inputSearch relative cursor-default select-none py-2 pl-3 pr-9 text-gray-900"
                            id="option-0" role="option" tabindex="-1">
                            <p>Aucun résultat</p>
                        </li>
                    @endif
                    @foreach($this->filteredContact as $contact)
                        <li
                            tabindex="0"
                            @focus="clearTimeout(blurTimeout); isFocused = true"
                            @blur="blurTimeout = setTimeout(() => { isFocused = false }, 200)"
                            wire:key="contact_search-{{$contact->id}}"
                            wire:click="addToJiri({{$contact->id}})"
                            x-data="{ isHovered: false }"
                            @keyup.enter="$wire.addToJiri({{$contact->id}})"
                            @mouseenter="isHovered = true"
                            @mouseleave="isHovered = false"
                            :class="isHovered ? 'text-white bg-indigo-600' : 'text-gray-900'"
                            class="relative cursor-default select-none py-2 pl-3 pr-9 text-gray-900"
                            id="option-0" role="option"
                        >
                            <div class="flex items-center">
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
            <div class="flex justify-between gap-x-4 py-6 px-4 rounded-xl border border-gray-200">
                <div class="flex items-center  gap-x-2">
                    <img class="h-8 w-8 rounded-full"
                         src="{{$student->photo ? asset($student->photo) : 'https://ui-avatars.com/api/?length=1&name='. $student->name}}"
                         alt="Photo de {{$student->name}}"/>
                    <div class="font-medium text-gray-900">{{$student->name}}</div>
                </div>
                <div class="flex items-center gap-x-2">
                    <button wire:click="deleteFromJiri({{$student}})" type="button"
                            class="font-semibold text-indigo-600 hover:text-indigo-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                        </svg>
                    </button>
                    <button wire:click="editAttendance({{$student}}, 'student')" type="button"
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
        <button wire:click="createAttendance('student')" type="button"
                class="text-sm/6 font-semibold text-indigo-600 hover:text-indigo-500"><span
                aria-hidden="true">+</span> Ajouter un étudiant qui n'existe pas dans les contacts
        </button>
    </div>
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
