<tr>
    <td>
        {{$report->created_at}}
    </td>
    <td>{{number_format($report->bet, 2)}}</td>
    <td>{{number_format($report->win, 2)}}</td>    
    <td>{{$report->bet == 0 ? '---' : number_format($report->win / $report->bet * 100, 2)}}</td>
</tr>