<?php 
namespace VanguardLTE
{

    use Carbon\Carbon;
    use DateTime;

    class Transaction extends \Illuminate\Database\Eloquent\Model
    {
        protected $table = 'transactions';
        protected $fillable = [
            'user_id', 
            'payeer_id', 
            'system', 
            'value', 
            'type', 
            'summ', 
            'status', 
            'shop_id'
        ];
        public static function boot()
        {
            parent::boot();
        }
        public function admin()
        {
            return $this->hasOne('VanguardLTE\User', 'id', 'payeer_id');
        }
        public function user()
        {
            return $this->hasOne('VanguardLTE\User', 'id', 'user_id');
        }
        public function shop()
        {
            return $this->belongsTo('VanguardLTE\Shop');
        }

        public function getCreatedAtAttribute($value)
        {
            // $user = auth()->user();
            // $serverTimezone = (new DateTime())->getTimezone();
            // $userTimezone = \VanguardLTE\TimeZone::find($user->timezone);            
            // $userDatetime = Carbon::parse($value, $serverTimezone)->timezone($userTimezone->value);
            // return $userDatetime->format('Y-m-d H:i:s');
            return Carbon::parse($value)->format('m-d-Y H:i:s');
        }
    }

}
