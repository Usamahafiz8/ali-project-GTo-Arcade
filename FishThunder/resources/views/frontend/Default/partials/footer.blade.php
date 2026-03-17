<footer class="bs-footer">
    <div class="container-fluid d-flex">
        <div class="row">
            <div class="col-xl-3 col-lg-6 col-md-6 col-12">
                <div class="bs-footer-content ml-5">
                    <div class="bs-footer-title">
                        <h4>Balance</h4>
                        <h4 class="ml-3">{{ number_format($user->balance, 2,".","") }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="row .d-flex flex-row-reverse" style="width: 100%;">
        @if( Auth::check() )
            <a href="{{ route('frontend.auth.logout') }}" class="btn btn-logout">            
            </a>
        @endif
        </div>
    </div>
    
</footer>