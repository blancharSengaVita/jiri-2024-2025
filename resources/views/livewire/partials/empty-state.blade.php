<?php

use function Livewire\Volt\{state, mount};

state([
	'modelName',
	'eventName',
	'componentToTarget',
	'svgPath',
]);

mount(function ($modelName, $eventName, $componentToTarget, $svgPath){
	$this->modelName = $modelName;
	$this->eventName = $eventName;
	$this->componentToTarget = $componentToTarget;
	$this->svgPath = $svgPath;

});

$open = function (){
	$this->dispatch($this->eventName)->to($this->componentToTarget);
}
?>

<div class="mt-10 flex items-center text-center justify-center">
    <div class="">
        <svg class="mx-auto size-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">

        </svg>
        <h3 class="mt-2 text-sm font-semibold text-gray-900">Aucun {{$modelName}} crée</h3>
        <p class="mt-1 text-sm text-gray-500">Vous pouvez créer un {{$modelName}} ici</p>
        <div class="mt-6">
            <button wire:click="open" type="button" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <svg class="-ml-0.5 mr-1.5 size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                    {{$svgPath}}
                </svg>
                Nouveau {{$modelName}}
            </button>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
            </svg>

        </div>
    </div>
</div>
