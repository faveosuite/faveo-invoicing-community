@extends('themes.default1.layouts.master')
@section('title')
Edit Group
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>Edit Group</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('groups')}}"><i class="fa fa-dashboard"></i> Groups</a></li>
            <li class="breadcrumb-item active">Edit Group</li>
        </ol>
    </div><!-- /.col -->
@stop

@section('content')

<div class="row">

    <div class="col-md-12">
        <div class="card card-secondary card-outline">


            {!! html()->modelForm($group, 'PATCH', url('groups/'.$group->id))->open() !!}
            <div class="card-body">


                <table class="table table-condensed">



                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.name'), 'company')->class('required') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">

                                <div class='row'>
                                    <div class="col-md-10">
                                        {!! html()->text('name')->class('form-control') !!}
                                    </div>

                                </div>


                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.headline'), 'type') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('headline') ? 'has-error' : '' }}">

                                <div class='row'>
                                    <div class="col-md-10">
                                        {!! html()->text('headline')->class('form-control') !!}
                                    </div>

                                </div>

                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.tagline'), 'tagline') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('tagline') ? 'has-error' : '' }}">

                                <div class='row'>
                                    <div class="col-md-10">
                                        {!! html()->text('tagline')->class('form-control') !!}
                                    </div>

                                </div>

                            </div>
                        </td>

                    </tr>



                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.hidden'), 'hidden') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('hidden') ? 'has-error' : '' }}">

                                {!! html()->hidden('hidden', 0) !!}
                                <?php
                                                $value=  "";
                                                if($group->hidden==1){
                                                 $value = 'true';   
                                                }
                                                ?>
                                                <p>{!! html()->checkbox('hidden', $value, 1) !!} {{Lang::get('message.check-this-box-if-this-is-a-hidden-group')}}</p>

                               


                            </div>
                        </td>

                    </tr>


                    <tr>
                          
                        <td><b>{!! html()->label(__('message.select_design'), 'design') !!}</b></td>
                        <td>

                           <div class="form-group">
                            <div class="col-md-4">
                             <img src='{{ asset("images/$template->image")}}' class="img-thumbnail" style="height: 150;">
                             <br/>
                             @if($template->id == $selectedTemplate)
                             <input type="radio" id="template" name= 'pricing_templates_id' value="{{$template->id}}" checked style="text-align: center;">
                             @else
                             <input type="radio" id="template" name= 'pricing_templates_id' value="{{$template->id}}" style="text-align: center;">
                             @endif
                            {{$template->name}}

                             <br/><br/>
                        </div>
                   
                          
                            </div> 
                        </td>

                    </tr>

                           <tr>

                        <td><b>{!! html()->label(__('message.toggle_status'), 'status') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">

                                {!! html()->hidden('status', 0) !!}
                                <?php
                                                $value=  "";
                                                if($group->status==1){
                                                 $value = 'true';   
                                                }
                                                ?>
                                                <p>{!! html()->checkbox('status', 1, $value) !!} {{Lang::get('message.check-this-box_to_toggle_status')}}</p>

                               


                            </div>
                        </td>

                    </tr>



                    {!! html()->closeModelForm() !!}
                </table>

                <button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Saving..."><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.update')!!}</button>


            </div>

        </div>
        <!-- /.box -->

    </div>


</div>
<script>
     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'group';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'group';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
</script>

<script src="{{asset('plugins/jQuery/jquery.js')}}"></script>

<script>
$(document).ready(function () {
    var max_fields = 10; //maximum input boxes allowed
    var wrapper = $(".input_fields_wrap"); //Fields wrapper
    var add_button = $(".add_field_button"); //Add button ID

    var x = 1; //initlal text box count
    $(add_button).click(function (e) { //on add input button click
        e.preventDefault();
        if (x < max_fields) { //max input box allowed
            x++; //text box increment
            $(wrapper).append('<div class="row"><div class="col-md-6 form-group"><input type="text" name="features[][name]" class="form-control" /></div><a href="#" class="remove_field">Remove</a></div>'); //add input box
        }
    });

    $(wrapper).on("click", ".remove_field", function (e) { //user click on remove text
        e.preventDefault();
        $(this).parent('div').remove();
        x--;
    })
});


</script>

<script>
    $(document).ready(function () {
        var max_fields = 10; //maximum input boxes allowed
        var wrapper = $(".input_fields_wrap2"); //Fields wrapper
        var add_button = $(".add_field_button2"); //Add button ID

        var x = 1; //initlal text box count
        $(add_button).click(function (e) { //on add input button click
            e.preventDefault();
            if (x < max_fields) { //max input box allowed
                x++; //text box increment
                $(wrapper).append('<div class="row"><div class="col-md-4 form-group"><input type="text" name="value[][name]" class="form-control"/></div><div class="col-md-4 form-group"><input type="text" name="price[][name]" class="form-control" /></div><a href="#" class="remove_field2">Remove</a></div>'); //add input box
            }
        });

        $(wrapper).on("click", ".remove_field2", function (e) { //user click on remove text
            e.preventDefault();
            $(this).parent('div').remove();
            x--;
        })
    });
</script>

@stop
