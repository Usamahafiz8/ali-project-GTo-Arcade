<tr>
    <td>
        {{$user->username}}
    </td>
    <td>
        {{$user->created_at}}
    </td>
    <td>
        {{$user->last_online}}
    </td>
    <td style="text-align: center;">
        <div class="btn-group">
            <a title="Edit" class="btn btn-mini btn-info" href="{{route('backend.user.cashier_edit', $user->id)}}">
                <i class="icon-pencil icon-white icon-wide"></i>
            </a> 
            <a title="Enable/Disable" data-id="{{$user->id}}" class="btn btn-mini toggle-switch {{$user->is_blocked? 'btn-warning' : 'btn-success'}}" href="#">
                <i class="icon-off icon-white"></i>
            </a>
        </div>
    </td>
</tr>