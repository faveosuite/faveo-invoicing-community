<a href="#renew" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#renew{{$id}}">Download</a>
<div class="modal fade" id="renew{{$id}}">
    <div class="modal-dialog">
        <div class="modal-content">
            {!! Form::open(['url'=>'client/renew/'.$id]) !!}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{{ __('message.renew') }}</h4>
            </div>
            <div class="modal-body">
                <!-- Form  -->
                
                <?php 


                // {{dump($product->name)}};
                // $plans = App\Model\Payment\Plan::pluck('name','id')->toArray();


                $userid = Auth::user()->id;
                ?>
                <div class="form-group {{ $errors->has('plan') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('plan','Plans',['class'=>'required']) !!}
                        {!! Form::select('plan',[''=>'Select','Plans'=>$plans],null,['class' => 'form-control','onchange'=>'getPrice(this.value)']) !!}
                        {!! Form::hidden('user',$userid) !!}
                    </div>

                   

                   <div class="form-group {{ $errors->has('payment_method') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('payment_method',Lang::get('message.payment-method'),['class'=>'required']) !!}
                        {!! Form::select('payment_method',[''=>'Select','cash'=>'Cash','check'=>'Check','online payment'=>'Online Payment','razorpay'=>'Razorpay'],null,['class' => 'form-control']) !!}

                    </div>
                     <div class="form-group {{ $errors->has('cost') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('cost',Lang::get('message.price'),['class'=>'required']) !!}
                        {!! Form::text('cost',null,['class' => 'form-control','id'=>'price']) !!}

                    </div>


            </div>
            <div class="modal-footer">
                <button type="button" id="close" class="btn btn-default pull-left" data-dismiss="modal">{{ __('message.close') }}</button>
                <input type="submit" class="btn btn-primary" value="{{Lang::get('message.save')}}">
                {!! Form::close()  !!}
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