<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->string('reschedule_status')->nullable()->after('status');        // null | pending | approved | rejected
            $table->date('reschedule_date')->nullable()->after('reschedule_status');
            $table->string('reschedule_time')->nullable()->after('reschedule_date');
            $table->string('reschedule_venue')->nullable()->after('reschedule_time');
            $table->text('reschedule_reason')->nullable()->after('reschedule_venue');
            $table->text('reschedule_remarks')->nullable()->after('reschedule_reason');   // dean's remarks on approve/reject
            $table->timestamp('reschedule_requested_at')->nullable()->after('reschedule_remarks');
            $table->timestamp('reschedule_decided_at')->nullable()->after('reschedule_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn([
                'reschedule_status',
                'reschedule_date',
                'reschedule_time',
                'reschedule_venue',
                'reschedule_reason',
                'reschedule_remarks',
                'reschedule_requested_at',
                'reschedule_decided_at',
            ]);
        });
    }
};
