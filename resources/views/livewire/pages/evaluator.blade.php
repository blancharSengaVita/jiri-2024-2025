<?php

use App\Models\Jiri;
use function Livewire\Volt\{layout, mount, rules, state, on};
use Illuminate\Support\Facades\Auth;
use Masmerise\Toaster\Toaster;
use Carbon\Carbon;
use App\Models\Duties;

state([
    'user',
]);

layout('layouts.app');

mount(function () {

});
?>

<div class="py-10">
    <h1 class="text-3xl font-bold leading-tight tracking-tight text-gray-900 mb-4">Dashboard</h1>
    <p>Salut</p>
</div>

