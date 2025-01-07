<?php

use App\Models\Attendance;
use App\Models\Jiri;
use function Livewire\Volt\{layout, mount, rules, state, on};
use Illuminate\Support\Facades\Auth;
use Masmerise\Toaster\Toaster;
use Carbon\Carbon;
use App\Models\Duties;

state([
    'student',
    'evaluator',
    'jiri'
]);

layout('layouts.app');

mount(function (Attendance $student) {
	$this->student = $student;
	$this->student->load('jiri');
    $this->jiri = $this->student->jiri;
	$this->student->load('contact');
});
?>

<div class="py-10"
     x-data="{
     }"
>
        <div class="flex gap-x-2 items-center mb-4">
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-gray-900">
                {{$this->student->contact->name}}, {{$this->jiri->name}}  <span class="text-red-500"></span></h1>
        </div>

        <div class="gap-4">
            <div class="col-span-5 mb-6">
                <h2 class="text-base/7 font-semibold text-gray-900 mb-2">Projet 1</h2>
            </div>
        </div>
</div>

