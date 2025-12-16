<?php

namespace App\Model\Mailjob;

use App\Model\Common\StatusSetting;
use Illuminate\Database\Eloquent\Model;

class Condition extends Model
{
    protected $table = 'conditions';

    protected $fillable = ['job', 'value'];

    public function checkActiveJob()
    {
        $status = StatusSetting::find(1);

        return [
            'expiryMail'     => $status && $status->expiry_mail == 1,
            'deleteLogs'     => $status && $status->activity_log_delete == 1,
            'subsExpirymail' => $status && $status->subs_expirymail == 1,
            'postExpirymail' => $status && $status->post_expirymail == 1,
            'cloud'          => $status && $status->cloud_mail_status == 1,
            'invoice'        => $status && $status->invoice_deletion_status == 1,
            'msg91Reports'   => $status && $status->msg91_report_delete_status == 1,
            'reoonLogs'      => $status && $status->reoon_deletion_status == 1,
            'systemLogs'     => $status && $status->system_log_status == 1,
        ];
    }


    public function getConditionValue($job)
    {
        $value = ['condition' => '', 'at' => ''];
        $condition = $this->where('job', $job)->first();
        if ($condition) {
            $condition_value = explode(',', $condition->value);
            $value = ['condition' => $condition_value, 'at' => ''];
            if (is_array($condition_value)) {
                $value = ['condition' => $this->checkArray(0, $condition_value), 'at' => $this->checkArray(1, $condition_value)];
            }
        }

        return $value;
    }

    public function checkArray($key, $array)
    {
        $value = '';
        if (is_array($array)) {
            if (array_key_exists($key, $array)) {
                $value = $array[$key];
            }
        }

        return $value;
    }
}
