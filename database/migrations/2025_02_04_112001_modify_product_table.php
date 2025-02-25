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
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('id'); // Drop existing id column
        });

        Schema::table('products', function (Blueprint $table) {
            $table->id()->first(); // Re-add id column with auto-increment
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('id'); // Drop the new id column
        });

        Schema::table('users', function (Blueprint $table) {
            $table->bigIncrements('id')->first(); // Restore original id column
        });
    }
};
