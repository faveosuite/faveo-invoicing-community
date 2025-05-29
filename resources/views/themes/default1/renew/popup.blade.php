<a href="#renew" <?php if(\Cart::getContent()->isNotEmpty()) {?> class="btn btn-light-scale-2 btn-sm text-dark" data-toggle="tooltip" style="font-weight:500;" data-placement="top" title="{{ __('message.renew_product') }}" onclick="return false" <?php } else {?> class="btn btn-light-scale-2 btn-sm text-dark" <?php } ?> data-toggle="modal" data-target="#renew{{$id}}"><i class="fa fa-refresh" @if( \Cart::getContent()->isEmpty()) data-toggle="tooltip" title="{{ __('message.click_renew') }}" @endif></i>&nbsp;</a>
<div class="modal fade" id="renew{{$id}}" tabindex="-1" role="dialog" aria-labelledby="renewModalLabel" aria-hidden="true">

                            <div class="modal-dialog">
                                {!! html()->form('POST', url('client/renew/'.$id))->attribute('data-form-id', $id)->open() !!}

                                <div class="modal-content">

                                    <div class="modal-header">

                                        <h4 class="modal-title" id="renewModalLabel">{{ __('message.renew_your_order') }}</h4>

                                        <button type="button" class="btn-close closebutton" data-dismiss="modal"  aria-hidden="true">&times;</button>
                                    </div>

                                    <div class="modal-body">

                                      
                                         <p class="text-black"><strong>{{ __('message.current_no_agents') }}</strong> {{$agents}}</p>
                                        <input type="hidden" id="agentsForSelf" value="{{ $agents }}">


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
                                                    <label class="form-label">{{ __('message.plans') }} <span class="text-danger"> *</span></label>
                                                    <div class="custom-select-1">
                                                        @if($agents == 'Unlimited')
                                                            {!! html()->select('plan')->options(['' => 'Select'] + $plans)->class('form-control plan-dropdown')->attribute('onchange', 'fetchPlanCost(this.value)')->id("plan$id") !!}
                                                        @else
                                                            {!! html()->select('plan')->options(['' => 'Select'] + $plans)->class('form-control plan-dropdown')->attribute('onchange', 'fetchPlanCost(this.value, ' . $agents . ')')->id("plan$id") !!}
                                                        @endif

                                                        {!! html()->hidden('user', $userid) !!}
                                                    </div>
                                                </div>
                                            </div>
                                        @php
                                           $isAgentAllowed = in_array($productid,cloudPopupProducts());
                                        @endphp
                                            @if($isAgentAllowed)

                                            <div class="row">
                                                <div class="form-group col">
                                                    <label class="form-label">{{ __('message.agents') }} <span class="text-danger"> *</span></label>
                                                    <div class="custom-select-1">
                                                        {!! html()->number('agents', $agents)->class('form-control agents')->id('agents'.$id)->attribute('min', 1)->placeholder('') !!}
                                                    </div>
                                                </div>
                                            </div>
                                            @endif

                                            <p class="text-black"><strong>{{ __('message.price_to_be_paid') }}</strong><span id="price" class="price"></span></p>
                                            
                                            

                                    
                                    </div>
                                        <div class="loader-wrapper" style="display: none; background: white;" >
                                        <i class="fas fa-spinner fa-spin" style="font-size: 40px;"></i>
                    
                                    </div>
                     

                                        <div class="modal-footer d-flex justify-content-between">
                                            <button type="button" class="btn btn-light closebutton" id="closebutton" data-dismiss="modal">{{ __('message.close') }}</button>
                                            <button type="submit" class="btn btn-primary" data-bs-dismiss="modal" id="{{$id}}">{{ __('message.renew') }}</button>
                                        </div>
                                </div>
                                 {!! html()->form()->close()  !!}
                            </div>
                        </div>
