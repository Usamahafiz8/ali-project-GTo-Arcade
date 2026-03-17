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
        <span >Agent</span>       
        @elseif($user->hasRole('manager'))
        <span >Shop</span>
        @endif
    </td>
        
    <td style="text-align: right;">
        {{$user->balance}}
    </td>
    <td style="text-align: right;">
        {{$deposits[$user->id]}}
    </td>
    <td style="text-align: right;">
        {{$reedems[$user->id]}}
    </td>
    <td style="text-align: right;">
        {{$deposits[$user->id] - $reedems[$user->id]}}
    </td>
    <td style="text-align: right;">
        {{ $deposits[$user->id] > 0 ? number_format(($reedems[$user->id]) * 100 / $deposits[$user->id], 2) . '%' : '0'}}
    </td>
</tr>
