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

$restartJiri = function () {
    session('currentJiri')->status = Jiri::STATUS_IN_PROGRESS;
    session('currentJiri')->save();
    $this->dispatch('refreshSidebar');
    $this->dispatch('refreshComponent');
    Toaster::success('Le jiri a été relancé');
};

?>

<button id=""
        type="button"
        value="Relancer le jiri"
        wire:click="restartJiri"
        class="flex items-center justify-center rounded  px-2 py-2 text-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 bg-white font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:block"
>Relancer le jiri
</button>
