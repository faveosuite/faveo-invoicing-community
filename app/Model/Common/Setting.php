<?php

namespace App\Model\Common;

use App\Facades\Attach;
use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory, SystemActivityLogsTrait;

    protected $table = 'settings';

    protected $fillable = ['company', 'website', 'phone', 'logo', 'phone_country_iso',
        'address', 'host', 'port', 'encryption', 'email', 'password',
        'error_log', 'error_email', 'state', 'city', 'country',
        'invoice', 'download', 'subscription_over', 'subscription_going_to_end',
        'forgot_password', 'order_mail', 'welcome_mail', 'invoice_template',
        'driver', 'admin_logo', 'title', 'favicon_title', 'fav_icon',
        'company_email', 'favicon_title_client', 'default_currency', 'default_symbol', 'file_storage', 'cin_no', 'gstin', 'zip', 'from_name', 'phone_code', 'knowledge_base_url', 'content'];

    protected $logName = 'settings';

    protected $logNameColumn = 'Company Settings';

    protected $logAttributes = [
        'company', 'website', 'phone', 'logo', 'phone_country_iso',
        'address', 'host', 'port', 'encryption', 'email', 'password',
        'error_log', 'error_email', 'state', 'city', 'country',
        'invoice', 'download', 'subscription_over', 'subscription_going_to_end',
        'forgot_password', 'order_mail', 'welcome_mail', 'invoice_template',
        'driver', 'admin_logo', 'title', 'favicon_title', 'fav_icon',
        'company_email', 'favicon_title_client', 'default_currency', 'default_symbol', 'file_storage', 'cin_no', 'gstin', 'zip', 'from_name', 'phone_code', 'knowledge_base_url', 'content',
    ];

    protected $logUrl = ['settings/system'];

    protected function getMappings(): array
    {
        return [
            'company' => ['Company', fn ($value) => $value],
            'website' => ['Website', fn ($value) => $value],
            'phone' => ['Phone', fn ($value) => $value],
            'logo' => ['Logo', fn ($value) => $value],
            'phone_country_iso' => ['Phone Country ISO', fn ($value) => $value],
            'address' => ['Address', fn ($value) => $value],
            'host' => ['Host', fn ($value) => $value],
            'port' => ['Port', fn ($value) => $value],
            'encryption' => ['Encryption', fn ($value) => $value],
            'email' => ['Email', fn ($value) => $value],
            'error_log' => ['Error Log', fn ($value) => $value],
            'error_email' => ['Error Email', fn ($value) => $value],
            'state' => ['State', fn ($value) => $value],
            'city' => ['City', fn ($value) => $value],
            'country' => ['Country', fn ($value) => $value],
            'invoice' => ['Invoice', fn ($value) => $value],
            'download' => ['Download', fn ($value) => $value],
            'subscription_over' => ['Subscription Over', fn ($value) => $value],
            'subscription_going_to_end' => ['Subscription Going To End', fn ($value) => $value],
            'forgot_password' => ['Forgot Password', fn ($value) => $value],
            'order_mail' => ['Order Mail', fn ($value) => $value],
            'welcome_mail' => ['Welcome Mail', fn ($value) => $value],
            'invoice_template' => ['Invoice Template', fn ($value) => $value],
            'driver' => ['Driver', fn ($value) => $value],
            'admin_logo' => ['Admin Logo', fn ($value) => $value],
            'title' => ['Title', fn ($value) => $value],
            'favicon_title' => ['Favicon Title', fn ($value) => $value],
            'fav_icon' => ['Fav Icon', fn ($value) => $value],
            'company_email' => ['Company Email', fn ($value) => $value],
            'favicon_title_client' => ['Favicon Title (Client)', fn ($value) => $value],
            'default_currency' => ['Default Currency', fn ($value) => $value],
            'default_symbol' => ['Default Symbol', fn ($value) => $value],
            'file_storage' => ['File Storage', fn ($value) => $value],
            'cin_no' => ['CIN Number', fn ($value) => $value],
            'gstin' => ['GSTIN', fn ($value) => $value],
            'zip' => ['ZIP', fn ($value) => $value],
            'from_name' => ['From Name', fn ($value) => $value],
            'phone_code' => ['Phone Code', fn ($value) => $value],
            'knowledge_base_url' => ['Knowledge Base URL', fn ($value) => $value],
            'content' => ['Content', fn ($value) => $value],
        ];
    }

    public function getPasswordAttribute($value)
    {
        if ($value) {
            $value = \Crypt::decrypt($value);
        }

        return $value;
    }

    public function setPasswordAttribute($value)
    {
        $value = \Crypt::encrypt($value);
        $this->attributes['password'] = $value;
    }

    public function getImage($value, $path, $default = null)
    {
        try {
            return $value
                ? Attach::getUrlPath($path.'/'.$value)
                : $default;
        } catch (\Exception $e) {
            return $default;
        }
    }

    public function getLogoAttribute($value)
    {
        return $this->getImage($value, 'images', asset('images/agora-invoicing.png'));
    }

    public function getAdminLogoAttribute($value)
    {
        return $this->getImage($value, 'admin/images', asset('images/agora_admin_logo.png'));
    }

    public function getFavIconAttribute($value)
    {
        return $this->getImage($value, 'common/images', asset('images/faveo.png'));
    }
}
