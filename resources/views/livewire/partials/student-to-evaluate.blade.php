<?php

use function Livewire\Volt\{state, mount};
use App\Models\Jiri;
use App\Models\Attendance;
use Masmerise\Toaster\Toaster;
use Illuminate\Support\Facades\Auth;

state([
	'jiri',
	'user',
	'students',
]);

mount(function (Jiri $jiri) {
	$this->jiri = $jiri;
	$this->user = Auth::user() ?: session('evaluator');
	$this->students = $this->jiri->students()->get();
	foreach ($this->students as $student) {
		$student->myAttendance = Attendance::where('jiri_id', $this->jiri->id)
			->where('contact_id', $student->id)
            ->first();
	}
});

?>
<div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4 border-t border-gray-100 text-sm/6 ">
    @foreach($students as $student)
        <div class="flex justify-between bg-white border shadow-sm ring-1 ring-gray-900/5 p-4">
            <div class="flex items-center  gap-x-2">
                <img class="h-8 w-8 rounded-full"
                     src="{{$student->photo ? asset($student->photo) : 'https://ui-avatars.com/api/?length=1&name='. $student->name}}"
                     alt="Photo de {{$student->name}}"/>
                <div class="font-medium text-gray-900">{{$student->name}}</div>
            </div>
            <div class="flex items-center gap-x-2">
                {{--                <button wire:click="deleteFromJiri({{$student}})" type="button"--}}
                {{--                        class="font-semibold text-indigo-600 hover:text-indigo-500">--}}
                {{--                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"--}}
                {{--                         stroke="currentColor" class="size-6">--}}
                {{--                        <path stroke-linecap="round" stroke-linejoin="round"--}}
                {{--                              d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>--}}
                {{--                    </svg>--}}
                {{--                </button>--}}
                <a
                    wire:navigate
                    href="{{route('pages.students', $student->myAttendance->id)}}"
                    title="Voir les projets de l'étudiant"
                    class="font-semibold text-indigo-600 hover:text-indigo-500 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                         stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </svg>
                </a>
            </div>
        </div>
    @endforeach
</div>
{{--</div>--}}
