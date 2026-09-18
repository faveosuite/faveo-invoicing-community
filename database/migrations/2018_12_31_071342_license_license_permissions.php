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
        if (! Schema::hasTable('license_license_permissions')) {
            Schema::create('license_license_permissions', function (Blueprint $table): void {
                $table->increments('id');
                $table->unsignedInteger('license_type_id');
                $table->unsignedInteger('license_permission_id');

                $table->foreign('license_type_id')->references('id')->on('license_types')->onDelete('cascade');
                $table->foreign('license_permission_id')->references('id')->on('license_permissions')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_license_permissions');
    }
};
