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
            <div class="table-wrapper">
                <table id="table-accounts" class="table table-striped table-hover">
                <tbody>
                    <tr>
                        <th width="250">Login</th>
                        <th width="110">Type</th>
                        <th width="180">Created</th>
                        <th>Last Login</th>
                        <th width="130" style="text-align: right;">Balance</th>
                        <th width="160" style="text-align: center;">Deposit / Redeem</th>
                        <th width="70" style="text-align: center;">Manage</th>
                        <!-- <th width="70" style="text-align: center;">Delete</th> -->
                    </tr>                                   
                    @foreach ($users as $user)
                        @php $parent = \VanguardLTE\User::where('id', '=', $user->parent_id)->get()[0]; @endphp
                        @include('backend.user.partials.row')
                    @endforeach
                </tbody>
            </div>
        </div>
	</section>	
@stop
@if ( count($users) > 0)
    @include('backend.user.partials.modals')
@endif
@section('scripts')
	<script>
        
        function expandChildren(id, hide){
            var $table = $('#table-accounts');
            var children = $table.find('tr[data-parent="' + id + '"]');
            children.each(function(i, el){
                var node = $(el); hide ? node.hide() : node.toggle();
                if(node.is(':hidden')){
                    node.find('.tree-minus').toggleClass('tree-minus tree-plus');
                    expandChildren(node.data('id'), true);
                }
            });
            if(!hide){
                $table.find('tr[data-id="' + id + '"]').find('[rel="tree-icon"]').toggleClass('tree-plus tree-minus');
            }
            return false;
        }


		$(function() {
			
            $('.tree_switch').click(function(){
                var data_id = $(this).data('id');
                return expandChildren(data_id);
            })

            $('.toggle-switch').click(function(){
                var data_id = $(this).data('id');
                var btn = this;
                var url = "{{route('backend.user.toggle', ':user')}}";
                url = url.replace(':user', data_id);
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

            $('.delete-user').click(function(){
                if(confirm("Do you really want to delete this user?"))
                {
                    var data_id = $(this).data('id');                
                    var url = "{{route('backend.user.delete', ':user')}}";
                    url = url.replace(':user', data_id);
                    jQuery.ajax(
                    {
                    'type':'POST',
                    'data': {"_token": "{{ csrf_token() }}"},
                    'success': function(data){
                        window.location.reload();
                    },
                    
                    'url': url,
                    'cache':false});
                }
            })

		});
	</script>
@stop
