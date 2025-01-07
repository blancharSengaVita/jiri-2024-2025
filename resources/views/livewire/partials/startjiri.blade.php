<?php

use function Livewire\Volt\{state, mount};
use App\Models\Jiri;
use App\Models\Duties;
use Masmerise\Toaster\Toaster;
use App\Jobs\SendJiriLaunchedEmails;
use Illuminate\Support\Facades\Auth;

state([
	'jiri',
	'user',
]);

mount(function (Jiri $jiri) {
	$this->jiri = $jiri;
	$this->user = Auth::user();
});



$start = function () {
	$this->mount($this->jiri);
	$duties = Duties::where('jiri_id', $this->jiri->id)->get();
	$sum = 0;

    if ($this->jiri->students->isEmpty()) {
        Toaster::error('Il n\'y a pas d\'élèves dans le jiri');
        return false;
    }

    if ($this->jiri->evaluators->isEmpty()) {
        Toaster::error('Il n\'y a pas d\'évaluateurs dans le jiri');
        return false;
    }


    if ($this->jiri->duties->isEmpty()) {
        Toaster::error('Il n\'y a pas de projects dans le jiri');
        return false;
    }

	foreach ($duties as $duty) {
		$sum += (int)$duty->weighting;

		if ($duty['weighting'] === null || $duty['weighting'] === '') {
			Toaster::error('Un projet ou plusieurs n\'ont pas de pondération.');
			return false;
		}
	}

	if ($sum !== 100) {
		Toaster::error('La somme des pondérations des projets doit être égale à 100.');
		return false;
	}

	//pour chaque evaluator d'un jiri
    //leur assigner un étudiants
    //et pour chaques etudiants leurs assigner un projets
    //leurs assigner un projets

	$this->jiri = Jiri::find($this->jiri->id);
	SendJiriLaunchedEmails::dispatch($this->jiri, $this->user->name);
	$this->jiri->status = Jiri::STATUS_IN_PROGRESS;
	$this->jiri->save();
	session(['jiriLaunched' => 'Le jiri a été lancé avec succès!']);
	session(['currentJiri' => $this->jiri]);
	Toaster::success('Le jiri a été lancé');
	$this->redirect(route('pages.dashboard', absolute: false), navigate: true);
	$this->mount($this->jiri);
};

?>

<button type="button"
        wire:click="start({{$jiri}})"
        class="rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:block">
    Lancer le jiri<span class="sr-only">{{$jiri->name}}</span>
</button>

