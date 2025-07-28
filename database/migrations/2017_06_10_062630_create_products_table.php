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
        if (! Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->unique('name');
                $table->string('description', 2000);
                $table->string('category', 225);
                $table->integer('parent');
                $table->integer('type')->nullable();
                $table->integer('group')->nullable();
                // $table->foreign('group')->references('id')->on('product_groups');
                // $table->foreign('type')->references('id')->on('license_types');
                $table->string('welcome_email');
                $table->integer('require_domain');
                $table->boolean('can_modify_agent')->nullable();
                $table->boolean('can_modify_quantity')->nullable();
                $table->boolean('show_agent')->nullable();
                $table->boolean('show_product_quantity')->nullable();
                $table->integer('tax_apply');
                $table->integer('deny_after_subscription');
                $table->integer('hidden');
                $table->integer('multiple_qty');
                $table->string('auto_terminate');
                $table->integer('setup_order_placed');
                $table->integer('setup_first_payment');
                $table->integer('setup_accept_manually');
                $table->integer('no_auto_setup');
                $table->string('shoping_cart_link');
                $table->string('file');
                $table->string('image');
                $table->string('version', 225);
                $table->string('github_owner', 225);
                $table->string('github_repository', 225);
                $table->string('process_url');
                $table->integer('subscription');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('products');
    }
};
