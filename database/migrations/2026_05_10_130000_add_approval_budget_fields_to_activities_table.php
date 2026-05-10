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
                'budget_dean_sa' => 'remarks_avp_finance',
                'budget_avp_sps' => 'budget_dean_sa',
                'budget_dir_basic_ed' => 'budget_avp_sps',
                'budget_vp_acad' => 'budget_dir_basic_ed',
                'budget_vp_hrd_legal' => 'budget_vp_acad',
                'budget_vp_comptroller' => 'budget_vp_hrd_legal',
                'budget_avp_finance' => 'budget_vp_comptroller',
            ];

            foreach ($fields as $field => $after) {
                if (! Schema::hasColumn('activities', $field)) {
                    $table->decimal($field, 12, 2)->nullable()->after($after);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $fields = [
                'budget_dean_sa',
                'budget_avp_sps',
                'budget_dir_basic_ed',
                'budget_vp_acad',
                'budget_vp_hrd_legal',
                'budget_vp_comptroller',
                'budget_avp_finance',
            ];

            foreach ($fields as $field) {
                if (Schema::hasColumn('activities', $field)) {
                    $table->dropColumn($field);
                }
            }
        });
    }
};
