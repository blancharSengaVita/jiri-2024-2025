<?php

use function Livewire\Volt\{state, mount};

state([
	'paginator'
]);
mount(function ($paginator){
	$this->paginator = $paginator;
});
?>

<div>
    <p class="text-sm text-gray-700 leading-5">
        <span>{!! __('Showing') !!}</span>
        <span class="font-medium">{{ $paginator->firstItem() }}</span>
        <span>{!! __('to') !!}</span>
        <span class="font-medium">{{ $paginator->lastItem() }}</span>
        <span>{!! __('of') !!}</span>
        <span class="font-medium">{{ $paginator->total() }}</span>
        <span>{!! __('results') !!}</span>
    </p>
</div>
