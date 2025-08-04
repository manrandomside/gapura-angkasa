<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations - Ensure NIP is unique and properly indexed
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Check and add unique constraint for NIP if not exists
            if (!$this->hasUniqueConstraint('employees', 'nip')) {
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
            try {
                $table->dropUnique('employees_nip_unique');
            } catch (Exception $e) {
                // Index might not exist
            }
            
            try {
                $table->dropIndex('employees_created_at_index');
            } catch (Exception $e) {
                // Index might not exist
            }
            
            try {
                $table->dropIndex('employees_status_index');
            } catch (Exception $e) {
                // Index might not exist
            }
            
            try {
                $table->dropIndex('employees_nama_lengkap_index');
            } catch (Exception $e) {
                // Index might not exist
            }
            
            try {
                $table->dropIndex('employees_unit_organisasi_index');
            } catch (Exception $e) {
                // Index might not exist
            }
        });
    }
    
    /**
     * Check if unique constraint exists using raw SQL
     */
    private function hasUniqueConstraint($table, $column)
    {
        $database = DB::connection()->getDatabaseName();
        
        $result = DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.TABLE_CONSTRAINTS tc
            JOIN information_schema.KEY_COLUMN_USAGE kcu 
                ON tc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
            WHERE tc.TABLE_SCHEMA = ? 
                AND tc.TABLE_NAME = ? 
                AND tc.CONSTRAINT_TYPE = 'UNIQUE'
                AND kcu.COLUMN_NAME = ?
        ", [$database, $table, $column]);

        return $result[0]->count > 0;
    }
    
    /**
     * Check if regular index exists using raw SQL
     */
    private function hasIndex($table, $column)
    {
        $database = DB::connection()->getDatabaseName();
        
        $result = DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.STATISTICS 
            WHERE TABLE_SCHEMA = ? 
                AND TABLE_NAME = ? 
                AND COLUMN_NAME = ?
        ", [$database, $table, $column]);

        return $result[0]->count > 0;
    }
};