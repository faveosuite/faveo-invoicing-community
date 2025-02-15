<a href="#renew" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#renew{{$id}}">Download</a>
<div class="modal fade" id="renew{{$id}}">
    <div class="modal-dialog">
        <div class="modal-content">
            {!! html()->form('POST', 'client/renew/' . $id)->open() !!}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Renew</h4>
            </div>
            <div class="modal-body">
                <!-- Form  -->
                
                <?php 


                // {{dump($product->name)}};
                // $plans = App\Model\Payment\Plan::pluck('name','id')->toArray();


                $userid = Auth::user()->id;
                ?>
                <div class="form-group {{ $errors->has('plan') ? 'has-error' : '' }}">
                    <!-- Plan -->
                    {!! html()->label('Plans', 'plan')->class('required') !!}
                    {!! html()->select('plan', ['' => 'Select', 'Plans' => $plans], null)->class('form-control')->attribute('onchange', 'getPrice(this.value)') !!}
                    {!! html()->hidden('user', $userid) !!}
                </div>

                <div class="form-group {{ $errors->has('payment_method') ? 'has-error' : '' }}">
                    <!-- Payment method -->
                    {!! html()->label(Lang::get('message.payment-method'), 'payment_method')->class('required') !!}
                    {!! html()->select('payment_method', ['' => 'Select', 'cash' => 'Cash', 'check' => 'Check', 'online payment' => 'Online Payment', 'razorpay' => 'Razorpay'], null)->class('form-control') !!}
                </div>

                <div class="form-group {{ $errors->has('cost') ? 'has-error' : '' }}">
                    <!-- Cost -->
                    {!! html()->label(Lang::get('message.price'), 'cost')->class('required') !!}
                    {!! html()->text('cost', null)->class('form-control')->id('price') !!}
                </div>


            </div>
            <div class="modal-footer">
                <button type="button" id="close" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
                <input type="submit" class="btn btn-primary" value="{{Lang::get('message.save')}}">
                {!! html()->form()->close()  !!}
            </div>
            <!-- /Form -->
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->  
  
<script>
    function getPrice(val) {
        
        var user = document.getElementsByName('user')[0].value;
        $.ajax({
            type: "get",
            url: "{{url('get-renew-cost')}}",
            data: {'user': user, 'plan': val},
            success: function (data) {
                var price = data
                $("#price").val(price);
            }
        });
    }
</script>