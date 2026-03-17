@extends('backend.layouts.app')

@section('page-title', trans('app.edit_jpg'))


@section('content')

<section class="content-header">
@include('backend.partials.messages')
</section>

    <section class="content">
        <div id="main-content" class="for-print">
            <form class="form-horizontal form-vertical" action="{{route('backend.jp.update')}}" method="post">
                @csrf
                <div class="table-wrapper">
                    <table class="table table-striped table-hover">
                        <tbody>
                            <tr>
                                <th width="10%">LEVEL</th>
                                <th width="10%">Balance</th>
                                <th width="20%">STARTING VALUE</th>
                                <th width="20%">STARTING PAYOUT VALUE</th>                                
                                <th width="20%">END PAYOUT VALUE</th>     
                                <th width="10%">PAYSUM</th>
                                <th width="10%"></th>
                            </tr>
                            @foreach($jackpots as $jackpot)
                                @include('backend.jpg.partials.row_jackpot')
                            @endforeach
                        </tbody>
                    </table>                    
                </div>
                <label class="control-label" for="">Global Fee</label>
                <input class="ml-5" type="number" step=".01" tabindex="0" autocomplete="off" role="textbox" style="margin-left: 5px;" value="{{ $jackpots[0]->percent }}" name="percent">
                <button class="ok btn btn-info" type="submit">Save</button>
            </form>
        </div>
    </section>

@stop

@section('scripts')
<script>
    $(".jp_regenerate").click(function(e){
        var jackpot_name = $(this).data('name');        
        $.ajax({
            url: '/jp/regenerate',
            type: "POST",
            dataType: 'json',
            data: {'name' : jackpot_name,  "_token": "{{ csrf_token() }}",},            
            success: function (data) {
                location.reload();
            },
            error: function () {
            }
        });    
    });
</script>
@stop