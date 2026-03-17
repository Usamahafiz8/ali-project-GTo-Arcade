@extends('backend.layouts.app')

@section('page-title', trans('app.edit_user'))
@section('page-heading', $user->present()->username)

@section('content')

    <section class="content-header">
        @include('backend.partials.messages')
    </section>

    <section class="content">
        <div id="main-content" class="for-print">
        <form class="form-horizontal" id="yw0" action="{{route('backend.user.update', ['user' => $user->id])}}" method="post">
        @csrf
            <ul id="settings-tabs" class="nav nav-tabs">
                <li class="active"><a href="#settings-general" data-toggle="tab">General</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="settings-general">
                    <div class="row-fluid">
                        <fieldset>
                            <legend>Master Fields</legend>
                            <div class="control-group">
                                <label class="control-label">
                                    <label for="Users_parent">Parent</label> </label>
                                <div class="controls">
                                    <div class="input-xxlarge uneditable-input">{{$selector}}</div>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">
                                    <label for="Users_login">Login</label> </label>
                                <div class="controls">
                                    <input id="users-login" class="input-xxlarge" autocomplete="off" disabled="disabled"
                                        name="Users[login]" type="text" maxlength="32" value="{{$user->username}}">
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">
                                    <label for="Users_password">Password</label> </label>
                                <div class="controls">
                                    <input class="input-xxlarge" autocomplete="off" value="" name="Users[password]"
                                        id="Users_password" type="password" maxlength="64">
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">
                                    <label for="Users_password_repeat">Repeat password</label> </label>
                                <div class="controls">
                                    <input class="input-xxlarge" autocomplete="off" value="" name="Users[password_confirmation]"
                                        id="Users_password_repeat" type="password">
                                </div>
                            </div>                         
                            <div class="control-group">
                                <label class="control-label">
                                    <label for="Users_status">Status</label> </label>
                                <div class="controls">
                                    <select class="input-xxlarge" name="Users[status]" id="Users_status">
                                        <option value="disabled" {{$user->is_blocked == 1? 'selected' : ''}}>Disabled</option>
                                        <option value="enabled" {{$user->is_blocked == 0? 'selected' : ''}}>Enabled</option>
                                    </select>
                                </div>
                            </div>
                            <div class="control-group">
                                    <label class="control-label">
                                        <label for="Users_status">RTP</label> </label>
                                    <div class="controls">
                                        <select class="input-xxlarge" name="Users[rtp]" id="Users_rtp">
                                            @if(Auth::user()->hasRole('admin'))
                                                <option value="70" {{$user->percent == 70 ? 'selected' : ''}}>70</option>
                                                <option value="80" {{$user->percent == 80 ? 'selected' : ''}}>80</option>
                                                <option value="87" {{$user->percent == 87 ? 'selected' : ''}}>87</option>
                                                <option value="90" {{$user->percent == 90 ? 'selected' : ''}}>90</option>
                                                <option value="95" {{$user->percent == 95 ? 'selected' : ''}}>95</option>
                                            @else
                                                <option value="{{$user->percent}}" selected>{{$user->percent}}</option>
                                            @endif                                       
                                        </select>
                                    </div>
                                </div>
                            <legend>Timezone settings</legend>
                            <div class="control-group">
                                <label class="control-label">
                                <label for="Users_time_zone" class="required">Time Zone <span class="required">*</span></label>            </label>
                                <div class="controls">
                                    <select class="input-xxlarge" name="Users[time_zone]" id="Users_time_zone">
                                        @foreach($zones as $timezone)
                                            @include('backend.user.partials.row_timezone')
                                        @endforeach                                            
                                    </select> 
                                </div>
                            </div>                            
                            <legend>Contact Details</legend>
                            <div class="control-group">
                                <label class="control-label">
                                    <label for="Users_country">Country</label> </label>
                                <div class="controls">
                                    <input class="input-xxlarge" name="Users[country]" id="Users_country" type="text" value="{{$contact_info['country']}}"
                                        maxlength="128">
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">
                                    <label for="Users_city">City</label> </label>
                                <div class="controls">
                                    <input class="input-xxlarge" name="Users[city]" id="Users_city" type="text" value="{{$contact_info['city']}}"
                                        maxlength="128">
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">
                                    <label for="Users_name">Name</label> </label>
                                <div class="controls">
                                    <input class="input-xxlarge" name="Users[name]" id="Users_name" type="text" value="{{$contact_info['name']}}"
                                        maxlength="128">
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">
                                    <label for="Users_email">E-mail</label> </label>
                                <div class="controls">
                                    <input class="input-xxlarge" name="Users[email]" id="Users_email" type="text" value="{{$contact_info['email']}}"
                                        maxlength="128">
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">
                                    <label for="Users_phone">Phone number</label> </label>
                                <div class="controls">
                                    <input class="input-xxlarge" name="Users[phone]" id="Users_phone" type="text" value="{{$contact_info['phone']}}"
                                        maxlength="128">
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">
                                    <label for="Users_percent">Finance</label> </label>
                                <div class="controls">
                                    <input class="input-xxlarge" placeholder="0.00" name="Users[percent]" id="Users_percent" value="{{$contact_info['percent']}}"
                                        type="text" maxlength="6" value="0.00">
                                </div>
                            </div>
                            <div class="controls">
                                <input class="btn btn-success" type="submit" name="yt0" value="Save Changes"> <a class="btn"
                                    href="{{route('backend.user.list')}}">Cancel</a>
                            </div>
                        </fieldset>
                    </div>
                    <script>
                        $('#unlock-login-field').one('click', function (e) {
                            $(this).prop('disabled', true).closest('form').find('#users-login:disabled').prop('disabled', false);
                        });
                        $('#unlock-password-fields').one('click', function (e) {
                            $(this).prop('disabled', true).closest('form').find('input[type="password"]:disabled').prop('disabled', false);
                        });
                        $('.phone-mask1').mask('(999) 999-9999');
                        $('.phone-mask2').mask('(999) 999-9999');
                    </script>
                </div>
            </div>
            <input type="hidden" id="visitorId" name="visitorId" value="cbefa723e1f11abf90de823eccb6a20d">
        </form>
        <script>
            $(function () {
                var currentTab = window.localStorage['settings-tab-active-admin'];
                if (currentTab) {
                    $('#settings-tabs').find('a[href="' + currentTab + '"]').tab('show');
                }
                else {
                    $('#settings-tabs').find('a:first').tab('show');
                }
            });
            $('#settings-tabs').on('click', 'a[data-toggle="tab"]', function (e) {
                window.localStorage.removeItem('settings-tab-active-admin');
                window.localStorage.setItem('settings-tab-active-admin', $(this).attr('href'));
            });
        </script>
    </div>
    </section>

@stop

@section('scripts')
    <script>
        $(function() {
            $('input[name="date"]').datepicker({
                format: 'yyyy-mm-dd',
            });
        });
        $('.outPayment').click(function(event){
            $('#outAll').val('');
        });
        $('#doOutAll').click(function () {
            $('#outAll').val('1');
            $('form#outForm').submit();
        });
    </script>
    {!! HTML::script('/back/js/as/app.js') !!}
    {!! HTML::script('/back/js/as/btn.js') !!}
    {!! HTML::script('/back/js/as/profile.js') !!}
    {!! JsValidator::formRequest('VanguardLTE\Http\Requests\User\UpdateDetailsRequest', '#details-form') !!}
    {!! JsValidator::formRequest('VanguardLTE\Http\Requests\User\UpdateLoginDetailsRequest', '#login-details-form') !!}
@stop
