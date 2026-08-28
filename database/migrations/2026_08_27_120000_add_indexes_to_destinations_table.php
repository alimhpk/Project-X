<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->unique(['name', 'country']);
            $table->index('region');
            $table->index('cost_level');
        });
    }

    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->dropUnique(['name', 'country']);
            $table->dropIndex(['region']);
            $table->dropIndex(['cost_level']);
        });
    }
};
