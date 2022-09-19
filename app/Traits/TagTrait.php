<?php

namespace App\Traits;

use App\Models\Tag;
use App\Models\TelegramUser;
use App\Traits\ComponentTrait;
use App\Traits\RequestTrait;
use App\Traits\TextTrait;
use Illuminate\Support\Str;

trait TagTrait
{
    use RequestTrait;
    use ComponentTrait;
    use TextTrait;

    private function getTag($entityId, $request)
    {
        $method = "sendMessage";

        if (isset($request->message)) {
            $telegramId = $request->message->from->id;
        } else if (isset($request->callback_query)) {
            $telegramId = $request->callback_query->from->id;
            $params['message_id'] = $request->callback_query->message->message_id;
            $method = "editMessageText";
        }

        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $params['chat_id'] = $telegramId;

        if (!$teleUser) {
            return $this->startBot($request);
        } else if (count($teleUser->tags)) {
            $tag = Tag::where('contact_id', $entityId)->first();
            $tagURL = preg_replace("/^http:/i", "https:", url(route('tag', ['contact_id' => $tag->contact_id])));

            $option = [
                [
                    ["text" => "Get QR Code", "callback_data" => "$tag->contact_id;qr_code"],
                    ["text" => "Edit Tag", "callback_data" => "$tag->contact_id;edit"],
                ],
                [
                    ["text" => "Delete Tag", "callback_data" => "$tag->contact_id;delete"],
                    ["text" => "Test Tag", "url" => $tagURL],
                ],
                [
                    ["text" => "Back", "callback_data" => "$tag->contact_id;tag_list"],
                ],
            ];

            $params['text'] = $this->tagDetailText($tag);
            $params['parse_mode'] = 'html';
            $params['reply_markup'] = $this->inlineKeyboardButton($option);
        } else {
            $params['text'] = $this->tagNotFoundText();
        }

        return $this->apiRequest($method, $params);
    }

    private function editTag($entityId, $request)
    {
        $method = "sendMessage";

        if (isset($request->message)) {
            $telegramId = $request->message->from->id;
        } else if (isset($request->callback_query)) {
            $telegramId = $request->callback_query->from->id;
            $params['message_id'] = $request->callback_query->message->message_id;
            $method = "editMessageText";
        }

        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $params['chat_id'] = $telegramId;

        if (!$teleUser) {
            return $this->startBot($request);
        } else if (count($teleUser->tags)) {
            $tag = Tag::where('contact_id', $entityId)->first();
            $toggling = $tag->toggle ? "Disable Tag" : "Enable Tag";

            $option = [
                [
                    ["text" => "Name", "callback_data" => "$tag->contact_id;edit_name"],
                    ["text" => "Contact Number", "callback_data" => "$tag->contact_id;edit_num"],
                ],
                [
                    ["text" => "Header", "callback_data" => "$tag->contact_id;edit_header"],
                    ["text" => "Description", "callback_data" => "$tag->contact_id;edit_description"],
                ],
                [
                    ["text" => "Message", "callback_data" => "$tag->contact_id;edit_message"],
                    ["text" => $toggling, "callback_data" => "$tag->contact_id;toggle"],
                ],
                [
                    ["text" => "Back", "callback_data" => "$tag->contact_id;get"],
                ],
            ];

            $params['text'] = $this->tagDetailText($tag);
            $params['parse_mode'] = 'html';
            $params['reply_markup'] = $this->inlineKeyboardButton($option);
        } else {
            $params['text'] = $this->tagNotFoundText();
        }

        return $this->apiRequest($method, $params);
    }

    private function editName($entityId, $request)
    {
        $telegramId = $request->callback_query->message->chat->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();

        $params['chat_id'] = $telegramId;
        $method = "sendMessage";

        if (!$teleUser) {
            return $this->startBot($request);
        } else if (count($teleUser->tags)) {
            $tag = Tag::where('contact_id', $entityId)->first();
            $this->setSession($teleUser, $tag->contact_id, "update_name");

            $option = [
                [
                    ["text" => "❌ Cancel"],
                ],
            ];

            $params['text'] = "Enter new name";
            $params['reply_markup'] = $this->keyboardButton($option);
        } else {
            $params['text'] = $this->tagNotFoundText();
        }

        return $this->apiRequest($method, $params);
    }

