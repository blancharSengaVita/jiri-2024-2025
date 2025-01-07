<?php

use function Livewire\Volt\{state, mount};
use App\Models\Jiri;
use App\Models\Attendance;
use Masmerise\Toaster\Toaster;
use Illuminate\Support\Facades\Auth;

state([
	'jiri',
	'user',
]);

mount(function (Jiri $jiri) {
	$this->jiri = $jiri;
	$this->user = Auth::user();
});

$pauseJiri = function () {
	session('currentJiri')->status = Jiri::STATUS_ON_PAUSE;
	session('currentJiri')->save();
	$this->dispatch('refreshComponent');
	$this->dispatch('refreshSidebar');
	Toaster::success('Le jiri a été mis en pause');
};
?>

<button id=""
        type="button"
        value="Mettre en pause le jiri"
        wire:click="pauseJiri"
        class="flex items-center justify-center rounded  px-2 py-2 text-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 bg-white font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:block"
>Mettre en pause le jiri
</button>
