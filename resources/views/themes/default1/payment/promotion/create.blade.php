@extends('themes.default1.layouts.master')
@section('title')
Create Coupon
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.create_new_coupon') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('promotions')}}"><i class="fa fa-dashboard"></i> {{ __('message.all_coupons') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.create_new_coupon') }}</li>
        </ol>
    </div><!-- /.col -->


@stop

@section('content')

<div class="row">

    <div class="col-md-12">
        <div class="card card-secondary card-outline">


            <div class="card-body">
                {!! Form::open(['url'=>'promotions','id'=>'myform']) !!}


                <table class="table table-condensed">



                    <tr>

                        <td><b>{!! Form::label('company',Lang::get('message.code'),['class'=>'required']) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">

                                <div class='row'>
                                    <div class="col-md-6">
                                        <!-- {!! Form::text('code',null,['class' => 'form-control','id'=>'code']) !!} -->
                                        {!! Form::text('code', null, ['class' => 'form-control', 'id' => 'code', 'title' => __('message.generation_coupon_code')]) !!}
                                       <!--   <input id="code" name="code" type="text" class="form-control" title="Generate Coupon Code"/> -->
                                    </div>
                                    <div class="col-md-4">
                                        <a href="#" class="btn btn-primary" id="get-code"><i class="fa fa-refresh"></i>&nbsp;{{ __('message.generate_code') }}</a>
                                    </div>
                                </div>


                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td ><b>{!! Form::label('type',Lang::get('message.type'),['class'=>'required']) !!}</b></td>

                        <td>
                            <div class="form-group col-lg-6 {{ $errors->has('type') ? 'has-error' : '' }}">


                                {!! Form::select('type', ['' => __('message.select'), 'Types' => $type], null, ['class' => 'form-control', 'title' => __('message.type_of_coupon')]) !!}

                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! Form::label('value',Lang::get('message.value'),['class'=>'required']) !!} &nbsp;&nbsp;<i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="{{ __('message.enter_discount_amount') }}"></i></b></td>
                        <td>
                            <div class="form-group col-lg-6 {{ $errors->has('value') ? 'has-error' : '' }}">


                                {!! Form::text('value', null, ['class' => 'form-control', 'title' => __('message.value_of_coupon')]) !!}


                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! Form::label('uses',Lang::get('message.uses'),['class'=>'required']) !!} &nbsp;&nbsp;<i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="{!! __('message.enter_times_coupon') !!}"></i></b></td>
                        <td>
                            <div class="form-group col-lg-6 {{ $errors->has('uses') ? 'has-error' : '' }}">


                                {!! Form::text('uses', null, ['class' => 'form-control', 'title' => __('message.coupon_used')]) !!}


                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! Form::label('applied',Lang::get('message.applied'),['class'=>'required']) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('applied') ? 'has-error' : '' }}" style="width: 53%;">
                                

                                {!! Form::select('applied',[''=>'Choose','Products'=>$product],null,['class' => 'form-control select2 col-lg-18','data-live-search'=>'true','data-live-search-placeholder' => 'Search','data-dropup-auto'=>'false','data-size'=>'10','title'=> __('message.coupon_applied')]) !!}



                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! Form::label('start',Lang::get('message.start'),['class'=>'required']) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('start') ? 'has-error' : '' }}">
                                <div class="input-group date" id="startDate" data-target-input="nearest" style="width: 50%;">

                                    {!! Form::text('start', null, ['class' => 'form-control datetimepicker-input', 'title' => __('message.coupon_valid'), 'data-target' => '#startDate']) !!}

                                   
                                    <div class="input-group-append" data-target="#startDate" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>

                                </div>

                            </div>
                        </td>

                    </tr>


                    <tr>

                        <td><b>{!! Form::label('expiry',Lang::get('message.expiry'),['class'=>'required']) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('expiry') ? 'has-error' : '' }}">
                                <div class="input-group date" id="endDate" data-target-input="nearest" style="width: 50%;">

                                    {!! Form::text('expiry', null, ['class' => 'form-control datetimepicker-input', 'title' => __('message.coupon_expires'), 'data-target' => '#endDate']) !!}


                                    <div class="input-group-append" data-target="#endDate" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>

                                </div>


                            </div>
                        </td>

                    </tr>




                    {!! Form::close() !!}

                </table>

                <button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> {{ __('message.saving') }}"><i class="fa fa-save">&nbsp;</i>{!!Lang::get('message.save')!!}</button>


            </div>

        </div>

        <!-- /.box -->

    </div>


</div>


<script>
     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'coupon';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'coupon';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
</script>
<script>
   $(document).ready(function(){
            $(function () {
                //Initialize Select2 Elements
                $('.select2').select2()
            });
        })

        $('#get-code').on('click',function(){
            $.ajax({
            type: "GET",
            url: "{{url('get-promotion-code')}}",
            success: function (data) {
                console.log(data);
                $("#code").val(data)
            }
        });

        })
        

      $("#myform :input").tooltip({
 
      // place tooltip on the right edge
      position: "center right",
 
      // a little tweaking of the position
      offset: [-2, 10],
 
      // use the built-in fadeIn/fadeOut effect
      effect: "fade",
 
      // custom opacity setting
      opacity: 0.7
 
      });

  
</script>
@stop
@section('datepicker')
<script type="text/javascript">

    $('#startDate').datetimepicker({
        format: 'L'
    });
    $('#endDate').datetimepicker({
        format: 'L'
    });

    $(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();
});


</script>


@stop