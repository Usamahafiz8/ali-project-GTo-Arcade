@extends('frontend.Default.layouts.app')

@section('page-title', trans('app.login'))
@section('add-main-class', 'main-login')

@section('content')   
    <div id="root" type='login' token='{{ csrf_token() }}'></div>      
    <script src="{{asset('js/index.js')}}" defer></script>
    @if(isset ($errors) && count($errors) > 0)
    
        <div class="notification_bonus popup" style="display: block; z-index:1000">    
            <button class="notification__close"></button>            
            <p class="popup_message">
                @foreach($errors->all() as $error)
                    {!!  $error  !!}<br>
                @endforeach
            </p>
        </div>
        <script>
            $(function(){
                var width = $('.notification_bonus').width();
                var height = width * 659 / 1463;
                $('.notification_bonus').css('height', height + 'px');

                width = width * 0.08;
                $('.notification__close').css('height', width + 'px');
            })

            $('.notification__close').on('click', () => {
                $('.popup').css('display', 'none');
            })    
        </script>
    @endif

@stop

@section('scripts')
    <link rel="stylesheet" type="text/css" href="/frontend/Default/css/login.css" />
    <link rel="stylesheet" type="text/css" href="/frontend/Default/css/notification.css" />
    <script type="text/javascript">
        $('.notification__close').on('click', () => {
            $('.notification').css('display', 'none');
        })
        
    </script>
  {!! JsValidator::formRequest('VanguardLTE\Http\Requests\Auth\LoginRequest', '#login-form') !!}
@stop
