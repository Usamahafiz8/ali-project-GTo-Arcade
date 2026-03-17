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
        <form class="form-horizontal form-vertical" id="yw0" action="{{route('backend.user.statistics')}}" method="get">
            @csrf
            <div id="date-filter" class="well">
                <div class="control-group">
                    <label class="control-label">
                        <label for="DateFilterForm_dateFrom">From</label> </label>
                    <div class="controls controls-row">
                        <div class="input-append date" data-date="" data-date-format="mm-dd-yyyy">
                            <input class="input-large" autocomplete="off" placeholder="mm-dd-yyyy" id="DateFilterForm_dateFrom" name="DateFilterForm[dateFrom]" value="{{$DateFilterForm['dateFrom']}}" type="text"> <a class="button add-on" href="#"><i class="icon-calendar"></i></a>
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
                                <a href="{{route('backend.user.statistics', ['filter' => 'today'])}}">for today</a>
                            </li>
                            <li>
                                <a href="{{route('backend.user.statistics', ['filter' => 'yesterday'])}}">for yesterday</a>
                            </li>
                            <li>
                                <a href="{{route('backend.user.statistics', ['filter' => 'week'])}}">this week</a>
                            </li>
                            <li>
                                <a href="{{route('backend.user.statistics', ['filter' => 'month'])}}">this month</a>
                            </li>
                        </ul>
                    </div>
                    <a class="btn" href="{{route('backend.user.statistics')}}">Reset</a>
                    <button type="button" id="print-button" onclick="printContent()" class="btn"><i class="icon icon-print"></i></button>
                </div>
            </div>
        </form>
        <div class="table-wrapper">
            <table id="table-accounts" class="table table-bordered table-hover">
                <tbody>
                    <tr style="background: #eee">
                        <th>
                            <div role="columnheader" class="webix_hcell">Login</div>
                        </th>
                        <th>
                            <div role="columnheader" class="webix_hcell">Type</div>
                        </th>
                        <th>
                            <div role="columnheader" class="webix_hcell">Balance</div>
                        </th>
                        <th>
                            <div role="columnheader" class="webix_hcell">Deposits</div>
                        </th>
                        <th>
                            <div role="columnheader" class="webix_hcell">Redeems</div>
                        </th>
                        <th>
                            <div role="columnheader" class="webix_hcell">Profit</div>
                        </th>
                        <th>
                            <div role="columnheader" class="webix_hcell">PayOut %</div>
                        </th>
                    </tr>
                    @foreach ($users as $user)
                    @include('backend.user.partials.row_statistics')
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
@stop
@section('scripts')
<script>
    function expandChildren(id, hide) {
        var $table = $('#table-accounts');
        var children = $table.find('tr[data-parent="' + id + '"]');
        children.each(function(i, el) {
            var node = $(el);
            hide ? node.hide() : node.toggle();
            if (node.is(':hidden')) {
                node.find('.tree-minus').toggleClass('tree-minus tree-plus');
                expandChildren(node.data('id'), true);
            }
        });
        if (!hide) {
            $table.find('tr[data-id="' + id + '"]').find('[rel="tree-icon"]').toggleClass('tree-plus tree-minus');
        }
        return false;
    }


    $(function() {

        $('.tree_switch').click(function() {
            var data_id = $(this).data('id');
            return expandChildren(data_id);
        })

        $('.toggle-switch').click(function() {
            var data_id = $(this).data('id');
            var btn = this;
            var url = "{{route('backend.user.toggle', ':user')}}";
            url.replace(':user', data_id);
            jQuery.ajax({
                'type': 'POST',
                'data': {
                    'user_id': data_id,
                    "_token": "{{ csrf_token() }}"
                },
                'success': function(data) {
                    $(btn).attr('class', data == 'enabled' ? 'btn btn-mini btn-success' : 'btn btn-mini btn-warning');
                },

                'url': url,
                'cache': false
            });
        })

        $('.delete-user').click(function() {
            var data_id = $(this).data('id');
            var btn = this;

        })

        jQuery("#DateFilterForm_dateFrom").mask("99-99-9999");
        jQuery("#DateFilterForm_timeFrom").mask("99:99");
        jQuery("#DateFilterForm_dateTill").mask("99-99-9999");
        jQuery("#DateFilterForm_timeTill").mask("99:99");

        $('.date').datepicker({

        });
    });
</script>
@stop