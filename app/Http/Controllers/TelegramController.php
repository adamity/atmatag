<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\TelegramUser;
use App\Traits\CommandTrait;
use App\Traits\ComponentTrait;
use App\Traits\RequestTrait;
use App\Traits\SessionTrait;
use App\Traits\TagTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class TelegramController extends Controller
{
    use CommandTrait;
    use ComponentTrait;
    use RequestTrait;
    use SessionTrait;
    use TagTrait;

    // Checked
    public function webhook()
    {
        $url = preg_replace("/^http:/i", "https:", url(route('webhook')));

        return $this->apiRequest('setWebhook', [
            'url' => $url,
        ]) ? ['success'] : ['something wrong'];
    }

    // Checked
    public function index()
    {
        $request = json_decode(file_get_contents('php://input'));
        $response = "Not the expected update type.";
        // More about update types: https://core.telegram.org/bots/api#update

        if (isset($request->message)) {
            $response = $this->updateMessage($request);
        } else if (isset($request->callback_query)) {
            $response = $this->updateCallbackQuery($request);
        } else if (isset($request->my_chat_member)) {
            $response = $this->updateMyChatMember($request);
        }

        return $response;
    }

    // Checked, except for the function call inside the condition
    public function updateMessage($request)
    {
        $action = $request->message->text;
        $teleUser = TelegramUser::where('telegram_id', $request->message->from->id)->first();

        if ($teleUser && $teleUser->session) {
            $response = $this->updateSession($request);
        } else switch ($action) {
            case '/start':
                $response = $this->startBot($request);
                break;
            case '/help':
                $response = $this->getHelp($request);
                break;
            case '/create':
            case '🏷️ Create Tag':
                $response = $this->createTag($request);
                break;
            case '/tags':
            case '📦 Get Tags':
                $response = $this->getTags($request);
                break;
            case '/cancel':
            case '❌ Cancel':
                $response = $this->cancelOperation($request);
                break;
            default:
                $response = $this->getCommands($request, null);
                break;
        }

        return $response;
    }

    // Checked, except for the function call inside the condition
    public function updateCallbackQuery($request)
    {
        $data = explode(";", $request->callback_query->data);
        $response = "Not the expected callback query data.";

        if (count($data) == 2) {
            $tagId = $data[0];
            $action = $data[1];

            switch ($action) {
                case 'get':
                    $response = $this->getTag($tagId, $request);
                    break;
                case 'edit':
                    $response = $this->editTag($tagId, $request);
                    break;
                case 'delete':
                    $response = $this->deleteTag($tagId, $request);
                    break;
                case 'toggle':
                    $response = $this->toggleTag($tagId, $request);
                    break;
                case 'qr_code':
                    $response = $this->getQrCode($tagId, $request);
                    break;
                case 'tag_list':
                    $response = $this->getTags($request);
                    break;
                case 'edit_name':
                    $response = $this->editName($tagId, $request);
                    break;
                case 'edit_num':
                    $response = $this->editNum($tagId, $request);
                    break;
                case 'edit_header':
                    $response = $this->editHeader($tagId, $request);
                    break;
                case 'edit_description':
                    $response = $this->editDescription($tagId, $request);
                    break;
                case 'edit_message':
                    $response = $this->editMessage($tagId, $request);
                    break;
                case 'confirm_delete':
                    $response = $this->confirmDeleteTag($tagId, $request);
                    break;
                case 'cancel_delete':
                    $response = $this->cancelDeleteTag($tagId, $request);
                    break;
            }
        }

        return $response;
    }

    // Keeped for deactivation feature
    public function updateMyChatMember($request)
    {
        // Do nothing for now ...
        $response = "My Chat Member";
        return $response;
    }
}
