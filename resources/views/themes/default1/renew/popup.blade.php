<a href="#renew" <?php if(\Cart::getContent()->isNotEmpty()) {?> class="btn btn-light-scale-2 btn-sm text-dark" data-toggle="tooltip" style="font-weight:500;" data-placement="top" title="{{ __('message.renew_product') }}" onclick="return false" <?php } else {?> class="btn btn-light-scale-2 btn-sm text-dark" <?php } ?> data-toggle="modal" data-target="#renew{{$id}}"><i class="fa fa-refresh" @if( \Cart::getContent()->isEmpty()) data-toggle="tooltip" title="{{ __('message.click_renew') }}" @endif></i>&nbsp;</a>
<div class="modal fade" id="renew{{$id}}" tabindex="-1" role="dialog" aria-labelledby="renewModalLabel" aria-hidden="true">

                            <div class="modal-dialog">
                                 {!! Form::open(['url'=>'client/renew/'.$id]) !!}

                                <div class="modal-content">

                                    <div class="modal-header">

                                        <h4 class="modal-title" id="renewModalLabel">{{ __('message.renew_your_order') }}</h4>

                                        <button type="button" class="btn-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                    </div>

                                    <div class="modal-body">

                                      
                                         <p class="text-black"><strong>{{ __('message.current_no_agents') }}</strong> {{$agents}}</p>

                                            <p class="text-black"><strong>{{ __('message.current_plan') }}</strong> {{$planName}}</p>
                                                    <?php

                                          $plans = App\Model\Payment\Plan::join('products', 'plans.product', '=', 'products.id')
                                                  ->leftJoin('plan_prices','plans.id','=','plan_prices.plan_id')
                                                  ->where('plans.product',$productid)
                                                  ->where('plan_prices.renew_price','!=','0')
                                                  ->pluck('plans.name', 'plans.id')
                                                   ->toArray();

                                            $planIds = array_keys($plans);

                                            $countryids = \App\Model\Common\Country::where('country_code_char2', \Auth::user()->country)->first();

                                            $renewalPrices = \App\Model\Payment\PlanPrice::whereIn('plan_id', $planIds)
                                                ->where('country_id',$countryids->country_id)
                                                ->where('currency',getCurrencyForClient(\Auth::user()->country))
                                                ->latest()
                                                ->pluck('renew_price', 'plan_id')
                                                ->toArray();

                                            if(empty($renewalPrices)){
                                                $renewalPrices = \App\Model\Payment\PlanPrice::whereIn('plan_id', $planIds)
                                                    ->where('country_id',0)
                                                    ->where('currency',getCurrencyForClient(\Auth::user()->country))
                                                    ->latest()
                                                    ->pluck('renew_price', 'plan_id')
                                                    ->toArray();
                                            }

                                            foreach ($plans as $planId => $planName) {
                                                if (isset($renewalPrices[$planId])) {
                                                    if(in_array($productid,cloudPopupProducts())) {
                                                        $plans[$planId] .= " (Renewal price-per agent: " . currencyFormat($renewalPrices[$planId], getCurrencyForClient(\Auth::user()->country), true) . ")";
                                                    }
                                                    else{
                                                        $plans[$planId] .= " (Renewal price: " . currencyFormat($renewalPrices[$planId], getCurrencyForClient(\Auth::user()->country), true) . ")";
                                                    }
                                                }
                                            }
                                          //add more cloud ids until we have a generic way to differentiate
                                          if(in_array($productid,cloudPopupProducts())){
                                              $plans = array_filter($plans, function ($value) {
                                                  return stripos($value, 'free') === false;
                                              });
                                          }
                                            // $plans = App\Model\Payment\Plan::where('product',$productid)->pluck('name','id')->toArray();
                                            $userid = Auth::user()->id;
                                            ?>

                                            <div class="row">
                                                <div class="form-group col">
                                                    <label class="form-label">Plans <span class="text-danger"> *</span></label>
                                                    <div class="custom-select-1">
                                                            @if($agents == 'Unlimited')
                                                                {!! Form::select('plan',['' => 'Select'] + $plans, null, ['class' => 'form-control plan-dropdown', 'onchange' => 'fetchPlanCost(this.value)',]) !!}
                                                            @else
                                                                {!! Form::select('plan',['' => 'Select'] + $plans, null, ['class' => 'form-control plan-dropdown', 'onchange' => 'fetchPlanCost(this.value, ' . $agents . ')',]) !!}
                                                            @endif
                                                            {!! Form::hidden('user',$userid) !!}
                                                    </div>
                                                </div>
                                            </div>
                                            @if(in_array($productid,cloudPopupProducts()))

                                            <div class="row">
                                                <div class="form-group col">
                                                    <label class="form-label">{{ __('message.agents') }} <span class="text-danger"> *</span></label>
                                                    <div class="custom-select-1">
                                                         {!! Form::number('agents', $agents, ['class' => 'form-control agents', 'id' => 'agents','min' => '1', 'placeholder' => '']) !!}
                                                    </div>
                                                </div>
                                            </div>
                                            @endif

                                            <p class="text-black"><strong>{{ __('message.price_to_be_paid') }}</strong><span id="price" class="price"></span></p>
                                            
                                            

                                    
                                    </div>
                                        <div class="loader-wrapper" style="display: none; background: white;" >
                                        <i class="fas fa-spinner fa-spin" style="font-size: 40px;"></i>
                    
                                    </div>
                     

                                    <div class="modal-footer">

                                        <button type="button" class="btn btn-light closebutton" id="closebutton" data-dismiss="modal">{{ __('message.close') }}</button>

                                        <button type="submit" class="btn btn-primary" data-bs-dismiss="modal" id="saveRenew">{{ __('message.renew') }}</button>
                                    </div>
                                 
                                </div>
                                 {!! Form::close()  !!} 
                            </div>
                        </div>
  
