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
        Schema::table('activities', function (Blueprint $table) {
            $table->string('modification_type')->nullable()->after('reschedule_decided_at');       // null | revision | rescheduling
            $table->text('modification_remarks')->nullable()->after('modification_type');           // admin notes about the modification
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn([
                'modification_type',
                'modification_remarks',
            ]);
        });
    }
};
