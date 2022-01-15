<?php
namespace Florian\NewStar\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class,'id','message_owner');
    }
}