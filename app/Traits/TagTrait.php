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
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();

        if (!$teleUser) {
            // KIV : This will return error because the $result passed is not the expected one
            // $response = $this->startBot($result);
        } else {
            if (count($teleUser->tags)) {
                $tag = Tag::where('contact_id', $entityId)->first();
                $tagURL = preg_replace("/^http:/i", "https:", url(route('tag', ['contact_id' => $tag->contact_id])));

                $message = "<b>Name :</b> $tag->name";
                $message .= "\n<b>Contact Number :</b> $tag->contact_number";
                $message .= "\n<b>Header :</b> $tag->header";
                $message .= "\n<b>Description :</b> $tag->description";
                $message .= "\n<b>Message :</b> $tag->message";
                $message .= "\n<b>Availability :</b> $tag->toggle";

                $option = [
                    [
                        ["text" => "Edit", "callback_data" => "tag;$tag->contact_id;edit"],
                        ["text" => "Delete", "callback_data" => "tag;$tag->contact_id;delete"],
                    ],
                    [
                        ["text" => "QR Code", "callback_data" => "qr code"],
                        ["text" => "Testing", "url" => $tagURL],
                    ],
                    [
                        ["text" => "Back to Tags List", "callback_data" => "back"],
                    ],
                ];

                $response = $this->apiRequest('sendPhoto', [
                    'chat_id' => $telegramId,
                    'photo' => $this->generateQrCode($tagURL),
                    'caption' => $message,
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
        }

        return $response;
    }

    private function editTag($entityId, $result)
    {
        $telegramId = $result->callback_query->message->chat->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $response = false;

        if ($teleUser) {
            if (count($teleUser->tags)) {
                $tag = Tag::where('contact_id', $entityId)->first();
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
                        ["text" => "Back", "callback_data" => "tag;$tag->contact_id;back"],
                    ],
                ];

                $response = $this->apiRequest('editMessageCaption', [
                    'chat_id' => $result->callback_query->message->chat->id,
                    'message_id' => $result->callback_query->message->message_id,
                    'caption' => $result->callback_query->message->caption,
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

    private function editName($entityId, $result)
    {
        $telegramId = $result->callback_query->message->chat->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $response = false;

        if ($teleUser) {
            if (count($teleUser->tags)) {
                $tag = Tag::where('contact_id', $entityId)->first();

                $teleUser->session = "tag;$tag->contact_id;update_name";
                $teleUser->save();

                $message = "Enter new name";

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

    private function editNum($entityId, $result)
    {
        $telegramId = $result->callback_query->message->chat->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $response = false;

        if ($teleUser) {
            if (count($teleUser->tags)) {
                $tag = Tag::where('contact_id', $entityId)->first();

                $teleUser->session = "tag;$tag->contact_id;update_num";
                $teleUser->save();

                $message = "Enter new Contact Number";

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

    private function editHeader($entityId, $result)
    {
        $telegramId = $result->callback_query->message->chat->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $response = false;

        if ($teleUser) {
            if (count($teleUser->tags)) {
                $tag = Tag::where('contact_id', $entityId)->first();

                $teleUser->session = "tag;$tag->contact_id;update_header";
                $teleUser->save();

                $message = "Enter new header";

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

    private function editDescription($entityId, $result)
    {
        $telegramId = $result->callback_query->message->chat->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $response = false;

        if ($teleUser) {
            if (count($teleUser->tags)) {
                $tag = Tag::where('contact_id', $entityId)->first();

                $teleUser->session = "tag;$tag->contact_id;update_description";
                $teleUser->save();

                $message = "Enter new description";

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

    private function editMessage($entityId, $result)
    {
        $telegramId = $result->callback_query->message->chat->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $response = false;

        if ($teleUser) {
            if (count($teleUser->tags)) {
                $tag = Tag::where('contact_id', $entityId)->first();

                $teleUser->session = "tag;$tag->contact_id;update_message";
                $teleUser->save();

                $message = "Enter new message";

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
                        ["text" => "Back", "callback_data" => "tag;$tag->contact_id;back"],
                    ],
                ];

                $response = $this->apiRequest('editMessageCaption', [
                    'chat_id' => $result->callback_query->message->chat->id,
                    'message_id' => $result->callback_query->message->message_id,
                    'caption' => $result->callback_query->message->caption,
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

    private function deleteTag($entityId, $result)
    {
        $telegramId = $result->callback_query->message->chat->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $response = false;

        if ($teleUser) {
            if (count($teleUser->tags)) {
                $tag = Tag::where('contact_id', $entityId)->first();
                // $tag->delete();

                // $message = "Tag Deleted!\n/create - create tags";

                // $response = $this->apiRequest('sendMessage', [
                //     'chat_id' => $telegramId,
                //     'text' => $message,
                // ]);

                $teleUser->session = "tag;$tag->contact_id;delete";
                $teleUser->save();

                $message = "Are you sure to delete this tag?";

                $option = [
                    [
                        ["text" => "Yes", "callback_data" => "tag;$tag->contact_id;confirm_delete"],
                        ["text" => "No", "callback_data" => "tag;$tag->contact_id;cancel_delete"],
                    ],
                ];

                $response = $this->apiRequest('editMessageCaption', [
                    'chat_id' => $result->callback_query->message->chat->id,
                    'message_id' => $result->callback_query->message->message_id,
                    'caption' => $message,
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

                $teleUser->session = null;
                $teleUser->save();

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

    private function cancelDeleteTag($entityId, $result)
    {
        $telegramId = $result->callback_query->message->chat->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $response = false;

        if ($teleUser) {
            if (count($teleUser->tags)) {
                $teleUser->session = null;
                $teleUser->save();

                $message = "Cancel delete!";

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
