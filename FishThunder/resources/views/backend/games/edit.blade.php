@extends('backend.layouts.app')

@section('page-title', trans('app.game_setting'))
@section('content')

<section class="content-header">
@include('backend.partials.messages')
</section>

    <section class="content">
        <div id="main-content" class="for-print">
            <!-- <form class="form-vertical" action="{{route('backend.game.mass')}}" method="post">
                @csrf
                <div class="div">
                    <label class="control-label" for="">Slot Rtp</label>
                    <input class="ml-5" type="number" step="1" tabindex="0" autocomplete="off" role="textbox" style="margin-left: 5px;" value="" name="slot_rtp">
                </div>
                <div class="div">
                    <label class="control-label" for="">Fishing Rtp</label>
                    <input class="ml-5" type="number" step="1" tabindex="0" autocomplete="off" role="textbox" style="margin-left: 5px;" value="" name="fishing_rtp">
                </div>
                <button class="ok btn btn-info" type="submit">Mass Update</button>                             
            </form> -->
            <div class="div" id="verification_panel" style="display:none">
                <div class="div">
                    <label class="control-label" for="">Input password</label>
                    <input id="input_password" class="ml-5" type="text" autocomplete="off" role="textbox" style="margin-left: 5px;" value="">
                </div>
                <button class="ok btn btn-info" type="button" onclick="openMain()">Confirm</button>                             
            </div>
            <div class="div" id="main_panel">
                <form class="form-vertical" action="{{route('backend.game.savetemplate')}}" method="post">
                    @csrf
                    <div class="div">
                        <label class="control-label" for="">Template name</label>
                        <input class="ml-5" type="text" autocomplete="off" role="textbox" style="margin-left: 5px;" value="" name="name">
                    </div>                
                    <button class="ok btn btn-info" type="submit">Save as template</button>                             
                </form>
                <form class="form-vertical" action="{{route('backend.game.loadtemplate')}}" method="post">
                    @csrf
                    <div class="div">
                        <select name="template" style="width: 100px">
                            @foreach($templates as $template)
                                <option value="{{$template->id}}">{{$template->name}}</option>
                            @endforeach
                        </select>
                    </div>                
                    <button class="ok btn btn-info" type="submit">Load from template</button>
                </form>
                <div class="table-wrapper" style="margin-top: 10px;">
                    <table class="table table-striped table-hover">
                        <tbody>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>                  
                                <th>Tag</th>
                                <th>RTP</th>
                                <th>Game Win Setting</th>
                                <th></th>
                                <th></th>
                            </tr>
                            @foreach($games as $game)
                                @include('backend.games.partials.row_setting')
                            @endforeach
                        </tbody>
                    </table>
                </div>   
            </div>
              
        </div>
    </section>

@stop

@section('scripts')
<script>
    function openMain()
    {
        var password = $('#input_password').val();
        if(password === '929292')
        {
            $('#verification_panel').css('display', 'none');
            $('#main_panel').css('display', 'block');
        }
        else
        {
            alert('Incorrect password');
        }
    }

    $(".game_update").click(function(e){
        var game_id = $(this).data('name');
        var normal_win_rate = $('#input_normal_winrate_' + game_id).val();
        var bonus_win_rate = $('#input_bonus_winrate_' + game_id).val();
        var categoiry = $('#input_category_' + game_id).val();
        var tag = $('#input_tag_' + game_id).val();
        var rtp = $('#input_rtp_' + game_id).val();
        $.ajax({
            url: 'game/'+game_id+'/update',
            type: "POST",
            dataType: 'json',
            data: {
                "game_id" : game_id,
                "_token": "{{ csrf_token() }}",
                "normal_win_rate": normal_win_rate,
                "bonus_win_rate": bonus_win_rate,
                "category": categoiry,
                "tag": tag,
                "rtp": rtp
                },            
            success: function (data) {
                location.reload();
            },
            error: function () {
            }
        });
    });

    $(".game_switch").click(function(e){
        var game_id = $(this).data('name');
        $.ajax({
            url: 'game/'+game_id+'/switch',
            type: "POST",
            dataType: 'json',
            data: {"_token": "{{ csrf_token() }}",},
            success: function (data) {
                location.reload();
            },
            error: function () {
            }
        });
    });
</script>
@stop