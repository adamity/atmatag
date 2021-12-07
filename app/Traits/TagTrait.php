<?php

namespace App\Traits;

use App\Models\Tag;
use App\Models\TelegramUser;
use App\Traits\MakeComponents;
use App\Traits\RequestTrait;
use Illuminate\Support\Str;

trait TagTrait
{
    use RequestTrait;
    use MakeComponents;

    private function getTag($entityId, $telegramId, $result)
    {
        if (isset($result->message)) {
            $messageId = $result->message->message_id;
            $sendMessage = true;
        } else if (isset($result->callback_query)) {
            $messageId = $result->callback_query->message->message_id;
            $sendMessage = false;
        }

        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();

        if (!$teleUser) {
            $response = $this->startBot($result);
            return $response;
        } else {
            if (count($teleUser->tags)) {
                $tag = Tag::where('contact_id', $entityId)->first();
                $tagURL = preg_replace("/^http:/i", "https:", url(route('tag', ['contact_id' => $tag->contact_id])));

                $message = "<b>Name :</b> $tag->name";
                $message .= "\n<b>Contact Number :</b> $tag->contact_number_view";
                $message .= "\n<b>Header :</b> $tag->header";
                $message .= "\n<b>Description :</b> $tag->description";
                $message .= "\n<b>Message :</b> $tag->message";
                $message .= "\n<b>Availability :</b> $tag->toggle_view";

                $option = [
                    [
                        ["text" => "Get QR Code", "callback_data" => "tag;$tag->contact_id;qr_code"],
                        ["text" => "Edit Tag", "callback_data" => "tag;$tag->contact_id;edit"],
                    ],
                    [
                        ["text" => "Delete Tag", "callback_data" => "tag;$tag->contact_id;delete"],
                        ["text" => "Test Tag", "url" => $tagURL],
                    ],
                    [
                        ["text" => "Back", "callback_data" => "tag;$tag->contact_id;tag_list"],
                    ],
                ];

                if ($sendMessage) {
                    $response = $this->apiRequest('sendMessage', [
                        'chat_id' => $telegramId,
                        'text' => $message,
                        'parse_mode' => 'html',
                        'reply_markup' => $this->inlineKeyboardButton($option),
                    ]);
                } else {
                    $response = $this->apiRequest('editMessageText', [
                        'chat_id' => $telegramId,
                        'message_id' => $messageId,
                        'text' => $message,
                        'parse_mode' => 'html',
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
        }

        return $response;
    }

    private function editTag($entityId, $result)
    {
        if (isset($result->message)) {
            $telegramId = $result->message->from->id;
            $sendMessage = true;
        } else if (isset($result->callback_query)) {
            $telegramId = $result->callback_query->from->id;
            $sendMessage = false;
        }

        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $response = false;

        if ($teleUser) {
            if (count($teleUser->tags)) {
                $tag = Tag::where('contact_id', $entityId)->first();

                $message = "<b>Name :</b> $tag->name";
                $message .= "\n<b>Contact Number :</b> $tag->contact_number_view";
                $message .= "\n<b>Header :</b> $tag->header";
                $message .= "\n<b>Description :</b> $tag->description";
                $message .= "\n<b>Message :</b> $tag->message";
                $message .= "\n<b>Availability :</b> $tag->toggle_view";

                $toggling = "Enable Tag";
                if ($tag->toggle) $toggling = "Disable Tag";

                $option = [
                    [
                        ["text" => "Name", "callback_data" => "tag;$tag->contact_id;edit_name"],
                        ["text" => "Contact Number", "callback_data" => "tag;$tag->contact_id;edit_num"],
                    ],
                    [
                        ["text" => "Header", "callback_data" => "tag;$tag->contact_id;edit_header"],
                        ["text" => "Description", "callback_data" => "tag;$tag->contact_id;edit_description"],
                    ],
                    [
                        ["text" => "Message", "callback_data" => "tag;$tag->contact_id;edit_message"],
                        ["text" => $toggling, "callback_data" => "tag;$tag->contact_id;toggle"],
                    ],
                    [
                        ["text" => "Back", "callback_data" => "tag;$tag->contact_id;get"],
                    ],
                ];

                if ($sendMessage) {
                    $response = $this->apiRequest('sendMessage', [
                        'chat_id' => $telegramId,
                        'text' => $message,
                        'parse_mode' => 'html',
                        'reply_markup' => $this->inlineKeyboardButton($option),
                    ]);
                } else {
                    $response = $this->apiRequest('editMessageText', [
                        'chat_id' => $telegramId,
                        'message_id' => $result->callback_query->message->message_id,
                        'text' => $message,
                        'parse_mode' => 'html',
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

    private function editName($entityId, $result)
    {
        $telegramId = $result->callback_query->message->chat->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $response = false;

        if ($teleUser) {
            if (count($teleUser->tags)) {
                $tag = Tag::where('contact_id', $entityId)->first();
                $this->setSession($teleUser, "tag", $tag->contact_id, "update_name");

                $message = "Enter new name";

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

    private function editNum($entityId, $result)
    {
        $telegramId = $result->callback_query->message->chat->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $response = false;

        if ($teleUser) {
            if (count($teleUser->tags)) {
                $tag = Tag::where('contact_id', $entityId)->first();
                $this->setSession($teleUser, "tag", $tag->contact_id, "update_num");

                $message = "Enter new Contact Number\n/unset - unset contact number";

                $option = [
                    [
                        ["text" => "❌ Cancel"],
                        ["text" => "↪️ Unset Number"],
                    ],
                ];

                $response = $this->apiRequest('sendMessage', [
                    'chat_id' => $telegramId,
                    'text' => $message,
                    'reply_markup' => $this->keyboardButton($option),
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

    private function editHeader($entityId, $result)
    {
        $telegramId = $result->callback_query->message->chat->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $response = false;

        if ($teleUser) {
            if (count($teleUser->tags)) {
                $tag = Tag::where('contact_id', $entityId)->first();
                $this->setSession($teleUser, "tag", $tag->contact_id, "update_header");

                $message = "Enter new header";

                $option = [
                    [
                        ["text" => "❌ Cancel"],
                        ["text" => "↪️ Use Default"],
                    ],
                ];

                $response = $this->apiRequest('sendMessage', [
                    'chat_id' => $telegramId,
                    'text' => $message,
                    'reply_markup' => $this->keyboardButton($option),
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

    private function editDescription($entityId, $result)
    {
        $telegramId = $result->callback_query->message->chat->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $response = false;

        if ($teleUser) {
            if (count($teleUser->tags)) {
                $tag = Tag::where('contact_id', $entityId)->first();
                $this->setSession($teleUser, "tag", $tag->contact_id, "update_description");

                $message = "Enter new description";

                $option = [
                    [
                        ["text" => "❌ Cancel"],
                        ["text" => "↪️ Use Default"],
                    ],
                ];

                $response = $this->apiRequest('sendMessage', [
                    'chat_id' => $telegramId,
                    'text' => $message,
                    'reply_markup' => $this->keyboardButton($option),
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

    private function editMessage($entityId, $result)
    {
        $telegramId = $result->callback_query->message->chat->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $response = false;

        if ($teleUser) {
            if (count($teleUser->tags)) {
                $tag = Tag::where('contact_id', $entityId)->first();
                $this->setSession($teleUser, "tag", $tag->contact_id, "update_message");

                $message = "Enter new message";

                $option = [
                    [
                        ["text" => "❌ Cancel"],
                        ["text" => "↪️ Use Default"],
                    ],
                ];

                $response = $this->apiRequest('sendMessage', [
                    'chat_id' => $telegramId,
                    'text' => $message,
                    'reply_markup' => $this->keyboardButton($option),
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

    private function toggleTag($entityId, $result)
    {
        $telegramId = $result->callback_query->message->chat->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $response = false;

        if ($teleUser) {
            if (count($teleUser->tags)) {
                $tag = Tag::where('contact_id', $entityId)->first();

                if ($tag->toggle) {
                    $tag->toggle = null;
                    $toggling = "Enable Tag";
                } else {
                    $tag->toggle = 1;
                    $toggling = "Disable Tag";
                }

                $tag->save();

                $response = $this->editTag($entityId, $result);
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

    private function deleteTag($entityId, $result)
    {
        $telegramId = $result->callback_query->message->chat->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $response = false;

        if ($teleUser) {
            if (count($teleUser->tags)) {
                $tag = Tag::where('contact_id', $entityId)->first();
                $this->setSession($teleUser, "tag", $tag->contact_id, "delete");

                $message = "Are you sure to delete this tag?";

                $option = [
                    [
                        ["text" => "Yes", "callback_data" => "tag;$tag->contact_id;confirm_delete"],
                        ["text" => "No", "callback_data" => "tag;$tag->contact_id;cancel_delete"],
                    ],
                ];

                $response = $this->apiRequest('editMessageText', [
                    'chat_id' => $telegramId,
                    'message_id' => $result->callback_query->message->message_id,
                    'text' => $message,
                    'parse_mode' => 'html',
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

    private function confirmDeleteTag($entityId, $result)
    {
        $telegramId = $result->callback_query->message->chat->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $response = false;

        if ($teleUser) {
            if (count($teleUser->tags)) {
                $tag = Tag::where('contact_id', $entityId)->first();
                $tag->delete();

                $this->clearSession($teleUser);

                $message = "Tag Deleted!";

                $response = $this->apiRequest('editMessageText', [
                    'chat_id' => $telegramId,
                    'message_id' => $result->callback_query->message->message_id,
                    'text' => $message,
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

    private function cancelDeleteTag($entityId, $result)
    {
        $telegramId = $result->callback_query->message->chat->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $response = false;

        if ($teleUser) {
            if (count($teleUser->tags)) {
                $this->clearSession($teleUser);
                $response = $this->getTag($entityId, $telegramId, $result);
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

    private function getQrCode($entityId, $result)
    {
        $telegramId = $result->callback_query->message->chat->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $response = false;

        if ($teleUser) {
            if (count($teleUser->tags)) {
                $tag = Tag::where('contact_id', $entityId)->first();
                $tagURL = preg_replace("/^http:/i", "https:", url(route('tag', ['contact_id' => $tag->contact_id])));

                $response = $this->apiRequest('sendPhoto', [
                    'chat_id' => $telegramId,
                    'photo' => $this->generateQrCode($tagURL),
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

    // Update Details Functions Here
    private function setName($result)
    {
        $action = $result->message->text;
        $telegramId = $result->message->from->id;

        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $data = explode(";", $teleUser->session);

        $response = false;
        $entityId = $data[1];

        $tag = Tag::where('contact_id', $entityId)->first();
        if ($this->validateText($action, 50)) {
            $tag->name = $action;
            $tag->save();

            $option = [
                [
                    ["text" => "🏷️ Create Tag"],
                    ["text" => "📦 Get Tags"],
                ],
                [
                    ["text" => "☕ Buy Me a Coffee"],
                ],
            ];

            $this->clearSession($teleUser);
            $this->apiRequest('sendMessage', [
                'chat_id' => $telegramId,
                'text' => "Tag Created!",
                'reply_markup' => $this->keyboardButton($option),
            ]);

            $response = $this->getTag($entityId, $telegramId, $result);
        } else {
            $message = "Invalid name!";
            $response = $this->apiRequest('sendMessage', [
                'chat_id' => $telegramId,
                'text' => $message,
            ]);
        }

        return $response;
    }

    private function updateName($result)
    {
        $action = $result->message->text;
        $telegramId = $result->message->from->id;

        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $data = explode(";", $teleUser->session);

        $response = false;
        $entityId = $data[1];

        $tag = Tag::where('contact_id', $entityId)->first();
        if ($this->validateText($action, 50)) {
            $tag->name = $action;
            $option = [
                [
                    ["text" => "🏷️ Create Tag"],
                    ["text" => "📦 Get Tags"],
                ],
                [
                    ["text" => "☕ Buy Me a Coffee"],
                ],
            ];

            $teleUser->save();
            $this->clearSession($teleUser);

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

        return $response;
    }

    private function updateNum($result)
    {
        $action = $result->message->text;
        $telegramId = $result->message->from->id;

        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $data = explode(";", $teleUser->session);

        $response = false;
        $entityId = $data[1];

        $tag = Tag::where('contact_id', $entityId)->first();
        $option = [
            [
                ["text" => "🏷️ Create Tag"],
                ["text" => "📦 Get Tags"],
            ],
            [
                ["text" => "☕ Buy Me a Coffee"],
            ],
        ];

        if ($action == "/unset" || $action == '↪️ Unset Number') {
            $tag->contact_number = null;
            $message = "Contact Number deleted";

            $this->clearSession($teleUser);
        } else if ($this->validatePhoneNumber($action)) {
            $tag->contact_number = $action;
            $message = "Contact Number updated";

            $this->clearSession($teleUser);
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

        $response = $this->apiRequest('sendMessage', [
            'chat_id' => $telegramId,
            'text' => $message,
            'reply_markup' => $this->keyboardButton($option),
        ]);

        return $response;
    }

    private function updateHeader($result)
    {
        $action = $result->message->text;
        $telegramId = $result->message->from->id;

        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $data = explode(";", $teleUser->session);

        $response = false;
        $entityId = $data[1];

        $tag = Tag::where('contact_id', $entityId)->first();
        if ($action == "↪️ Use Default") $action = "Contact Me";

        if ($this->validateText($action, 20)) {
            $tag->header = $action;
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
            $this->clearSession($teleUser);
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

        $response = $this->apiRequest('sendMessage', [
            'chat_id' => $telegramId,
            'text' => $message,
            'reply_markup' => $this->keyboardButton($option),
        ]);

        return $response;
    }

    private function updateDescription($result)
    {
        $action = $result->message->text;
        $telegramId = $result->message->from->id;

        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $data = explode(";", $teleUser->session);

        $response = false;
        $entityId = $data[1];

        $tag = Tag::where('contact_id', $entityId)->first();
        if ($action == "↪️ Use Default") $action = "Send me a message in case of emergency";

        if ($this->validateText($action, 50)) {
            $tag->description = $action;
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
            $this->clearSession($teleUser);
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

        $response = $this->apiRequest('sendMessage', [
            'chat_id' => $telegramId,
            'text' => $message,
            'reply_markup' => $this->keyboardButton($option),
        ]);

        return $response;
    }

    private function updateMessage($result)
    {
        $action = $result->message->text;
        $telegramId = $result->message->from->id;

        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $data = explode(";", $teleUser->session);

        $response = false;
        $entityId = $data[1];

        $tag = Tag::where('contact_id', $entityId)->first();
        if ($action == "↪️ Use Default") $action = "Hye there! Send me a message in case of emergency";

        if ($this->validateText($action, 255)) {
            $tag->message = $action;
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
            $this->clearSession($teleUser);
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

        return $response;
    }
}
