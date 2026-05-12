<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSLangTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('s_lang_translates')) {
            Schema::create('s_lang_translates', function (Blueprint $table) {
                $table->id('tid');
                $table->string('key', 256)->index()->comment('Translate Key');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('s_lang_content')) {
            Schema::create('s_lang_content', function (Blueprint $table) {
                $table->id();
                $table->integer('resource')->index()->comment('Resource ID');
                $table->string('lang', 4)->default('base')->index()->comment('Translate lang key');
                $table->string('pagetitle', 255)->default('')->comment('Translate pagetitle');
                $table->string('longtitle', 255)->default('')->comment('Translate longtitle');
                $table->string('description', 255)->default('')->comment('Translate description');
                $table->text('introtext')->comment('Translate introtext');
                $table->longText('content')->comment('Translate content');
                $table->string('menutitle', 255)->default('')->comment('Translate menutitle');
                $table->string('seotitle', 128)->default('')->comment('SEO title document');
                $table->string('seodescription', 128)->default('')->comment('SEO description document');
                $table->unique(['resource', 'lang'], 'resource_lang');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('s_lang_tmplvar_contentvalues')) {
            Schema::create('s_lang_tmplvar_contentvalues', function (Blueprint $table) {
                $table->id();
                $table->string('lang', 4)->default('base')->index()->comment('Language of content data');
                $table->unsignedInteger('tmplvarid')->index()->comment('Template variable ID');
                $table->foreign('tmplvarid')->references('id')->on('site_tmplvars')->cascadeOnDelete();
                $table->unsignedInteger('contentid')->index()->comment('Site content resource ID');
                $table->foreign('contentid')->references('id')->on('site_content')->cascadeOnDelete();
                $table->longText('value')->nullable()->comment('Translated value of the template variable');
            });
        }

        $this->ensureTmplvarValueSearchIndex();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (DB::getDriverName() === 'sqlite') {
            $prefix = DB::getTablePrefix();
            $tableName = $prefix . 's_lang_tmplvar_contentvalues';
            $ftsTableName = $tableName . '_fts';

            DB::statement("DROP TRIGGER IF EXISTS \"{$ftsTableName}_ai\"");
            DB::statement("DROP TRIGGER IF EXISTS \"{$ftsTableName}_ad\"");
            DB::statement("DROP TRIGGER IF EXISTS \"{$ftsTableName}_au\"");
            DB::statement("DROP TABLE IF EXISTS \"{$ftsTableName}\"");
        }

        if (Schema::hasTable('s_lang_content')) {
            Schema::table('s_lang_content', function ($table) {
                if (Schema::hasIndex('s_lang_content', 'resource_lang')) {
                    $table->dropUnique('resource_lang');
                }
            });
        }

        Schema::dropIfExists('s_lang_tmplvar_contentvalues');
        Schema::dropIfExists('s_lang_content');
        Schema::dropIfExists('s_lang_translates');
    }

    /**
     * Ensure translated TV values have a driver-specific full-text search index.
     *
     * The migration may be retried after a failed package update or against a database where
     * parts of the schema already exist. Keeping index creation separate and idempotent prevents
     * duplicate PostgreSQL relation errors while preserving SQLite FTS support.
     *
     * @since 2.0.0
     */
    protected function ensureTmplvarValueSearchIndex(): void
    {
        if (!Schema::hasTable('s_lang_tmplvar_contentvalues')) {
            return;
        }

        $driver = DB::getDriverName();
        $prefix = DB::getTablePrefix();
        $tableName = $prefix . 's_lang_tmplvar_contentvalues';
        $indexName = 's_lang_tmplvar_contentvalues_value_fulltext';

        if ($driver === 'sqlite') {
            if (!$this->sqliteSupportsFts5()) {
                return;
            }

            $ftsTableName = $tableName . '_fts';

            DB::statement("CREATE VIRTUAL TABLE IF NOT EXISTS \"{$ftsTableName}\" USING fts5(value, content='{$tableName}', content_rowid='id')");
            DB::statement("INSERT INTO \"{$ftsTableName}\"(rowid, value) SELECT id, value FROM \"{$tableName}\" WHERE NOT EXISTS (SELECT 1 FROM \"{$ftsTableName}\" WHERE \"{$ftsTableName}\".rowid = \"{$tableName}\".id)");

            DB::statement("DROP TRIGGER IF EXISTS \"{$ftsTableName}_ai\"");
            DB::statement("DROP TRIGGER IF EXISTS \"{$ftsTableName}_ad\"");
            DB::statement("DROP TRIGGER IF EXISTS \"{$ftsTableName}_au\"");

            DB::statement("CREATE TRIGGER \"{$ftsTableName}_ai\" AFTER INSERT ON \"{$tableName}\" BEGIN
                INSERT INTO \"{$ftsTableName}\"(rowid, value) VALUES (new.id, new.value);
            END");
            DB::statement("CREATE TRIGGER \"{$ftsTableName}_ad\" AFTER DELETE ON \"{$tableName}\" BEGIN
                INSERT INTO \"{$ftsTableName}\"(\"{$ftsTableName}\", rowid, value) VALUES('delete', old.id, old.value);
            END");
            DB::statement("CREATE TRIGGER \"{$ftsTableName}_au\" AFTER UPDATE ON \"{$tableName}\" BEGIN
                INSERT INTO \"{$ftsTableName}\"(\"{$ftsTableName}\", rowid, value) VALUES('delete', old.id, old.value);
                INSERT INTO \"{$ftsTableName}\"(rowid, value) VALUES(new.id, new.value);
            END");

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("CREATE INDEX IF NOT EXISTS \"{$indexName}\" ON \"{$tableName}\" USING gin ((to_tsvector('english', \"value\")))");

            return;
        }

        if (Schema::hasIndex('s_lang_tmplvar_contentvalues', $indexName)) {
            return;
        }

        Schema::table('s_lang_tmplvar_contentvalues', function (Blueprint $table) use ($indexName) {
            $table->fullText('value', $indexName);
        });
    }

    /**
     * Determine whether the active SQLite connection can create FTS5 virtual tables.
     *
     * SQLite has supported FTS5 since 3.9.0, but the extension can still be omitted from
     * a PHP SQLite build. Treating the index as optional keeps the schema compatible with
     * SQLite 3.25+ environments that do not expose ENABLE_FTS5.
     *
     * @since 2.1.0
     */
    protected function sqliteSupportsFts5(): bool
    {
        try {
            $options = DB::select('PRAGMA compile_options');
        } catch (\Throwable $exception) {
            return false;
        }

        foreach ($options as $option) {
            $option = (array)$option;
            if (strtoupper((string)($option['compile_options'] ?? reset($option))) === 'ENABLE_FTS5') {
                return true;
            }
        }

        return false;
    }
}
