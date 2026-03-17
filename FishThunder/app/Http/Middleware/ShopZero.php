<?php 
namespace VanguardLTE\Http\Middleware
{
    class ShopZero
    {
        public function handle($request, \Closure $next)
        {
            if( auth()->check() ) 
            {
            }
            if( auth()->user()->shop_id == 0 ) 
            {
                if( auth()->user()->role_id == 6 && 
                    ($request->is('/progress*') 
                    || $request->is('/daily_entries*') 
                    || $request->is('/invite*') 
                    || $request->is('/wheelfortune*') 
                    || $request->is('/happyhours*') 
                    || $request->is('/refunds*') 
                    || $request->is('/category*') 
                    || $request->is('/banks*') 
                    || $request->is('/pincodes*') 
                    || $request->is('/jpgame*') 
                    || $request->is('/game*') 
                    || $request->is('/permission*') 
                    || $request->is('/welcome_bonuses*') 
                    || $request->is('/smsbonuses*') 
                    || $request->is('/settings*') 
                    || $request->is('/jp_edit') 
                    || $request->is('/jp/updatejp') 
                    || $request->is('/jp/regenerate')  
                    || $request->is('game_setting')) ) 
                {
                    return $next($request);
                }
                else
                {
                    abort(403);
                    return redirect()->route('backend.dashboard')->withErrors([trans('app.no_permission')]);
                }
            }
            return $next($request);
        }
    }

}
