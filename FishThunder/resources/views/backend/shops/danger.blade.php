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
            <table class="table table-striped table-hover">
                <tbody>
                    <tr class="table-active">                        
                        <th>ShopID</th>
                        <th>Name</th>
                        <th>Slot</th>
                        <th>Bonus</th>                                                             
                    </tr>
                    @foreach($danger_shops as $danger_shop)
                        @include('backend.shops.partials.row_danger')
                    @endforeach
                </tbody>
            </table>            
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

        /*<![CDATA[*/
        jQuery(function($) {
            jQuery("#DateFilterForm_dateFrom").mask("99-99-9999");
            jQuery("#DateFilterForm_timeFrom").mask("99:99");
            jQuery("#DateFilterForm_dateTill").mask("99-99-9999");
            jQuery("#DateFilterForm_timeTill").mask("99:99");
        });
        jQuery(window).on('load', function() {
            $("body").tooltip({
                selector: "[rel=tooltip]",
                placement: "top"
            });
        });
        /*]]>*/

        $('.date').datepicker({

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