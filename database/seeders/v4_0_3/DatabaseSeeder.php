<?php

namespace Database\Seeders\v4_0_3;

use App\ApiKey;
use App\BillingLog\Model\LogCategory;
use App\BillingLog\Model\MailLog;
use App\Email_log;
use App\Model\Common\EmailMobileValidationProviders;
use App\Model\Common\Msg91Status;
use App\Http\Controllers\Common\PipedriveController;
use App\Model\Common\PipedriveGroups;
use App\Model\Common\PipedriveLocalFields;
use App\Model\Common\PricingTemplate;
use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use App\Model\Github\Github;
use App\Model\Mailjob\ExpiryMailDay;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Seeder;
use Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->removeDuplicateEmailLogs();
    }

    protected function removeDuplicateEmailLogs()
    {
        // Check if email_log table exists
        if (!Schema::hasTable('email_log')) {
            return; // nothing to migrate
        }

        // Cache template type and template data
        $templateTypeCache = TemplateType::pluck('name', 'id'); // [id => typeName]
        $templateCache     = Template::pluck('type', 'name');   // [name => typeId]

        // Ensure required categories exist
        $existingCategories = LogCategory::pluck('id', 'name'); // [name => id]
        $requiredCategories = collect($templateTypeCache->values())->push('default')->unique();

        $missingCategories = $requiredCategories->diff($existingCategories->keys());
        if ($missingCategories->isNotEmpty()) {
            LogCategory::insert($missingCategories->map(fn($name) => ['name' => $name])->all());

            $newCategories      = LogCategory::whereIn('name', $missingCategories)->pluck('id', 'name');
            $existingCategories = $existingCategories->union($newCategories);
        }

        // Map subject → categoryId
        $subjectToCategory = collect($templateCache)->mapWithKeys(function ($typeId, $subject) use ($templateTypeCache, $existingCategories) {
            $typeName = $templateTypeCache[$typeId] ?? null;
            return ($typeName && isset($existingCategories[$typeName]))
                ? [$subject => $existingCategories[$typeName]]
                : [];
        });

        \DB::table('email_log')
            ->whereNotNull('status')
            ->orderBy('id')
            ->chunk(10000, function ($logs) use ($subjectToCategory, $existingCategories) {
                $newLogs = collect($logs)->map(function ($log) use ($subjectToCategory, $existingCategories) {
                    $logCategoryId = $subjectToCategory[$log->subject] ?? $existingCategories['default'];

                    return [
                        'log_category_id'   => $logCategoryId,
                        'sender_mail'       => $log->from,
                        'receiver_mail'     => $log->to,
                        'carbon_copy'       => $log->cc,
                        'blind_carbon_copy' => $log->bcc,
                        'subject'           => $log->subject,
                        'body'              => $log->body,
                        'status'            => $log->status === 'success' ? 'sent' : 'failed',
                        'created_at'        => $log->date,
                        'updated_at'        => $log->date,
                    ];
                });

                if ($newLogs->isNotEmpty()) {
                    \DB::transaction(function () use ($newLogs) {
                        $newLogs->chunk(5000)->each(fn($batch) => \DB::table('mail_logs')->insert($batch->all()));
                    });
                }
            });

        // Finally: truncate and drop the old email_log table
        DB::statement('TRUNCATE TABLE email_log');
        Schema::dropIfExists('email_log');
    }

}