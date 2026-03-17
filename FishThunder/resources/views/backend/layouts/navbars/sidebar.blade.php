<nav class="navbar navbar-vertical fixed-left navbar-expand-md navbar-light bg-white" id="sidenav-main">
    <div class="container-fluid">
        <!-- Toggler -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#sidenav-collapse-main" aria-controls="sidenav-main" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- Brand -->
        <a class="navbar-brand pt-0" href="">
           <b>Fish Thunder</b>
        </a>        
        
        <!-- Collapse -->
        <div class="collapse navbar-collapse" id="sidenav-collapse-main">
            <!-- Collapse header -->
            <div class="navbar-collapse-header d-md-none">
                <div class="row">
                    <div class="col-6 collapse-brand">
                        <b>Fish Thunder</b>
                    </div>
                    <div class="col-6 collapse-close">
                        <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#sidenav-collapse-main" aria-controls="sidenav-main" aria-expanded="false" aria-label="Toggle sidenav">
                            <span></span>
                            <span></span>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Navigation -->
            <ul class="navbar-nav">
                
                <li class="nav-item">
                    <a class="nav-link" href="{{route('backend.user.list')}}">
                        <i class="ni ni-ruler-pencil text-primary"></i> {{ __('User management') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{route('backend.user.search')}}">
                        <i class="ni ni-tv-2 text-primary"></i> {{ __('Search user') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{route('backend.user.transactions')}}">
                        <i class="ni ni-money-coins text-primary"></i> {{ __('Transactions') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{route('backend.user.gamelogs')}}">
                        <i class="ni ni-archive-2 text-primary"></i> {{ __('Game log') }}
                    </a>
                </li>
                <!-- <li class="nav-item">
                    <a class="nav-link" href="">
                        <i class="ni ni-active-40 text-primary"></i> {{ __('operate history') }}
                    </a>
                </li> -->
                <li class="nav-item">
                    <a class="nav-link" href="{{route('backend.bonus.logs')}}">
                        <i class="ni ni-trophy text-primary"></i> {{ __('Bonus log') }}
                    </a>
                </li>
                @if( auth()->user()->hasRole(['admin']) )
                <li class="nav-item">
                    <a class="nav-link" href="{{route('backend.bonus.setting')}}">
                        <i class="ni ni-settings-gear-65 text-primary"></i> {{ __('Bonus setting') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{route('backend.game.setting')}}">
                        <i class="ni ni-settings-gear-65 text-primary"></i> {{ __('Game setting') }}
                    </a>
                </li>

                <a class="nav-link" href="#homesubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="ni ni-collection text-blue"></i> Report </a>
                <ul class="collapse list-unstyled" id="homesubmenu" >
                    <li class="nav-item">
                        <a  class="nav-link" href="{{route('backend.user.report_daily')}}">
                            <i class="ni text-blue"></i> {{ __('Daily report') }} </a>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a  class="nav-link" href="{{route('backend.user.report_game')}}">
                            <i class="ni text-blue"></i> {{ __('Game report') }} </a>
                        </a>
                    </li>
                </ul>                
                @endif
                <li class="nav-item">
                    <a class="nav-link" href="{{route('backend.user.changepassword')}}">
                        <i class="ni ni-lock-circle-open text-primary"></i> {{ __('Change password') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{route('backend.auth.logout')}}">
                        <i class="ni ni-user-run text-primary"></i> {{ __('Sign out') }}
                    </a>
                </li>    
            </ul>
            <!-- Divider -->
            <hr class="my-3">      
              
        </div>
    </div>
</nav>
