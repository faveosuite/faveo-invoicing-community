@extends('themes.default1.layouts.master')
@section('title')
Github Setting
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>Github Setting</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> Settings</a></li>
            <li class="breadcrumb-item active">Github Setting</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')
<style>
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
</style>
<div class="card card-secondary card-outline">

    <div class="card-body">
          <div id="alertMessage"></div>
                <td class="col-md-2">
                    <label class="switch toggle_event_editing">
                          
                         <input type="checkbox" value="{{$githubStatus}}"  name="github_settings" 
                          class="checkbox" id="github">
                          <span class="slider round"></span>
                    </label>
 
                  </td>

        <div class="row github-setting">

            <div class="col-md-12">
                 
               
                <div class="row">
                     <input type ="hidden" id="hidden_git_username" value="{{$githubFileds->username}}">
                         <input type ="hidden" id="hidden_git_password" value="{{$githubFileds->password}}">
                          <input type ="hidden" id="hidden_git_client" value="{{$githubFileds->client_id}}">
                           <input type ="hidden" id="hidden_client_secret" value="{{$githubFileds->client_secret}}">
                    <div class="col-md-6 form-group {{ $errors->has('username') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('username',Lang::get('message.username'),['class'=>'required']) !!}
                        {!! Form::text('username',$githubFileds->username,['class' => 'form-control git_username','id'=>'git_username']) !!}
                        <h6 id="user"></h6>
                    </div>

                    <div class="col-md-6 form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('password',Lang::get('message.password'),['class'=>'required']) !!}
                         <!-- {!! Form::password('password',['class' => 'form-control', 'id'=>'password']) !!}
                        {!! Form::password('password',null,['class' => 'form-control']) !!} -->
                        <input type= "password" value="" name="password" id="git_password" class="form-control git_password">
                        <h6 id="pass"></h6>
                    </div>



                </div>



                <div class="row">

                    <div class="col-md-6 form-group {{ $errors->has('client_id') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('client_id',Lang::get('message.client_id'),['class'=>'required']) !!}
                        {!! Form::text('client_id',$githubFileds->client_id,['class' => 'form-control git_client','id'=>'git_client']) !!}
                        <h6 id="c_id"></h6>
                    </div>

                    <div class="col-md-6 form-group {{ $errors->has('client_secret') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('client_secret',Lang::get('message.client_secret'),['class'=>'required']) !!}
                        {!! Form::text('client_secret',$githubFileds->client_secret,['class' => 'form-control git_secret','id'=>'git_secret']) !!}
                        <h6 id="c_secret"></h6>
                    </div>

                </div>

                <button type="submit" id="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Saving..."><i class="fa fa-sync-alt">&nbsp;</i>{!!Lang::get('message.update')!!}</button>


            </div>

        </div>

    </div>

</div>



<script>
$(document).ready(function (){
    var githubstatus=0;
  githubstatus =  $('.checkbox').val();
    if(githubstatus ===1)
     {
         console.log(1);
        $('#github').prop('checked',true);
       $('#git_username').attr('enabled', true);
       $('#git_password').attr('enabled', true);
       $('#git_client').attr('enabled', true);
       $('#git_secret').attr('enabled', true);

     } else if(githubstatus ===0){
      $('#github').prop('checked',false);
        $('.git_username').attr('disabled', true);
       $('.git_password').attr('disabled', true);
       $('.git_client').attr('disabled', true);
       $('.git_secret').attr('disabled', true);
     }
  });

    $("#github").on('change',function (){
    if($(this).prop('checked')) {
      var username =  $('#hidden_git_username').val();
       var password =  $('#hidden_git_password').val();
        var client =  $('#hidden_git_client').val();
         var secret =  $('#hidden_git_secret').val();
      $('.git_username').attr('disabled', false);
      $('.git_password').attr('disabled', false);
      $('.git_client').attr('disabled', false);
      $('.git_secret').attr('disabled', false);
       $('#git_username').val(username);
       $('#git_password').val(password);
       $('#git_client').val(client);
       $('#git_secret').val(secret);

     } else {
        $('.git_username').attr('disabled', true);
      $('.git_password').attr('disabled', true);
      $('.git_client').attr('disabled', true);
      $('.git_secret').attr('disabled', true);
       $('#git_username').val('');
       $('#git_password').val('');
       $('#git_client').val('');
       $('#git_secret').val('');
     }
  });






        //Validate and pass value through ajax
  $("#submit").on('click',function (e){ //When Submit button is clicked
     if ($('#github').prop('checked')) {//if button is on

         const userRequiredFields = {
             git_username:@json(trans('message.github_details.name')),
             git_password:@json(trans('message.github_details.password')),
             git_client:@json(trans('message.github_details.client')),
             git_secret:@json(trans('message.github_details.secret')),

         };
                 const userFields = {
                     git_username: $('#git_username'),
                     git_password: $('#git_password'),
                     git_client: $('#git_client'),
                     git_secret: $('#git_secret'),
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


                 // If validation fails, prevent form submission
                 if (!isValid) {
                     githubstatus = 1;
                     e.preventDefault();
                 }else{
                     githubstatus=0;
                 }

         /////////////////////////////////////////////

         if(githubstatus==0) {
             $("#submit").html("<i class='fas fa-circle-notch fa-spin'></i>  Please Wait...");
             $.ajax({
                 url: '{{url("github-setting")}}',
                 type: 'post',
                 data: {
                     "status": githubstatus,
                     "git_username": $('#git_username').val(), "git_password": $('#git_password').val(),
                     "git_client": $('#git_client').val(), "git_secret": $('#git_secret').val()
                 },
                 success: function (data) {
                     $('#alertMessage').show();
                     var result = '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> Success! </strong>' + data.update + '.</div>';
                     $('#alertMessage').html(result + ".");
                     $("#submit").html("<i class='fa fa-sync-alt'>&nbsp;</i>Update");
                     setInterval(function () {
                         $('#alertMessage').slideUp(7000);
                     }, 1000);
                 },
             })
         }
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
['git_username', 'git_password', 'git_client','git_secret'].forEach(id => {

    document.getElementById(id).addEventListener('input', function () {
        removeErrorMessage(this);

    });

});
</script>
<script>
     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'setting';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'setting';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
</script>

<script>

    const userRequiredFields = {
        git_username:@json(trans('message.templateEdit_details.subject')),
        git_password:@json(trans('message.templateEdit_details.template_type')),
        git_client:@json(trans('message.templateEdit_details.content')),
        git_secret:@json(trans('message.templateEdit_details.content')),

    };
    if ($('#github').prop('checked')) {//if button is on
        $('#submi').on('click', function (e) {
            const userFields = {
                git_username: $('#git_username'),
                git_password: $('#git_password'),
                git_client: $('#git_client'),
                git_secret: $('#git_secret'),
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


            // If validation fails, prevent form submission
            if (!isValid) {
                var githubstatus = 1;
                console.log(3);
                e.preventDefault();
            }
        });
    }
        // Function to remove error when input'id' => 'changePasswordForm'ng data
        const removeErrorMessage = (field) => {
            field.classList.remove('is-invalid');
            const error = field.nextElementSibling;
            if (error && error.classList.contains('error')) {
                error.remove();
            }
        };

        // Add input event listeners for all fields
        ['name', 'type', 'textarea'].forEach(id => {

            document.getElementById(id).addEventListener('input', function () {
                removeErrorMessage(this);

            });

        });


</script>


@stop