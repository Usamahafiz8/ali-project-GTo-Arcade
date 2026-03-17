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
        <form class="form-horizontal form-vertical" id="yw0" action="{{route('backend.user.daily_report')}}" method="get">
            @csrf
            <div id="date-filter" class="well">
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
                
                <div class="control-group">
                    <label class="control-label">
                        <label for="DateFilterForm_search">Search</label> </label>
                    <div class="controls controls-row">
                        <input class="input-large" placeholder="Account" name="DateFilterForm[search]" id="DateFilterForm_search" type="text" value="{{$DateFilterForm['search']}}">
                    </div>
                </div>
                <div class="controls">
                    <div class="btn-group">
                        <button class="btn btn-info" type="submit" name="yt0">Apply Filter</button> <button class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="/user/history?filter=today">for today</a>
                            </li>
                            <li>
                                <a href="/user/history?filter=yesterday">for yesterday</a>
                            </li>
                            <li>
                                <a href="/user/history?filter=week">this week</a>
                            </li>
                            <li>
                                <a href="/user/history?filter=month">this month</a>
                            </li>
                        </ul>
                    </div>
                    <a class="btn" href="/user/history">Reset</a>
                </div>
            </div>            
        </form>        


        <div class="table-wrapper">
            <table class="table table-striped table-hover">
                <tbody>
                    <tr>
                        <th>Bet</th>
                        <th>Win</th>
                        <th>Profit</th>
                        <th>Rtp</th>
                        <th>Date</th>
                    </tr>
                    @foreach($reports as $report)
                        @include('backend.user.partials.row_daily_report')
                    @endforeach
                    @if(count($total) > 0)
                    <td>Total : {{$total[0]->bet}}</td>
                    <td>Total : {{$total[0]->win}}</td>
                    <td>Total : {{$total[0]->bet - $total[0]->win}}</td>
                    <td>Total : {{$total[0]->bet == 0 ? '0.00' : number_format($total[0]->win / $total[0]->bet * 100, 2)}}</td>
                    @endif
                </tbody>
            </table>

            @if(count($reports) > 0)
            <div class="pagination">
                {{$reports->appends(request()->query())->links()}}
            </div>
            @endif
        </div>
    </div>
</section>
@stop

@section('scripts')
<script>
    

    function inputPrependSize(form) {
        var $form = $(form);
        setTimeout(function() {
            $form.find('input[type=text]').filter(':first').focus();
        }, 500);
    }


    $(function() {
        (function clock(container){
	    String.prototype.twoDigist = function(str){ return this.length == 1 ? ("0" + this) : this; };
        var serverTime = new Date("Jan 21, 2022 23:28:00"), localTime = Date.now(), timeDiff = serverTime.getTime() - localTime;
        function dateTime(){
            var date = new Date(Date.now() + timeDiff);
            return date.getFullYear() + "/" + (date.getMonth() + 1).toString().twoDigist() + "/" + date.getDate().toString().twoDigist() + " " +
                date.getHours().toString().twoDigist() + ":" + date.getMinutes().toString().twoDigist() + ":" + date.getSeconds().toString().twoDigist();
        }
        (function adminClock(){ $(container).html(dateTime()); setTimeout(adminClock, 1000); })();
        })("#clock-block");

        /*<![CDATA[*/
        jQuery(function($) {
        jQuery("#DateFilterForm_dateFrom").mask("99-99-9999");
        jQuery("#DateFilterForm_timeFrom").mask("99:99");
        jQuery("#DateFilterForm_dateTill").mask("99-99-9999");
        jQuery("#DateFilterForm_timeTill").mask("99:99");
        });
        jQuery(window).on('load',function() {
        $("body").tooltip({selector: "[rel=tooltip]", placement: "top"});
        });
        /*]]>*/

        $('.date').datepicker({
            
        });
        $('select.data-select-login').select2({
            ajax: {
                url: '/office/searchLogin?roles%5B0%5D=agent&roles%5B1%5D=cashier',
                dataType: 'json',
                delay: 250,
                cache: true,
                data: function(params) {
                    return {
                        login: params.term,
                        currency: $('#DateFilterForm_currencyId').val()
                    };
                },
                processResults: function(data) {
                    return {
                        results: $.map(data, function(item) {
                            return {
                                id: item.id,
                                text: item.text
                            }
                        })
                    };
                }
            },
            maximumSelectionLength: 0,
            minimumInputLength: 2
        });
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