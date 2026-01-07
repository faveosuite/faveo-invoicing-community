<?php

namespace App\Plugins\Zoho\database\seeders;

class DasbaseSeeder
{
    public function faveoLocalFieldsSeeder()
    {
        $fields = [
            ['label' => 'User Name',  'key' => 'user_name',  'type' => 'string'],
            ['label' => 'First Name', 'key' => 'first_name', 'type' => 'string'],
            ['label' => 'Last Name',  'key' => 'last_name',  'type' => 'string'],
            ['label' => 'Email',      'key' => 'email',      'type' => 'email'],
            ['label' => 'Mobile',     'key' => 'mobile',     'type' => 'phone'],
            ['label' => 'Company',    'key' => 'company',    'type' => 'string'],
            ['label' => 'Address',    'key' => 'address',    'type' => 'text'],
            ['label' => 'Town',       'key' => 'town',       'type' => 'string'],
            ['label' => 'State',      'key' => 'state',      'type' => 'string'],
            ['label' => 'Country',    'key' => 'country',    'type' => 'string'],
            ['label' => 'Created At', 'key' => 'created_at', 'type' => 'datetime'],
        ];

        FaveoLocalFields::insert(
            collect($fields)->map(fn ($field) => [
                'display_name' => $field['label'],
                'field_key'    => $field['key'],
                'field_type'   => $field['type'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ])->toArray()
        );
    }
}