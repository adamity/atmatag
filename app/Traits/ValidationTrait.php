<?php

namespace App\Traits;

trait ValidationTrait
{
    private function validatePhoneNumber($phoneNumber)
    {
        $phoneNumber = str_replace('+', '', $phoneNumber);
        $phoneNumber = str_replace('-', '', $phoneNumber);
        $phoneNumber = str_replace(' ', '', $phoneNumber);
        $phoneNumber = str_replace('(', '', $phoneNumber);
        $phoneNumber = str_replace(')', '', $phoneNumber);

        if (preg_match('/[\p{L}]/u', $phoneNumber)) {
            return false;
        } else if (strlen($phoneNumber) <= 15) {
            return $phoneNumber;
        } else {
            return false;
        }
    }

    private function validateText($text, $length)
    {
        if (preg_match('/[\p{L}]/u', $text) && strlen($text) <= $length && substr($text, 0, 1) !== '/') {
            return $text;
        } else {
            return false;
        }
    }
}
