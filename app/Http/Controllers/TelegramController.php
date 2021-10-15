<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\TelegramUser;
use App\Traits\MakeComponents;
use App\Traits\RequestTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class TelegramController extends Controller
{
    use RequestTrait;
    use MakeComponents;

    public function webhook()
    {
        $url = preg_replace("/^http:/i", "https:", url(route('webhook')));

        return $this->apiRequest('setWebhook', [
            'url' => $url,
        ]) ? ['success'] : ['something wrong'];
    }

    public function index()
    {
        $result = json_decode(file_get_contents('php://input'));
        $action = $result->message->text;

        if ($action == '/start') return $this->startBot($result);
        if ($action == '/create') return $this->createTag($result);
        if ($action == '/tags') return $this->getTags($result);
        if ($action == '/delete') return $this->deleteTag($result);

        return $this->getCommands($result);
    }

    public function getCommands($result)
    {
        $telegramId = $result->message->from->id;
        $message = "What Can I Help?\n\n/create - create tag\n/tags - get tags\n/delete - delete tag";

        $this->apiRequest('sendMessage', [
            'chat_id' => $telegramId,
            'text' => $message,
        ]);
    }

    public function startBot($result)
    {
        $telegramId = $result->message->from->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();

        $message = "Welcome Back!\n\n/create - create tag\n/tags - get tags\n/delete - delete tag";

        if (!$teleUser) {
            $teleUser = new TelegramUser();
            $teleUser->telegram_id = $telegramId;
            $teleUser->save();

            $message = "Hye There!\n/start - start the bot";
        }

        $this->apiRequest('sendMessage', [
            'chat_id' => $telegramId,
            'text' => $message,
        ]);
    }

    public function createTag($result)
    {
        $telegramId = $result->message->from->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();

        if ($teleUser) {
            if (!count($teleUser->tags)) {
                $contact_id = $this->generateContactId();

                $tag = new Tag();
                $tag->telegram_user_id = $teleUser->id;
                $tag->contact_id = $contact_id;
                $tag->name = "My Tag";
                $tag->header = "Contact Me";
                $tag->description = "Send me a message in case of emergency";
                $tag->message = "Hye there! Sorry for double parked, please send me a message or call me.";
                $tag->status = 1;
                $tag->save();

                $message = "Tag Created!\n/tags - get tags";

                $this->apiRequest('sendMessage', [
                    'chat_id' => $telegramId,
                    'text' => $message,
                ]);
            } else {
                $message = "For now we only support 1 tag per user!\n/tags - get tags";

                $this->apiRequest('sendMessage', [
                    'chat_id' => $telegramId,
                    'text' => $message,
                ]);
            }
        } else {
            $message = "Hye There!\n/start - start the bot";

            $this->apiRequest('sendMessage', [
                'chat_id' => $telegramId,
                'text' => $message,
            ]);
        }
    }

    public function getTags($result)
    {
        $telegramId = $result->message->from->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();

        if ($teleUser) {
            if (count($teleUser->tags)) {
                $tag = $teleUser->tags->first();

                $tagURL = preg_replace("/^http:/i", "https:", url(route('tag', ['contact_id' => $tag->contact_id])));
                $message = "This is your tag URL:\n$tagURL\n\nWe recommend you not to share this URL on public, only for your testing purpose.\n\n/edit - edit tag\n/delete - delete tag";

                $this->apiRequest('sendPhoto', [
                    'chat_id' => $telegramId,
                    'photo' => $this->generateQrCode($tagURL),
                    'caption' => $message,
                ]);
            } else {
                $message = "No Tag Found!\n/create - create tag";

                $this->apiRequest('sendMessage', [
                    'chat_id' => $telegramId,
                    'text' => $message,
                ]);
            }
        } else {
            $message = "Hye There!\n/start - start the bot";

            $this->apiRequest('sendMessage', [
                'chat_id' => $telegramId,
                'text' => $message,
            ]);
        }
    }

    public function deleteTag($result)
    {
        $telegramId = $result->message->from->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();

        if ($teleUser) {
            if (count($teleUser->tags)) {
                $tag = $teleUser->tags->first();
                $tag->delete();

                $message = "Tag Deleted!\n/create - create tags";

                $this->apiRequest('sendMessage', [
                    'chat_id' => $telegramId,
                    'text' => $message,
                ]);
            } else {
                $message = "No Tag Found!\n/create - create tag";

                $this->apiRequest('sendMessage', [
                    'chat_id' => $telegramId,
                    'text' => $message,
                ]);
            }
        } else {
            $message = "Hye There!\n/start - start the bot";

            $this->apiRequest('sendMessage', [
                'chat_id' => $telegramId,
                'text' => $message,
            ]);
        }
    }

    public function editTag($result)
    {
        $telegramId = $result->message->from->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
    }

    public function generateQrCode($tagURL)
    {
        $url = "https://image-charts.com/chart?chs=900x900&cht=qr&chl=$tagURL&choe=UTF-8&chof=.png";
        return $url;
    }
}
