<?php

namespace App\Http\Controllers;

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
        $userId = $result->message->from->id;

        if ($action == '/start') {
            $text = 'Please Choose City That Can See Time';

            $options = [
                ['Tehran', 'Adelaide'],
                ['Istanbul', 'New York'],
            ];

            $this->apiRequest('sendMessage', [
                'chat_id' => $userId,
                'text' => $text,
                'reply_markup' => $this->keyboardButton($options),
            ]);
        } else {
            $this->apiRequest('sendMessage', [
                'chat_id' => $userId,
                'text' => "You Send " . $action,
            ]);
        }
    }
}
