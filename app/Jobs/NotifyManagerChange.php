<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Http\Controllers\Common\PhpMailController;
use App\Model\Common\Setting;
use App\Model\Common\Template;
use App\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotifyManagerChange implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @param  array<int>  $userIds
     */
    public function __construct(
        private array $userIds,
        private string $managerColumn,
        private int $newManagerId
    ) {
    }

    public function handle(PhpMailController $mailer): void
    {
        $isAccount = $this->managerColumn === 'account_manager';
        $templateType = $isAccount ? 'account_manager_email' : 'sales_manager_email';
        $categoryName = $isAccount ? 'account-manager-mail' : 'sales-manager-mail';

        $setting = Setting::first();
        if (! $setting) {
            return;
        }

        $template = Template::join('template_types', 'templates.type', '=', 'template_types.id')
            ->where('template_types.name', $templateType)
            ->select('templates.data', 'templates.name', 'template_types.name as type_name')
            ->first();
        if (! $template) {
            return;
        }

        $newManager = User::select('first_name', 'last_name', 'email', 'mobile_code', 'mobile', 'skype')
            ->find($this->newManagerId);
        if (! $newManager) {
            return;
        }

        $contact = getContactData();

        User::whereIn('id', $this->userIds)
            ->cursor()
            ->each(function (User $user) use ($setting, $template, $newManager, $contact, $mailer, $categoryName): void {
                $replace = [
                    'name' => $user->first_name.' '.$user->last_name,
                    'manager_first_name' => $newManager->first_name,
                    'manager_last_name' => $newManager->last_name,
                    'manager_email' => $newManager->email,
                    'manager_code' => '+'.$newManager->mobile_code,
                    'manager_mobile' => $newManager->mobile,
                    'manager_skype' => $newManager->skype,
                    'contact' => $contact['contact'],
                    'logo' => $contact['logo'],
                    'reply_email' => $setting->company_email,
                ];

                $mailer->sendEmail(
                    $setting->email,
                    $user->email,
                    $template->data,
                    $template->name,
                    $categoryName,
                    $replace,
                    $template->type_name
                );
            });
    }
}
