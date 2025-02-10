@extends('themes.default1.layouts.front.master')
@section('title')
Pricing | Faveo Helpdesk
@stop
@section('page-heading')
 {{$headline}}
@stop
@section('breadcrumb')
@if(Auth::check())
        <li><a class="text-primary" href="{{url('my-invoices')}}">{{ __('message.home') }}</a></li>
    @else
         <li><a class="text-primary" href="{{url('login')}}">{{ __('message.home') }}</a></li>
    @endif
     <li class="active text-dark">{{ __('message.pricing') }}</li>
@stop
@section('main-class') 
main
@stop


@section('content')
<style>
        .planhide{
            display: none;
        }
      .highlight_batch {
        background: green;
        padding: 0px 5px;
        font-size: smaller;
        color: #FFF;
        border-radius: 16px;
        border-bottom-left-radius: 0;
        position: absolute;
        right: -20px;
        top: -25px;
    }
    .strike
    {
        text-decoration: line-through;
        font-weight: normal;
        font-size: 20px;
        color: #212529;
        font-weight: 300;
        display: flex;
        justify-content: center;
        align-items: flex-start;
    }

        .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
        }

        .switch input {display:none;}

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
        }

        input:checked + .slider {
            background-color: #2196F3;
        }

        input:focus + .slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked + .slider:before {
            -webkit-transform: translateX(26px);
            -ms-transform: translateX(26px);
            transform: translateX(26px);
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
          border-radius: 50%;
        }
        .scrollit {
            overflow:scroll;
            height:600px;
        }

        .cantact
        {
            font-size: 2.7 rem !important;
        }
        .sales
        {
            margin-top: -20px !important;
        }
        .subscription
        {
           display: block;
            width: 100%;
            text-align: center;
        }

         .stylePlan {
           width: auto;
           text-align: center;
          }

        .buttonsale{
            text-align: center;
            border-radius: 10px;
            padding-top: 11px;
        }
       .plan-features ul {
    list-style-type: none;
    padding: 0;
}

.plan-features li {
    position: relative;
    padding-left: 30px;
    margin-bottom: 10px;
}

.plan-features li::before {
    content: "\f00c";
    font-family: FontAwesome;
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 20px;
    height: 20px;
    background-color: black;
    border-radius: 50%;
    text-align: center;
    line-height: 20px;
    color: white;
}

.blue li::before{

    background-color: #099fdc;
}

.owl-carousel .owl-item img{
    display: unset;
    width: unset;
}
.plan-features p{
    margin-top: 100px !important;
}
.box-shadow-6:not(.box-shadow-hover) {
     box-shadow: unset;
}
.price.text-color-primary {
    font-size: 50px !important;
}

.center-templates {
    display: flex !important;
    justify-content: center !important;
}

/* Firefox-only CSS */
@-moz-document url-prefix() {
    /* Firefox-specific CSS rules here */
    .pricing-block .plan-price .price-label {
        margin-bottom: 20px;
    }
}


</style>

      <?php
     $templates = preg_replace(
        '/<span\s+class="price">Custom Pricing<\/span>/',
        '<span class="price cantact" >Custom Pricing</span>',
        $templates
    );
  ?>
<div class="row">

 <div class="col-md-12">




         @if($status && $status->status == "1")
         
            <div class="row mb-5">
            <div class="col text-center">
              <div class="d-flex justify-content-center align-items-center">
                <div class="text-3 p-relative bottom-7">{{ __('message.monthly') }}</div>
                <div class="px-2">
                  <label class="switch toggle_event_editing">

                    <input data-content-switcher data-content-switcher-content-id="pricingTable1" type="checkbox" id="kol" class="form-check-input checkbox" checked>
                    <span class="slider round"></span>
                  </label>
                  
                </div>
                <div class="text-3 p-relative bottom-7">{{ __('message.yearly') }}</div>
              </div>
            </div>
          </div>
          @endif
        <h4 style="text-align: center;">{{$tagline}} </h4>





        <div class="row mb-5 appear-animation" data-appear-animation="fadeInUpShorter" data-appear-animation-delay="100">
                    <div class="col">
                        <div class="owl-carousel nav-outside nav-arrows-1 custom-carousel-box-shadow-2 appear-animation" data-appear-animation="fadeInUpShorter" data-appear-animation-delay="750" data-plugin-options="{'responsive': {'0': {'items': 1}, '479': {'items': 1}, '768': {'items': 2}, '979': {'items': 3}, '1199': {'items': 3}}, 'autoplay': false, 'autoplayTimeout': 5000, 'autoplayHoverPause': true, 'dots': false, 'nav': true, 'loop': false, 'margin': 20, 'stagePadding': '75'}">
                             {!! html_entity_decode($templates) !!}


                          </div>
                      </div>
                  </div>

    </div>

</div>


<script>
  $(document).ready(function() {
    $('.toggle_event_editing input').on('change', function() {
      const toggleValue = $(this).prop('checked');
      if (toggleValue) {
        console.log('Toggle switch is selected.');
        handleSelectedState();
      } else {
        console.log('Toggle switch is unselected.');
        handleUnselectedState();
      }
    });
    function handleSelectedState() {
      const priceDisplay = $('#priceDisplay');
      const yearlyPrice = 'Yearly Price';
      priceDisplay.text(yearlyPrice);
      $.ajax({
        type: 'POST',
        url: "{{ url('store_toggle_state') }}",
        data: { toggleState: 'selected' },
        success: function(response) {
          console.log('Selected state value sent to the controller successfully.');
        },
        error: function(error) {
          console.error('Error sending selected state value to the controller:', error);
        }
      });
    }
    function handleUnselectedState() {
      const priceDisplay = $('#priceDisplay');
      const monthlyPrice = 'Monthly Price';
      priceDisplay.text(monthlyPrice);
      $.ajax({
        type: 'POST',
        url: "{{ url('store_toggle_state') }}",
        data: { toggleState: 'unselected' },
        success: function(response) {
          console.log('Unselected state value sent to the controller successfully.');
        },
        error: function(error) {
          console.error('Error sending unselected state value to the controller:', error);
        }
      });
    }
  });


$(document).ready(function() {
    var numberOfTemplates = $('.owl-carousel.nav-outside .owl-item').length;
    if (numberOfTemplates < 3) {
        $('.owl-carousel.nav-outside').addClass('center-templates');
    }
});

  document.addEventListener("DOMContentLoaded", function () {
      document.querySelectorAll(".content-switcher").forEach(function (switcher) {
          const card = switcher.closest(".card");
          const priceElement = card.querySelector(".price");
          const priceUnit = priceElement.querySelector(".price-unit");
          const stylePlanSelect = card.querySelector(".stylePlan");

          if (stylePlanSelect && priceUnit) {
              stylePlanSelect.value = priceUnit.id;

              // Listen for change event on the select element
              stylePlanSelect.addEventListener("change", function () {
                  const selectedOption = this.options[this.selectedIndex];
                  const newPrice = selectedOption.getAttribute("data-price");
                  const newCurrency = selectedOption.textContent.trim().charAt(0);

                  if (priceUnit && newPrice) {
                      priceUnit.textContent = newCurrency;
                      priceUnit.nextSibling.textContent = newPrice;
                  }
              });
          }
      });
  });



</script>
@stop



