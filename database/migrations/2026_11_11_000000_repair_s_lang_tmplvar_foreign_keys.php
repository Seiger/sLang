<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Repair translated TV tables left without constraints by the old MySQL DDL.
 *
 * @since 2.2.0
 */
return new class extends Migration {
    private const TABLE = 's_lang_tmplvar_contentvalues';

    /**
     * Add missing constraints after a failed or retried create-table migration.
     *
     * MySQL and MariaDB can create the table before rejecting the first foreign
     * key. A retry of the old migration could then skip the table and report success.
     *
     * @return void
     * @since 2.2.0
     */
    public function up(): void
    {
        if (!in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        $foreignKeyColumns = array_column(Schema::getForeignKeys(self::TABLE), 'columns');
        if (!in_array(['tmplvarid'], $foreignKeyColumns, true)) {
            if (str_contains(Schema::getColumnType(self::TABLE, 'tmplvarid', true), 'unsigned')) {
                if (DB::table(self::TABLE)->where('tmplvarid', '>', 2_147_483_647)->exists()) {
                    throw new RuntimeException('A translated TV ID exceeds the signed INT range of site_tmplvars.id.');
                }

                Schema::table(self::TABLE, function (Blueprint $table) {
                    $table->integer('tmplvarid')->comment('Template variable ID')->change();
                });
            }

            Schema::table(self::TABLE, function (Blueprint $table) {
                $table->foreign('tmplvarid')->references('id')->on('site_tmplvars')->cascadeOnDelete();
            });
        }

        if (!in_array(['contentid'], $foreignKeyColumns, true)) {
            Schema::table(self::TABLE, function (Blueprint $table) {
                $table->foreign('contentid')->references('id')->on('site_content')->cascadeOnDelete();
            });
        }
    }

    /**
     * Leave the repaired constraints in place when rolling back this migration.
     *
     * The create-table migration owns the schema and removes the table on rollback.
     *
     * @return void
     * @since 2.2.0
     */
    public function down(): void
    {
    }
};
