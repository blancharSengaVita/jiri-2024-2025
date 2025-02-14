<?php

use App\Models\Jiri;
use App\Livewire\Actions\Logout;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use  \Illuminate\Support\Facades\Route;
use function Livewire\Volt\{
	state,
	mount,
	on,
};

state([
	'mobileMenu',
	'profilePictureSource',
	'route' => request()->url(),
	'user',
	'title',
	'disconnectButton',
	'headerDisconnectButton',
]);

mount(function () {
	$this->mobileMenu = false;
	$this->disconnectButton = false;
	$this->headerDisconnectButton = false;
	if (session('evaluator')) {
		session('evaluator')->load('contact');
		session('evaluator')->load('jiri');
		$this->attendance = session('evaluator');
			$this->attendance->contact()->first()->name ?? '';
	}
	$this->user = Auth::user() ?: $this->attendance->contact()->first();

	if ($this->user->profil_picture) {
		$this->profilePictureSource = '/storage/images/1024/' . $this->user->profil_picture;
	} else {
		$this->profilePictureSource = 'https://ui-avatars.com/api/?length=1&name=' . $this->user->game_name;
	}
});

$showDisconnectButton = function () {
	$this->disconnectButton = !$this->disconnectButton;
};

$showHeaderDisconnectButton = function () {
	$this->headerDisconnectButton = !$this->headerDisconnectButton;
};

$destroySession = function () {
	session()->forget('attendance');
	$this->redirect(\route('login'), navigate: true);
};

$openMobileMenu = function () {
	$this->mobileMenu = !$this->mobileMenu;
};

$logout = function (Logout $logout) {
	$logout();

	$this->redirect(\route('login'), navigate: true);
};

on(['refreshSidebar' => function () {
	$this->mount();
}]);
?>

<div
    x-data="{
    open: $wire.entangle('mobileMenu'),
    disconnectButton: $wire.entangle('disconnectButton'),
    headerDisconnectButton: $wire.entangle('headerDisconnectButton'),
    }"
