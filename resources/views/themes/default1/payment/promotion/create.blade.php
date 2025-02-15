@extends('themes.default1.layouts.master')
@section('title')
Create Coupon
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>Create New Coupon</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('promotions')}}"><i class="fa fa-dashboard"></i> All Coupons</a></li>
            <li class="breadcrumb-item active">Create New Coupon</li>
        </ol>
    </div><!-- /.col -->


@stop

@section('content')

<div class="row">

    <div class="col-md-12">
        <div class="card card-secondary card-outline">


            <div class="card-body">
                {!! html()->form('POST', url('promotions'))->id('myform')->open() !!}


                <table class="table table-condensed">



                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.code'), 'company')->class('required') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">

                                <div class='row'>
                                    <div class="col-md-6">
                                        <!-- {!! html()->text('code')->class('form-control')->id('code') !!} -->
                                        {!! html()->text('code')->class('form-control')->id('code')->attribute('title', 'Generate Coupon Code') !!}
                                        <!--   <input id="code" name="code" type="text" class="form-control" title="Generate Coupon Code"/> -->
                                    </div>
                                    <div class="col-md-4">
                                        <a href="#" class="btn btn-primary" id="get-code"><i class="fa fa-refresh"></i>&nbsp;Generate Code</a>
                                    </div>
                                </div>


                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.type'), 'type')->class('required') !!}</b></td>

                        <td>
                            <div class="form-group col-lg-6 {{ $errors->has('type') ? 'has-error' : '' }}">


                                {!! html()->select('type', ['' => 'Select', 'Types' => $type])->class('form-control')->attribute('title', 'Type Of Coupon') !!}


                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.value'), 'value')->class('required') !!}&nbsp;&nbsp;<i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="Enter the discount amount here"></i></b></td>
                        <td>
                            <div class="form-group col-lg-6 {{ $errors->has('value') ? 'has-error' : '' }}">


                                {!! html()->text('value')->class('form-control')->attribute('title','Value of the Coupon') !!}


                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.uses'), 'uses')->class('required') !!}&nbsp;&nbsp;<i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="Enter here how many times that coupon can be used"></i></b></td>
                        <td>
                            <div class="form-group col-lg-6 {{ $errors->has('uses') ? 'has-error' : '' }}">


                                {!! html()->text('uses')->class('form-control')->title('No. Of times the coupon can be Used') !!}


                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.applied'), 'applied')->class('required') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('applied') ? 'has-error' : '' }}" style="width: 53%;">


                                {!! html()->select('applied', ['' => 'Choose', 'Products' => $product])
    ->class('form-control select2 col-lg-18')
    ->attribute('data-live-search', 'true')
    ->attribute('data-live-search-placeholder', 'Search')
    ->attribute('data-dropup-auto', 'false')
    ->attribute('data-size', '10')
    ->attribute('title','Products for which coupon is Applied') !!}



                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.start'), 'start')->class('required') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('start') ? 'has-error' : '' }}">
                                <div class="input-group date" id="startDate" data-target-input="nearest" style="width: 50%;">

                                    {!! html()->text('start')
   ->class('form-control datetimepicker-input')
   ->attribute('title','Date from which Coupon is Valid')
   ->attribute('data-target', '#startDate') !!}


                                    <div class="input-group-append" data-target="#startDate" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>

                                </div>

                            </div>
                        </td>

                    </tr>


                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.expiry'), 'expiry')->class('required') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('expiry') ? 'has-error' : '' }}">
                                <div class="input-group date" id="endDate" data-target-input="nearest" style="width: 50%;">

                                    {!! html()->text('expiry')
    ->class('form-control datetimepicker-input')
    ->attribute('title','Date on which Coupon Expires')
    ->attribute('data-target', '#endDate') !!}


                                    <div class="input-group-append" data-target="#endDate" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>

                                </div>


                            </div>
                        </td>

                    </tr>




                    {!! html()->form()->close() !!}

                </table>

                <button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Saving..."><i class="fa fa-save">&nbsp;</i>{!!Lang::get('message.save')!!}</button>


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