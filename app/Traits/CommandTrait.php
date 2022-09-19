<?php

namespace App\Traits;

use App\Models\Tag;
use App\Models\TelegramUser;
use App\Traits\ComponentTrait;
use App\Traits\RequestTrait;
use App\Traits\TagTrait;

trait CommandTrait
{
    use ComponentTrait;
    use RequestTrait;
    use TagTrait;

    private function getCommands($request, $text)
    {
        if (isset($request->message)) {
            $telegramId = $request->message->from->id;
        } else if (isset($request->callback_query)) {
            $telegramId = $request->callback_query->from->id;
        }

        $message = "<b>Commands :</b> \n\n/create - create tag\n/tags - get tags";
        if ($text) $message = $text . "\n\n" . $message;

        $option = [
            [
                ["text" => "🏷️ Create Tag"],
                ["text" => "📦 Get Tags"],
            ],
            [
                ["text" => "☕ Buy Me a Coffee"],
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

    private function startBot($request)
    {
        if (isset($request->message)) {
            $telegramId = $request->message->from->id;
        } else if (isset($request->callback_query)) {
            $telegramId = $request->callback_query->from->id;
        }

        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();

        if (!$teleUser) {
            $teleUser = new TelegramUser();
            $teleUser->telegram_id = $telegramId;
            $teleUser->tag_limit = 2;
            $teleUser->save();
        }

        $response = $this->getCommands($request, null);
        return $response;
    }

    private function getHelp($request)
    {
        $response = $this->getCommands($request, 'help');
        return $response;
    }

    private function createTag($request)
    {
        $telegramId = $request->message->from->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();

        if (!$teleUser) {
            $response = $this->startBot($request);
        } else {
            if (count($teleUser->tags) >= $teleUser->tag_limit) {
                $message = "Limit exceed!";
                $response = $this->getCommands($request, $message);
                return $response;
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

                $this->setSession($teleUser, $tag->contact_id, "name");
                $message = "What should we call this tag?";
            }

            $option = [
                [
                    ["text" => "❌ Cancel"],
                ],
            ];

            $response = $this->apiRequest('sendMessage', [
                'chat_id' => $telegramId,
                'text' => $message,
                'reply_markup' => $this->keyboardButton($option),
            ]);
        }

        return $response;
    }

    private function getTags($request)
    {
        if (isset($request->message)) {
            $telegramId = $request->message->from->id;
            $sendMessage = true;
        } else if (isset($request->callback_query)) {
            $telegramId = $request->callback_query->from->id;
            $sendMessage = false;
        }

        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();

        if ($teleUser) {
            if (count($teleUser->tags)) {
                $tags = $teleUser->tags;
                $option = [];

                foreach ($tags as $tag) {
                    $temp = [
                        [
                            "text" => $tag->name ?? "Untitled",
                            "callback_data" => "$tag->contact_id;get"
                        ]
                    ];

                    array_push($option, $temp);
                }

                $message = "Choose your tag from the list below:";

                if ($sendMessage) {
                    $response = $this->apiRequest('sendMessage', [
                        'chat_id' => $telegramId,
                        'text' => $message,
                        'reply_markup' => $this->inlineKeyboardButton($option),
                    ]);
                } else {
                    $response = $this->apiRequest('editMessageText', [
                        'chat_id' => $telegramId,
                        'message_id' => $request->callback_query->message->message_id,
                        'text' => $message,
                        'reply_markup' => $this->inlineKeyboardButton($option),
                    ]);
                }
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

    private function cancelOperation($request)
    {
        $telegramId = $request->message->from->id;
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

            $this->clearSession($teleUser);
            $message = "Operation cancelled.";
        }

        $option = [
            [
                ["text" => "🏷️ Create Tag"],
                ["text" => "📦 Get Tags"],
            ],
            [
                ["text" => "☕ Buy Me a Coffee"],
            ],
        ];

        $response = $this->apiRequest('sendMessage', [
            'chat_id' => $telegramId,
            'text' => $message,
            'reply_markup' => $this->keyboardButton($option),
        ]);

        return $response;
    }
}
