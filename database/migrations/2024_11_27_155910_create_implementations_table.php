<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('implementations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->foreignId('jiri_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->json('urls')->nullable();
            $table->json('tasks')->nullable();
            $table->integer('weighting')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('implementations', static function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['contact_id']);
            $table->dropForeign(['jiri_id']);
        });
        Schema::dropIfExists('implementations');
    }
};
