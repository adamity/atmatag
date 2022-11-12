<?php

namespace App\Traits;

trait TextTrait
{
    private function tagNotFoundText()
    {
        $text = "No Tag Found!\n/create - create tag";
        return $text;
    }

    private function tagDetailText($tag)
    {
        $text = "<b>Name :</b> $tag->name";
        $text .= "\n<b>Contact Number :</b> $tag->contact_number_view";
        $text .= "\n<b>Header :</b> $tag->header";
        $text .= "\n<b>Description :</b> $tag->description";
        $text .= "\n<b>Message :</b> $tag->message";
        $text .= "\n<b>Availability :</b> $tag->toggle_view";
        return $text;
    }

    private function tagCreatedText()
    {
        $text = "Tag Created!";
        return $text;
    }

    private function nameNotValidText()
    {
        $text = "Name is not valid!";
        return $text;
    }

    private function nameUpdatedText()
    {
        $text = "Name Updated!";
        return $text;
    }

    private function unsetNumberText()
    {
        $text = "Contact Number deleted";
        return $text;
    }

    private function numberUpdatedText()
    {
        $text = "Contact Number updated";
        return $text;
    }

    private function numberNotValidText()
    {
        $text = "Contact Number is not valid!";
        return $text;
    }

    private function defaultHeaderText()
    {
        $text = "Contact Me";
        return $text;
    }

    private function headerUpdatedText()
    {
        $text = "Header updated";
        return $text;
    }

    private function headerNotValidText()
    {
        $text = "Invalid header!";
        return $text;
    }

    private function defaultDescriptionText()
    {
        $text = "Send me a message in case of emergency";
        return $text;
    }

    private function descriptionUpdatedText()
    {
        $text = "Description updated";
        return $text;
    }

    private function descriptionNotValidText()
    {
        $text = "Invalid description!";
        return $text;
    }

    private function defaultMessageText()
    {
        $text = "Hye there! Send me a message in case of emergency";
        return $text;
    }

    private function messageUpdatedText()
    {
        $text = "Message updated";
        return $text;
    }

    private function messageNotValidText()
    {
        $text = "Invalid message!";
        return $text;
    }

    private function commandText($text = null)
    {
        $message = "<b>Commands :</b> \n\n/create - create tag\n/tags - get tags";
        if ($text) $message = $text . "\n\n" . $message;
        return $message;
    }

    private function createTagText()
    {
        $text = "What should we call this tag?";
        return $text;
    }
}
