<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add unit and sub unit relationship to employees table
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Add foreign keys untuk unit dan sub unit
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null')->after('unit_organisasi');
            $table->foreignId('sub_unit_id')->nullable()->constrained('sub_units')->onDelete('set null')->after('unit_id');
            
            // Add index untuk performance
            $table->index(['unit_id', 'sub_unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropForeign(['sub_unit_id']);
            $table->dropColumn(['unit_id', 'sub_unit_id']);
        });
    }
};