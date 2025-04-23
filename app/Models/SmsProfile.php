<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsProfile extends Model
{
    use HasFactory;

    protected $table = 'sms_profile';
    public $timestamps = false;
}
