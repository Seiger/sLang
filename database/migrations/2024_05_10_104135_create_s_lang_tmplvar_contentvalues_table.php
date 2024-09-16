<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('s_lang_tmplvar_contentvalues', function (Blueprint $table) {
            $table->id();
            $table->string('lang', 4)->default('base')->index()->comment('Language of content data');
            $table->integer('tmplvarid')->index()->comment('Template variable ID');
            $table->foreign('tmplvarid')->references('id')->on('site_tmplvars')->cascadeOnDelete();
            $table->unsignedInteger('contentid')->index()->comment('Site content resource ID');
            $table->foreign('contentid')->references('id')->on('site_content')->cascadeOnDelete();
            $table->longText('value')->fulltext('value')->nullable()->comment('Translated value of the template variable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('s_lang_tmplvar_contentvalues');
    }
};
