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

<div class="flex min-h-full flex-col justify-center py-16 sm:px-6 lg:px-8">
    <x-slot name="h1">
        Inscrivez-vous
    </x-slot>
    <x-auth-session-status class="mb-4" :status="session('status')"/>
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
{{--        <div class="my-auto flex h-16 shrink-0 justify-center items-center text-indigo-600">--}}
{{--            <svg class="fill-current" width="37" height="37" viewBox="0 0 37 37" xmlns="http://www.w3.org/2000/svg">--}}
{{--                <rect x="35.1433" y="17.14" width="6" height="24" transform="rotate(130.61 35.1433 17.14)" />--}}
{{--                <path d="M16.9235 1.51831L21.4784 5.42372L11.064 17.5703L8.46173 11.3874L16.9235 1.51831Z" />--}}
{{--                <path d="M19.2009 3.47102L16.9235 1.51831L18.2253 -8.88109e-06L19.2009 3.47102Z" />--}}
{{--                <path d="M32.8659 15.1873L35.1433 17.14L36.4451 15.6216L32.8659 15.1873Z" />--}}
{{--                <rect x="1.3018" y="19.7382" width="6" height="24" transform="rotate(-49.3903 1.3018 19.7382)" />--}}
{{--                <path d="M19.5217 35.3598L14.9667 31.4544L25.3811 19.3079L27.9834 25.4907L19.5217 35.3598Z" />--}}
{{--                <path d="M17.2442 33.4071L19.5217 35.3598L18.2199 36.8782L17.2442 33.4071Z" />--}}
{{--                <path d="M3.57929 21.6909L1.3018 19.7382L-3.51667e-06 21.2565L3.57929 21.6909Z" />--}}
{{--                <rect x="22.4451" y="18.3232" width="6" height="6" transform="rotate(130.61 22.4451 18.3232)" />--}}
{{--            </svg>--}}
{{--        </div>--}}
        <p class="mt-6 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">Inscrivez-vous</p>
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

            <div>
                {{--                <div class="relative mt-10">--}}
                {{--                    <div class="absolute inset-0 flex items-center" aria-hidden="true">--}}
                {{--                        <div class="w-full border-t border-gray-200"></div>--}}
                {{--                    </div>--}}
                {{--                    <div class="relative flex justify-center text-sm font-medium leading-6">--}}
                {{--                        <span class="bg-white px-6 text-gray-900">Ou s'inscrire avec </span>--}}
                {{--                    </div>--}}
                {{--                </div>--}}

                {{--                <div class="mt-6 grid grid-cols-2 gap-4">--}}
                {{--                    <a href="#" class="flex w-full items-center justify-center gap-3 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus-visible:ring-transparent">--}}
                {{--                        <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true">--}}
                {{--                            <path d="M12.0003 4.75C13.7703 4.75 15.3553 5.36002 16.6053 6.54998L20.0303 3.125C17.9502 1.19 15.2353 0 12.0003 0C7.31028 0 3.25527 2.69 1.28027 6.60998L5.27028 9.70498C6.21525 6.86002 8.87028 4.75 12.0003 4.75Z" fill="#EA4335"/>--}}
                {{--                            <path d="M23.49 12.275C23.49 11.49 23.415 10.73 23.3 10H12V14.51H18.47C18.18 15.99 17.34 17.25 16.08 18.1L19.945 21.1C22.2 19.01 23.49 15.92 23.49 12.275Z" fill="#4285F4"/>--}}
                {{--                            <path d="M5.26498 14.2949C5.02498 13.5699 4.88501 12.7999 4.88501 11.9999C4.88501 11.1999 5.01998 10.4299 5.26498 9.7049L1.275 6.60986C0.46 8.22986 0 10.0599 0 11.9999C0 13.9399 0.46 15.7699 1.28 17.3899L5.26498 14.2949Z" fill="#FBBC05"/>--}}
                {{--                            <path d="M12.0004 24.0001C15.2404 24.0001 17.9654 22.935 19.9454 21.095L16.0804 18.095C15.0054 18.82 13.6204 19.245 12.0004 19.245C8.8704 19.245 6.21537 17.135 5.2654 14.29L1.27539 17.385C3.25539 21.31 7.3104 24.0001 12.0004 24.0001Z" fill="#34A853"/>--}}
                {{--                        </svg>--}}
                {{--                        <span class="text-sm font-semibold leading-6">Google</span>--}}
                {{--                    </a>--}}

                {{--                    <a href="#" class="flex w-full items-center justify-center gap-3 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus-visible:ring-transparent">--}}
                {{--                        <svg class="h-5 w-5 fill-[#24292F]" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">--}}
                {{--                            <path fill-rule="evenodd" d="M10 0C4.477 0 0 4.484 0 10.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0110 4.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.203 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.942.359.31.678.921.678 1.856 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0020 10.017C20 4.484 15.522 0 10 0z" clip-rule="evenodd"/>--}}
                {{--                        </svg>--}}
                {{--                        <span class="text-sm font-semibold leading-6">GitHub</span>--}}
                {{--                    </a>--}}
                {{--                </div>--}}
            </div>
        </div>
        <p class="mt-10 text-center text-sm text-gray-500">
            DéJà inscrit ?
            <a href="{{ route('login') }}" title="Vers la page de connexion" wire:navigate class="font-semibold leading-6 text-indigo-600 hover:text-indigo-500">Connectez-vous</a>
        </p>
    </div>
</div>
