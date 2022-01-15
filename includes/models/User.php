<?php
namespace Florian\NewStar\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public function messages()
    {
        return $this->hasMany(Message::class,'message_owner','id');
    }
}