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
            <div class="row-fluid">
                <div class="span8">
                    <form class="form-horizontal form-vertical" novalidate="novalidate" id="yw0" action="{{route('backend.user.player_store')}}" method="post">
                        @csrf
                        <div class="well">
                            <div class="control-group">
                                <label class="control-label">
                                    <label for="Accounts_name">Name</label> </label>
                                <div class="controls">
                                    <input class="input-xlarge" autocomplete="off" name="Accounts[name]" id="Accounts_name" type="text" maxlength="100">
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">
                                    <label for="Accounts_comments">Username</label> </label>
                                <div class="controls">
                                    <input class="input-xlarge" autocomplete="off" name="Accounts[username]" id="Accounts_comments" type="text" maxlength="100">
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">
                                    <label for="Accounts_comments">Password</label> </label>
                                <div class="controls">
                                    <input class="input-xlarge" autocomplete="off" name="Accounts[password]" id="Accounts_password" type="password" maxlength="100">
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">
                                    <label for="Accounts_balance" class="required">Deposit Amount <span class="required">*</span></label> </label>
                                <div class="controls">
                                    <input class="input-xlarge" autocomplete="off" placeholder="0.00" step="0.01" name="Accounts[balance]" id="Accounts_balance" type="number" value="0">
                                </div>
                            </div>
                            <div class="controls">
                                <input class="btn btn-success" type="submit" name="yt0" value="Create account">
                            </div>
                        </div>                
                    </form>
                </div>                
            </div>
            <div id="modal-usersetting" class="modal hide fade">
                <form class="form-horizontal" id="yw1" action="{{ route('backend.user.updateprofile') }}" method="post">
                    <input id="modal-profile-user-id" name="user-id" type="hidden" value="{{$user->id}}">
                    @csrf
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h3>Update Profile</h3>
                    </div>
                    <div class="modal-body">
                        <div class="control-group">
                            <label class="control-label"><label for="DepositeForm_amount" class="required">Username</label></label>
                            <div class="controls">
                                <input id="profile-username" autocomplete="off" name="profile-username" type="text" disabled>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label"><label for="DepositeForm_amount" class="required">Password</label></label>
                            <div class="controls">
                                <input id="profile-passwword" autocomplete="off" name="profile-password" type="password">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input class="btn btn-success" type="submit" name="yt1" value="Update">
                        <input class="btn" data-dismiss="modal" aria-hidden="true" name="yt2" type="button" value="Cancel">
                    </div>                    
                </form>
            </div>

            <div id="modal-deposite" class="modal hide fade">
                <form class="form-horizontal" id="yw1" action="{{ route('backend.user.balance.update') }}" method="post"><input id="modal-deposite-id" name="DepositeForm[id]" type="hidden" value="{{$user->id}}"><input name="DepositeForm[type]" id="DepositeForm_type" type="hidden" value="add">
                    @csrf
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h3>Deposit the balance</h3>
                    </div>
                    <div class="modal-body">
                        <div class="control-group text-center">
                            <span id="modal-deposite-code" class="text-success lead">xx-xx-xx-xx-xx-xx</span>
                        </div>
                        <div class="control-group text-center">
                            <span id="modal-deposite-available" class="">
                                Available: 
                            @if( auth()->user()->hasRole(['admin']))
                                unlimited
                            @elseif (auth()->user()->hasRole(['agent']) )
                                {{auth()->user()->balance}}                   
                            @else
                                {{\VanguardLTE\Shop::where('id',auth()->user()->shop_id)->get()[0]->balance}}
                            @endif
                            </span>
                        </div>
                        <div class="control-group">
                            <label class="control-label"><label for="DepositeForm_amount" class="required">Amount <span class="required">*</span></label></label>
                            <div class="controls">
                                <input id="modal-deposite-amount" autocomplete="off" placeholder="0.00" step="0.01" name="DepositeForm[amount]" type="number">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input class="btn btn-success" type="submit" name="yt1" value="Deposit"> <input class="btn" data-dismiss="modal" aria-hidden="true" name="yt2" type="button" value="Cancel">
                    </div>                    
                </form>
            </div>
            <div id="modal-withdrawal" class="modal hide fade">
                <form class="form-horizontal" id="yw2" action="{{ route('backend.user.balance.update') }}" method="post"><input id="modal-withdrawal-id" name="DepositeForm[id]" type="hidden" value="{{$user->id}}"><input name="DepositeForm[type]" id="DepositeForm_type" type="hidden" value="out">
                    @csrf
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h3>Redeem from the balance</h3>
                    </div>
                    <div class="modal-body">
                        <div class="control-group text-center">
                            <span id="modal-withdrawal-code" class="text-success lead">xx-xx-xx-xx-xx-xx</span>
                        </div>
                        <div class="control-group text-center">
                            <span id="modal-reedem-available" class=""></span>
                        </div>
                        <div class="control-group">
                            <label class="control-label"><label for="DepositeForm_amount" class="required">Amount <span class="required">*</span></label></label>
                            <div class="controls">
                                <input id="modal-withdrawal-amount" autocomplete="off" placeholder="0.00" step="0.01" name="DepositeForm[amount]" type="number">
                            </div>
                        </div>
                        
                    </div>
                    <div class="modal-footer">
                        <input class="btn btn-success" type="submit" name="yt3" value="Redeem"> <input class="btn" data-dismiss="modal" aria-hidden="true" name="yt4" type="button" value="Cancel">
                    </div>
                                     
                </form>
            </div>

            <div id="modal-close" class="modal hide fade">
                <form class="form-horizontal" id="yw3" action="/cashier/create?search=&" method="post"><input id="modal-close-id" name="CloseForm[id]" type="hidden">
                    @csrf
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h3>Close account</h3>
                    </div>
                    <div class="modal-body">
                        <div class="control-group text-center">
                            <span id="modal-close-code" class="text-success lead">xx-xx-xx-xx-xx-xx</span>
                        </div>
                        <div class="control-group pin-code hidden">
                            <label class="control-label">Confirmation code</label>
                            <div class="controls">
                                <input id="modal-close-pin" autocomplete="off" min="1000" max="9999" name="CloseForm[pin]" type="number">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input class="btn btn-warning" type="submit" name="yt5" value="Close"> <input class="btn" data-dismiss="modal" aria-hidden="true" name="yt6" type="button" value="Cancel">
                    </div>                    
                </form>
            </div>

            <div class="row">
                <form action="{{route('backend.user.player_create')}}" method="get" class="form-search pull-right">
                    @csrf
                    <div class="input-append">
                        <input id="search_value" class="input-large search-query" name="search" type="text" placeholder="Account # or Username" value="{{$search}}">
                        <button class="btn" onclick="onSearch();" type="button">Search</button>
                    </div>                    
                </form>
            </div>
            <div class="table-wrapper">
                
            </div>
        </div>
	</section>	
@stop

@section('scripts')
	<script>
        var curPage;
        var searchClue = '';
        $(document).ready(function(){
            curPage = 1;
            post_this(curPage);

            setInterval(()=>{
                post_this(curPage, searchClue);
            }, 10000);
        })        

        function post_this(page_num, search)
        {
            $.post("{{route('backend.user.player_table')}}", {
                "_token": "{{ csrf_token() }}",
                "page_num": page_num,
                "search": search
            },
            function(result){
                $(".table-wrapper").html(result)
            })
        }

        function onSearch()
        {
            var clue = $('#search_value').val();
            searchClue = clue;
            post_this(curPage, clue);            
        }

        function inputPrependSize(form) {
            var $form = $(form);
            setTimeout(function () {
                $form.find('input[type=text]').filter(':first').focus();
            }, 500);
        }		
	</script>
@stop
