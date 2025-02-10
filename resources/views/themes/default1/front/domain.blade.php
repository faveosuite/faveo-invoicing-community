<div class="modal fade" id="domain">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('message.enter_domain_new')}}</h4>
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                
            </div>
            <div class="modal-body">
                <!-- Form  -->
                {!! Form::open(['url'=>'checkout','method'=>'get','id'=>'domain1']) !!}
                
                @foreach($domain as $product)
                <?php
                $name =  App\Model\Product\Product::where('id', $product)->first()->name;
                ?>
                <div class="form-group {{ $errors->has('domain') ? 'has-error' : '' }}">
                    <!-- name -->
                    <b>{{$name}}</b>
                    {!! Form::label('domain',Lang::get('message.domain'),['class'=>'required']) !!}
                    {!! Form::text('domain['.$product.']', null, ['class' => 'form-control', 'id' => 'validDomain', 'required' => 'required', 'placeholder' => __('message.enter_domain_name')]) !!}
                           <h6 id ="domaincheck"></h6>
                </div>
                
                
                @endforeach
       
              
            </div>
            <div class="modal-footer">
                <button type="button" id="close" class="btn btn-default pull-left" data-dismiss="modal">{{ __('message.close')}}</button>
                <input type="submit" class="btn btn-primary" value="{{Lang::get('message.save')}}">
            </div>
             {!! Form::close()  !!}
        

            <!-- /Form -->
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->  
@section('script')
  <script type="text/javascript">

 $(document).ready(function(){
 $('#domain1').submit(function(){
      $('#domaincheck').hide();
   
   var domErr = true;
    function validdomaincheck(){

            var pattern = new RegExp(/^((?!-))(xn--)?[a-z0-9][a-z0-9-_]{0,61}[a-z0-9]{0,1}\.(xn--)?([a-z0-9\-]{1,61}|[a-z0-9-]{1,30}\.[a-z]{2,})$/);
            var ip_pattern = new RegExp(/^\b((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)(\.|$)){4}\b/);
            if (pattern.test($('#validDomain').val()) || ip_pattern.test($('#validDomain').val())) {
                 $('#domaincheck').hide();
                 $('#validDomain').css("border-color","");
                 return true;
               
              }
              else{
                 $('#domaincheck').show();
               $('#domaincheck').html("{{ __('message.enter_domain_form')}}");
                 $('#domaincheck').focus();
                  $('#validDomain').css("border-color","red");
                 $('#domaincheck').css({"color":"red","margin-top":"5px"});
                   domErr = false;
                    return false;
              
    }

   }
    validdomaincheck();
    if(validdomaincheck()){
        return true;
     }
     else{
        return false;
     }
       });
});
   


</script>
@stop