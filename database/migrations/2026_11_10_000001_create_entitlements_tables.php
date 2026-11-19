<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Entitlements (features): what a license lets the customer use (on/off features, numeric
 * limits, a choice from a list, free text), sent to the installed product in the signed license
 * file and the license check.
 *
 *  - entitlements:        one shared list of features for all products, with each default value.
 *  - plan_entitlements:   the features a plan includes, and its value when not the default.
 *  - entitlement_prices:  what one more unit costs on a plan, per currency (renewal price and
 *                         offer % optional). A feature with a price on a plan can be bought as
 *                         an extra on that plan; agents use it as the per-agent price.
 *  - order_entitlements:  extras a customer bought, at the renewal price they keep.
 *  - grants:              every right a license (or a customer, for services) has: one feature code,
 *                         its value and dates, and where it came from (source):
 *                           plan    - the plan's value, copied at purchase so a later plan edit
 *                                     doesn't change what existing customers bought
 *                           extra   - units bought on top of the plan (mirrors order_entitlements)
 *                           admin   - an admin override, optionally until a date
 *                           date    - a date feature (updates, support, …): ends_at = its end,
 *                                     null = never. The plan sets how it ends (DateRules, stored as
 *                                     JSON in plan_entitlements.value); the license itself ends
 *                                     with its subscription.
 *  - products.downloads_limited_by: the date feature code whose end limits which releases the
 *    product's customers can download; null = no limit.
 *  - products.downloadable: customers can download the product at all (SaaS products can't).
 *  - products.keep_old_downloads: after that date ends, releases from before it stay
 *    downloadable; off = every download stops.
 *    These replace the license-type permissions (license_permissions tables, dropped by the
 *    v4.0.2.8.RC.1 seeder after copying them here).
 *  - licenses.version:    goes up on every grant change, so a product can tell a newer record.
 *  - cart_items.extras / invoice_items.extras: extras picked at checkout.
 *  - products.unlocks_entitlement_id: on a plugin product, the feature that unlocks it. A license
 *    may run and download the plugin when it has that feature.
 *
 * Replaces the configurable-option tables (config_group / config_option / config_option_values):
 * they had no admin screen, held no rows, and copied every plan's options onto a license instead
 * of only the plan bought.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entitlements', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 50)->unique(); // permanent name products check (sla, agents, api_daily_limit); never renamed
            $table->string('category', 50)->nullable(); // admin grouping only (Channels, Security)
            $table->string('label', 100);
            $table->string('type', 20); // boolean | number | select | text | date
            $table->json('options')->nullable(); // choices for a select item
            $table->string('default_value', 255)->nullable();
            $table->boolean('is_active')->default(value: true); // false = archived: kept for existing customers, hidden for new
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('plan_entitlements', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('plan_id');
            $table->foreignId('entitlement_id')->constrained('entitlements')->cascadeOnDelete();
            $table->string('value', 255)->nullable();
            $table->timestamps();

            $table->foreign('plan_id')->references('id')->on('plans')->cascadeOnDelete();
            $table->unique(['plan_id', 'entitlement_id']);
        });

        Schema::create('entitlement_prices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('entitlement_id')->constrained('entitlements')->cascadeOnDelete();
            $table->unsignedInteger('plan_id');
            $table->string('currency', 10);
            $table->decimal('unit_price', 14, 4);
            $table->decimal('renew_price', 14, 4)->nullable();
            $table->decimal('offer_percent', 5, 2)->nullable();
            $table->timestamps();

            $table->foreign('plan_id')->references('id')->on('plans')->cascadeOnDelete();
            $table->unique(['entitlement_id', 'plan_id', 'currency']);
        });

        Schema::create('order_entitlements', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('order_id');
            $table->foreignId('entitlement_id')->constrained('entitlements')->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('renew_quantity')->nullable();
            $table->decimal('unit_price', 14, 4); // renewal price per unit, kept for this customer
            $table->string('currency', 10);
            $table->unsignedInteger('period_id')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->unique(['order_id', 'entitlement_id']);
        });

        Schema::create('grants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('license_id')->nullable()->constrained('licenses')->cascadeOnDelete();
            $table->unsignedInteger('user_id')->nullable()->index(); // customer-level grants (services); license_id null
            $table->string('code', 50); // entitlements.code, or run / updates / support
            $table->string('value', 255)->nullable();
            $table->string('source', 20); // plan | extra | admin | date
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['license_id', 'code', 'source']);
            $table->index('code');
        });
        Schema::table('licenses', function (Blueprint $table): void {
            $table->unsignedInteger('version')->default(0)->after('license_status');
        });
        Schema::table('cart_items', function (Blueprint $table): void {
            $table->json('extras')->nullable()->after('agents');
        });
        Schema::table('products', function (Blueprint $table): void {
            $table->foreignId('unlocks_entitlement_id')->nullable()->constrained('entitlements')->nullOnDelete();
            $table->string('downloads_limited_by', 50)->nullable();
            $table->boolean('downloadable')->default(true);
            $table->boolean('keep_old_downloads')->default(true);
        });
        Schema::table('invoice_items', function (Blueprint $table): void {
            $table->json('extras')->nullable()->after('agents');
        });

        Schema::dropIfExists('config_option_values');
        Schema::dropIfExists('config_option');
        Schema::dropIfExists('config_group');
    }

    public function down(): void
    {
        Schema::create('config_group', function (Blueprint $table): void {
            $table->id();
            $table->string('config_group_name', 255);
            $table->binary('description')->nullable();
            $table->timestamps();
        });

        Schema::create('config_option', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->string('config_option_name', 255);
            $table->binary('config_option_description')->nullable();
            $table->unsignedInteger('plan_id');
            $table->unsignedInteger('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('config_group')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('config_option_values', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('option_id');
            $table->string('key', 255);
            $table->string('value', 255);
            $table->foreign('option_id')->references('id')->on('config_option')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::table('licenses', fn (Blueprint $table) => $table->dropColumn('version'));
        Schema::dropIfExists('grants');
        Schema::table('cart_items', fn (Blueprint $table) => $table->dropColumn('extras'));
        Schema::table('products', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('unlocks_entitlement_id');
            $table->dropColumn(['downloads_limited_by', 'downloadable', 'keep_old_downloads']);
        });
        Schema::table('invoice_items', fn (Blueprint $table) => $table->dropColumn('extras'));

        Schema::dropIfExists('order_entitlements');
        Schema::dropIfExists('entitlement_prices');
        Schema::dropIfExists('plan_entitlements');
        Schema::dropIfExists('entitlements');
    }
};
