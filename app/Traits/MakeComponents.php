<?php

namespace App\Traits;

trait MakeComponents
{
    private function keyboardButton($option)
    {
        $keyboard = [
            'keyboard' => $option,
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
            'selective' => true,
        ];

        $keyboard = json_encode($keyboard);
        return $keyboard;
    }
}
