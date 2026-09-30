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
        if (! Schema::hasTable('templates')) {
            Schema::create('templates', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('name');
                $table->integer('type')->unsigned()->index('templates_type_foreign');
                $table->string('url');
                $table->text('data');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('templates');
    }
};
