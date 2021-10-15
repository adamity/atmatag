<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;
    protected $table = 'tags';

    public function telegramUser()
    {
        return $this->belongsTo(TelegramUser::class);
    }
}
