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


@stop

@section('content')

<div class="row">

    <div class="col-md-12">
        <div class="card card-secondary card-outline">

          

            <div class="card-body">
                {!! Form::open(['url'=>'groups','id'=>'groupForm']) !!}
        


                <table class="table table-condensed">



                    <tr>

                        <td><b>{!! Form::label('name',Lang::get('message.name'),['class'=>'required']) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">

                                <div class='row'>
                                    <div class="col-md-10">
                                        {!! Form::text('name',null,['class' => 'form-control','id'=>'name']) !!}
                                        @error('name')
                                        <span class="error-message"> {{$message}}</span>
                                        @enderror
                                        <div class="input-group-append">
                                        </div>
                                    </div>

                                </div>


                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! Form::label('headline',Lang::get('message.headline')) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('headline') ? 'has-error' : '' }}">

                                <div class='row'>
                                    <div class="col-md-10">
                                        {!! Form::text('headline',null,['class' => 'form-control']) !!}
                                        @error('headline')
                                        <span class="error-message"> {{$message}}</span>
                                        @enderror
                                    </div>

                                </div>

                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! Form::label('tagline',Lang::get('message.tagline')) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('tagline') ? 'has-error' : '' }}">

                                <div class='row'>
                                    <div class="col-md-10">
                                        {!! Form::text('tagline',null,['class' => 'form-control']) !!}
                                        @error('tagline')
                                        <span class="error-message"> {{$message}}</span>
                                        @enderror
                                    </div>

                                </div>

                            </div>
                        </td>

                    </tr>
  


                    <tr>

                        <td><b>{!! Form::label('hidden',Lang::get('message.hidden')) !!}</b></td>
                        <td>
                             <p>{!! Form::hidden('hidden',0) !!}</p>
                            <div class="form-group {{ $errors->has('hidden') ? 'has-error' : '' }}">

                               
                                <p>{!! Form::checkbox('hidden',1) !!}  {{Lang::get('message.check-this-box-if-this-is-a-hidden-group')}}</p>


                            </div>
                        </td>

                    </tr>

                   

                     <tr>
                          
                        <td><b>{!! Form::label('design',Lang::get('message.select_design'),['class'=>'required']) !!}</b></td>
                        <td>

                           <div class="form-group">
                            
                            <div class="col-md-4">
                             <img src='{{ asset("storage/$template->image")}}' alt="Porto Theme" class="img-thumbnail" >
                             <br/>
                            <input type="radio" name='pricing_templates_id' value='{{$template->id}}' id='template' style="text-align: center;">
                            {{$template->name}}

                             <br/><br/>
                                <span id="error-message"></span>
                                @error('pricing_templates_id')
                                <span class="error-message"> {{$message}}</span>
                                @enderror
                            </div>
                   
                            
                            </div>
                        </td>

                    </tr>


                    {!! Form::close() !!}
                </table>
                <button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Saving..."><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button>



            </div>

        </div>
        <!-- /.box -->

    </div>


</div>

<script>

    $(document).ready(function() {
        const userRequiredFields = {
            name:@json(trans('message.group_details.group_name')),
            template:@json(trans('message.group_details.template')),
        };

        $('#groupForm').on('submit', function (e) {
            const userFields = {
                name:$('#name'),
                template:$('#template'),
            };


            // Clear previous errors
            Object.values(userFields).forEach(field => {
                field.removeClass('is-invalid');
                field.next().next('.error').remove();

            });

            let isValid = true;

            const showError = (field, message) => {
                field.addClass('is-invalid');
                field.next().after(`<span class='error invalid-feedback'>${message}</span>`);
            };

            // Validate required fields
            Object.keys(userFields).forEach(field => {
                if (!userFields[field].val()) {
                    showError(userFields[field], userRequiredFields[field]);
                    isValid = false;
                }
            });

            if(!document.querySelector('input[name="pricing_templates_id"]:checked')){
                $('#error-message').css({"color": "#dc3545", "margin-top": "5px", "font-size": "80%"});

                document.getElementById("error-message").textContent = @json(trans('message.group_details.template'));
                document.getElementById("error-message").textContent = "Please select the template";

                isValid=false;
            }else{

                document.getElementById("error-message").textContent = "";

            }

            // If validation fails, prevent form submission
            if (!isValid) {
                e.preventDefault();
            }
        });
        // Function to remove error when input'id' => 'changePasswordForm'ng data
        const removeErrorMessage = (field) => {
            field.classList.remove('is-invalid');
            const error = field.nextElementSibling;
            if (error && error.classList.contains('error')) {
                error.remove();
            }
        };

        // Add input event listeners for all fields
        ['name'].forEach(id => {

            document.getElementById(id).addEventListener('input', function () {
                removeErrorMessage(this);

            });
        });
    });


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