<script>


        $('.closebutton').on('click', function () {
            location.reload();
        });
        var shouldFetchPlanCost = true; // Disable further calls until needed

   function fetchPlanCost(planId,agents=null) {
       // Show the loader and disable modal body
       // Show the loader and disable modal body
       $('.loader-wrapper').show();
       $('.overlay').show(); // Show the overlay
       $('.modal-body').css('pointer-events', 'none');

       if(!shouldFetchPlanCost){
           return
       }
       var user = document.getElementsByName('user')[0].value;
       shouldFetchPlanCost = false;
       agents=$('.agents').val();

       $.ajax({
           type: "get",
           url: "{{ url('get-renew-cost') }}",
           data: { 'user': user, 'plan': planId },
           success: function (data) {
               if(data[1]) {
                   var totalPrice = agents * parseFloat(data[0]);
               }
               else{
                   var totalPrice = parseFloat(data[0])
               }
               var someprice = totalPrice.toFixed(2);
               $.ajax({
                   url: 'processFormat', // Update with the correct URL
                   method: 'GET',
                   data: { totalPrice: someprice },
                   success: function (data) {
                       $('.price').text(data);
                       shouldFetchPlanCost = true;
                   },
               });
               $('.loader-wrapper').hide();
               $('.overlay').hide(); // Hide the overlay
               $('.modal-body').css('pointer-events', 'auto');
               $('#saveRenew').attr('disabled', false);
               $("#agents").prop("disabled", false);
           }
       });
   }

        // Call the fetchPlanCost function when the plan dropdown selection changes
        $('#plan').on('change', function () {
            var selectedPlanId = $(this).val(); // Get the selected plan ID
            var agts = $('.agents').val();
            fetchPlanCost(selectedPlanId,agts); // Call the function to fetch plan cost
        });

        // Call the fetchPlanCost function initially with the default selected plan
        $(document).ready(function () {
            var initialPlanId = $('#plan').val();
            var agt = $('.agents').val();
            fetchPlanCost(initialPlanId,agt);
        });

        // Prevent form submission when Enter key is pressed
        $('form').on('keypress', function (e) {
            if (e.keyCode === 13) {
                e.preventDefault();
            }
        });

        $(document).ready(function () {
        $('.agents').on('input', function () {
            $("#agents").prop("disabled", true);
            var selectedPlanId = $('#renew{{$id}}').find('.plan-dropdown').val();
            if (selectedPlanId) {
                fetchPlanCost(selectedPlanId,$(this).val());
                $("#agents").prop("disabled", false);

            }
            $("#agents").prop("disabled", false);
        });

    });

</script>
<style>
    .loader-wrapper {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 500px;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: transparent;
        z-index: 9998; /* Below the loader */
    }


</style>
