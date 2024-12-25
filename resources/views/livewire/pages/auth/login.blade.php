<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;

use function Livewire\Volt\form;
use function Livewire\Volt\layout;

layout('layouts.guest');

form(LoginForm::class);

$login = function () {
    $this->validate();

    $this->form->authenticate();

    Session::regenerate();

    $this->redirectIntended(default: route('pages.dashboard', absolute: false), navigate: true);
};

?>
<div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <img class="mx-auto h-10 w-auto" src="https://tailwindui.com/plus/img/logos/mark.svg?color=indigo&shade=600" alt="Your Company">
        <h2 class="mt-6 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">{{ __('auth.login.title')}}</h2>
        <p class="mt-2 text-sm text-gray-700 text-center">Connectez-vous pour accéder à votre espace dédié pour optimisez la gestion de vos jurys avec simplicité.</p>
    </div>


    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-[480px]">
        <div class="bg-white px-6 py-12 shadow sm:rounded-lg sm:px-12">
            <form
                wire:submit.prevent="login"
                class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium leading-6 text-gray-900">{{__('auth.fields.email.label')}}</label>
                    <div class="mt-2">
                        <input dusk="email"
                               id="email"
                               name="email"
                               wire:model="form.email"
                               type="email"
                               autocomplete="email"
                               required
                               class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    <x-input-error
                        dusk="email-error"
                        :messages="$errors->get('form.email')" class="mt-2"/>
                </div>

                <div>
                    <label for="password"
                           class="block text-sm font-medium leading-6 text-gray-900">{{__('auth.fields.password.label')}}</label>
                    <div class="mt-2">
                        <input dusk="password"
                               id="password"
                               name="password"
                               wire:model="form.password"
                               type="password"
                               autocomplete="current-password"
                               required
                               class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focs:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    <x-input-error dusk="password-error" :messages="$errors->get('form.password')" class="mt-2"/>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" wire:model="form.remember-me" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                        <label for="remember-me" class="ml-3 block text-sm leading-6 text-gray-900">{{__('auth.login.remember_me')}}</label>
                    </div>

                    <div class="text-sm leading-6">
                        <a href="#" class="font-semibold text-indigo-600 hover:text-indigo-500">{{__('auth.login.forgot_password')}}</a>
                    </div>
                </div>

                <div>
                    <button type="submit" class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        {{__('auth.login.sign_in')}}
                    </button>
                </div>
            </form>
        </div>
                <p class="mt-10 text-center text-sm text-gray-500">
                    Pas encore inscrit ?
                    <a wire:navigate href="/register" class="font-semibold leading-6 text-indigo-600 hover:text-indigo-500">Inscrivez-vous</a>
                </p>
    </div>
</div>