    private function editNum($entityId, $request)
    {
        $telegramId = $request->callback_query->message->chat->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();

        $params['chat_id'] = $telegramId;
        $method = "sendMessage";

        if (!$teleUser) {
            return $this->startBot($request);
        } else if (count($teleUser->tags)) {
            $tag = Tag::where('contact_id', $entityId)->first();
            $this->setSession($teleUser, $tag->contact_id, "update_num");

            $option = [
                [
                    ["text" => "❌ Cancel"],
                    ["text" => "↪️ Unset Number"],
                ],
            ];

            $params['text'] = "Enter new Contact Number\n/unset - unset contact number";
            $params['reply_markup'] = $this->keyboardButton($option);
        } else {
            $params['text'] = $this->tagNotFoundText();
        }

        return $this->apiRequest($method, $params);
    }

    private function editHeader($entityId, $request)
    {
        $telegramId = $request->callback_query->message->chat->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();

        $params['chat_id'] = $telegramId;
        $method = "sendMessage";

        if (!$teleUser) {
            return $this->startBot($request);
        } else if (count($teleUser->tags)) {
            $tag = Tag::where('contact_id', $entityId)->first();
            $this->setSession($teleUser, $tag->contact_id, "update_header");

            $option = [
                [
                    ["text" => "❌ Cancel"],
                    ["text" => "↪️ Use Default"],
                ],
            ];

            $params['text'] = "Enter new header";
            $params['reply_markup'] = $this->keyboardButton($option);
        } else {
            $params['text'] = $this->tagNotFoundText();
        }

        return $this->apiRequest($method, $params);
    }

    private function editDescription($entityId, $request)
    {
        $telegramId = $request->callback_query->message->chat->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();

        $params['chat_id'] = $telegramId;
        $method = "sendMessage";

        if (!$teleUser) {
            return $this->startBot($request);
        } else if (count($teleUser->tags)) {
            $tag = Tag::where('contact_id', $entityId)->first();
            $this->setSession($teleUser, $tag->contact_id, "update_description");

            $option = [
                [
                    ["text" => "❌ Cancel"],
                    ["text" => "↪️ Use Default"],
                ],
            ];

            $params['text'] = "Enter new description";
            $params['reply_markup'] = $this->keyboardButton($option);
        } else {
            $params['text'] = $this->tagNotFoundText();
        }

        return $this->apiRequest($method, $params);
    }

    private function editMessage($entityId, $request)
    {
        $telegramId = $request->callback_query->message->chat->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();

        $params['chat_id'] = $telegramId;
        $method = "sendMessage";

        if (!$teleUser) {
            return $this->startBot($request);
        } else if (count($teleUser->tags)) {
            $tag = Tag::where('contact_id', $entityId)->first();
            $this->setSession($teleUser, $tag->contact_id, "update_message");

            $option = [
                [
                    ["text" => "❌ Cancel"],
                    ["text" => "↪️ Use Default"],
                ],
            ];

            $params['text'] = "Enter new message";
            $params['reply_markup'] = $this->keyboardButton($option);
        } else {
            $params['text'] = $this->tagNotFoundText();
        }

        return $this->apiRequest($method, $params);
    }

    private function toggleTag($entityId, $request)
    {
        $telegramId = $request->callback_query->message->chat->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();

        $params['chat_id'] = $telegramId;
        $method = "sendMessage";

        if (!$teleUser) {
            return $this->startBot($request);
        } else if (count($teleUser->tags)) {
            $tag = Tag::where('contact_id', $entityId)->first();
            $tag->toggle = !$tag->toggle;
            $tag->save();

            return $this->editTag($entityId, $request);
        } else {
            $params['text'] = $this->tagNotFoundText();
        }

        return $this->apiRequest($method, $params);
    }

