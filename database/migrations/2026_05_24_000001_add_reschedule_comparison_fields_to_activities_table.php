<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->date('reschedule_original_date')->nullable()->after('reschedule_status');
            $table->string('reschedule_original_time')->nullable()->after('reschedule_original_date');
            $table->string('reschedule_original_mode')->nullable()->after('reschedule_original_time');
            $table->string('reschedule_original_venue')->nullable()->after('reschedule_original_mode');
            $table->string('reschedule_original_venue_type')->nullable()->after('reschedule_original_venue');
            $table->string('reschedule_original_platform')->nullable()->after('reschedule_original_venue_type');
            $table->string('reschedule_mode')->nullable()->after('reschedule_time');
            $table->string('reschedule_venue_type')->nullable()->after('reschedule_venue');
            $table->string('reschedule_platform')->nullable()->after('reschedule_venue_type');
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn([
                'reschedule_original_date',
                'reschedule_original_time',
                'reschedule_original_mode',
                'reschedule_original_venue',
                'reschedule_original_venue_type',
                'reschedule_original_platform',
                'reschedule_mode',
                'reschedule_venue_type',
                'reschedule_platform',
            ]);
        });
    }
};
