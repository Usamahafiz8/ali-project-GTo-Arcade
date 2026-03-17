<tr>
	<td >{{ $game->name }}</td>
    <td><input id="{{'input_category_' . $game->id}}" type="number" step="1" tabindex="0" autocomplete="off" role="textbox" value="{{ $game->category_temp }}" name="{{$game->id}}" style="width:50px"></td>	    
    <td>
        <select name="tag" id="{{'input_tag_' . $game->id}}" style="width: 100px">
            <option value="0" {{$game->tag == 0 ? 'selected' : ''}}>None</option>
            <option value="1" {{$game->tag == 1 ? 'selected' : ''}}>Hot</option>
            <option value="2" {{$game->tag == 2 ? 'selected' : ''}}>New</option>
        </select>
        
    </td>
    <td><input id="{{'input_rtp_' . $game->id}}"  type="number" step="1" tabindex="0" autocomplete="off" role="textbox" value="{{ $game->rtp }}" name="{{$game->id}}" style="width:50px"></td>
    <td><a class="ok btn btn-info game_win_setting" type="button" href="{{route('backend.game.win_setting', ['game' => $game->id])}}">Edit</a></td>
    <td><button class="ok btn btn-info game_update" type="button" data-name="{{$game->id}}" style="width: 100px">Update</button></td>
    <td><button class="ok btn {{ $game->view == 1 ? 'btn-danger' : 'btn-info'}} game_switch" type="button" style="width: 100px" data-name="{{$game->id}}">{{$game->view == 1 ? 'Deactivate' : 'Activate'}}</button></td>
</tr>
