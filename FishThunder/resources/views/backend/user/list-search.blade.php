<table id="table-accounts" class="table align-items-center table-flush">
    <tbody>
        <tr>
            <th width="105">Account #</th>
            <th width="20"></th>
            <th width="145">Created</th>
            <th>Username</th>
            <th width="175" >Balance</th>
            <th width="100">State</th>
            <th width="100">Logout player</th>
            <th style="text-align: center;">Deposit / Redeem</th>
            <th style="text-align: center;">Operation</th>            
        </tr>
        @foreach ($players as $user)
            @include('backend.user.partials.row_search')
        @endforeach
    </tbody>
</table>
<div class="pagination">
    @if(count($players) > 0)    
    {{$players->links()}}
    @endif
</div>

<script>
    $(function() {
        $(".pagination a").click(function() {
            var href = $(this).attr('href');
            var page = href.substring(href.indexOf("page=", 0), href.length).replace("page=", "");
            curPage = page;
            return call_post_func(page);
        });

        $('.force-logout').click(function(){
            var action = confirm('Are you sure to kickout this player?');

            if(action)
            {
                var data_id = $(this).data('id');
                var url = "{{route('backend.user.kickout', ':user')}}";
                url = url.replace(':user', data_id);
                var btn = this;
                jQuery.ajax(
                    {
                    'type':'POST',
                    'data': {'user_id':data_id, "_token": "{{ csrf_token() }}"},
                    'success': function(data){
                        
                    },
                    
                    'url': url,
                    'cache':false});
            }
        })

        $('.delete-user').click(function(){
            var data_id = $(this).data('id');
            var btn = this;
        })
    });
    function call_post_func(href)
    {
        console.log(href);
        post_this(href, searchClue);
        return false;
    }

</script>