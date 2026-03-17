<tr>
    <td>
        <span class="label label-inverse">{{$stat->account}}</span>
    </td>
    <td>{{$stat->name}}</td>
    <td style="text-align: right;">
        <span class="badge {{$stat->type == 'add' ? 'badge-success' : 'badge-important'}}" style="text-align: center; width: 90%;">
            {{$stat->type == 'add' ? $stat->sum : number_format(-$stat->sum, 2)}} </span>
    </td>
    <td class="{{$stat->type == 'add' ? 'text-success' : 'text-error'}}">
        {{$stat->description}} </td>
    <td>
        {{$stat->cashier}} </td>
    <td class="text-info" style="text-align: right;">
        {{$stat->last_balance . ' → ' . $stat->result_balance}} </td>
    <td class="">
        {{$stat->created_at}} </td>
    <td>
        <span class="">{{$stat->id}}</span>
    </td>
    <td class="print">
    </td>
</tr>