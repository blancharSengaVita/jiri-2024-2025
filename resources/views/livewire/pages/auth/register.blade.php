<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use function Livewire\Volt\layout;
use function Livewire\Volt\rules;
use function Livewire\Volt\state;

layout('layouts.guest');

state([
    'email' => '',
    'name' => '',
    'password' => '',
    'password_confirmation' => ''
]);

rules([
	'name' => 'required',
    'email' => ['required', 'string', 'lowercase', 'email:strict', 'max:255', 'unique:' . User::class],
    'password' => [
        'required',
        'string',
        'confirmed',
        Rules\Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised()
    ],
]);

$register = function () {
    $validated = $this->validate();
    $validated['password'] = Hash::make($validated['password']);

    $user = User::create($validated);

    Auth::login($user);

    $this->redirect(route('pages.dashboard', absolute: false), navigate: true);
};

?>

<div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
    <x-slot name="h1">
        Inscrivez-vous
    </x-slot>
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <img class="mx-auto h-10 w-auto" src="https://tailwindui.com/plus/img/logos/mark.svg?color=indigo&shade=600" alt="Your Company">
        <p class="mt-6 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">Inscrivez-vous</p>
        <p class="mt-2 text-sm text-gray-700 text-center">Créez votre compte pour accéder à votre espace dédié et optimiser la gestion de vos jurys en toute simplicité.</p>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-[480px]">
        <div class="bg-white px-6 py-12 shadow sm:rounded-lg sm:px-12">
            <form wire:submit="register" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Nom</label>
                    <div class="mt-2">
                        <input wire:model="name" id="name" name="name" type="text" autocomplete="name" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    <x-input-error :messages="$errors->get('name')" class="mt-2"/>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium leading-6 text-gray-900">E-mail</label>
                    <div class="mt-2">
                        <input wire:model="email" id="email" name="email" type="email" autocomplete="email" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2"/>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium leading-6 text-gray-900">Mot de passe</label>
                    <div class="mt-2">
                        <input wire:model="password" id="password" name="password" type="password" autocomplete="current-password" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    <p class="mt-2 text-sm text-gray-500" id="password-description">Le mot de passe doit contenir au
                        moins une lettre majuscule, une lettre minuscule, un chiffre et un symbole. </p>
                    <x-input-error :messages="$errors->get('password')" class="mt-2"/>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium leading-6 text-gray-900">Confirmation
                        du mot de passe</label>
                    <div class="mt-2">
                        <input wire:model="password_confirmation" id="password_confirmation" name="password_confirmation" type="password" autocomplete="current-password" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2"/>

                </div>

                <div>
                    <button type="submit" class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        S'inscrire
                    </button>
                </div>
            </form>
        </div>
        <p class="mt-10 text-center text-sm text-gray-500">
            DéJà inscrit ?
            <a href="{{ route('login') }}" title="Vers la page de connexion" wire:navigate class="font-semibold leading-6 text-indigo-600 hover:text-indigo-500">Connectez-vous</a>
        </p>
    </div>
</div>
