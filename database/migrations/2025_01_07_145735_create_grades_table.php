<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jiri_id')->constrained('jiris')->onDelete('cascade');
            $table->foreignId('duty_id')->constrained('duties')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('attendances')->onDelete('cascade');
            $table->foreignId('evaluator_id')->nullable()->constrained('attendances')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->integer('grade')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropForeign(['duty_id']);
            $table->dropForeign(['evaluator_id']);
            $table->dropForeign(['student_id']);
            $table->dropForeign(['jiri_id']);
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('grades');
    }
};
