<?php

namespace Database\Factories\Model\Common;

use App\Model\Common\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

class SettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $model = Setting::class;

    public function definition()
    {
        return [
            'version' => 'V1.5.2',

        ];
    }
}
