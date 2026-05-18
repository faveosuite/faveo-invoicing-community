<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('template_types', function (Blueprint $table) {
            $table->unsignedInteger('selected_template_id')->nullable()->after('name');
            $table->foreign('selected_template_id')->references('id')->on('templates')->nullOnDelete();
        });

        // Seed from existing settings columns
        $setting = DB::table('settings')->find(1);
        if ($setting) {
            $map = [
                'welcome_mail' => $setting->welcome_mail ?? null,
                'forgot_password_mail' => $setting->forgot_password ?? null,
                'subscription_going_to_end_mail' => $setting->subscription_going_to_end ?? null,
                'subscription_over_mail' => $setting->subscription_over ?? null,
                'invoice_mail' => $setting->invoice ?? null,
                'order_mail' => $setting->order_mail ?? null,
                'auto_subscription_going_to_end' => $setting->autosubscription_going_to_end ?? null,
                'payment_successfull' => $setting->payment_successfull ?? null,
                'payment_failed' => $setting->payment_failed ?? null,
                'cloud_deleted' => $setting->cloud_deleted ?? null,
                'cloud_created' => $setting->cloud_order ?? null,
            ];

            foreach ($map as $typeName => $templateId) {
                if ($templateId) {
                    DB::table('template_types')
                        ->where('name', $typeName)
                        ->update(['selected_template_id' => $templateId]);
                }
            }
        }

        // For any type still without a selection, fall back to the first template of that type
        DB::table('template_types')->whereNull('selected_template_id')->get()
            ->each(function ($type) {
                $firstId = DB::table('templates')->where('type', $type->id)->value('id');
                if ($firstId) {
                    DB::table('template_types')->where('id', $type->id)
                        ->update(['selected_template_id' => $firstId]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('template_types', function (Blueprint $table) {
            $table->dropForeign(['selected_template_id']);
            $table->dropColumn('selected_template_id');
        });
    }
};
