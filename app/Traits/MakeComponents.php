<?php

namespace App\Traits;

use App\Models\Tag;
use App\Models\TelegramUser;

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

    private function removeKeyboardButton()
    {
        $keyboard = [
            'remove_keyboard' => true,
            'selective' => true,
        ];

        $keyboard = json_encode($keyboard);
        return $keyboard;
    }

    private function inlineKeyboardButton($option)
    {
        $keyboard = [
            'inline_keyboard' => $option,
        ];

        $keyboard = json_encode($keyboard);
        return $keyboard;
    }

    private function inputMediaPhoto($option)
    {
        $inputMedia = [
            'type' => 'photo',
            'media' => $option,
        ];

        $inputMedia = json_encode($inputMedia);
        return $inputMedia;
    }

    // Last in CommandTrait, check function by function
    // Create option for keyboard button
    private function option()
    {
    }
}
