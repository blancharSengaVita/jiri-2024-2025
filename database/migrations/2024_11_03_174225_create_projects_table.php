<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description');
            $table->json('links')->nullable();
            $table->json('tasks')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::table('projects', static function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('projects');
    }
};
