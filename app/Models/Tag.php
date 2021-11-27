<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;
    protected $table = 'tags';
    protected $appends = [ 'contact_number_view', 'toggle_view' ];

    public function telegramUser()
    {
        return $this->belongsTo(TelegramUser::class);
    }

    public function getContactNumberViewAttribute()
    {
        if ($this->contact_number) {
            $response = $this->contact_number;
        } else {
            $response = 'Not Set';
        }

        return $response;
    }

    public function getToggleViewAttribute()
    {
        if ($this->toggle) {
            $response = 'Enabled';
        } else {
            $response = 'Disabled';
        }

        return $response;
    }
}
