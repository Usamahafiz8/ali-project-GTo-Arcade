<tr>
    <td>
        {{number_format($report->bet, 2)}}
    </td>
    <td>
        {{number_format($report->win, 2)}}
    </td>
    <td>
        {{number_format($report->bet - $report->win, 2)}}
    </td>
    <td>
        {{$report->bet == 0 ? '0.00' : number_format($report->win / $report->bet * 100, 2)}}
    </td>    
    <td>
        {{$report->date}}
    </td>
</tr>