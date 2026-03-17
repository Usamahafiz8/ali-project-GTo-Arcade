<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
        <!-- Sidebar user panel -->

        <div class="span2">
            <div class="alert alert-block">
                @if( auth()->user()->hasRole(['admin']))
                <p>
                    Your balance: <b style="white-space: nowrap;">unlimited</b>
                </p>
                @elseif (auth()->user()->hasRole(['agent']) )
                <p>
                    Your balance: <b style="white-space: nowrap;">{{auth()->user()->balance}} usd</b>
                </p>
                @else
                <p>
                    Your balance: <b style="white-space: nowrap;">{{\VanguardLTE\Shop::where('id',auth()->user()->shop_id)->get()[0]->balance}} usd</b>
                </p>
                @endif
            </div>
            <div class="well well-small">
                <ul class="nav nav-list">
                    <li>
                        @permission('users.manage')
                        @if( auth()->user()->hasRole(['admin']) || auth()->user()->hasRole(['agent']) )
                        <a href="{{ route('backend.user.list') }}">
                            <i class="icon-user"></i>
                            @if( auth()->user()->hasRole(['admin']) )
                            <span>@lang('app.agent_management')</span>
                            @elseif( auth()->user()->hasRole(['agent']) )
                            <span>@lang('app.shops_management')</span>
                            @endif
                        </a>
                        @elseif(auth()->user()->hasRole(['manager']) || auth()->user()->hasRole(['cashier']))
                        <a href="{{ route('backend.user.player_create') }}">
                            <i class="icon-user"></i>
                            <span>@lang('app.accounts_management')</span>
                        </a>
                        @endif
                        @endpermission
                    </li>
                    @if( auth()->user()->hasRole(['admin']) || auth()->user()->hasRole(['agent']) )
                    <li><a href="{{ route('backend.user.create') }}"><i class="icon-plus"></i>@lang('app.create_shop')</a></li>
                    @else
                    <li><a href="{{route('backend.user.player_history')}}"><i class="icon-calendar"></i>@lang('app.accounts_history')</a></li>
                    @endif

                    @if( auth()->user()->hasRole(['manager']) || auth()->user()->hasRole(['agent']) || auth()->user()->hasRole(['admin']) )
                    @permission('users.manage')
                    <li><a href="{{route('backend.user.transactions')}}"><i class="icon-search"></i>@lang('app.transaction_history')</a></li>
                    @endpermission
                    @endif

                    @if( auth()->user()->hasRole(['admin']) || auth()->user()->hasRole(['agent']) || auth()->user()->hasRole(['manager']) )
                    @permission('stats.pay')
                    <li>
                        <a href="{{ route('backend.user.statistics') }}">
                            <i class="icon-signal"></i>
                            @lang('app.statistics')
                        </a>
                    </li>
                    @endpermission
                    @endif

                    @if(auth()->user()->hasRole(['admin']))
                    <a class="sub-btn" href="#"> <i class="icon-tasks"></i> Report</a>
                    <div class="sub-menu">
                        <a href="{{ route('backend.user.report') }}">
                            @lang('app.game_report')
                        </a>
                        <a href="{{ route('backend.user.daily_report') }}">
                            @lang('app.daily_report')
                        </a>
                    </div>                    
                    <li>
                        <a href="{{ route('backend.game.setting') }}">
                            <i class="icon-wrench"></i>
                            @lang('app.game_setting')
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{route('backend.bonus.setting')}}">
                            <i class="ni ni-settings-gear-65 text-primary icon-cog"></i> {{ __('Bonus setting') }}
                        </a>
                    </li>                    
                    <!--
                    <li>
                        <a href="{{ route('backend.shop.danger') }}">
                            <i class="icon-wrench"></i>
                            @lang('app.danger_shop')
                        </a>
                    </li>
                    -->
                    @endif
                    <li class="nav-item">
                        <a class="nav-link" href="{{route('backend.bonus.logs')}}">
                            <i class="ni ni-trophy text-primary icon-briefcase"></i> {{ __('Bonus log') }}
                        </a>
                    </li>
                    @if(auth()->user()->hasRole(['manager']))
                    <li><a href="{{ route('backend.user.cashier_create') }}"><i class="icon-user"></i> Cashiers management</a></li>
                    @endif                    
                    <li>
                        <a class="sub-btn" href="#"> <i class="icon-camera"></i> Platform News</a>
                        <div class="sub-menu">
                            <a href="{{route('backend.post.poster')}}" class="sub-item">Download Posters</a>
                            <a href="{{route('backend.post.video')}}" class="sub-item">Download Videos</a>
                            <a href="{{route('backend.post.news')}}" class="sub-item">Coming soon</a>          
                            @if(auth()->user()->hasRole(['admin']))
                            <a href="{{route('backend.post.notification')}}" class="sub-item">Notification</a>
                            @endif                 
                        </div>
                    </li>
                    <li class="divider"></li>
                    <li><a href="{{ route('backend.user.changepassword') }}"><i class="icon-wrench"></i> Change Password</a></li>
                    <li><a href="{{ route('backend.auth.logout') }}"> <i class="icon-off"></i> @lang('app.logout')</a></li>                    
                </ul>
            </div>
        </div>
    </section>
</aside>

<script type="text/javascript">
    $(document).ready(function() {
        //jquery for toggle sub menus
        $('.sub-btn').click(function() {
            $(this).next('.sub-menu').slideToggle();
            $(this).next('.sub-menu').css("display", "flex");
            $(this).next('.sub-menu').css("flex-direction", "column");
            $(this).find('.dropdown').toggleClass('rotate');
        });
    });
</script>