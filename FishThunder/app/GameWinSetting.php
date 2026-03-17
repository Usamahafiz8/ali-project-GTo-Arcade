<?php

namespace VanguardLTE;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameWinSetting extends Model
{
    use HasFactory;
    protected $fillable = [
        'bsc_min',
        'bsc_max',
        'bsw_min',
        'bsw_max',
        'bbc_min',
        'bbc_max',
        'bbw_min',
        'bbw_max',
        'fc_min',
        'fc_max',
        'fw_min',
        'fw_max',
        'fw_bc_min',
        'fw_bc_max',
        'fw_bw_min',
        'fw_bw_max',
        'gamename',
        'gameid',
        'wavecnt',
        'wavesize'
    ];
}
