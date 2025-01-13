<?php

use App\Models\Attendance;
use App\Models\Jiri;
use function Livewire\Volt\{layout, mount, rules, state, on};
use Illuminate\Support\Facades\Auth;
use Masmerise\Toaster\Toaster;
use Carbon\Carbon;
use App\Models\Duties;

state([
    'user',
    'jiris',
    'contacts',
    'projects',
]);

layout('layouts.app');

mount(function () {
    $this->user = Auth::user();
    $this->jiris = $this->user->jiris()->orderBy('updated_at', 'desc')->limit(3)->get();
    $this->projects = $this->user->projects()->orderBy('updated_at', 'desc')->limit(3)->get();
    $this->contacts = $this->user->contacts()->orderBy('updated_at', 'desc')->limit(3)->get();

    if (session('jiriLaunched')) {
        $this->dispatch('JiriStarted')->self();
        session()->forget('jiriLaunched');
    }
});

$createJiriDrawer = function () {
    $this->mount();
    $this->dispatch('openCreateJiriDrawer')->to('partials.jiris-drawers');
};

$createProjectDrawer = function () {
    $this->dispatch('openCreateProjectDrawer')->to('partials.projects-drawers');
    $this->mount();
};

$createContactDrawer = function () {
    $this->mount();
    $this->dispatch('openCreateContactDrawer')->to('partials.contacts-drawers');
};

$openDeleteModal = function (Jiri $jiri) {
    $this->mount();
    $this->dispatch('openDeleteModal', modelId: $jiri->id, modelName: 'App\Models\Jiri')->to('partials.delete-modal');
};

on([
    'refreshComponent' => function () {
        $this->mount();
    }, 'JiriStarted' => function () {
        Toaster::success('Le jiri est lancé, Les mails ont bien été envoyés');
    }
]);
?>

<div class="py-10"
     x-data="{
     }"
