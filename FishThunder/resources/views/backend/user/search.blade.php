@extends('backend.layouts.app', ['class' => 'dark-version'])

@section('page-title', trans('app.users'))
@section('page-heading', trans('app.users'))

@section('content')

<div class="header bg-primary pb-6">
    <div class="container-fluid">
        <div class="header-body">
            <div class="row align-items-center py-4">
                <div class="col-lg-6 col-7">
                    <h6 class="h2 text-white d-inline-block mb-0">Search User</h6>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid mt--6">
    <section class="content-header">
        @include('backend.partials.messages')
    </section>
    <div class="row">
        <div class="col-12">
            <div class="card p-4">
                <form class="form-horizontal form-vertical" id="yw0" action="" method="get">
                    @csrf
                    <div class="row">
                        <div class="form-group col">
                            <label for="Users_login" class="form-label">Username <span class="required">*</span></label>
                            <input class="form-control" autocomplete="off" name="search" id="search_value" type="text" maxlength="32" value="{{$search}}">
                        </div>
                    </div>                    
                    <button class="btn btn-success" type="button" name="yt0" onclick="onSearch()">Search</button>                    
                </form>                
            </div>
        </div>
    </div>

    <!-- <div class="table-responsive">
        <div>
            <table id="table-accounts" class="table align-items-center table-flush">
                <thead>
                    <tr>
                        <th>Login</th>
                        <th>Created</th>
                        <th>Last Login</th>
                        <th>Balance</th>
                        <th style="text-align: center;">Deposit / Redeem</th>
                        <th style="text-align: center;">Operation</th>
                    </tr>
                </thead>
                <tbody>
                    
                </tbody>
            </table>
        </div>
    </div> -->
    <div class="row mt-2">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0 d-flex">
                    <h3 class="mb-0"></h3>                    
                </div>
                <div class="table-responsive">                    
                    <div class="player-table-wrapper">
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@include('backend.user.partials.modals')
@section('scripts')
<script>

    $(function() {
        $('.toggle-switch').click(function(){
            var action;
            if($(this).hasClass('btn-success'))
                action = confirm('This account will be disconnected and locked.\nAre you sure to continue?');                    
            else
                action = confirm('Are you sure to unblock this player?');                

            if(action)
            {
                var data_id = $(this).data('id');
                var btn = this;
                var url = "{{route('backend.user.toggle', ':user')}}";
                url = url.replace(':user', data_id);
                jQuery.ajax(
                    {
                    'type':'POST',
                    'data': {'user_id':data_id, "_token": "{{ csrf_token() }}"},
                    'success': function(data){
                        $(btn).attr('class', data == 'enabled' ? 'btn btn-sm btn-success' : 'btn btn-sm btn-danger');
                    },
                    
                    'url': url,
                    'cache':false});
            }
        }) 

        $('.delete-user').click(function() {
            if (confirm("Do you really want to delete this user?")) {
                var data_id = $(this).data('id');
                var url = "{{route('backend.user.delete', ':user')}}";
                url = url.replace(':user', data_id);
                jQuery.ajax({
                    'type': 'POST',
                    'data': {
                        "_token": "{{ csrf_token() }}"
                    },
                    'success': function(data) {
                        window.location.reload();
                    },

                    'url': url,
                    'cache': false
                });
            }
        })
        
    });

    var curPage;
    var searchClue = '';
    // $(document).ready(function() {
    //     curPage = 1;
    //     post_this(curPage);

    //     setInterval(() => {
    //         post_this(curPage, searchClue);
    //     }, 10000);
    // })

    function post_this(page_num, search) {
        curPage = 1;
        $.post("{{route('backend.user.search')}}", {
                "_token": "{{ csrf_token() }}",
                "page_num": page_num,
                "search": search,          
                "parent": "{{auth()->user()->id}}"      
            },
            function(result) {
                $(".player-table-wrapper").html(result)
            })
    }

    function onSearch() {
        var clue = $('#search_value').val();
        if(clue == '')
        {
            alert('please input username');
            return;
        }
        searchClue = clue;
        post_this(curPage, clue);
    }

    function toggleUser()
    {
        var action;
        if($(this).hasClass('btn-success'))
            action = confirm('This account will be disconnected and locked.\nAre you sure to continue?');                    
        else
            action = confirm('Are you sure to unblock this player?');                

        if(action)
        {
            var data_id = $(this).data('id');
            var btn = this;
            var url = "{{route('backend.user.toggle', ':user')}}";
            url = url.replace(':user', data_id);
            jQuery.ajax(
                {
                'type':'POST',
                'data': {'user_id':data_id, "_token": "{{ csrf_token() }}"},
                'success': function(data){
                    $(btn).attr('class', data == 'enabled' ? 'btn btn-sm btn-success' : 'btn btn-sm btn-danger');
                },
                
                'url': url,
                'cache':false});
        }
    }
    
</script>
@stop