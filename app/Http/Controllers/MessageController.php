<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Traits\RequestTrait;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    use RequestTrait;

    public function index($contact_id)
    {
        $tag = Tag::where('contact_id', $contact_id)->first();

        if ($tag) {
            $contact_number = $tag->contact_number;
            $message = $tag->message;

            if ($tag->toggle) {
                return view('message.index', compact('contact_id', 'contact_number', 'message'));
            } else {
                echo 'Tag Disabled!';
            }
        } else {
            echo 'Tag Not Exist!';
        }
    }

    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required',
            'contact_id' => 'required',
        ]);

        $contact_id = $request->input('contact_id');
        $message = $request->input('message');

        $success = false;
        $tag = Tag::where('contact_id', $contact_id)->first();

        if ($tag && $tag->toggle) {
            $teleUser = $tag->telegramUser;
            $message = "<b>No Reply</b>\n<i>Message From Tag : $tag->name</i>\n\n$message";

            $success =  $this->apiRequest('sendMessage', [
                'chat_id' => $teleUser->telegram_id,
                'text' => $message,
                'parse_mode' => 'html',
            ]);
        }

        $response = ['success' => $success];
        return $response;
    }
}