    private function deleteTag($entityId, $request)
    {
        $method = "sendMessage";

        if (isset($request->message)) {
            $telegramId = $request->message->from->id;
        } else if (isset($request->callback_query)) {
            $telegramId = $request->callback_query->from->id;
            $params['message_id'] = $request->callback_query->message->message_id;
            $method = "editMessageText";
        }

        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $params['chat_id'] = $telegramId;

        if (!$teleUser) {
            return $this->startBot($request);
        } else if (count($teleUser->tags)) {
            $tag = Tag::where('contact_id', $entityId)->first();
            $this->setSession($teleUser, $tag->contact_id, "delete");

            $option = [
                [
                    ["text" => "Yes", "callback_data" => "$tag->contact_id;confirm_delete"],
                    ["text" => "No", "callback_data" => "$tag->contact_id;cancel_delete"],
                ],
            ];

            $params['text'] = "Are you sure to delete this tag?";
            $params['parse_mode'] = 'html';
            $params['reply_markup'] = $this->inlineKeyboardButton($option);
        } else {
            $params['text'] = $this->tagNotFoundText();
        }

        return $this->apiRequest($method, $params);
    }

    private function confirmDeleteTag($entityId, $request)
    {
        $method = "sendMessage";

        if (isset($request->message)) {
            $telegramId = $request->message->from->id;
        } else if (isset($request->callback_query)) {
            $telegramId = $request->callback_query->from->id;
            $params['message_id'] = $request->callback_query->message->message_id;
            $method = "editMessageText";
        }

        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $params['chat_id'] = $telegramId;

        if (!$teleUser) {
            return $this->startBot($request);
        } else if (count($teleUser->tags)) {
            $tag = Tag::where('contact_id', $entityId)->first();
            $tag->delete();
            $this->clearSession($teleUser);

            $params['text'] = "Tag Deleted!";
            $params['parse_mode'] = 'html';
        } else {
            $params['text'] = $this->tagNotFoundText();
        }

        return $this->apiRequest($method, $params);
    }

    private function cancelDeleteTag($entityId, $request)
    {
        $method = "sendMessage";

        if (isset($request->message)) {
            $telegramId = $request->message->from->id;
        } else if (isset($request->callback_query)) {
            $telegramId = $request->callback_query->from->id;
            $params['message_id'] = $request->callback_query->message->message_id;
            $method = "editMessageText";
        }

        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $params['chat_id'] = $telegramId;

        if (!$teleUser) {
            return $this->startBot($request);
        } else if (count($teleUser->tags)) {
            $this->clearSession($teleUser);
            return $this->getTag($entityId, $request);
        } else {
            $params['text'] = $this->tagNotFoundText();
        }

        return $this->apiRequest($method, $params);
    }

    private function getQrCode($entityId, $request)
    {
        $telegramId = $request->callback_query->message->chat->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();

        $params['chat_id'] = $telegramId;
        $method = "sendMessage";

        if (!$teleUser) {
            return $this->startBot($request);
        } else if (count($teleUser->tags)) {
            $tag = Tag::where('contact_id', $entityId)->first();
            $tagURL = preg_replace("/^http:/i", "https:", url(route('tag', ['contact_id' => $tag->contact_id])));

            $method = "sendPhoto";
            $params['photo'] = $this->generateQrCode($tagURL);
        } else {
            $params['text'] = $this->tagNotFoundText();
        }

        return $this->apiRequest($method, $params);
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
            $contact_id = substr($uuid, 0, 8);

            $tag = Tag::where('contact_id', $contact_id)->first();
            if (!$tag) $success = true;

            $count++;
        } while (!$success || $count < 10);

