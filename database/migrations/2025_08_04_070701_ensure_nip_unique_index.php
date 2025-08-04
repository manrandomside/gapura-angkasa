<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Ensure NIP is unique and properly indexed
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Ensure NIP is unique if not already
            if (!$this->hasUniqueIndex('employees', 'nip')) {
                $table->unique('nip', 'employees_nip_unique');
            }
            
            // Add additional indexes for better performance
            if (!$this->hasIndex('employees', 'created_at')) {
                $table->index('created_at', 'employees_created_at_index');
            }
            
            if (!$this->hasIndex('employees', 'status')) {
                $table->index('status', 'employees_status_index');
            }
            
            if (!$this->hasIndex('employees', 'nama_lengkap')) {
                $table->index('nama_lengkap', 'employees_nama_lengkap_index');
            }
            
            if (!$this->hasIndex('employees', 'unit_organisasi')) {
                $table->index('unit_organisasi', 'employees_unit_organisasi_index');
            }
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Drop indexes if they exist
            $table->dropIndex('employees_nip_unique');
            $table->dropIndex('employees_created_at_index');
            $table->dropIndex('employees_status_index');
            $table->dropIndex('employees_nama_lengkap_index');
            $table->dropIndex('employees_unit_organisasi_index');
        });
    }
    
    /**
     * Check if unique index exists
     */
    private function hasUniqueIndex($table, $column)
    {
        $indexes = collect(Schema::getConnection()->getDoctrineSchemaManager()
            ->listTableIndexes($table));
            
        return $indexes->contains(function ($index) use ($column) {
            return $index->isUnique() && 
                   in_array($column, $index->getColumns());
        });
    }
    
    /**
     * Check if regular index exists
     */
    private function hasIndex($table, $column)
    {
        $indexes = collect(Schema::getConnection()->getDoctrineSchemaManager()
            ->listTableIndexes($table));
            
        return $indexes->contains(function ($index) use ($column) {
            return in_array($column, $index->getColumns());
        });
    }
};