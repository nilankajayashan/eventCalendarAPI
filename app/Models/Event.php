<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Event extends Model
{
    use HasApiTokens,HasFactory;
    protected $primaryKey = ['user_id','event_id'];
    public $incrementing = false;

}
