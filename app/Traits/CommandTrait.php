<?php

namespace App\Traits;

use App\Models\Tag;
use App\Traits\MakeComponents;
use App\Traits\RequestTrait;
use App\Traits\TagTrait;
use App\Models\TelegramUser;
use Faker\Provider\Lorem;

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
                $message = "Limit exceed!";
                $response = $this->getCommands($result, $message);
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

                $teleUser->session = "tag;$tag->contact_id;name";
                $teleUser->save();

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

    private function getTags($result)
    {
        if (isset($result->message)) {
            $telegramId = $result->message->from->id;
            $sendMessage = true;
        } else if (isset($result->callback_query)) {
            $telegramId = $result->callback_query->from->id;
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
                            "text" => $tag->name,
                            "callback_data" => "tag;$tag->contact_id;get"
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
                        'message_id' => $result->callback_query->message->message_id,
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

        if ($action == "/cancel" || $action == '❌ Cancel') {
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
                if ($this->validateText($action, 50)) {
                    $tag->name = $action;
                    $teleUser->session = null;
                    $option = [
                        [
                            ["text" => "🏷️ Create Tag"],
                            ["text" => "📦 Get Tags"],
                        ],
                        [
                            ["text" => "☕ Buy Me a Coffee"],
                        ],
                    ];

                    $tag->save();
                    $teleUser->save();

                    $message = "Name updated";

                    $response = $this->apiRequest('sendMessage', [
                        'chat_id' => $telegramId,
                        'text' => $message,
                        'reply_markup' => $this->keyboardButton($option),
                    ]);
                } else {
                    $message = "Invalid name!";
                    $response = $this->apiRequest('sendMessage', [
                        'chat_id' => $telegramId,
                        'text' => $message,
                    ]);
                }
            }

            if ($entityAttribute == 'update_num') {
                if ($action == "/unset" || $action == '↪️ Unset Number') {
                    $tag->contact_number = null;
                    $message = "Contact Number deleted";
                    $option = [
                        [
                            ["text" => "🏷️ Create Tag"],
                            ["text" => "📦 Get Tags"],
                        ],
                        [
                            ["text" => "☕ Buy Me a Coffee"],
                        ],
                    ];

                    $teleUser->session = null;
                } else if ($this->validatePhoneNumber($action)) {
                    $tag->contact_number = $action;
                    $message = "Contact Number updated";
                    $option = [
                        [
                            ["text" => "🏷️ Create Tag"],
                            ["text" => "📦 Get Tags"],
                        ],
                        [
                            ["text" => "☕ Buy Me a Coffee"],
                        ],
                    ];

                    $teleUser->session = null;
                } else {
                    $message = "Invalid Contact Number";
                    $option = [
                        [
                            ["text" => "❌ Cancel"],
                            ["text" => "↪️ Unset Number"],
                        ],
                    ];
                }
                $tag->save();
                $teleUser->save();

                $response = $this->apiRequest('sendMessage', [
                    'chat_id' => $telegramId,
                    'text' => $message,
                    'reply_markup' => $this->keyboardButton($option),
                ]);
            }

            if ($entityAttribute == 'update_header') {
                if ($this->validateText($action, 20)) {
                    $tag->header = $action;
                    $teleUser->session = null;
                    $message = "Header updated";
                    $option = [
                        [
                            ["text" => "🏷️ Create Tag"],
                            ["text" => "📦 Get Tags"],
                        ],
                        [
                            ["text" => "☕ Buy Me a Coffee"],
                        ],
                    ];
                } else {
                    $message = "Invalid header!";
                    $option = [
                        [
                            ["text" => "❌ Cancel"],
                            ["text" => "↪️ Use Default"],
                        ],
                    ];
                }
                $tag->save();
                $teleUser->save();

                $response = $this->apiRequest('sendMessage', [
                    'chat_id' => $telegramId,
                    'text' => $message,
                    'reply_markup' => $this->keyboardButton($option),
                ]);
            }

            if ($entityAttribute == 'update_description') {
                if ($this->validateText($action, 50)) {
                    $tag->description = $action;
                    $teleUser->session = null;
                    $message = "Description updated";
                    $option = [
                        [
                            ["text" => "🏷️ Create Tag"],
                            ["text" => "📦 Get Tags"],
                        ],
                        [
                            ["text" => "☕ Buy Me a Coffee"],
                        ],
                    ];
                } else {
                    $message = "Invalid description!";
                    $option = [
                        [
                            ["text" => "❌ Cancel"],
                            ["text" => "↪️ Use Default"],
                        ],
                    ];
                }
                $tag->save();
                $teleUser->save();

                $response = $this->apiRequest('sendMessage', [
                    'chat_id' => $telegramId,
                    'text' => $message,
                    'reply_markup' => $this->keyboardButton($option),
                ]);
            }

            if ($entityAttribute == 'update_message') {
                if ($this->validateText($action, 255)) {
                    $tag->message = $action;
                    $teleUser->session = null;
                    $message = "Message updated";
                    $option = [
                        [
                            ["text" => "🏷️ Create Tag"],
                            ["text" => "📦 Get Tags"],
                        ],
                        [
                            ["text" => "☕ Buy Me a Coffee"],
                        ],
                    ];
                } else {
                    $message = "Invalid message!";
                    $option = [
                        [
                            ["text" => "❌ Cancel"],
                            ["text" => "↪️ Use Default"],
                        ],
                    ];
                }
                $tag->save();
                $teleUser->save();

                $response = $this->apiRequest('sendMessage', [
                    'chat_id' => $telegramId,
                    'text' => $message,
                    'reply_markup' => $this->keyboardButton($option),
                ]);
            }
        }

        return $response;
    }

    private function validatePhoneNumber($phoneNumber)
    {
        $phoneNumber = str_replace('+', '', $phoneNumber);
        $phoneNumber = str_replace('-', '', $phoneNumber);
        $phoneNumber = str_replace(' ', '', $phoneNumber);
        $phoneNumber = str_replace('(', '', $phoneNumber);
        $phoneNumber = str_replace(')', '', $phoneNumber);

        if (preg_match('/[\p{L}]/u', $phoneNumber)) {
            return false;
        } else if (strlen($phoneNumber) <= 15) {
            return $phoneNumber;
        } else {
            return false;
        }
    }

    private function validateText($text, $length)
    {
        if (preg_match('/[\p{L}]/u', $text) && strlen($text) <= $length) {
            return $text;
        } else {
            return false;
        }
    }
}