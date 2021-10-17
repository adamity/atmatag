<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\TelegramUser;
use App\Traits\CommandTrait;
use App\Traits\MakeComponents;
use App\Traits\RequestTrait;
use App\Traits\TagTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class TelegramController extends Controller
{
    use RequestTrait;
    use MakeComponents;
    use TagTrait;
    use CommandTrait;

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
        $response = "Not The Expected Update Type";

        // TODO : Check Response Type (message, callback_query, my_chat_member, etc)
        if (isset($result->message)) $response = $this->updateMessage($result);
        if (isset($result->callback_query)) $response = $this->updateCallbackQuery($result);
        if (isset($result->my_chat_member)) $response = "My Chat Member";

        return $response;
    }

    public function updateMessage($result)
    {
        $action = $result->message->text;

        $telegramId = $result->message->from->id;
        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();

        if ($teleUser && $teleUser->session) $response = $this->updateSession($result);

        if ($action == '/start') $response = $this->startBot($result);
        if ($action == '/create') $response = $this->createTag($result);
        if ($action == '/tags') $response = $this->getTags($result);
        if ($action == '/delete') $response = $this->deleteTag($result);

        if (!isset($response)) $response = $this->getCommands($result);
        return $response;
    }

    public function updateCallbackQuery($result)
    {
        $data = $result->callback_query->data;
        $data = explode(";", $data);
        $response = false;

        $entityType = $data[0];
        $entityId = $data[1];
        $entityAttribute = $data[2];

        if ($entityType == 'tag' && $entityAttribute == 'get') $response = $this->getTag($entityId, $result);

        return $response;
    }
}
