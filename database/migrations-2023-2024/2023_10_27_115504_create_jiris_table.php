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
        Schema::create('jiris', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('starting_at')->nullable();
            $table->enum('status', ['en cours', 'non commencé', 'terminé', 'en pause'])->default('non commencé');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jiris');
    }
};
