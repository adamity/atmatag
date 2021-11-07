<?php

namespace App\Traits;

use App\Models\Tag;
use App\Traits\MakeComponents;
use App\Traits\RequestTrait;
use App\Traits\TagTrait;
use App\Models\TelegramUser;

trait CommandTrait
{
    use RequestTrait;
    use MakeComponents;
    use TagTrait;

    private function createTag($result)
    {
        $telegramId = $result->message->from->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();

        if (!$teleUser) {
            $response = $this->startBot($result);
        } else {
            if (count($teleUser->tags) >= $teleUser->tag_limit) {
                $message = "Limit exceed!\n/tags - get tags";
            } else {
                $contact_id = $this->generateContactId();

                $tag = new Tag();
                $tag->telegram_user_id = $teleUser->id;
                $tag->contact_id = $contact_id;
                $tag->header = "Contact Me";
                $tag->description = "Send me a message in case of emergency";
                $tag->message = "Hye there! Send me a message in case of emergency";
                $tag->toggle = 1;
                $tag->save();

                $teleUser->session = "tag;$tag->contact_id;name";
                $teleUser->save();

                $message = "What should we call this tag?";
            }

            $response = $this->apiRequest('sendMessage', [
                'chat_id' => $telegramId,
                'text' => $message,
                'reply_markup' => $this->removeKeyboardButton(),
            ]);
        }

        return $response;
    }

    private function getTags($result)
    {
        $telegramId = $result->message->from->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();

        if ($teleUser) {
            if (count($teleUser->tags)) {
                $tags = $teleUser->tags;
                $option = [];

                foreach ($tags as $tag) {
                    $temp = [
                        [
                            "text" => $tag->name,
                            "callback_data" => "tag;$tag->contact_id;get"
                        ]
                    ];

                    array_push($option, $temp);
                }

                $message = "Choose your tag from the list below:";

                $response = $this->apiRequest('sendMessage', [
                    'chat_id' => $telegramId,
                    'text' => $message,
                    'reply_markup' => $this->inlineKeyboardButton($option),
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

    private function updateSession($result)
    {
        $action = $result->message->text;
        $telegramId = $result->message->from->id;

        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $data = explode(";", $teleUser->session);

        $response = false;

        $entityType = $data[0];
        $entityId = $data[1];
        $entityAttribute = $data[2];

        if ($action == "/cancel") {
            $response = $this->cancelOperation($result);
        } else if ($entityType == 'tag') {
            $tag = Tag::where('contact_id', $entityId)->first();

            if ($entityAttribute == 'name') {
                $tag->name = $action;
                $tag->save();

                $teleUser->session = null;
                $teleUser->save();

                $response = $this->getTag($entityId, $telegramId, $result);
            }

            if ($entityAttribute == 'update_name') {
                $tag->name = $action;
                $tag->save();

                $teleUser->session = null;
                $teleUser->save();

                $message = "Name updated";

                $response = $this->apiRequest('sendMessage', [
                    'chat_id' => $telegramId,
                    'text' => $message,
                ]);
            }

            if ($entityAttribute == 'update_num') {
                $tag->contact_number = $action;
                $tag->save();

                $teleUser->session = null;
                $teleUser->save();

                $message = "Number updated";

                $response = $this->apiRequest('sendMessage', [
                    'chat_id' => $telegramId,
                    'text' => $message,
                ]);
            }

            if ($entityAttribute == 'update_header') {
                $tag->header = $action;
                $tag->save();

                $teleUser->session = null;
                $teleUser->save();

                $message = "Header updated";

                $response = $this->apiRequest('sendMessage', [
                    'chat_id' => $telegramId,
                    'text' => $message,
                ]);
            }

            if ($entityAttribute == 'update_description') {
                $tag->description = $action;
                $tag->save();

                $teleUser->session = null;
                $teleUser->save();

                $message = "Description updated";

                $response = $this->apiRequest('sendMessage', [
                    'chat_id' => $telegramId,
                    'text' => $message,
                ]);
            }

            if ($entityAttribute == 'update_message') {
                $tag->message = $action;
                $tag->save();

                $teleUser->session = null;
                $teleUser->save();

                $message = "Message updated";

                $response = $this->apiRequest('sendMessage', [
                    'chat_id' => $telegramId,
                    'text' => $message,
                ]);
            }
        }

        return $response;
    }
}
