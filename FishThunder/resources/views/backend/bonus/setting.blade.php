@extends('backend.layouts.app', ['class' => 'dark-version'])

@section('page-title', trans('app.users'))
@section('page-heading', trans('app.users'))

@section('content')

<div class="container-fluid mt--6">
    <section class="content-header">
        @include('backend.partials.messages')
    </section>
    <div class="row">
        <div class="col-12">
            <div class="card p-3">                
                <h3 class="mb-0">Wheel Bonus Values</h3>
                <div class="table-responsive">
                    <div>
                        <table id="table-accounts" class="table align-items-center table-flush">
                            <thead>
                                <tr>
                                    <th>Index</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $i = 1; @endphp
                                @foreach ($wheel_values as $row)
                                    @include('backend.bonus.partials.row_setting')
                                    @php $i++; @endphp
                                @endforeach                                
                            </tbody>
                        </table>
                    </div>
                </div>                
            </div>
        </div>
    </div>
    <td><button class="ok btn btn-info bonus_update" type="button">Update</button></td>    
</div>

@stop

@section('scripts')
<script>
    $(".bonus_update").click(function(e)
    {        
        var values = [];
        for(var i = 1; i <= 30; i++)
        {
            var amount = $('#input_amount_' + i).val();
            values.push(amount);
        }
       
        $.ajax({
            url: '/bonus/update',
            type: "POST",
            dataType: 'json',
            data: {
                "_token": "{{ csrf_token() }}",
                "values": values,
                },            
            success: function (data) {
                location.reload();
            },
            error: function () {
            }
        });
    });
</script>
@stop