        return $contact_id;
    }

    private function setName($request)
    {
        $method = "sendMessage";
        $action = $request->message->text;
        $telegramId = $request->message->from->id;

        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $params['chat_id'] = $telegramId;

        $data = explode(";", $teleUser->session);
        $entityId = $data[0];

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
            $this->getTag($entityId, $request);

            $params['text'] = $this->tagCreatedText();
            $params['reply_markup'] = $this->keyboardButton($option);
        } else {
            $params['text'] = $this->nameNotValidText();
        }

        return $this->apiRequest($method, $params);
    }

    private function updateName($request)
    {
        $method = "sendMessage";
        $action = $request->message->text;
        $telegramId = $request->message->from->id;

        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $params['chat_id'] = $telegramId;

        $data = explode(";", $teleUser->session);
        $entityId = $data[0];

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
            $params['text'] = $this->nameUpdatedText();
            $params['reply_markup'] = $this->keyboardButton($option);
        } else {
            $params['text'] = $this->nameNotValidText();
        }

        return $this->apiRequest($method, $params);
    }

    private function updateNum($request)
    {
        $method = "sendMessage";
        $action = $request->message->text;
        $telegramId = $request->message->from->id;

        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $params['chat_id'] = $telegramId;

        $data = explode(";", $teleUser->session);
        $entityId = $data[0];

        $tag = Tag::where('contact_id', $entityId)->first();
        if ($action == "/unset" || $action == '↪️ Unset Number') {
            $tag->contact_number = null;
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
            $params['text'] = $this->unsetNumberText();
            $params['reply_markup'] = $this->keyboardButton($option);
        } else if ($this->validatePhoneNumber($action)) {
            $tag->contact_number = $action;
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
            $params['text'] = $this->numberUpdatedText();
            $params['reply_markup'] = $this->keyboardButton($option);
        } else {
            $params['text'] = $this->numberNotValidText();
        }

        return $this->apiRequest($method, $params);
    }

    private function updateHeader($request)
    {
        $method = "sendMessage";
        $action = $request->message->text;
        $telegramId = $request->message->from->id;

        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $params['chat_id'] = $telegramId;

        $data = explode(";", $teleUser->session);
        $entityId = $data[0];

        $tag = Tag::where('contact_id', $entityId)->first();
        if ($action == "↪️ Use Default") $action = $this->defaultHeaderText();

        if ($this->validateText($action, 20)) {
            $tag->header = $action;
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
            $params['text'] = $this->headerUpdatedText();
            $params['reply_markup'] = $this->keyboardButton($option);
        } else {
            $params['text'] = $this->headerNotValidText();
        }

        return $this->apiRequest($method, $params);
    }

    private function updateDescription($request)
    {
        $method = "sendMessage";
        $action = $request->message->text;
        $telegramId = $request->message->from->id;

        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $params['chat_id'] = $telegramId;

        $data = explode(";", $teleUser->session);
        $entityId = $data[0];

        $tag = Tag::where('contact_id', $entityId)->first();
        if ($action == "↪️ Use Default") $action = $this->defaultDescriptionText();

        if ($this->validateText($action, 50)) {
            $tag->description = $action;
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
            $params['text'] = $this->descriptionUpdatedText();
            $params['reply_markup'] = $this->keyboardButton($option);
        } else {
            $params['text'] = $this->descriptionNotValidText();
        }

        return $this->apiRequest($method, $params);
    }

    private function updateMessage($request)
    {
        $method = "sendMessage";
        $action = $request->message->text;
        $telegramId = $request->message->from->id;

        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $params['chat_id'] = $telegramId;

        $data = explode(";", $teleUser->session);
        $entityId = $data[0];

        $tag = Tag::where('contact_id', $entityId)->first();
        if ($action == "↪️ Use Default") $action = $this->defaultMessageText();

        if ($this->validateText($action, 255)) {
            $tag->message = $action;
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
            $params['text'] = $this->messageUpdatedText();
            $params['reply_markup'] = $this->keyboardButton($option);
        } else {
            $params['text'] = $this->messageNotValidText();
        }

        return $this->apiRequest($method, $params);
    }
}