>
    @if(session('currentJiri'))
        <div class="flex gap-x-2 items-center mb-2">
            @if(session('currentJiri')->status === Jiri::STATUS_IN_PROGRESS)
                <h1 class="text-3xl font-bold leading-tight tracking-tight text-gray-900">
                    Dashboard - jiri en cours : {{session('currentJiri')->name}}<span class="text-red-500"></span></h1>
                <div class="w-4 h-4 bg-red-500 rounded-full animate-pulse"></div>
            @endif
            @if(session('currentJiri')->status === Jiri::STATUS_ON_PAUSE)
                <h1 class="text-3xl font-bold leading-tight tracking-tight text-gray-900">
                    Dashboard - jiri en pause : {{session('currentJiri')->name}}<span class="text-red-500"></span></h1>
                <div class="w-4 h-4 bg-yellow-500 rounded-full animate-pulse"></div>
            @endif
        </div>
        <div class="flex gap-x-2 mb-4">
            @if(session('currentJiri')->canBeStopped())
                <a wire:navigate
                   href="{{route('pages.jiris.edit', session('currentJiri'))}}"
                   type="button"
                   value="Mettre fin au jiri"
                   class="flex items-center justify-center rounded  px-2 py-2 text-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 bg-white font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:block"
                >
                    Voir le jiri
                </a>
            @endif
            @if(session('currentJiri')->canBePaused())
                <livewire:partials.pausejiri :jiri="session('currentJiri')"/>
            @endif
            @if(session('currentJiri')->canBeRelaunched())
                <livewire:partials.restartjiri :jiri="session('currentJiri')"/>
            @endif
            @if(session('currentJiri')->canBeStopped())
                <livewire:partials.stopjiri :jiri="session('currentJiri')"/>
            @endif
        </div>
    @else
        <div class="flex gap-x-2 items-center mb-4">
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-gray-900">
                Dashboard<span class="text-red-500"></span></h1>
        </div>
    @endif
    @if(session('currentJiri'))
        <div class="gap-4">
            <div class="col-span-5 mb-6">
                <h2 class="text-base/7 font-semibold text-gray-900 mb-2">Étudiant à évaluer</h2>
                <livewire:partials.student-to-evaluate :jiri="session('currentJiri')"/>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-5 gap-4 gap-x-8">
        @if(!session('currentJiri'))
            <div class="col-span-5 xl:col-span-3 mb-4">
                <div class="flex justify-between">
                    <div class="flex gap-x-2">
                        <h2 class="text-base/7 font-semibold text-gray-900">Les derniers jiris</h2>
                        <button id="addLinks"
                                type="button"
                                value="L'intitulé des liens qui seront attribués aux projets"
                                wire:click="createJiriDrawer"
                                class="flex items-center justify-center rounded bg-indigo-600 ml-1 px-2 py-1 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        >Ajouter
                        </button>
                    </div>
                    <a wire:navigate href="/jiris" title="Aller vers la page du jiris" type="button" class="text-sm/6 font-semibold text-indigo-600 hover:text-indigo-500">Voir
                        plus</a>
                </div>
                @if(count($jiris))
                    <ul role="list" class="divide-y divide-gray-100 bg-white border mt-4 shadow-sm ring-1 ring-gray-900/5">
                        @foreach($jiris as $jiri)
                            <livewire:partials.jiri-item :$jiri :key="'jiri'.$jiri->id"/>
                        @endforeach
                    </ul>
                @else
                    <a class="mt-4 text-center justify-center flex items-center gap-x-6 py-5 p-4 rounded-lg border-2 border-dashed border-gray-300 ring-gray-900/5 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 cursor-pointer" wire:click="createJiriDrawer">
                        <div>
                            <svg class="mx-auto size-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-semibold text-gray-900">Aucun jiri créé</h3>
                            <p class="mt-1 text-sm text-gray-500 flex items-center">Vous pouvez créer un jiri ici
                                <svg width="12"
                                     height="12"
                                     xmlns="http://www.w3.org/2000/svg"
                                     class="inline cursor-pointer rounded h-5 w-5 shrink-0 ml-1 rotate-45"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke-width="1.5"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M6 18 18 6M6 6l12 12"/>
                                </svg>
                            </p>
                        </div>
                    </a>
                @endif
            </div>
            <div class="col-span-5 xl:col-span-2">
                <div class="flex justify-between">
                    <div class="flex gap-x-2">
                        <h2 class="text-base/7 font-semibold text-gray-900">Les projets</h2>
                        <button id="addLinks"
                                type="button"
                                value="L'intitulé des liens qui seront attribués aux projets"
                                wire:click="createProjectDrawer"
                                class="flex items-center justify-center rounded bg-indigo-600 ml-1 px-2 py-1 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        >Ajouter
                        </button>
                    </div>
                    <a wire:navigate href="/projects" title="Aller vers la page du jiris" type="button" class="text-sm/6 font-semibold text-indigo-600 hover:text-indigo-500">Voir
                        plus</a>
                </div>
                @if(count($projects))
                    <ul role="list" class="divide-y divide-gray-100 bg-white border mt-4 shadow-sm ring-1 ring-gray-900/5">
                        @foreach($projects as $project)
                            {{--                    {{ $projects }}--}}
                            <livewire:partials.project-item :$project :key="'project'.$project->id"/>
                        @endforeach
                    </ul>
                @else
                    <a class="mt-4 text-center justify-center flex items-center gap-x-6 py-5 p-4 rounded-lg border-2 border-dashed border-gray-300 ring-gray-900/5 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 cursor-pointer" wire:click="createProjectDrawer">
                        <div>
                            <svg class="mx-auto size-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-semibold text-gray-900">Aucun Projets créé</h3>
                            <p class="mt-1 text-sm text-gray-500 flex items-center">Vous pouvez en créer un jiri ici
                                <svg width="12"
                                     height="12"
                                     xmlns="http://www.w3.org/2000/svg"
                                     class="inline cursor-pointer rounded h-5 w-5 shrink-0 ml-1 rotate-45"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke-width="1.5"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M6 18 18 6M6 6l12 12"/>
                                </svg>
                            </p>
                        </div>
                    </a>
                @endif
            </div>
            <div class="col-span-5 mt-4">
                <div class="flex justify-between">
                    <div class="flex gap-x-2">
                        <h2 class="text-base/7 font-semibold text-gray-900">Les derniers contacts</h2>
                        <button id="addLinks"
                                type="button"
                                value="L'intitulé des liens qui seront attribués aux projets"
                                wire:click="createContactDrawer"
                                class="flex items-center justify-center rounded bg-indigo-600 ml-1 px-2 py-1 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        >Ajouter
                        </button>
                    </div>
                    <a wire:navigate href="/projects" title="Aller vers la page du jiris" type="button" class="text-sm/6 font-semibold text-indigo-600 hover:text-indigo-500">Voir
                        plus</a>
                </div>
                <div class="inline-block min-w-full py-2 align-middle">
                    @if(count($contacts))
                        <table class="min-w-full divide-y divide-gray-300 border mt-4 shadow-sm ring-1 ring-gray-900/5">
                            <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($contacts as $contact)
                                <livewire:partials.contact-item :$contact :key="'contact'.$contact->id"/>
                            @endforeach
                            </tbody>
                        </table>
                    @else
                        <a class="mt-4 text-center justify-center flex items-center gap-x-6 py-5 p-4 rounded-lg border-2 border-dashed border-gray-300 ring-gray-900/5 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 cursor-pointer" wire:click="createContactDrawer">
                            <div>
                                <svg class="mx-auto size-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-semibold text-gray-900">Aucun contacts créé</h3>
                                <p class="mt-1 text-sm text-gray-500 flex items-center">Vous pouvez en créer un contact
                                    ici
                                    <svg width="12"
                                         height="12"
                                         xmlns="http://www.w3.org/2000/svg"
                                         class="inline cursor-pointer rounded h-5 w-5 shrink-0 ml-1 rotate-45"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke-width="1.5"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M6 18 18 6M6 6l12 12"/>
                                    </svg>
                                </p>
                            </div>
                        </a>
                    @endif
                </div>
            </div>
            <livewire:partials.delete-modal/>
            <livewire:partials.jiris-drawers/>
            <livewire:partials.contacts-drawers/>
            <livewire:partials.projects-drawers/>
        @endif
    </div>
</div>

