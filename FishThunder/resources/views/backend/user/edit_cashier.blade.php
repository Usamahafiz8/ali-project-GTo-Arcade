@extends('backend.layouts.app')

@section('page-title', trans('app.edit_user'))
@section('page-heading', $user->present()->username)

@section('content')
    <section class="content-header">
        @include('backend.partials.messages')
        <div id="main-content" class="for-print">
            <form class="form-horizontal" id="yw0" action="{{route('backend.user.cashier_update', $user->id)}}" method="post">
                @csrf
                <h3>Cashier Settings</h3>
                <div class="well">
                    <div class="control-group">
                        <label class="control-label">
                            <label for="Users_login">Login</label> </label>
                        <div class="controls">
                            <input class="input-xxlarge" autocomplete="off" readonly="readonly" name="Users[username]"
                                id="Users_login" type="text" maxlength="32" value="{{$user->username}}">
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">
                            <label for="Users_password">Password</label> </label>
                        <div class="controls">
                            <input class="input-xxlarge" name="Users[password]" id="Users_password" type="password"
                                maxlength="64">
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">
                            <label for="Users_password_repeat">Repeat password</label> </label>
                        <div class="controls">
                            <input class="input-xxlarge" name="Users[password_confirmation]" id="Users_password_repeat"
                                type="password">
                        </div>
                    </div>                    
                    <div class="controls">
                        <input class="btn btn-success" type="submit" name="yt0" value="Update"> <input class="btn"
                            data-dismiss="modal" aria-hidden="true" name="yt1" type="button" value="Cancel">
                    </div>
                </div>
                
            </form>
            <div class="table-wrapper">
                <table id="table-accounts" class="table table-striped table-hover">
                <tbody>
                    <tr>
                        <th width="200">Login</th>                       
                        <th>Created</th>                        
                        <th>Last Login</th>                        
                        <th style="text-align: center;">Manage</th>
                    </tr>                               
                    @foreach ($cashiers as $user)
                        @include('backend.user.partials.row_cashier')
                    @endforeach
                </tbody>
            </div>
        </div>
    </section>

@stop

@section('scripts')
    <script>
		$(function() {		

            $('.toggle-switch').click(function(){
                var data_id = $(this).data('id');
                var btn = this;
                var url = "{{route('backend.user.toggle', ':user')}}";
                url.replace(':user', data_id);
                jQuery.ajax(
                    {
                    'type':'POST',
                    'data': {'user_id':data_id, "_token": "{{ csrf_token() }}"},
                    'success': function(data){
                        $(btn).attr('class', data == 'enabled' ? 'btn btn-mini btn-success' : 'btn btn-mini btn-warning');
                    },
                    
                    'url': url,
                    'cache':false});
            })
		});
	</script>
@stop
