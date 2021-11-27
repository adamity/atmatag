<?php

namespace App\Traits;

use App\Models\Tag;
use App\Models\TelegramUser;

trait MakeComponents
{
    private function getCommands($result, $text)
    {
        if (isset($result->message)) {
            $telegramId = $result->message->from->id;
        } else if (isset($result->callback_query)) {
            $telegramId = $result->callback_query->from->id;
        }

        $message = "<b>Commands :</b> \n\n/create - create tag\n/tags - get tags";
        if ($text) $message = $text . "\n\n" . $message;

        $option = [
            [
                ["text" => "Create Tag"],
                ["text" => "Get Tags"],
            ],
            [
                ["text" => "Buy Me a Coffee"],
            ],
        ];

        $response = $this->apiRequest('sendMessage', [
            'chat_id' => $telegramId,
            'text' => $message,
            'parse_mode' => 'html',
            'reply_markup' => $this->keyboardButton($option),
        ]);

        return $response;
    }

    private function startBot($result)
    {
        if (isset($result->message)) {
            $telegramId = $result->message->from->id;
        } else if (isset($result->callback_query)) {
            $telegramId = $result->callback_query->from->id;
        }

        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();

        if (!$teleUser) {
            $teleUser = new TelegramUser();
            $teleUser->telegram_id = $telegramId;
            $teleUser->tag_limit = 2;
            $teleUser->save();
        }

        $response = $this->getCommands($result, null);
        return $response;
    }

    private function cancelOperation($result)
    {
        $telegramId = $result->message->from->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();

        $message = "No active command.";
        if ($teleUser && $teleUser->session) {
            $session = explode(";", $teleUser->session);

            if (count($session) == 3) {
                $entityType = $session[0];
                $entityId = $session[1];
                $entityAttribute = $session[2];
        
                if ($entityType == 'tag' && $entityAttribute == 'name') {
                    $tag = Tag::where('contact_id', $entityId)->first();
                    $tag->delete();
                }
            }

            $teleUser->session = null;
            $teleUser->save();

            $message = "Operation cancelled.";
        }

        $option = [
            [
                ["text" => "Create Tag"],
                ["text" => "Get Tags"],
            ],
            [
                ["text" => "Buy Me a Coffee"],
            ],
        ];

        $response = $this->apiRequest('sendMessage', [
            'chat_id' => $telegramId,
            'text' => $message,
            'reply_markup' => $this->keyboardButton($option),
        ]);

        return $response;
    }

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
}
