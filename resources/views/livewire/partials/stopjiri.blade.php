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


$stopJiri = function () {
    $currentJiri = Jiri::find(session('currentJiri')->id);
    $currentJiri->status = Jiri::STATUS_FINISHED;
    $currentJiri->save();

    foreach ($currentJiri->evaluators as $evaluator){
        $attendance = Attendance::where('role', 'evaluator')
            ->where('jiri_id', $currentJiri->id)
            ->where('contact_id',$evaluator->id)
            ->first();
        $attendance->token = null;
        $attendance->save();
    }
    session()->forget('currentJiri');
    $this->dispatch('refreshSidebar');
    $this->dispatch('refreshJiriItem');
    $this->dispatch('refreshComponent');
    Toaster::success('Le jiri a été stoppé');
};

?>

<button id=""
        type="button"
        value="Mettre fin au jiri"
        wire:click="stopJiri"
        class="rounded-md bg-red-600 px-2.5 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 sm:block">
    Stopper le jiri
</button>
