<tr>
	<td>{{ $jackpot->name }}</td>
    <td>{{ number_format($jackpot->balance, 2) }}</td>
	<td><input class="ml-5" type="number" step=".01" tabindex="0" autocomplete="off" role="textbox" value="{{ $jackpot->start_balance }}" name="{{$jackpot->name}}[start_balance]"></td>
	<td><input class="ml-5" type="number" step=".01" tabindex="0" autocomplete="off" role="textbox" value="{{ $jackpot->start_payout }}" name="{{$jackpot->name}}[start_payout]"></td>
	<td><input class="ml-5" type="number" step=".01" tabindex="0" autocomplete="off" role="textbox" value="{{ $jackpot->end_payout }}" name="{{$jackpot->name}}[end_payout]"></td>	
    <td>{{ $jackpot->pay_sum }}</td>
    <td><button class="ok btn btn-info jp_regenerate" type="button" data-name="{{$jackpot->name}}">Regenerate</button></td>
</tr>
