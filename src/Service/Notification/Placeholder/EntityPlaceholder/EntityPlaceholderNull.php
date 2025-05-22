<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder;

class EntityPlaceholderNull
{
    /**
     * @return array
     */
    public function getPlaceholder(): array
    {
        return [
            'user_email' => 'user_email',
            'user_name' => 'user_name',
            'data_message' => 'data_message',
            'reg_no' => 'reg_no',
            'model' => 'model',
            'team' => 'team',
            'driver' => 'driver',
            'status' => 'status',
            'triggered_by' => 'triggered_by',
            'event_time' => 'event_time',
            'data_url' => 'data_url',
            'vehicle_url' => 'vehicle_url',
            'driver_url' => 'driver_url',
            'device' => 'device',
            'device_imei' => 'device_imei',
            'title' => 'title',
            'battery_voltage' => 'battery_voltage',
            'area' => 'area',
            'expiration_date' => 'expiration_date',
            'form_title' => 'form_title',
            'new_value' => 'new_value',
            'old_value' => 'old_value',
        ];
    }
}
