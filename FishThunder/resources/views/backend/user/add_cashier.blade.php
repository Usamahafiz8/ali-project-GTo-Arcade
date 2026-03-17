<!DOCTYPE html>
@extends('backend.layouts.app')

@section('page-title', trans('app.users'))
@section('page-heading', trans('app.users'))

@section('content')

	<section class="content-header">
	@include('backend.partials.messages')
	</section>

	<section class="content">		
        <div id="main-content" class="for-print">            
            <div id="modal-create" class="modal hide fade" aria-hidden="true" style="display: none;">
                <form class="form-horizontal" id="seller-create-form" action="{{ route('backend.user.create') }}" method="post">
                @csrf
                    <input type="hidden" name="Users[balance]" value="0">
                    <input type="hidden" name="Users[rtp]" value="{{auth()->user()->percent}}">
                    <input type="hidden" name="Users[type]" value="cashier">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h3>Create cashier</h3>
                    </div>
                    <div class="modal-body">
                        <div class="control-group">
                            <label class="control-label">
                                <label for="Users_login" class="required">Username <span class="required">*</span></label> </label>
                            <div class="controls">
                                <input class="input-block-level" autocomplete="off" name="Users[username]" id="Users_login"
                                    type="text" maxlength="32">
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label">
                                <label for="Users_password" class="required">Password <span class="required">*</span></label>
                            </label>
                            <div class="controls">
                                <input class="input-block-level" name="Users[password]" id="Users_password" type="password"
                                    maxlength="64">
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label">
                                <label for="Users_password_repeat" class="required">Repeat password <span
                                        class="required">*</span></label> </label>
                            <div class="controls">
                                <input class="input-block-level" name="Users[password_confirmation]" id="Users_password_repeat"
                                    type="password">
                            </div>
                        </div>                        
                    </div>
                    <div class="modal-footer">
                        <input class="btn btn-success" type="submit" name="yt0" value="Create"> <input class="btn"
                            data-dismiss="modal" aria-hidden="true" name="yt1" type="button" value="Cancel">
                    </div>                    
                </form>
            </div>
            <div class="row">
                <p style="margin-left: 30px">
                    <button type="button" class="btn btn-success btn-block" data-toggle="modal" data-target="#modal-create"
                        onclick="inputPrependSize('#seller-create-form')">
                        <i class="icon-plus icon-white"></i> Create cashier </button>
                </p>
            </div>
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
        
        function inputPrependSize(form) {
            var $form = $(form);
            setTimeout(function () {
                $form.find('input[type=text]').filter(':first').focus();
            }, 500);
        }


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