<script>
    $(document).ready(function () {
        const formSelector = 'form[data-form-id]';

        $(document).on('submit', formSelector, function (e) {
            e.preventDefault();

            const form = $(this);
            const formId = form.data('form-id');

            const userFields = {
                planname: form.find(`#plan${formId}`),
            };

            const userRequiredFields = {
                planname: @json(trans('message.plan_renew')),
            };

            @if(in_array($productid, cloudPopupProducts()))
                userFields.planproduct = agentsField;
            userRequiredFields.planproduct = @json(trans('message.agents-error-message'));
            @endif

            Object.values(userFields).forEach(field => {
                field.removeClass('is-invalid');
                field.siblings('.error').remove();
            });

            let isValid = true;

            const showError = (field, message) => {
                field.addClass('is-invalid');
                field.after(`<span class='error invalid-feedback'>${message}</span>`);
            };

            Object.keys(userFields).forEach(key => {
                if (!userFields[key].val()) {
                    showError(userFields[key], userRequiredFields[key]);
                    isValid = false;
                }
            });

            if (isValid) {
                form[0].submit(); // Use the DOM form submission to avoid re-triggering jQuery event
            }
        });

        ['plan{{$id}}', 'agents{{$id}}'].forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener('input', function () {
                    this.classList.remove('is-invalid');
                    const nextError = this.nextElementSibling;
                    if (nextError && nextError.classList.contains('error')) {
                        nextError.remove();
                    }
                });
            }
        });

        $('.closebutton').on('click', function () {
            location.reload();
        });

        let shouldFetchPlanCost = true;

        // 🔧 Fix: Define as a global function
        window.fetchPlanCost = function (planId, agents = null) {
            if (!shouldFetchPlanCost) return;

            shouldFetchPlanCost = false;

            const user = $('[name="user"]').val();
            agents =  agents ?? $('#agents{{ $id }}').val();

            $('.loader-wrapper').show();
            $('.overlay').show();
            $('.modal-body').css('pointer-events', 'none');

            $.ajax({
                type: "GET",
                url: "{{ url('get-renew-cost') }}",
                data: { user, plan: planId },
                success: function (data) {
                    let totalPrice = 0;
                    if (data[1]) {
                        agents = agents || $('#agentsForSelf').val();
                        totalPrice = agents * parseFloat(data[0]);
                    } else {
                        totalPrice = parseFloat(data[0]);
                    }

                    const formattedPrice = totalPrice.toFixed(2);

                    $.ajax({
                        url: 'processFormat',
                        method: 'GET',
                        data: { totalPrice: formattedPrice },
                        success: function (data) {
                            $('.price').text(data);
                            shouldFetchPlanCost = true;
                        },
                        complete: function () {
                            $('.loader-wrapper').hide();
                            $('.overlay').hide();
                            $('.modal-body').css('pointer-events', 'auto');
                            $('#saveRenew').prop('disabled', false);
                            $('.agents').prop('disabled', false);
                        }
                    });
                },
                error: function () {
                    shouldFetchPlanCost = true;
                    $('.loader-wrapper').hide();
                    $('.overlay').hide();
                    $('.modal-body').css('pointer-events', 'auto');
                    alert("Failed to fetch cost. Please try again.");
                }
            });
        };

        $('#plan').on('change', function () {
            const selectedPlanId = $(this).val();
            const agts = $('.agents').val();
            fetchPlanCost(selectedPlanId, agts);
        });

        $('.agents').on('input', function () {
            $(this).prop('disabled', true);
            const selectedPlanId = $('#renew{{$id}} .plan-dropdown').val();
            if (selectedPlanId) {
                fetchPlanCost(selectedPlanId, $(this).val());
            }
            $(this).prop('disabled', false);
        });

        const initialPlanId = $('#plan').val();
        const initialAgents = $('.agents').val();
        fetchPlanCost(initialPlanId, initialAgents);

        $('form').on('keypress', function (e) {
            if (e.keyCode === 13) {
                e.preventDefault();
            }
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
