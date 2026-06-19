<?php

namespace App\Model\Mailjob;

use App\Model\Common\StatusSetting;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $job
 * @property string|null $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Condition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Condition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Condition query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Condition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Condition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Condition whereJob($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Condition whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Condition whereValue($value)
 *
 * @mixin \Eloquent
 */
class Condition extends Model
{
    protected $table = 'conditions';

    protected $fillable = ['job', 'value'];

    public function checkActiveJob(): array
    {
        $result = ['expiryMail' => '', 'deleteLogs' => '', 'subsExpirymail' => '', 'postExpirymail' => '', 'cloud' => '', 'invoice' => '', 'msg91Reports' => '', 'reoonLogs' => '', 'systemLogs' => '', 'installationLogs' => '', 'licenseReportsCleanup' => '', 'licenseCallbacksCleanup' => '', 'licenseCrackReportsCleanup' => '', 'licenseSystemReportsCleanup' => '', 'licenseVersionsCleanup' => ''];
        $allStatus = new StatusSetting();
        $status = $allStatus->find(1);
        if ($status) {
            if ($status->expiry_mail == 1) {
                $result['expiryMail'] = true;
            }

            if ($status->activity_log_delete == 1) {
                $result['deleteLogs'] = true;
            }

            if ($status->subs_expirymail == 1) {
                $result['subsExpirymail'] = true;
            }

            if ($status->post_expirymail == 1) {
                $result['postExpirymail'] = true;
            }

            if ($status->cloud_mail_status == 1) {
                $result['cloud'] = true;
            }

            if ($status->invoice_deletion_status == 1) {
                $result['invoice'] = true;
            }

            if ($status->msg91_report_delete_status == 1) {
                $result['msg91Reports'] = true;
            }

            if ($status->reoon_deletion_status == 1) {
                $result['reoonLogs'] = true;
            }

            if ($status->system_log_status == 1) {
                $result['systemLogs'] = true;
            }

            if ($status->installation_logs_status == 1) {
                $result['installationLogs'] = true;
            }

            if ($status->license_reports_cleanup_status == 1) {
                $result['licenseReportsCleanup'] = true;
            }

            if ($status->license_callbacks_cleanup_status == 1) {
                $result['licenseCallbacksCleanup'] = true;
            }

            if ($status->license_crack_reports_cleanup_status == 1) {
                $result['licenseCrackReportsCleanup'] = true;
            }

            if ($status->license_system_reports_cleanup_status == 1) {
                $result['licenseSystemReportsCleanup'] = true;
            }

            if ($status->license_versions_cleanup_status == 1) {
                $result['licenseVersionsCleanup'] = true;
            }
        }

        return $result;
    }

    public function getConditionValue(mixed $job): array
    {
        $value = ['condition' => '', 'at' => ''];
        $condition = $this->where('job', $job)->first();
        if ($condition) {
            $condition_value = explode(',', (string) $condition->value);
            $value = ['condition' => $condition_value, 'at' => ''];
            if (is_array($condition_value)) { // @phpstan-ignore function.alreadyNarrowedType
                $value = ['condition' => $this->checkArray(0, $condition_value), 'at' => $this->checkArray(1, $condition_value)];
            }
        }

        return $value;
    }

    public function checkArray(mixed $key, mixed $array): mixed
    {
        if (is_array($array) && array_key_exists($key, $array)) {
            return $array[$key];
        }

        return '';
    }
}
