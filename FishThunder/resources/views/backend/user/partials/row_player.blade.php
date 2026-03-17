<tr rel="account" data-id="{{$user->username}}" data-confirm-redeem="0">
    <td>
        <span class="label label-inverse">{{$user->first_name}}</span>
    </td>
    <td>
        <span class="copy-to-clipboard-btn" style="margin-left: -10px;" data-text="{{$user->username}}">
            ��<span>��</span>
        </span>
    </td>    
    <td class="">{{$user->created_at}}</td>
    <td>{{$user->username}}</td>
    <td>
        <div class="pull-right">
            <code rel="balance">{{$user->balance}}</code>            
        </div>
    </td>
    <td style="text-align: center;">
        <span rel="online" class="label {{$user->status == 'online' ? 'btn-success' : 'btn-danger'}}">{{$user->status}}</span>
    </td>
    <td style="text-align: center;">
        <a class="btn btn-mini btn-round btn-danger force-logout" data-id={{$user->id}}><i class="icon icon-white icon-home"></i></a>
    </td>  
    <td style="text-align: center;">
        <div class="btn-group">
            <button rel="deposite" data-target="#modal-deposite" data-toggle="modal" class="btn btn-mini btn-success" onclick="
				$('#modal-deposite-id').val({{$user->id}});
				$('#modal-deposite-code').html('{{$user->username}}');
			" name="yt7" type="button">Deposit</button>
            <button rel="withdrawal" data-target="#modal-withdrawal" data-toggle="modal" class="btn btn-mini btn-danger" onclick="
				$('#modal-withdrawal-id').val({{$user->id}});
				$('#modal-withdrawal-code').html('{{$user->username}}');
				$('#modal-reedem-available').html('Available: {{$user->balance}}');                
			" name="yt8" type="button">Redeem</button>
        </div>
    </td>    
    <td style="text-align: center;">
        <a class="btn btn-mini btn-round {{$user->is_blocked == 0 ? 'btn-success' : 'btn-danger'}} toggle-switch" data-id={{$user->id}}><i class="icon icon-white icon-off"></i></a>
    </td>    
    <td style="text-align: center;">
        <button class="btn btn-mini btn-round user-setting" data-target="#modal-usersetting" data-toggle="modal" onclick="
            $('#profile-username').val('{{$user->username}}');
            $('#modal-profile-user-id').val('{{$user->id}}');
        "><i class="icon icon-user"></i></button>
    </td>
    <td style="text-align: center;">
        <a href="{{route('backend.user.player_history', ['DateFilterForm[search]' => $user->username])}}"><i class="icon-calendar"></i></a>
    </td>
    <td style="text-align: center;">
        <a target="_blank" href="{{route('backend.user.gamelogs', ['DateFilterForm[search]' => $user->username])}}"><i class="icon-list"></i></a>
    </td>
</tr>