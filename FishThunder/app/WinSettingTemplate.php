<?php

namespace VanguardLTE;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WinSettingTemplate extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'info'
    ];
}
