<tr rel="account" data-id="{{$user->username}}" data-confirm-redeem="0">
    <td>
        <span class="label label-inverse">{{$user->username}}</span>
    </td>
    <td>
        <span class="copy-to-clipboard-btn" style="margin-left: -10px;" data-text="{{$user->username}}">        
        </span>
    </td>    
    <td class="">{{$user->created_at}}</td>
    <td>{{$user->first_name}}</td>
    <td>
        <div class="pull-right">
            {{$user->balance}}
        </div>
    </td>
    <td style="text-align: center;">
        <span rel="online" class="badge {{$user->status == 'online' ? 'bagde-success' : 'badge-danger'}}">{{$user->status}}</span>
    </td>
    <td style="text-align: center;">
        <a class="btn btn-sm btn-danger btn-round force-logout" data-id={{$user->id}}>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-power" viewBox="0 0 16 16">
                <path d="M7.5 1v7h1V1h-1z"></path>
                <path d="M3 8.812a4.999 4.999 0 0 1 2.578-4.375l-.485-.874A6 6 0 1 0 11 3.616l-.501.865A5 5 0 1 1 3 8.812z"></path>
            </svg>
        </a>
    </td>  
    <td style="text-align: center;">
        <div class="btn-group">
            <button rel="deposite" data-target="#modal-deposite" data-toggle="modal" class="btn btn-sm btn-success" onclick="
				$('#modal-deposite-code').html('{{$user->username}}');
                $('#modal-deposite-id').val('{{ $user->hasRole('manager') ? $user->shop_id : $user->id}}');
                $('#modal-deposite-available').html('Available: {{$current_user->balance}}');
                $('#yw1').attr('action', '{{ $user->hasRole('manager') ? route('backend.shop.balance', ['user'=>$current_user]) : route('backend.user.balance.update', ['user'=>$current_user])}}');
			" type="button">Deposit</button>
            <button rel="withdrawal" data-target="#modal-withdrawal" data-toggle="modal" class="btn btn-sm btn-danger" onclick="
                $('#modal-withdrawal-code').html('{{$user->username}}');
                $('#modal-withdrawal-id').val('{{ $user->hasRole('manager') ? $user->shop_id : $user->id}}');
                $('#modal-reedem-available').html('Available: {{ $user->hasRole('manager') ? \VanguardLTE\Shop::where('id',$user->shop_id)->get()[0]->balance : $user->balance}}');
                $('#yw2').attr('action', '{{ $user->hasRole('manager') ? route('backend.shop.balance', ['user'=>$current_user]) : route('backend.user.balance.update', ['user'=>$current_user])}}');
			" type="button">Redeem</button>
        </div>
    </td>    
    <td style="text-align: center;">
        @if($user->hasRole('manager'))
        <a title="Edit" class="btn btn-sm btn-info" href="{{ route('backend.shop.edit', ['shop'=>$user->shop_id, 'user'=>$current_user]) }}">
            Edit
        </a>
        @else
        <button class="btn btn-sm btn-round user-setting btn-info" data-target="#modal-setting" data-toggle="modal" onclick="
            $('#profile-username').val('{{$user->username}}');
            $('#modal-profile-user-id').val('{{$user->id}}');
            $('#yw3').attr('action', '{{route('backend.user.updateprofile', ['user' => $current_user])}}');            
        ">Edit</button>
        @endif
        <button class="{{$user->is_blocked == 0 ? 'btn btn-sm btn-success' : 'btn btn-sm btn-danger'}}" data-id={{$user->id}} onclick="toggleUser();">Toggle</button>
        @if($user->role_id == 1)
        <a target="Game Logs" class="btn btn-sm btn-info" href="{{route('backend.user.gamelogs', ['DateFilterForm[search]' => $user->username])}}">Game Logs</a>
        <button class="btn btn-sm btn-danger force-logout" data-id={{$user->id}}>Logout</button>
        @endif        
        <a title="Transactions" class="btn btn-sm btn-info" href="{{route('backend.user.transactions').'/?DateFilterForm[search]='.$user->username}}">Transactions</a>
        <a target="Report" class="btn btn-sm btn-info" href="{{route('backend.user.report_daily').'/?search='.$user->username}}">Report</a>
    </td>
</tr>