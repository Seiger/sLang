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
            $table->unique(['resource', 'lang'], 'resource_lang');
            $table->timestamps();
        });

        Schema::create('s_lang_tmplvar_contentvalues', function (Blueprint $table) {
            $table->id();
            $table->string('lang', 4)->default('base')->index()->comment('Language of content data');
            $table->unsignedInteger('tmplvarid')->index()->comment('Template variable ID');
            $table->foreign('tmplvarid')->references('id')->on('site_tmplvars')->cascadeOnDelete();
            $table->unsignedInteger('contentid')->index()->comment('Site content resource ID');
            $table->foreign('contentid')->references('id')->on('site_content')->cascadeOnDelete();
            $table->longText('value')->fulltext('value')->nullable()->comment('Translated value of the template variable');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('s_lang_content', function ($table) {
            $table->dropUnique('resource_lang');
        });
        Schema::dropIfExists('s_lang_tmplvar_contentvalues');
        Schema::dropIfExists('s_lang_content');
        Schema::dropIfExists('s_lang_translates');
    }
}