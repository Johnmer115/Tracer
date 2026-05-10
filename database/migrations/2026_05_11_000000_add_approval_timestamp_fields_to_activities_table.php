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
                'approved_at_dean_sa' => 'budget_comptroller_final',
                'approved_at_avp_sps' => 'approved_at_dean_sa',
                'approved_at_dir_basic_ed' => 'approved_at_avp_sps',
                'approved_at_vp_acad' => 'approved_at_dir_basic_ed',
                'approved_at_vp_hrd_legal' => 'approved_at_vp_acad',
                'approved_at_auditing' => 'approved_at_vp_hrd_legal',
                'approved_at_comptroller_initial' => 'approved_at_auditing',
                'approved_at_finance_initial' => 'approved_at_comptroller_initial',
                'approved_at_osa_finance' => 'approved_at_finance_initial',
                'approved_at_finance_final' => 'approved_at_osa_finance',
                'approved_at_comptroller_final' => 'approved_at_finance_final',
            ];

            foreach ($fields as $field => $after) {
                if (! Schema::hasColumn('activities', $field)) {
                    $table->timestamp($field)->nullable()->after($after);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $fields = [
                'approved_at_dean_sa',
                'approved_at_avp_sps',
                'approved_at_dir_basic_ed',
                'approved_at_vp_acad',
                'approved_at_vp_hrd_legal',
                'approved_at_auditing',
                'approved_at_comptroller_initial',
                'approved_at_finance_initial',
                'approved_at_osa_finance',
                'approved_at_finance_final',
                'approved_at_comptroller_final',
            ];

            foreach ($fields as $field) {
                if (Schema::hasColumn('activities', $field)) {
                    $table->dropColumn($field);
                }
            }
        });
    }
};
