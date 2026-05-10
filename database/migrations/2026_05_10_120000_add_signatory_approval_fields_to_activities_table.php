<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            if (!Schema::hasColumn('activities', 'approval_dean_sa')) {
                $table->string('approval_dean_sa')->default('pending')->after('status');
            }
            if (!Schema::hasColumn('activities', 'approval_avp_sps')) {
                $table->string('approval_avp_sps')->default('pending')->after('approval_dean_sa');
            }
            if (!Schema::hasColumn('activities', 'approval_dir_basic_ed')) {
                $table->string('approval_dir_basic_ed')->default('pending')->after('approval_avp_sps');
            }
            if (!Schema::hasColumn('activities', 'approval_vp_acad')) {
                $table->string('approval_vp_acad')->default('pending')->after('approval_dir_basic_ed');
            }
            if (!Schema::hasColumn('activities', 'approval_vp_hrd_legal')) {
                $table->string('approval_vp_hrd_legal')->default('pending')->after('approval_vp_acad');
            }
            if (!Schema::hasColumn('activities', 'approval_vp_comptroller')) {
                $table->string('approval_vp_comptroller')->default('pending')->after('approval_vp_hrd_legal');
            }
            if (!Schema::hasColumn('activities', 'approval_avp_finance')) {
                $table->string('approval_avp_finance')->default('pending')->after('approval_vp_comptroller');
            }

            if (!Schema::hasColumn('activities', 'remarks_dean_sa')) {
                $table->text('remarks_dean_sa')->nullable()->after('approval_avp_finance');
            }
            if (!Schema::hasColumn('activities', 'remarks_avp_sps')) {
                $table->text('remarks_avp_sps')->nullable()->after('remarks_dean_sa');
            }
            if (!Schema::hasColumn('activities', 'remarks_dir_basic_ed')) {
                $table->text('remarks_dir_basic_ed')->nullable()->after('remarks_avp_sps');
            }
            if (!Schema::hasColumn('activities', 'remarks_vp_acad')) {
                $table->text('remarks_vp_acad')->nullable()->after('remarks_dir_basic_ed');
            }
            if (!Schema::hasColumn('activities', 'remarks_vp_hrd_legal')) {
                $table->text('remarks_vp_hrd_legal')->nullable()->after('remarks_vp_acad');
            }
            if (!Schema::hasColumn('activities', 'remarks_vp_comptroller')) {
                $table->text('remarks_vp_comptroller')->nullable()->after('remarks_vp_hrd_legal');
            }
            if (!Schema::hasColumn('activities', 'remarks_avp_finance')) {
                $table->text('remarks_avp_finance')->nullable()->after('remarks_vp_comptroller');
            }
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $columns = [
                'approval_dean_sa',
                'approval_avp_sps',
                'approval_dir_basic_ed',
                'approval_vp_acad',
                'approval_vp_hrd_legal',
                'approval_vp_comptroller',
                'approval_avp_finance',
                'remarks_dean_sa',
                'remarks_avp_sps',
                'remarks_dir_basic_ed',
                'remarks_vp_acad',
                'remarks_vp_hrd_legal',
                'remarks_vp_comptroller',
                'remarks_avp_finance',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('activities', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

