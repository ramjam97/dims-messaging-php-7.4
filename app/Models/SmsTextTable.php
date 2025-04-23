<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsTextTable extends Model
{
    use HasFactory;
    protected $table = 'tb_text';
    protected $primaryKey = 'messageID';
    public $timestamps = false;
    protected $fillable = [
        'status',
        'successfullysent',
        'systemlog',
        'systemlog_time',
        'sms_provider'
    ];
}