>
    <!-- Off-canvas menu for mobile, show/hide based on off-canvas menu state. -->
    <div x-cloak x-show="open" :class=" open ? 'z-50 ' : ''" role="dialog" aria-modal="true">
        <!--
          Off-canvas menu backdrop, show/hide based on off-canvas menu state.

          Entering: "transition-opacity ease-linear duration-300"
            From: "opacity-0"
            To: "opacity-100"
          Leaving: "transition-opacity ease-linear duration-300"
            From: "opacity-100"
            To: "opacity-0"
        -->
        <div class="fixed inset-0 bg-gray-900/80" aria-hidden="true"></div>

        <div class="fixed inset-0 flex">
            <!--
              Off-canvas menu, show/hide based on off-canvas menu state.

              Entering: "transition ease-in-out duration-300 transform"
                From: "-translate-x-full"
                To: "translate-x-0"
              Leaving: "transition ease-in-out duration-300 transform"
                From: "translate-x-0"
                To: "-translate-x-full"
            -->
            <div class="relative mr-16 flex w-full max-w-xs flex-1">
                <!--
                  Close button, show/hide based on off-canvas menu state.

                  Entering: "ease-in-out duration-300"
                    From: "opacity-0"
                    To: "opacity-100"
                  Leaving: "ease-in-out duration-300"
                    From: "opacity-100"
                    To: "opacity-0"
                -->
                <div :class=" open ? '' : 'hidden'" class="absolute left-full top-0 flex w-16 justify-center pt-5">
                    <button wire:click="openMobileMenu" type="button" class="-m-2.5 p-2.5">
                        <span class="sr-only">Close sidebar</span>
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" aria-hidden="true" data-slot="icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Sidebar component, swap this element with another sidebar if you like -->
                <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-gray-900 px-6 pb-2 ring-1 ring-white/10">
                    <div class="flex h-16 shrink-0 items-center">
                        <img class="h-8 w-auto"
                             src="https://tailwindui.com/plus/img/logos/mark.svg?color=indigo&shade=500"
                             alt="Your Company">
                    </div>
                    <div class="flex flex-1 flex-col">
                        <ul role="list" class="flex flex-1 flex-col gap-y-7">
                            <li>
                                <ul role="list" class="-mx-2 space-y-1">
                                    @guest()
                                        <li>
                                            <!-- Current: "bg-gray-800 text-white", Default: "text-gray-400 hover:text-white hover:bg-gray-800" -->
                                            <a wire:navigate
                                               href="{{route('pages.evaluator')}}"
                                               class=" {{ Route::is('pages.dashboard') ? 'bg-gray-800 p-2 text-sm font-semibold leading-6 text-white' : 'p-2 text-sm font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white' }} group flex gap-x-3 rounded-md"
                                            >
                                                <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24"
                                                     stroke-width="1.5" stroke="currentColor" aria-hidden="true"
                                                     data-slot="icon">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                                                </svg>
                                                Dashboard
                                            </a>
                                        </li>
                                    @endguest()
                                    @auth()
                                        <li>
                                            <!-- Current: "bg-gray-800 text-white", Default: "text-gray-400 hover:text-white hover:bg-gray-800" -->
                                            <a wire:navigate
                                               href="{{route('pages.dashboard')}}"
                                               class=" {{ Route::is('pages.dashboard') ? 'bg-gray-800 p-2 text-sm font-semibold leading-6 text-white' : 'p-2 text-sm font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white' }} group flex gap-x-3 rounded-md"
                                            >
                                                <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24"
                                                     stroke-width="1.5" stroke="currentColor" aria-hidden="true"
                                                     data-slot="icon">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                                                </svg>
                                                Dashboard
                                            </a>
                                        </li>
                                        <li>
                                        <li>
                                                <a href="{{route('pages.jiris')}}"
                                               class=" {{ Route::is('pages.jiris') || Route::is('pages.jiris.edit')  ? 'bg-gray-800 p-2 text-sm font-semibold leading-6 text-white' : 'p-2 text-sm font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white' }} group flex gap-x-3 rounded-md">
                                                <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24"
                                                     stroke-width="1.5" stroke="currentColor" aria-hidden="true"
                                                     data-slot="icon">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/>
                                                </svg>
                                                Jiris
                                            </a>
                                        </li>
                                        <li>
                                            <a wire:navigate
                                               href="{{route('pages.projects')}}"
                                               class=" {{ Route::is('pages.projects') ? 'bg-gray-800 p-2 text-sm font-semibold leading-6 text-white' : 'p-2 text-sm font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white' }} group flex gap-x-3 rounded-md">
                                                <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24"
                                                     stroke-width="1.5"
                                                     stroke="currentColor" aria-hidden="true" data-slot="icon">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/>
                                                </svg>
                                                Projects
                                            </a>
                                        </li>
                                        <li>
                                            <a wire:navigate
                                               href="{{route('pages.contacts')}}"
                                               class="{{ Route::is('pages.contacts') ? 'bg-gray-800 p-2 text-sm font-semibold leading-6 text-white' : 'p-2 text-sm font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white' }} group flex gap-x-3 rounded-md">
                                                <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24"
                                                     stroke-width="1.5"
                                                     stroke="currentColor" aria-hidden="true" data-slot="icon">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                                                </svg>
                                                Contacts
                                            </a>
                                        </li>
                                    @endauth
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Static sidebar for desktop -->
    <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
        <!-- Sidebar component, swap this element with another sidebar if you like -->
        <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-gray-900 px-6">
            <div class="flex h-16 shrink-0 items-center">
                <img class="h-8 w-auto" src="https://tailwindui.com/plus/img/logos/mark.svg?color=indigo&shade=500"
                     alt="Your Company">
            </div>
            <nav class="flex flex-1 flex-col">
                <h2 class="sr-only" >Menu de navigation</h2>
                <ul role="list" class="flex flex-1 flex-col gap-y-7">
                    <li>
                        <ul role="list" class="-mx-2 space-y-1">
                            @guest()
                                <li>
                                    <!-- Current: "bg-gray-800 text-white", Default: "text-gray-400 hover:text-white hover:bg-gray-800" -->
                                    <a wire:navigate
                                       href="{{route('pages.evaluator')}}"
                                       class=" {{ Route::is('pages.dashboard') ? 'bg-gray-800 p-2 text-sm font-semibold leading-6 text-white' : 'p-2 text-sm font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white' }} group flex gap-x-3 rounded-md"
                                    >
                                        <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24"
                                             stroke-width="1.5" stroke="currentColor" aria-hidden="true"
                                             data-slot="icon">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                                        </svg>
                                        Dashboard
                                    </a>
                                </li>
                            @endguest
                            @auth()
                                <li>
                                    <!-- Current: "bg-gray-800 text-white", Default: "text-gray-400 hover:text-white hover:bg-gray-800" -->
                                    <a wire:navigate
                                       href="{{route('pages.dashboard')}}"
                                       class=" {{ Route::is('pages.dashboard') ? 'bg-gray-800 p-2 text-sm font-semibold leading-6 text-white' : 'p-2 text-sm font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white' }} group flex gap-x-3 rounded-md items-center"
                                    >
                                        <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24"
                                             stroke-width="1.5" stroke="currentColor" aria-hidden="true"
                                             data-slot="icon">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                                        </svg>
                                        Dashboard
                                        @if(session('currentJiri') && session('currentJiri')->status === Jiri::STATUS_IN_PROGRESS)
                                            <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                                        @endif
                                        @if(session('currentJiri') && session('currentJiri')->status === Jiri::STATUS_ON_PAUSE)
                                            <div class="w-2 h-2 bg-amber-500 rounded-full"></div>
                                        @endif
                                    </a>
                                </li>
                                <li>
                                <li>
                                    <a href="{{route('pages.jiris')}}"
                                       class=" {{ Route::is('pages.jiris.index') || Route::is('pages.jiris.edit')  ? 'bg-gray-800 p-2 text-sm font-semibold leading-6 text-white' : 'p-2 text-sm font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white' }} group flex gap-x-3 rounded-md">
                                        <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24"
                                             stroke-width="1.5" stroke="currentColor" aria-hidden="true"
                                             data-slot="icon">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/>
                                        </svg>
                                        Jiris
                                    </a>
                                </li>
                                <li>
                                    <a wire:navigate
                                       href="{{route('pages.projects')}}"
                                       class=" {{ Route::is('pages.projects') ? 'bg-gray-800 p-2 text-sm font-semibold leading-6 text-white' : 'p-2 text-sm font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white' }} group flex gap-x-3 rounded-md">
                                        <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24"
                                             stroke-width="1.5"
                                             stroke="currentColor" aria-hidden="true" data-slot="icon">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/>
                                        </svg>
                                        Projects
                                    </a>
                                </li>
                                <li>
                                    <a wire:navigate
                                       href="{{route('pages.contacts')}}"
                                       class="{{ Route::is('pages.contacts') ? 'bg-gray-800 p-2 text-sm font-semibold leading-6 text-white' : 'p-2 text-sm font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white' }} group flex gap-x-3 rounded-md">
                                        <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24"
                                             stroke-width="1.5"
                                             stroke="currentColor" aria-hidden="true" data-slot="icon">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                                        </svg>
                                        Contacts
                                    </a>
                                </li>
                            @endauth
                        </ul>
                    </li>
                    <li class="-mx-6 mt-auto">
                        <div class="relative">
                            <div class="absolute
                            ms-4
                            mb-4
                        bottom-full
                        rounded-md z-50 mt-2 w-48
                        bg-white shadow-lg ring-1 ring-black/5 focus:outline-none
                        " role="menu" aria-orientation="vertical" aria-labelledby="options-menu-button" tabindex="-1">
                                <div class="py-1" role="none" x-cloak x-show="disconnectButton">
                                    <!-- Active: "bg-gray-100 text-gray-900 outline-none", Not Active: "text-gray-700" -->
                                    @guest()
                                        <button wire:click="destroySession" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1" id="options-menu-item-0">
                                            Se déconnecter
                                        </button>
                                    @endguest
                                    @auth()
                                        <button wire:click="logout" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1" id="options-menu-item-0">
                                            Se déconnecter
                                        </button>
                                    @endauth
                                </div>
                            </div>
                        </div>
                        <a href="#"
                           wire:click="showDisconnectButton"
                           class="flex items-center gap-x-4 px-6 py-3 text-sm font-semibold leading-6 text-white hover:bg-gray-800">
                            <img class="h-8 w-8 rounded-full bg-gray-800"
                                 src="https://ui-avatars.com/api/?length=1&name={{$user->name}}"
                                 alt="">
                            <span class="sr-only">Your profile</span>
                            <span aria-hidden="true">{{$user->name}}</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <nav class="sticky top-0 z-40 flex items-center gap-x-6 bg-gray-900 px-4 py-4 shadow-sm sm:px-6 lg:hidden"
         @click.away="open = false">
        <h2 class="sr-only">Menu de navigation</h2>
        <button wire:click="openMobileMenu" type="button" class="-m-2.5 p-2.5 text-gray-400 lg:hidden">
            <span class="sr-only">Open sidebar</span>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                 aria-hidden="true" data-slot="icon">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
            </svg>
        </button>
        <div class="flex-1 text-sm font-semibold leading-6 text-white">Dashboard</div>

        <div class="relative">
            <button type="button" wire:click="showHeaderDisconnectButton">
                <span class="sr-only">Your profile</span>
                <img class="h-8 w-8 rounded-full bg-gray-800"
                     src="https://ui-avatars.com/api/?length=1&name={{$user->name}}"
                     alt="">
            </button>
            <div class="absolute
                            ms-4
                            mb-4
                        right-0
                        top-full
                        rounded-md z-50 mt-2 w-48
                        bg-white shadow-lg ring-1 ring-black/5 focus:outline-none
                        " role="menu" aria-orientation="vertical" aria-labelledby="options-menu-button" tabindex="-1">
                <div class="py-1" role="none" x-cloak x-show="headerDisconnectButton">
                    <!-- Active: "bg-gray-100 text-gray-900 outline-none", Not Active: "text-gray-700" -->
                    @guest()
                        <button wire:click="destroySession" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1" id="options-menu-item-0">
                            Se déconnecter
                        </button>
                    @endguest
                    @auth()
                        <button wire:click="logout" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1" id="options-menu-item-0">
                            Se déconnecter
                        </button>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
</div>
