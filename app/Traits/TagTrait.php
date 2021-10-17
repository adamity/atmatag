<?php

namespace App\Traits;

use App\Models\Tag;
use App\Models\TelegramUser;
use App\Traits\RequestTrait;
use Illuminate\Support\Str;

trait TagTrait
{
    use RequestTrait;

    private function getTag($entityId, $result)
    {
        $telegramId = $result->callback_query->message->chat->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $response = false;

        if ($teleUser) {
            if (count($teleUser->tags)) {
                $tag = Tag::where('contact_id', $entityId)->first();
                $tagURL = preg_replace("/^http:/i", "https:", url(route('tag', ['contact_id' => $tag->contact_id])));

                $message = "<b>Name :</b> $tag->name";
                $message .= "\n<b>Contact Number :</b> $tag->contact_number";
                $message .= "\n<b>Header :</b> $tag->header";
                $message .= "\n<b>Description :</b> $tag->description";
                $message .= "\n<b>Message :</b> $tag->message";
                $message .= "\n<b>Availability :</b> $tag->toggle";

                $response = $this->apiRequest('sendPhoto', [
                    'chat_id' => $result->callback_query->message->chat->id,
                    'photo' => $this->generateQrCode($tagURL),
                    'caption' => $message,
                    'parse_mode' => 'html',
                ]);
            } else {
                $message = "No Tag Found!\n/create - create tag";

                $response = $this->apiRequest('sendMessage', [
                    'chat_id' => $telegramId,
                    'text' => $message,
                ]);
            }
        } else {
            $message = "Hye There!\n/start - start the bot";

            $response = $this->apiRequest('sendMessage', [
                'chat_id' => $telegramId,
                'text' => $message,
            ]);
        }

        return $response;
    }

    private function deleteTag($result)
    {
        $telegramId = $result->message->from->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();

        if ($teleUser) {
            if (count($teleUser->tags)) {
                $tag = $teleUser->tags->first();
                $tag->delete();

                $message = "Tag Deleted!\n/create - create tags";

                $response = $this->apiRequest('sendMessage', [
                    'chat_id' => $telegramId,
                    'text' => $message,
                ]);
            } else {
                $message = "No Tag Found!\n/create - create tag";

                $response = $this->apiRequest('sendMessage', [
                    'chat_id' => $telegramId,
                    'text' => $message,
                ]);
            }
        } else {
            $message = "Hye There!\n/start - start the bot";

            $response = $this->apiRequest('sendMessage', [
                'chat_id' => $telegramId,
                'text' => $message,
            ]);
        }

        return $response;
    }

    private function editTag($result)
    {
        $telegramId = $result->message->from->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
    }

    private function generateQrCode($tagURL)
    {
        $url = "https://image-charts.com/chart?chs=900x900&cht=qr&chl=$tagURL&choe=UTF-8&chof=.png";
        return $url;
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
