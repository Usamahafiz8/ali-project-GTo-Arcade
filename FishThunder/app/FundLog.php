<?php

namespace VanguardLTE;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FundLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'amount',
        'type' //0: deposit, 1: withdraw
    ];
}
