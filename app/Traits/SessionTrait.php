<?php

namespace App\Traits;

use App\Models\Tag;
use App\Traits\MakeComponents;
use App\Traits\RequestTrait;
use App\Traits\TagTrait;
use App\Traits\ValidationTrait;
use App\Traits\CommandTrait;
use App\Models\TelegramUser;

trait SessionTrait
{
    use RequestTrait;
    use MakeComponents;
    use TagTrait;
    use ValidationTrait;
    use CommandTrait;

    private function setSession($user, $entityType, $entityId, $entityAttribute)
    {
        $user->session = "$entityType;$entityId;$entityAttribute";
        $user->save();
    }

    private function clearSession($user)
    {
        $user->session = null;
        $user->save();
    }

    private function updateSession($result)
    {
        $action = $result->message->text;
        $telegramId = $result->message->from->id;

        $teleUser = TelegramUser::where('telegram_id', $telegramId)->first();
        $data = explode(";", $teleUser->session);

        $response = false;

        $entityType = $data[0];
        $entityAttribute = $data[2];

        if ($action == "/cancel" || $action == '❌ Cancel') {
            $response = $this->cancelOperation($result);
        } else if ($entityType == 'tag') {
            if ($entityAttribute == 'name') $response = $this->setName($result);
            else if ($entityAttribute == 'update_name') $response = $this->updateName($result);
            else if ($entityAttribute == 'update_num') $response = $this->updateNum($result);
            else if ($entityAttribute == 'update_header') $response = $this->updateHeader($result);
            else if ($entityAttribute == 'update_description') $response = $this->updateDescription($result);
            else if ($entityAttribute == 'update_message') $response = $this->updateMessage($result);
            else $response = $this->cancelOperation($teleUser);
        }

        return $response;
    }
}
