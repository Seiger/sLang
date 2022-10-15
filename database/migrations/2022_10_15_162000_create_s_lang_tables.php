<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::create('s_lang_translates', function (Blueprint $table) {
            $table->id('tid');
            $table->string('key', 256)->index()->comment('Translate Key');
            $table->timestamps();
        });

        Schema::create('s_lang_content', function (Blueprint $table) {
            $table->id();
            $table->integer('resource')->index()->comment('Resource ID');
            $table->string('lang', 4)->default('base')->index()->comment('Translate lang key');
            $table->string('pagetitle', 255)->default('')->comment('Translate pagetitle');
            $table->string('longtitle', 255)->default('')->comment('Translate longtitle');
            $table->string('description', 255)->default('')->comment('Translate description');
            $table->text('introtext')->default('')->comment('Translate introtext');
            $table->longText('content')->default('')->comment('Translate content');
            $table->string('menutitle', 255)->default('')->comment('Translate menutitle');
            $table->string('seotitle', 128)->default('')->comment('SEO title document');
            $table->string('seodescription', 128)->default('')->comment('SEO description document');
            $table->unique('resource', 'lang');
            $table->timestamps();
        });

        DB::raw("REPLACE INTO ".DB::getTablePrefix()."system_settings (`setting_name`, `setting_value`) VALUES ('s_lang_enable', '1')");
        DB::raw("REPLACE INTO ".DB::getTablePrefix()."system_settings (`setting_name`, `setting_value`) VALUES ('s_lang_default_show', '0')");
        DB::raw("REPLACE INTO ".DB::getTablePrefix()."system_settings (`setting_name`, `setting_value`) VALUES ('s_lang_default', 'uk')");
        DB::raw("REPLACE INTO ".DB::getTablePrefix()."system_settings (`setting_name`, `setting_value`) VALUES ('s_lang_config', 'uk,en')");
        DB::raw("REPLACE INTO ".DB::getTablePrefix()."system_settings (`setting_name`, `setting_value`) VALUES ('s_lang_front', 'uk,en')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('s_lang_content');
        Schema::dropIfExists('s_lang_translates');
    }
}
