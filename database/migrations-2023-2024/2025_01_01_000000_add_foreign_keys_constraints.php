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
        Schema::table('jiris', static function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
        });
        Schema::table('contacts', static function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
        });
        Schema::table('attendances', static function (Blueprint $table) {
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->foreignId('jiri_id')->constrained()->onDelete('cascade');
        });
        Schema::table('projects', static function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
        });
        Schema::table('implementations', static function (Blueprint $table) {
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->foreignId('jiri_id')->constrained()->onDelete('cascade');
        });
        Schema::table('duties', static function (Blueprint $table) {
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('jiri_id')->constrained()->onDelete('cascade');
        });
        Schema::table('implementation_cotations', static function (Blueprint $table) {
            $table->foreignId('implementation_id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jiris', static function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('contacts', static function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('attendances', static function (Blueprint $table) {
            $table->dropForeign(['contact_id']);
            $table->dropForeign(['jiri_id']);
        });
        Schema::table('projects', static function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('implementations', static function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['contact_id']);
            $table->dropForeign(['jiri_id']);
        });
        Schema::table('duties', static function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['jiri_id']);
        });
        Schema::table('implementation_cotations', static function (Blueprint $table) {
            $table->dropForeign(['implementation_id']);
            $table->dropForeign(['contact_id']);
        });
    }
};
