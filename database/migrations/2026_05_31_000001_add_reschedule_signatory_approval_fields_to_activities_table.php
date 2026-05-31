<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $approvalFields = [
                'reschedule_approval_dean_sa' => 'reschedule_decided_at',
                'reschedule_approval_avp_sps' => 'reschedule_approval_dean_sa',
                'reschedule_approval_dir_basic_ed' => 'reschedule_approval_avp_sps',
                'reschedule_approval_vp_acad' => 'reschedule_approval_dir_basic_ed',
                'reschedule_approval_vp_hrd_legal' => 'reschedule_approval_vp_acad',
                'reschedule_approval_auditing' => 'reschedule_approval_vp_hrd_legal',
                'reschedule_approval_comptroller_initial' => 'reschedule_approval_auditing',
                'reschedule_approval_finance_initial' => 'reschedule_approval_comptroller_initial',
                'reschedule_approval_osa_finance' => 'reschedule_approval_finance_initial',
                'reschedule_approval_finance_final' => 'reschedule_approval_osa_finance',
                'reschedule_approval_comptroller_final' => 'reschedule_approval_finance_final',
            ];

            foreach ($approvalFields as $field => $after) {
                if (! Schema::hasColumn('activities', $field)) {
                    $table->string($field)->nullable()->after($after);
                }
            }

            $remarkFields = [
                'reschedule_remarks_dean_sa' => 'reschedule_approval_comptroller_final',
                'reschedule_remarks_avp_sps' => 'reschedule_remarks_dean_sa',
                'reschedule_remarks_dir_basic_ed' => 'reschedule_remarks_avp_sps',
                'reschedule_remarks_vp_acad' => 'reschedule_remarks_dir_basic_ed',
                'reschedule_remarks_vp_hrd_legal' => 'reschedule_remarks_vp_acad',
                'reschedule_remarks_auditing' => 'reschedule_remarks_vp_hrd_legal',
                'reschedule_remarks_comptroller_initial' => 'reschedule_remarks_auditing',
                'reschedule_remarks_finance_initial' => 'reschedule_remarks_comptroller_initial',
                'reschedule_remarks_osa_finance' => 'reschedule_remarks_finance_initial',
                'reschedule_remarks_finance_final' => 'reschedule_remarks_osa_finance',
                'reschedule_remarks_comptroller_final' => 'reschedule_remarks_finance_final',
            ];

            foreach ($remarkFields as $field => $after) {
                if (! Schema::hasColumn('activities', $field)) {
                    $table->text($field)->nullable()->after($after);
                }
            }

            $approvedAtFields = [
                'reschedule_approved_at_dean_sa' => 'reschedule_remarks_comptroller_final',
                'reschedule_approved_at_avp_sps' => 'reschedule_approved_at_dean_sa',
                'reschedule_approved_at_dir_basic_ed' => 'reschedule_approved_at_avp_sps',
                'reschedule_approved_at_vp_acad' => 'reschedule_approved_at_dir_basic_ed',
                'reschedule_approved_at_vp_hrd_legal' => 'reschedule_approved_at_vp_acad',
                'reschedule_approved_at_auditing' => 'reschedule_approved_at_vp_hrd_legal',
                'reschedule_approved_at_comptroller_initial' => 'reschedule_approved_at_auditing',
                'reschedule_approved_at_finance_initial' => 'reschedule_approved_at_comptroller_initial',
                'reschedule_approved_at_osa_finance' => 'reschedule_approved_at_finance_initial',
                'reschedule_approved_at_finance_final' => 'reschedule_approved_at_osa_finance',
                'reschedule_approved_at_comptroller_final' => 'reschedule_approved_at_finance_final',
            ];

            foreach ($approvedAtFields as $field => $after) {
                if (! Schema::hasColumn('activities', $field)) {
                    $table->timestamp($field)->nullable()->after($after);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $columns = [
                'reschedule_approval_dean_sa',
                'reschedule_approval_avp_sps',
                'reschedule_approval_dir_basic_ed',
                'reschedule_approval_vp_acad',
                'reschedule_approval_vp_hrd_legal',
                'reschedule_approval_auditing',
                'reschedule_approval_comptroller_initial',
                'reschedule_approval_finance_initial',
                'reschedule_approval_osa_finance',
                'reschedule_approval_finance_final',
                'reschedule_approval_comptroller_final',
                'reschedule_remarks_dean_sa',
                'reschedule_remarks_avp_sps',
                'reschedule_remarks_dir_basic_ed',
                'reschedule_remarks_vp_acad',
                'reschedule_remarks_vp_hrd_legal',
                'reschedule_remarks_auditing',
                'reschedule_remarks_comptroller_initial',
                'reschedule_remarks_finance_initial',
                'reschedule_remarks_osa_finance',
                'reschedule_remarks_finance_final',
                'reschedule_remarks_comptroller_final',
                'reschedule_approved_at_dean_sa',
                'reschedule_approved_at_avp_sps',
                'reschedule_approved_at_dir_basic_ed',
                'reschedule_approved_at_vp_acad',
                'reschedule_approved_at_vp_hrd_legal',
                'reschedule_approved_at_auditing',
                'reschedule_approved_at_comptroller_initial',
                'reschedule_approved_at_finance_initial',
                'reschedule_approved_at_osa_finance',
                'reschedule_approved_at_finance_final',
                'reschedule_approved_at_comptroller_final',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('activities', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
