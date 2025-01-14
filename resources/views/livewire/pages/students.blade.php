<?php

use App\Models\Attendance;
use App\Models\Jiri;
use function Livewire\Volt\{layout, mount, state, rules, computed, updated, on};
use Illuminate\Support\Facades\Auth;
use App\Models\Grade;
use Illuminate\Support\Collection;
use \Masmerise\Toaster\Toaster;

state([
    'student',
    'evaluator',
    'jiri',
    'implementations',
    'grades',
    'marks',
    'updated',
    'comments',
    'coucou',
]);

layout('layouts.app');

rules(fn() => [
    'marks.*' => 'numeric',
])->messages([
    'marks.*.required' => 'Le champ doit être un nombre',
])->attributes([
]);

mount(function (Attendance $student) {
    $this->student = $student;
    $this->student->load('jiri');
    $this->jiri = $this->student->jiri;
    $this->marks = new Collection();
    $this->comments = new Collection();
    $this->updateds = new Collection();
    $this->marksBackup = $this->marks->toArray();


    $gradeQuery = Grade::where('jiri_id', $this->jiri->id)
        ->where('student_id', $this->student->id);

    if (auth::check()) {
        $this->user = Auth::user();
        $this->grades = $gradeQuery->where('user_id', $this->user->id)->get();
    }

    if (session('evaluator')) {
        $this->evaluator = session('evaluator');
        $this->grades = $gradeQuery->where('evaluator_id', $this->evaluator->id)->get();
    }

    foreach ($this->grades as $grade) {
        $grade->load('duty');
        $this->marks->put($grade->duty->project->name, $grade->grade);
        $this->comments->put($grade->duty->project->name, $grade->comment);
        $grade->updated = false;
    }
});

$save = function (Grade $grade) {
    $grade->grade = $this->marks[$grade->duty->project->name];
    $grade->comment = $this->comments[$grade->duty->project->name];
    $grade->save();
    Toaster::success('Changement enregistré pour le projet : ' . $grade->duty->project->name);
    $this->dispatch('saved')->self();
};

$cancel = function (Grade $grade) {
    $this->marks[$grade->duty->project->name] = $grade->grade;
    $this->comments[$grade->duty->project->name] = $grade->comments;
};

on(['saved' => function () {
    Toaster::success('saved');
}])
?>

<div class="py-10"
     x-data="{
     }"
>
    <x-slot name="h1">
        {{$this->student->contact->name}}, {{$this->jiri->name}}
    </x-slot>
    <div class="flex gap-x-2 items-center mb-4">
        <h1 class="text-3xl font-bold leading-tight tracking-tight text-gray-900">
            {{$this->student->contact->name}}, {{$this->jiri->name}} <span class="text-red-500"></span></h1>
    </div>
    {{--    <input type="text"--}}
    {{--           name="coucou"--}}
    {{--           id="coucou"--}}
    {{--           wire:model.live="coucou"--}}
    {{--           class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm/6">--}}
    @foreach($grades as $grade)
        <form wire:submit.prevent="save({{$grade}})" class=" bg-white border mt-4 shadow-sm ring-1 ring-gray-900/5 p-4">
            <div class="flex items-center gap-x-2">
                @if(true)
                    <h2 class="text-base/7 font-semibold text-gray-900">{{$grade->duty->project->name}}</h2>
                @else
                    <h2 class="text-base/7 font-semibold text-gray-900">{{$grade->duty->project->name}}</h2>
                    <svg class="text-red-500 sm:size-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon">
                        <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14ZM8 4a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-1.5 0v-3A.75.75 0 0 1 8 4Zm0 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm text-red-600" id="email-error"> Les changements n'ont pas été enregistré</p>
                @endif
            </div>

            <p class="mt-1 text-sm/6 text-gray-500">{{$grade->duty->project->description}}</p>
            <fieldset class="mt-6 pt-2 border-t border-gray-200 text-sm/6">
                <label for="name" class="mt-2 block text-sm/6 font-medium text-gray-900 sm:pt-1.5">Note sur 20</label>
                <input type="number"
                       @if ($loop->first)
                           x-init="$el.focus()"
                       autofocus
                       @endif
                       name="mark-{{$grade->duty->project->name}}"
                       id="mark-{{$grade->duty->project->name}}"
                       wire:model.live="marks.{{$grade->duty->project->name}}"
                       class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm/6 marks">
                @if ($messages = $errors->get('marks' . $grade->duty->project->name))
                    <div class="text-sm text-red-600 space-y-1 mt-2">
                        <p>{{$messages[0]}}</p>
                    </div>
                @endif
            </fieldset>
            <fieldset class="mb-2 text-sm/6 w-full">
                <label for="date"
                       class="mt-2 block text-sm/6 font-medium text-gray-900 sm:pt-1.5">Commentaire</label>
                <textarea
                    name="comment-{{$grade->duty->project->name}}"
                    id="comment-{{$grade->duty->project->name}}"
                    wire:model.live="comments.{{$grade->duty->project->name}}"
                    class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm/6 comments" rows="5"></textarea>
                @if ($messages = $errors->get('comments' . $grade->duty->project->name))
                    <div class="text-sm text-red-600 space-y-1 mt-2">
                        <p>{{$messages[0]}}</p>
                    </div>
                @endif
            </fieldset>
            <div class="flex justify-end mt-6 pt-4 divide-y divide-gray-100 border-t border-gray-200 text-sm/6">
                <div class="mt-4 sm:ml-16 sm:mt-0 flex">
                    <button type="button"
                            wire:click="cancel({{$grade}})"
                            class="rounded-md bg-white px-3 py-2 mr-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="submit"
                            class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Enregistrer
                    </button>
                </div>
            </div>
        </form>
    @endforeach
</div>
@script
<script>
    addEventListener('DOMContentLoaded', (event) => {
        const initialValues = {};

        const marks = document.querySelectorAll('.marks');
        const comments = document.querySelectorAll('.comments');

        function setInitialValues (input) {
                initialValues[input.id] = parseInt(input.value);
        }

        marks.forEach((mark) => {
            setInitialValues(mark);
            mark.addEventListener('change', (e) => {
                if (parseInt(mark.value) !== initialValues[mark.id]) {
                    console.log('On a changé');
                }
            });
        });

        $wire.on('saved', () => {
            marks.forEach((mark) => {
                setInitialValues(mark);
            });
        });
    });
</script>
@endscript

