@extends('themes.default1.layouts.master')
@section('title')
Social Media
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>Edit Social Media</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> Settings</a></li>
            <li class="breadcrumb-item"><a href="{{url('social-media')}}"><i class="fa fa-dashboard"></i>  Social Media</a></li>
            <li class="breadcrumb-item active">Edit Social Media</li>
        </ol>
    </div><!-- /.col -->
@stop

@section('content')

<div class="row">

    <div class="col-md-12">
        <div class="card card-secondary card-outline">



            <div class="card-body">
                {!! html()->modelForm($social, 'PATCH', url('social-media/' . $social->id))->id('socialForm')->open() !!}
                <table class="table table-condensed">



                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.name'))->class('required') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">

                                <p><i> {{Lang::get('message.enter-the-name-of-the-social-media')}}</i> </p>

                                {!! html()->text('name')->class('form-control'. ($errors->has('name') ? ' is-invalid' : ''))->id('name') !!}

                                <div class="input-group-append">
                                </div>
                                @error('name')
                                <span class="error-message"> {{$message}}</span>
                                @enderror
                            </div>
                        </td>

                    </tr>
                   
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.link'))->for('link')->class('required') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('link') ? 'has-error' : '' }}">
                                <p><i> {{Lang::get('message.enter-the-link-of-the-social-media')}}</i> </p>
                                {!! html()->text('link')->class('form-control'. ($errors->has('link') ? ' is-invalid' : ''))->id('link')->placeholder('https://example.com') !!}

                                <div class="input-group-append">
                                </div>
                                @error('link')
                                <span class="error-message"> {{$message}}</span>
                                @enderror
                            </div>
                        </td>

                    </tr>



                    {!! html()->form()->close() !!}

                </table>
                <button type="submit" class="btn btn-primary pull-right" style="margin-top:-40px;"><i class="fa fa-sync-alt">&nbsp;&nbsp;</i>{!!Lang::get('message.update')!!}</button>



            </div>

        </div>
        <!-- /.box -->

    </div>


</div>

<script>

    $(document).ready(function() {
        const userRequiredFields = {
            name:@json(trans('message.social_details.name')),
            link:@json(trans('message.social_details.link')),

        };

        $('#socialForm').on('submit', function (e) {
            const userFields = {
                name:$('#name'),
                link:$('#link'),

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

            if(isValid  && !isValidURL(userFields.link.val())){
                showError(userFields.link,@json(trans('message.page_details.valid_url')),);
                isValid=false;
            }

            if(isValid && userFields.name.val().length>50 || !isValidName(userFields.name.val())){
                showError(userFields.name,@json(trans('message.valid_widget_name')));
                isValid=false;
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
        ['name','publish','type'].forEach(id => {

            document.getElementById(id).addEventListener('input', function () {
                removeErrorMessage(this);

            });
        });
        function isValidURL(url) {
            const pattern = /^(https?:\/\/)?([\w-]+\.)+([a-z]{2,6})(\/[\w-]*)*(\?.*)?(#.*)?$/i;
            return pattern.test(url);
        }

        function isValidName(name) {
            const pattern = /^(?=.*[a-zA-Z])[a-zA-Z0-9\s!@#\$%\^&\*\(\)_\+\-=\[\]{};':"\\|,.<>\/?`~]*$/;
            return pattern.test(name);
        }
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
@stop