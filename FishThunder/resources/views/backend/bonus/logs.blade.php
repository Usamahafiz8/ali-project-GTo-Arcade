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
        <form class="form-horizontal form-vertical" action="{{route('backend.bonus.logs')}}" method="get">
            @csrf            
            <div id="date-filter" class="well">
                <div class="control-group col-md-5">
                    <label for="search" class="control-label">Username</label>
                    <div class="controls controls-row">
                        <input class="form-control" autocomplete="off" name="search" type="text" maxlength="32" value="{{$search}}">
                    </div>                    
                </div>
                <div class="control-group">
                    <label class="control-label">
                    <label for="DateFilterForm_dateFrom">From</label> </label>                    
                    <div class="controls controls-row">                        
                        <div class="input-append date" data-date="" data-date-format="mm-dd-yyyy">
                            <input class="input-large" autocomplete="off" placeholder="mm-dd-yyyy" id="DateFilterForm_dateFrom" name="DateFilterForm[dateFrom]" value="{{$DateFilterForm['dateFrom']}}" type="text" > <a class="button add-on" href="#"><i class="icon-calendar"></i></a>
                        </div>
                        <input class="input-mini" autocomplete="off" placeholder="hh:mm" id="DateFilterForm_timeFrom" name="DateFilterForm[timeFrom]" type="text" value="{{$DateFilterForm['timeFrom']}}">
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">
                        <label for="DateFilterForm_dateTill">Until</label> </label>
                    <div class="controls controls-row">
                        <div class="input-append date" data-date="" data-date-format="mm-dd-yyyy">
                            <input class="input-large" autocomplete="off" placeholder="mm-dd-yyyy" id="DateFilterForm_dateTill" name="DateFilterForm[dateTill]" type="text" value="{{$DateFilterForm['dateTill']}}"> <a class="button add-on" href="#"><i class="icon-calendar"></i></a>
                        </div>
                        <input class="input-mini" autocomplete="off" placeholder="hh:mm" id="DateFilterForm_timeTill" name="DateFilterForm[timeTill]" type="text" value="{{$DateFilterForm['timeTill']}}">
                    </div>
                </div>               
                
                <div class="controls">
                    <div class="btn-group">
                        <button class="btn btn-info" type="submit" name="yt0">Apply Filter</button> <button class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="{{route('backend.user.gamelogs', ['filter' => 'today'])}}">for today</a>
                            </li>
                            <li>
                                <a href="{{route('backend.user.gamelogs', ['filter' => 'yesterday'])}}">for yesterday</a>
                            </li>
                            <li>
                                <a href="{{route('backend.user.gamelogs', ['filter' => 'week'])}}">this week</a>
                            </li>
                            <li>
                                <a href="{{route('backend.user.gamelogs', ['filter' => 'month'])}}">this month</a>
                            </li>
                        </ul>
                    </div>
                    <a class="btn" href="{{route('backend.user.report')}}">Reset</a>
                </div>
            </div>            
        </form>
        <div class="table-responsive mt-3">
            <table class="table align-items-center table-flush">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Description</th>
                        <th style="text-align: center;">Amount</th>
                        <th style="text-align: center;">Last</th>
                        <th style="text-align: center;">Result</th>
                        <th>Date</th>                                    
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    @include('backend.bonus.partials.row_bonuslog')
                    @endforeach
                </tbody>
            </table>
            @if(count($logs) > 0)
            <div class="pagination">
                {{$logs->appends(request()->query())->links()}}
            </div>
            @endif
        </div>
    </div>
</section>
@stop

@section('scripts')
<script src="/assets/vendor/js-cookie/js.cookie.js"></script>
<script src="/assets/vendor/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
<script src="/assets/js/jquery.maskedinput.min.js"></script>
<script>
    function inputPrependSize(form) {
        var $form = $(form);
        setTimeout(function() {
            $form.find('input[type=text]').filter(':first').focus();
        }, 500);
    }


    $(function() {
        $("#DateFilterForm_dateFrom").mask("99-99-9999");
        $("#DateFilterForm_timeFrom").mask("99:99");
        $("#DateFilterForm_dateTill").mask("99-99-9999");
        $("#DateFilterForm_timeTill").mask("99:99");
        
        $('.datepicker').datepicker({

        });

        (function clock(container) {
            String.prototype.twoDigist = function(str) {
                return this.length == 1 ? ("0" + this) : this;
            };
            var serverTime = new Date("Jan 21, 2022 23:28:00"),
                localTime = Date.now(),
                timeDiff = serverTime.getTime() - localTime;

            function dateTime() {
                var date = new Date(Date.now() + timeDiff);
                return date.getFullYear() + "/" + (date.getMonth() + 1).toString().twoDigist() + "/" + date.getDate().toString().twoDigist() + " " +
                    date.getHours().toString().twoDigist() + ":" + date.getMinutes().toString().twoDigist() + ":" + date.getSeconds().toString().twoDigist();
            }
            (function adminClock() {
                $(container).html(dateTime());
                setTimeout(adminClock, 1000);
            })();
        })("#clock-block");

        $(document).ready(function() {
            if ($('.receipt').length) {
                $('#print-button').hide();
            }
        });

        function printContent() {
            const
                $report = $('#report'),
                content = $report.length ? $('#date-filter').html() + '<hr>' + $report.html() : $(".for-print").html();

            $("#forprint").html(content);

            window.print();
        }
    });
</script>
@stop