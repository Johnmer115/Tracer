<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $fields = [
                'reschedule_approved_by_dean_sa' => 'reschedule_approved_at_dean_sa',
                'reschedule_approved_by_avp_sps' => 'reschedule_approved_at_avp_sps',
                'reschedule_approved_by_dir_basic_ed' => 'reschedule_approved_at_dir_basic_ed',
                'reschedule_approved_by_vp_acad' => 'reschedule_approved_at_vp_acad',
                'reschedule_approved_by_vp_hrd_legal' => 'reschedule_approved_at_vp_hrd_legal',
                'reschedule_approved_by_auditing' => 'reschedule_approved_at_auditing',
                'reschedule_approved_by_comptroller_initial' => 'reschedule_approved_at_comptroller_initial',
                'reschedule_approved_by_finance_initial' => 'reschedule_approved_at_finance_initial',
                'reschedule_approved_by_osa_finance' => 'reschedule_approved_at_osa_finance',
                'reschedule_approved_by_finance_final' => 'reschedule_approved_at_finance_final',
                'reschedule_approved_by_comptroller_final' => 'reschedule_approved_at_comptroller_final',
            ];

            foreach ($fields as $field => $after) {
                if (! Schema::hasColumn('activities', $field)) {
                    $table->unsignedBigInteger($field)->nullable()->after($after);
                    $table->foreign($field)->references('id')->on('accounts')->nullOnDelete();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $fields = [
                'reschedule_approved_by_dean_sa',
                'reschedule_approved_by_avp_sps',
                'reschedule_approved_by_dir_basic_ed',
                'reschedule_approved_by_vp_acad',
                'reschedule_approved_by_vp_hrd_legal',
                'reschedule_approved_by_auditing',
                'reschedule_approved_by_comptroller_initial',
                'reschedule_approved_by_finance_initial',
                'reschedule_approved_by_osa_finance',
                'reschedule_approved_by_finance_final',
                'reschedule_approved_by_comptroller_final',
            ];

            foreach ($fields as $field) {
                if (Schema::hasColumn('activities', $field)) {
                    $table->dropForeign([$field]);
                    $table->dropColumn($field);
                }
            }
        });
    }
};
