<a data-id="{{$paymentId}}" href="#credit" class="btn btn-sm btn-secondary btn-xs open-createtenancyDialog" data-toggle="tooltip" title="{{ __('message.credit_activity')}}">
    <i class="fa fa-history" style='color:white;'></i>
</a>

<div class="modal fade" id="credit" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('message.credit_balance_history')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="list-group">
                    @php
                        $payment_activity=\DB::table('credit_activity')->where('payment_id',$paymentId)->where('role','admin')->orderBy('created_at', 'desc')->get();
                    @endphp
                    @if(!$payment_activity->isEmpty())
                    @foreach($payment_activity as $activity)
                        <li class="list-group-item">
                            {!! getDateHtml($activity->created_at) !!}
                            <br>
                            {!! $activity->text !!}
                            <br>
                        </li>
                    @endforeach
                    @else
                        <li class="list-group-item" style="text-align: center">{{ __('message.no_activity_credit')}}</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).on("click", ".open-createtenancyDialog", function () {
        $('#credit').modal('show');
    });
</script>
<style>
    /* Custom styles */
    .btn-credit {
        font-size: 14px;
        padding: 5px 10px;
    }
    .modal-dialog {
        max-width: 600px;
    }
    .modal-content {
        border: none;
        border-radius: 10px;
    }
    .modal-header {
        border-bottom: none;
    }
    .modal-title {
        font-size: 18px;
    }
    .modal-body {
        max-height: 400px;
        overflow-y: auto;
    }
    .list-group-item {
        border: none;
        padding: 8px 15px;
        margin-bottom: 5px; /* Add margin between list items */
        background-color: #f8f9fa; /* Light gray background */
        border-radius: 5px; /* Rounded corners */
        box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.1); /* Subtle shadow */
    }

</style>