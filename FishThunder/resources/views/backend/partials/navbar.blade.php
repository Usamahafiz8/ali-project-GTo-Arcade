<div class="navbar navbar-fixed-top ">
    <div class="navbar-inner">
        <div class="container-fluid">
            
            <span class="brand">&laquo;Fish Thunder&raquo;</span>
            
            <div class="">                
                <div class="pull-right">                    
                    <span id="time_indicator" class="brand" style="font-size: 14px;"></span>
                    <ul class="nav pull-right">                        
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <span class="label">{{ auth()->user()->username }}</span> <b class="caret"></b>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="{{ route('backend.auth.logout') }}"> @lang('app.logout')</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function() {
        $.ajax({
            url: "{{route('backend.user.timezone')}}",
            type: "GET",
            data: {},
            dataType: 'json',
            success: function (response) {
                var timezone = response.timezone;
                moment.tz.setDefault(timezone);
            },
            error: function () {
            }
            
        });
	});

    var tick = 5;
    moment.tz.setDefault("America/Los_Angeles");
    setInterval(() => {        
        
        var now = moment();
        var date = moment(now).format('MM/DD/YYYY HH:mm:ss');
        $("#time_indicator").html(date);
        
        if($("#users_indicator").length > 0)
        {
            tick++;
            if(tick >= 5)
            {
                tick = 0;
                $.ajax({
                    url: "{{route('backend.user.onlineusers')}}",
                    type: "GET",
                    data: {},
                    dataType: 'json',
                    success: function (response) {
                        var total = response.total;
                        var online = response.online;
                        $("#users_indicator").html("Online users: " + online + " / " + total);
                    },
                    error: function () {
                    }
                
                });
            }
        }
    }, 1000);

</script>