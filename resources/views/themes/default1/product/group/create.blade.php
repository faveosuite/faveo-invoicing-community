@extends('themes.default1.layouts.master')
@section('title')
Create Group
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>Create Product Group</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('groups')}}"><i class="fa fa-dashboard"></i> Groups</a></li>
            <li class="breadcrumb-item active">Create Group</li>
        </ol>
    </div><!-- /.col -->


@endsection

@section('content')

<div class="row">

    <div class="col-md-12">
        <div class="card card-secondary card-outline">

          

            <div class="card-body">
                {!! html()->form('POST', url('groups'))->open() !!}



                <table class="table table-condensed">



                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.name'), 'company')->class('required') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">

                                <div class='row'>
                                    <div class="col-md-10">
                                        {!! html()->text('name')->class('form-control')->id('name') !!}
                                    </div>

                                </div>


                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.headline'), 'headline') !!}</b></td>
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
                             <p>{!! html()->hidden('hidden', 0) !!}</p>
                            <div class="form-group {{ $errors->has('hidden') ? 'has-error' : '' }}">

                               
                                <p>{!! html()->checkbox('hidden', 1) !!} {{Lang::get('message.check-this-box-if-this-is-a-hidden-group')}}</p>


                            </div>
                        </td>

                    </tr>

                   

                     <tr>
                          
                        <td><b>{!! html()->label(Lang::get('message.select_design'), 'design')->class('required') !!}</b></td>
                        <td>

                           <div class="form-group">
                            
                            <div class="col-md-4">
                             <img src='{{ asset("images/$template->image")}}' class="img-thumbnail" style="height: 150;">
                             <br/>
                            <input type="radio" name= 'pricing_templates_id' value='{{$template->id}}' style="text-align: center;">
                            {{$template->name}}

                             <br/><br/>
                        </div>
                   
                            
                            </div> 
                        </td>

                    </tr>

                   <!--        <tr>

                        <td><b>{!! html()->label(Lang::get('message.toggle_status'), 'status') !!}</b></td>
                                            <td>
                                                 <p>{!! html()->hidden('status', 0) !!}</p>
                                                <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">

                               
                                <p>{!! html()->checkbox('status', false, 1) !!}{{Lang::get('message.check-this-box_to_toggle_status')}}</p>


                            </div>
                        </td>

                    </tr> -->



                   <!--  <tr>

                        <td>

                            <div class="box-header">
                                <h3 class="box-title">{{Lang::get('message.configurable-options')}}</h3>
                            </div>

                        </td>

                        <td>

                        </td>

                    </tr>

                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.title'), 'hidden')->class('required') !!}</b></td>

                                            <td>
                                                <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">

                                <div class='row'>
                                    <div class="col-md-6">
                                        {!! html()->text('title')->class('form-control') !!}
                    </div>

                                </div>

                            </div>
                        </td>

                    </tr>

                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.type'), 'hidden')->class('required') !!}</b></td>

                                            <td>
                                                <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">

                                <div class='row'>
                                    <div class="col-md-6">
                                        {!! html()->select('type', ['' => 'select a type', '1' => 'dropdown', '2' => 'radio'])->class('form-control')->id('type') !!}
                    </div>

                                </div>

                            </div>
                        </td>

                    </tr>

                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.options'), 'hidden') !!}</b></td>

                                            <td>
                                                <div class="input_fields_wrap2">



                                                            <div class='row form-group'>

                                                                <div class="col-md-4 {{ $errors->has('value.0.name') ? 'has-error' : '' }}">
                                                <b>{!! html()->label(Lang::get('message.value'), 'hidden')->class('required') !!}</b>
                                                                                        <input type="text" name="value[][name]" class="form-control" value="{{ old('value.0.name') }}">
                                            </div>
                                            <div class="col-md-4 {{ $errors->has('price.0.name') ? 'has-error' : '' }}">
                                                <b>{!! html()->label(Lang::get('message.price'), 'hidden')->class('required') !!}</b>
                                                                    <input type="text" name="price[][name]" class="form-control" value="{{ old('price.0.name') }}">
                                            </div>
                                            <div class="col-md-4">
                                                <br>
                                                <a href="#" class="add_field_button2 btn btn-primary">Add More Options</a>
                                            </div>
                                            


                                        </div>
                            </div>

                        </td>

                    </tr> -->

                    {!! html()->form()->close() !!}
                </table>
                <button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Saving..."><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button>



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
    $(function(){
       $(document).on('input','#name',function(){

         $.ajax ({
            type: "get",
            data : {'url' : this.value},
            url : "{{url('get-group-url')}}",
            success : function(data) {
                $('#groupslug').val(data);
            }
         })
        });
    })

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
            $(wrapper).append('<div class="row"><div class="col-md-4 form-group"><input type="text" name="value[][name]" class="form-control"/></div><div class="col-md-4 form-group"><input type="text" name="price[][name]" class="form-control" /></div><a href="#" class="remove_field">Remove</a></div>'); //add input box
        }
    });

    $(wrapper).on("click", ".remove_field", function (e) { //user click on remove text
        e.preventDefault();
        $(this).parent('div').remove();
        x--;
    })
});
</script>

@stop
