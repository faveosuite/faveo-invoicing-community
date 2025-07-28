<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cloud_data_centers', function (Blueprint $table) {
            $table->id();
            $table->string('cloud_countries');
            $table->string('cloud_state');
            $table->string('cloud_city');
            $table->decimal('latitude', 10, 8); // Assuming latitude is a decimal value
            $table->decimal('longitude', 11, 8); // Assuming longitude is a decimal value
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cloud_data_centers');
    }
};
