<tr>
    <td class="" >
        {{$transaction->created_at}}</td>
    <td>
        <span class="badge {{($transaction->type == 'add' && $transaction->user_id == auth()->user()->id || $transaction->type == 'out' && $transaction->user_id != auth()->user()->id) ? 'badge-success' : 'badge-important'}}" style="text-align: center; width: 90%;">
            {{($transaction->type == 'add' && $transaction->user_id == auth()->user()->id || $transaction->type == 'out' && $transaction->user_id != auth()->user()->id) ? $transaction->sum : number_format(-$transaction->sum, 2)}}</span>
    </td>
    <td class="{{($transaction->type == 'add' && $transaction->user_id == auth()->user()->id || $transaction->type == 'out' && $transaction->user_id != auth()->user()->id) ? 'text-success' : 'text-warning'}}">
        {{$transaction->description}} </td>
    <td class="text-info" >
        {{$transaction->type == 'add'? $transaction->payeer. '→' .$transaction->receipt : $transaction->receipt. '→' .$transaction->payeer}} </td>
    <td class="text-info" style="text-align: right;">{{ $transaction->last_payeer_balance . '→' . $transaction->result_payeer_balance}} </td>
    <td class="text-info" style="text-align: right;">{{ $transaction->last_balance . '→' . $transaction->result_balance }} </td>
</tr>