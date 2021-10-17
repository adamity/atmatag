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
        $message = "Not The Expected Update Type";

        // TODO : Check Response Type (message, callback_query, my_chat_member, etc)
        if (isset($result->message)) return $this->updateMessage($result);
        if (isset($result->callback_query)) return $this->updateCallbackQuery($result);
        if (isset($result->my_chat_member)) $message = "My Chat Member";

        return $message;
    }

    public function updateMessage($result)
    {
        $action = $result->message->text;

        $telegramId = $result->message->from->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();

        if ($teleUser && $teleUser->session) {
            $session = $teleUser->session;
            $this->updateSession($session, $result);
            exit;
        }

        if ($action == '/start') return $this->startBot($result);
        if ($action == '/create') return $this->createTag($result);
        if ($action == '/tags') return $this->getTags($result);
        if ($action == '/delete') return $this->deleteTag($result);

        return $this->getCommands($result);
    }

    public function updateCallbackQuery($result)
    {
        $data = $result->callback_query->data;
        $data = explode(";", $data);

        $telegramId = $result->callback_query->message->chat->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();

        $entityType = $data[0];
        $entityId = $data[1];
        $entityAttribute = $data[2];

        if ($entityType == 'tag' && $entityAttribute == 'get') $this->getTag($entityId, $result);
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
            $teleUser->tag_limit = 2;
            $teleUser->save();

            $message = "Hye There!\n\n/create - create tag\n/tags - get tags\n/delete - delete tag";
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
            if (count($teleUser->tags) < $teleUser->tag_limit) {
                $contact_id = $this->generateContactId();

                $tag = new Tag();
                $tag->telegram_user_id = $teleUser->id;
                $tag->contact_id = $contact_id;
                $tag->name = "New Tag";
                $tag->header = "Contact Me";
                $tag->description = "Send me a message in case of emergency";
                $tag->message = "Hye there! Send me a message in case of emergency";
                $tag->toggle = 1;
                $tag->save();

                $teleUser->session = "tag;$tag->contact_id;name";
                $teleUser->save();

                $message = "What should we call this tag?\nEnter /cancel to cancel the operation.";

                $this->apiRequest('sendMessage', [
                    'chat_id' => $telegramId,
                    'text' => $message,
                ]);
            } else {
                $message = "Limit exceed!\n/tags - get tags";

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

                return $this->apiRequest('sendMessage', [
                    'chat_id' => $telegramId,
                    'text' => $message,
                    'reply_markup' => $this->inlineKeyboardButton($option),
                ]);
            } else {
                $message = "No Tag Found!\n/create - create tag";

                return $this->apiRequest('sendMessage', [
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

    public function getTag($entityId, $result)
    {
        $telegramId = $result->callback_query->message->chat->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();

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

                $this->apiRequest('sendPhoto', [
                    'chat_id' => $result->callback_query->message->chat->id,
                    'photo' => $this->generateQrCode($tagURL),
                    'caption' => $message,
                    'parse_mode' => 'html',
                ]);
            } else {
                $message = "No Tag Found!\n/create - create tag";

                return $this->apiRequest('sendMessage', [
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

    public function updateSession($session, $result)
    {
        $action = $result->message->text;
        $telegramId = $result->message->from->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $data = explode(";", $session);

        $entityType = $data[0];
        $entityId = $data[1];
        $entityAttribute = $data[2];

        if ($entityType == 'tag') {
            $tag = Tag::where('contact_id', $entityId)->first();

            if ($entityAttribute == 'name') {
                $tag->name = $action;
                $tag->save();

                $teleUser->session = null;
                $teleUser->save();

                $message = "Tag Created!\n/tags - get tags";

                return $this->apiRequest('sendMessage', [
                    'chat_id' => $telegramId,
                    'text' => $message,
                ]);
            }
        }
    }

    public function generateQrCode($tagURL)
    {
        $url = "https://image-charts.com/chart?chs=900x900&cht=qr&chl=$tagURL&choe=UTF-8&chof=.png";
        return $url;
    }
}
