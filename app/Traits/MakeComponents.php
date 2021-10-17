<?php

namespace App\Traits;

use App\Models\Tag;
use Illuminate\Support\Str;

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

    private function generateContactId()
    {
        $success = false;
        $contact_id = false;
        $count = 0;

        do {
            $uuid = Str::uuid()->toString();
            $contact_id = substr($uuid,0,8);

            $tag = Tag::where('contact_id', $contact_id)->first();
            if (!$tag) $success = true;

            $count++;
        } while (!$success || $count < 10);

        return $contact_id;
    }
}
