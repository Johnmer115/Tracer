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
                'approval_auditing' => 'approval_avp_finance',
                'approval_comptroller_initial' => 'approval_auditing',
                'approval_finance_initial' => 'approval_comptroller_initial',
                'approval_osa_finance' => 'approval_finance_initial',
                'approval_finance_final' => 'approval_osa_finance',
                'approval_comptroller_final' => 'approval_finance_final',
            ];

            foreach ($approvalFields as $field => $after) {
                if (! Schema::hasColumn('activities', $field)) {
                    $table->string($field)->default('pending')->after($after);
                }
            }

            $remarkFields = [
                'remarks_auditing' => 'remarks_avp_finance',
                'remarks_comptroller_initial' => 'remarks_auditing',
                'remarks_finance_initial' => 'remarks_comptroller_initial',
                'remarks_osa_finance' => 'remarks_finance_initial',
                'remarks_finance_final' => 'remarks_osa_finance',
                'remarks_comptroller_final' => 'remarks_finance_final',
            ];

            foreach ($remarkFields as $field => $after) {
                if (! Schema::hasColumn('activities', $field)) {
                    $table->text($field)->nullable()->after($after);
                }
            }

            $budgetFields = [
                'budget_auditing' => 'budget_avp_finance',
                'budget_comptroller_initial' => 'budget_auditing',
                'budget_finance_initial' => 'budget_comptroller_initial',
                'budget_osa_finance' => 'budget_finance_initial',
                'budget_finance_final' => 'budget_osa_finance',
                'budget_comptroller_final' => 'budget_finance_final',
            ];

            foreach ($budgetFields as $field => $after) {
                if (! Schema::hasColumn('activities', $field)) {
                    $table->decimal($field, 12, 2)->nullable()->after($after);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $columns = [
                'approval_auditing',
                'approval_comptroller_initial',
                'approval_finance_initial',
                'approval_osa_finance',
                'approval_finance_final',
                'approval_comptroller_final',
                'remarks_auditing',
                'remarks_comptroller_initial',
                'remarks_finance_initial',
                'remarks_osa_finance',
                'remarks_finance_final',
                'remarks_comptroller_final',
                'budget_auditing',
                'budget_comptroller_initial',
                'budget_finance_initial',
                'budget_osa_finance',
                'budget_finance_final',
                'budget_comptroller_final',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('activities', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
