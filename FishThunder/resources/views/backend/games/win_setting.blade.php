@extends('backend.layouts.app')

@section('page-title', trans('app.game_setting'))
@section('content')

<section class="content-header">
@include('backend.partials.messages')
</section>

    <section class="content">
        <div id="main-content" class="for-print">
            <form class="form-vertical" action="{{route('backend.game.store_win_setting')}}" method="post">
                @csrf
                <input type="hidden" id="id" value="{{$winsetting->id}}"/>
                <input type="hidden" id="game_id" value="{{$game->id}}"/>
                <div class="div">
                    <legend>{{$game->name}}</legend>
                </div>
                <div class="div">
                    <label class="control-label" for="">Basic small win counter</label>
                    <input class="ml-5" type="number" step="1" tabindex="0" autocomplete="off" role="textbox" style="margin-left: 5px;" value="{{$winsetting->bsc_min}}" id="bsc_min">
                    <input type="number" step="1" tabindex="0" autocomplete="off" role="textbox" style="margin-left: 5px;" value="{{$winsetting->bsc_max}}" id="bsc_max">
                </div>
                <div class="div">
                    <label class="control-label" for="">Basic small win (x times)</label>
                    <input class="ml-5" type="number" step="0.1" tabindex="0" autocomplete="off" role="textbox" style="margin-left: 5px;" value="{{$winsetting->bsw_min}}" id="bsw_min">
                    <input type="number" step="0.1" tabindex="0" autocomplete="off" role="textbox" style="margin-left: 5px;" value="{{$winsetting->bsw_max}}" id="bsw_max">
                </div>
                <div class="div">
                    <label class="control-label" for="">Basic big win counter</label>
                    <input class="ml-5" type="number" step="1" tabindex="0" autocomplete="off" role="textbox" style="margin-left: 5px;" value="{{$winsetting->bbc_min}}" id="bbc_min">
                    <input type="number" step="1" tabindex="0" autocomplete="off" role="textbox" style="margin-left: 5px;" value="{{$winsetting->bbc_max}}" id="bbc_max">
                </div>
                <div class="div">
                    <label class="control-label" for="">Basic big win (x times)</label>
                    <input class="ml-5" type="number" step="0.1" tabindex="0" autocomplete="off" role="textbox" style="margin-left: 5px;" value="{{$winsetting->bbw_min}}" id="bbw_min">
                    <input type="number" step="0.1" tabindex="0" autocomplete="off" role="textbox" style="margin-left: 5px;" value="{{$winsetting->bbw_max}}" id="bbw_max">
                </div>
                <div class="div">
                    <label class="control-label" for="">Freespin counter</label>
                    <input class="ml-5" type="number" step="1" tabindex="0" autocomplete="off" role="textbox" style="margin-left: 5px;" value="{{$winsetting->fc_min}}" id="fc_min">
                    <input type="number" step="1" tabindex="0" autocomplete="off" role="textbox" style="margin-left: 5px;" value="{{$winsetting->fc_max}}" id="fc_max">
                </div>
                <div class="div">
                    <label class="control-label" for="">Freespin win (x times)</label>
                    <input class="ml-5" type="number" step="0.1" tabindex="0" autocomplete="off" role="textbox" style="margin-left: 5px;" value="{{$winsetting->fw_min}}" id="fw_min">
                    <input type="number" step="0.1" tabindex="0" autocomplete="off" role="textbox" style="margin-left: 5px;" value="{{$winsetting->fw_max}}" id="fw_max">
                </div>
                <div class="div">
                    <label class="control-label" for="">Freespin bigwin count</label>
                    <input class="ml-5" type="number" step="0.1" tabindex="0" autocomplete="off" role="textbox" style="margin-left: 5px;" value="{{$winsetting->fw_bc_min}}" id="fw_bc_min">
                    <input type="number" step="0.1" tabindex="0" autocomplete="off" role="textbox" style="margin-left: 5px;" value="{{$winsetting->fw_bc_max}}" id="fw_bc_max">
                </div>
                <div class="div">
                    <label class="control-label" for="">Freespin bigwin</label>
                    <input class="ml-5" type="number" step="0.1" tabindex="0" autocomplete="off" role="textbox" style="margin-left: 5px;" value="{{$winsetting->fw_bw_min}}" id="fw_bw_min">
                    <input type="number" step="0.1" tabindex="0" autocomplete="off" role="textbox" style="margin-left: 5px;" value="{{$winsetting->fw_bw_max}}" id="fw_bw_max">
                </div>
                <button class="ok btn btn-info" type="button" onclick="saveSetting(0);">Apply</button>
                <button class="ok btn btn-info" type="button" onclick="saveSetting(1);">Apply to all Games</button>
            </form>
        </div>
    </section>

@stop

@section('scripts')
<script>
    function saveSetting(type)
    {
        var id = $("#id").val();
        var game_id = $("#game_id").val();
        var bsc_min = $("#bsc_min").val();
        var bsc_max = $("#bsc_max").val();
        var bsw_min = $("#bsw_min").val();
        var bsw_max = $("#bsw_max").val();
        var bbc_min = $("#bbc_min").val();
        var bbc_max = $("#bbc_max").val();
        var bbw_min = $("#bbw_min").val();
        var bbw_max = $("#bbw_max").val();
        var fc_min = $("#fc_min").val();
        var fc_max = $("#fc_max").val();
        var fw_min = $("#fw_min").val();
        var fw_max = $("#fw_max").val();
        var fw_bc_min = $("#fw_bc_min").val();
        var fw_bc_max = $("#fw_bc_max").val();
        var fw_bw_min = $("#fw_bw_min").val();
        var fw_bw_max = $("#fw_bw_max").val();

        $.ajax({
            url: '{{route("backend.game.store_win_setting")}}',
            type: "POST",
            dataType: 'json',
            data: {
                "_token": "{{ csrf_token() }}",
                id: id,
                game_id: game_id,
                bsc_min: bsc_min,
                bsc_max: bsc_max,
                bsw_min: bsw_min,
                bsw_max: bsw_max,
                bbc_min: bbc_min,
                bbc_max: bbc_max,
                bbw_min: bbw_min,
                bbw_max: bbw_max,
                fc_min: fc_min,
                fc_max: fc_max,
                fw_min: fw_min,
                fw_max: fw_max,
                fw_bc_min: fw_bc_min,
                fw_bc_max: fw_bc_max,
                fw_bw_min: fw_bw_min,
                fw_bw_max: fw_bw_max,
                type: type
                },            
            success: function (data) {
                alert("Settings applied succesfully.")
            },
            error: function () {
            }
        });
    }
</script>
@stop