<tr style="display: {{ empty($hierarchy[$user->id]) ? 'auto' : 'none'}}" data-id="{{$user->id}}" data-parent="{{$user->parent_id}}">
    <td>
        @if($direct_children[$user->id] > 0)
        <a data-id="{{$user->id}}" style="margin-left: {{count($hierarchy[$user->id]) * 15}}px;" href="#" class="tree_switch"><span class="tree-plus" rel="tree-icon"></span></a>{{ $user->username ?: trans('app.n_a') }}        
        @else
        <span class="tree-line" style="margin-left: {{count($hierarchy[$user->id]) * 15}}px;"></span> {{ $user->username ?: trans('app.n_a') }}
        @endif
    </td>
    <td>
        @if( $user->hasRole('agent'))
        <span class="label">Agent</span>
        @elseif($user->hasRole('distributor'))
        <span class="label">Distributor</span>
        @elseif($user->hasRole('manager'))
        <span class="label">Shop</span>
        @endif
    </td>
    <td class="">{{$user->created_at}}</td>
    <td class="">{{$user->last_online}}</td>
    <td style="text-align: right;">
        <code rel="balance">{{$user->balance}}</code>
    </td>
    <td style="text-align: center;">
        @if($user->parent_id == $current_user || auth()->user()->hasRole('admin'))        
        <div class="btn-group">
        <button data-target="#modal-deposite" data-toggle="modal" class="btn btn-mini btn-success" type="button" onclick="
			$('#modal-deposite-code').html('{{$user->username}}');
			$('#modal-deposite-id').val('{{ $user->role_id == 3 ? $user->shop_id : $user->id}}');
            $('#modal-deposite-available').html(`Available: {{ $parent->role_id == 6 ? 'unlimited' : $parent->balance}}`);
            $('#yw1').attr('action', '{{ $user->role_id == 3 ? route('backend.shop.balance') : route('backend.user.balance.update')}}');
			">Deposit</button>
        <button data-target="#modal-withdrawal" data-toggle="modal" class="btn btn-mini btn-danger" type="button" onclick="
				$('#modal-withdrawal-code').html('{{$user->username}}');
				$('#modal-withdrawal-id').val('{{ $user->role_id == 3 ? $user->shop_id : $user->id}}');
				$('#modal-reedem-available').html('Available: {{ $user->role_id == 3 ? \VanguardLTE\Shop::where('id',$user->shop_id)->get()[0]->balance : $user->balance}}');
                $('#yw2').attr('action', '{{ $user->role_id == 3 ? route('backend.shop.balance') : route('backend.user.balance.update')}}');
			">Reedem</button>
        </div>
        @endif
    </td>
    <td style="text-align: center;">
        <div class="btn-group">
            <a title="Edit" class="btn btn-mini btn-info" href="{{ $user->role_id == 3 ? route('backend.shop.edit', ['shop' => $user->shop_id]) : route('backend.user.edit', ['user' => $user->id])}}">
                <i class="icon-pencil icon-white icon-wide"></i>
            </a>
            <a data-id="{{$user->id}}" title="Enable/Disable" class="{{$user->is_blocked == 0 ? 'btn btn-mini btn-success' : 'btn btn-mini btn-warning'}} toggle-switch" href="#">
                <i class="icon-off icon-white"></i>
            </a>
            <!-- @if( auth()->user()->hasRole('admin'))
            <a title="Remove" class="btn btn-mini btn-danger delete-user" data-id="{{$user->id}}">
                <i class="icon-trash icon-white icon-wide"></i>
            </a>
            @endif -->
        <div>
    </td>    
</tr>
