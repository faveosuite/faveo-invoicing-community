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
                {!! html()->modelForm($social, 'PATCH', 'social-media/' . $social->id)->open() !!}

                <table class="table table-condensed">



                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.name'))->class('required') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">


                                {!! html()->text('name')->class('form-control') !!}
                                <p><i> {{Lang::get('message.enter-the-name-of-the-social-media')}}</i> </p>


                            </div>
                        </td>

                    </tr>
                   
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.link'))->for('link')->class('required') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('link') ? 'has-error' : '' }}">


                                {!! html()->text('link')->class('form-control') !!}
                                <p><i> {{Lang::get('message.enter-the-link-of-the-social-media')}}</i> </p>


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
     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'setting';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'setting';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
</script>
@stop