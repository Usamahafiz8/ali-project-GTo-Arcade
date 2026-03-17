<tr>
    <td>
        <span class="label label-inverse">{{$gamelog->name}}</span>
    </td>    
    <td>{{$gamelog->account}}</td>
    <td>{{$gamelog->game}}</td>
    <td style="text-align: right;">
        <span class="badge badge-success" style="text-align: center; width: 90%;">
            {{number_format($gamelog->bet, 2)}} </span>
    </td>
    <td style="text-align: right;">
        <span class="badge badge-success" style="text-align: center; width: 90%;">
            {{number_format($gamelog->win, 2)}} </span>
    </td>        
    <td style="text-align: right;">{{$gamelog->balance - $gamelog->win + $gamelog->bet}}</td>
    <td style="text-align: right;">{{$gamelog->balance}}</td>    
    <td class="">
        {{$gamelog->date_time}} </td>    
    <td class="print">
    </td>
</tr>