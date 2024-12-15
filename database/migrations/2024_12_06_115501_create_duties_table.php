<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('duties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('jiri_id')->constrained()->onDelete('cascade');
            $table->integer('weighting')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('duties', static function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['jiri_id']);
        });
        Schema::dropIfExists('duties');
    }
};

