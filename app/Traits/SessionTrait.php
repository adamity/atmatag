<?php

namespace App\Traits;

use App\Models\Tag;
use App\Models\TelegramUser;
use App\Traits\CommandTrait;
use App\Traits\ComponentTrait;
use App\Traits\RequestTrait;
use App\Traits\TagTrait;
use App\Traits\ValidationTrait;

trait SessionTrait
{
    use RequestTrait;
    use ComponentTrait;
    use TagTrait;
    use ValidationTrait;
    use CommandTrait;

    // Checked
    private function setSession($teleUser, $tagId, $action)
    {
        $teleUser->session = "$tagId;$action";
        $teleUser->save();
    }

    // Checked
    private function clearSession($teleUser)
    {
        $teleUser->session = null;
        $teleUser->save();
    }

    // Checked, except for the cancelOperation() function call
    private function updateSession($request)
    {
        $action = $request->message->text;
        $teleUser = TelegramUser::where('telegram_id', $request->message->from->id)->first();
        $data = explode(";", $teleUser->session);

        if ($action == "/cancel" || $action == '❌ Cancel') {
            $response = $this->cancelOperation($request);
        } else switch ($data[1]) {
            case 'name':
                $response = $this->setName($request);
                break;
            case 'update_name':
                $response = $this->updateName($request);
                break;
            case 'update_num':
                $response = $this->updateNum($request);
                break;
            case 'update_header':
                $response = $this->updateHeader($request);
                break;
            case 'update_description':
                $response = $this->updateDescription($request);
                break;
            case 'update_message':
                $response = $this->updateMessage($request);
                break;
            default:
                $response = $this->cancelOperation($teleUser);
                break;
        }

        return $response;
    }
}
