<?php

use App\Models\Project;
use function Livewire\Volt\{state, mount, on};

state([
	'project',
]);

mount(function (Project $project) {
	$this->project = $project;
});

$edit = function (Project $project) {
	$this->dispatch('openEditProjectDrawer', project: $project)->to('partials.projects-drawers');
};

$create = function () {
	$this->dispatch('openCreateProjectDrawer')->to('partials.projects-drawers');
};

$delete = function (Project $project) {
	$this->dispatch('openDeleteModal', modelName: 'App\Models\Project',  modelId: $project->id,)->to('partials.delete-modal');
};

on(['refreshDashboardItems' => function () {
    $this->mount($this->project);
}]);
?>
<li class="flex items-center justify-between gap-x-6 py-5 p-4">
    <div class="min-w-0">
        <div class="flex items-start gap-x-3">
            <p class="text-sm/6 font-semibold text-gray-900">{{$project->name}}</p>
        </div>
    </div>
    <div class="flex flex-none items-center gap-x-4">
        <button href="#"
                wire:click="edit({{$project}})"
                class="rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 block">
            Voir
            le projet<span class="sr-only">, </span>
        </button>
        <button wire:click="delete({{$project}})"
                type="button"
                class="rounded-md bg-indigo-600 px-2.5 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Supprimer
        </button>
    </div>
</li>
