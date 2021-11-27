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

        if ($teleUser && $teleUser->session) {
            $response = $this->updateSession($result);
        } else if ($action == '/start') {
            $response = $this->startBot($result);
        } else if ($action == '/create') {
            $response = $this->createTag($result);
        } else if ($action == '/tags') {
            $response = $this->getTags($result);
        } else if ($action == '/cancel') {
            $response = $this->cancelOperation($result);
        } else {
            $response = $this->getCommands($result, null);
        }
        
        return $response;
    }

    public function updateCallbackQuery($result)
    {
        $data = $result->callback_query->data;
        $data = explode(";", $data);
        $response = $data;

        $telegramId = $result->callback_query->from->id;

        if (count($data) == 3) {
            $entityType = $data[0];
            $entityId = $data[1];
            $entityAttribute = $data[2];
    
            if ($entityType == 'tag' && $entityAttribute == 'get') $response = $this->getTag($entityId, $telegramId, $result);
            if ($entityType == 'tag' && $entityAttribute == 'edit') $response = $this->editTag($entityId, $result);
            if ($entityType == 'tag' && $entityAttribute == 'delete') $response = $this->deleteTag($entityId, $result);
            if ($entityType == 'tag' && $entityAttribute == 'toggle') $response = $this->toggleTag($entityId, $result);
            if ($entityType == 'tag' && $entityAttribute == 'qr_code') $response = $this->getQrCode($entityId, $result);
            if ($entityType == 'tag' && $entityAttribute == 'tag_list') $response = $this->getTags($result);

            if ($entityType == 'tag' && $entityAttribute == 'edit_name') $response = $this->editName($entityId, $result);
            if ($entityType == 'tag' && $entityAttribute == 'edit_num') $response = $this->editNum($entityId, $result);
            if ($entityType == 'tag' && $entityAttribute == 'edit_header') $response = $this->editHeader($entityId, $result);

            if ($entityType == 'tag' && $entityAttribute == 'edit_description') $response = $this->editDescription($entityId, $result);
            if ($entityType == 'tag' && $entityAttribute == 'edit_message') $response = $this->editMessage($entityId, $result);

            if ($entityType == 'tag' && $entityAttribute == 'confirm_delete') $response = $this->confirmDeleteTag($entityId, $result);
            if ($entityType == 'tag' && $entityAttribute == 'cancel_delete') $response = $this->cancelDeleteTag($entityId, $result);
        }

        return $response;
    }
}
