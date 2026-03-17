<tr>
    <td>
        {{$game->name}}
    </td>
    <td>{{$game->betcount}}</td>
    <td>{{number_format($game->bet, 2)}}</td>
    <td>{{number_format($game->win, 2)}}</td>
    <td>{{number_format($game->bet - $game->win, 2)}}</td>
    <td>{{$game->bet == 0 ? '---' : number_format($game->win / $game->bet * 100, 2)}}</td>
</tr>