<?php

use App\Models\Jiri;
use function Livewire\Volt\{layout, mount, rules, state, on};
use Illuminate\Support\Facades\Auth;
use Masmerise\Toaster\Toaster;
use Carbon\Carbon;
use App\Models\Duties;
use Illuminate\Support\Facades\URL;

state([
    'user',
]);

layout('layouts.app');

mount(function () {
    if(request()->has('token')) {
        $this->redirect(URL::current(), navigate: true);
    }
});
?>

<div class="py-10">
    <h1 class="text-3xl font-bold leading-tight tracking-tight text-gray-900 mb-4">Dashboard</h1>
    <p>Salut</p>
</div>

