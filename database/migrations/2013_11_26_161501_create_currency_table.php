<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Currencies table name.
     *
     * @var string
     */
    protected $table_name = 'format_currencies';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable($this->table_name)) {
            Schema::create($this->table_name, function (Blueprint $table): void {
                $table->increments('id')->unsigned();
                $table->string('name');
                $table->string('code', 10)->index();
                $table->string('symbol', 25);
                $table->string('format', 50);
                $table->string('exchange_rate');
                $table->boolean('active')->default(value: false);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop($this->table_name);
    }
};
