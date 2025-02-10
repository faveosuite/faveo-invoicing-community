<?php

namespace App\Model\Common;

use App\Facades\Attach;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';

    protected $fillable = ['company', 'website', 'phone', 'logo', 'phone_country_iso',
        'address', 'host', 'port', 'encryption', 'email', 'password',
        'error_log', 'error_email', 'state', 'city', 'country',
        'invoice', 'download', 'subscription_over', 'subscription_going_to_end',
        'forgot_password', 'order_mail', 'welcome_mail', 'invoice_template',
        'driver', 'admin_logo', 'title', 'favicon_title', 'fav_icon',
        'company_email', 'favicon_title_client', 'default_currency', 'default_symbol', 'file_storage', 'cin_no', 'gstin', 'zip', 'from_name', 'phone_code', 'knowledge_base_url','content'];

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
        return $value
            ? Attach::getUrlPath($path.'/'.$value)
            : $default;
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
