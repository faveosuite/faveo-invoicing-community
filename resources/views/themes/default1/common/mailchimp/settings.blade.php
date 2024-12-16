@extends('themes.default1.layouts.master')
@section('title')
Mailchimp
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>Mailchimp Settings</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> Settings</a></li>
            <li class="breadcrumb-item active">Mailchimp Setting</li>
        </ol>
    </div><!-- /.col -->
@stop

@section('content')

<div class="row">

    <div class="col-md-12">
        <div class="card card-secondary card-outline">



            <div class="card-body">
                {!! html()->modelForm($set, 'PATCH', url('mailchimp'))
    ->acceptsFiles()
    ->open() !!}


                <table class="table table-condensed">

                     
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.api_key'), 'api_key')->class('required') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('api_key') ? 'has-error' : '' }}">


                                {!! html()->text('api_key')->class('form-control')->disabled() !!}
                                <p><i> {{ Lang::get('message.enter-the-mailchimp-api-key-setting') }}</i></p>


                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.list_id'), 'list_id')->class('required') !!}</b></td>
                        <td>
                            <div class="row">
                                <div class="col-md-6 form-group {{ $errors->has('list_id') ? 'has-error' : '' }}">
                                <select name="list_id" class="form-control" </select>
                                    <option value="">Choose</option>
                                    @foreach($allists as $list) 
                                     <option value="{{$list->id}}"<?php  if(in_array($list->id, $selectedList) ) 
                        { echo "selected";} ?>>{{$list->name}}</option>
                                   
                                    @endforeach
                                    <p><i> {{Lang::get('message.enter-the-mailchimp-list-id')}}</i> </p>


                                </div>
                            </div>
                        </td>

                    </tr>

                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.subscribe_status'), 'subscribe_status')->class('required') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('subscribe_status') ? 'has-error' : '' }}">


                                {!! html()->select('subscribe_status', [
    'subscribed' => 'Subscribed',
    'unsubscribed' => 'Unsubscribed',
    'cleaned' => 'Cleaned',
    'pending' => 'Pending'
])->class('form-control') !!}
                                <p><i> {{Lang::get('message.enter-the-mailchimp-subscribe-status')}}</i> </p>


                            </div>
                        </td>

                    </tr>

                    @if($set->api_key&&$set->list_id)
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.mapping'), 'mapping')->class('required') !!}</b></td>
                        <td>
                            <div class="form-group">


                                <div class="col-md-6">
                                    <a href="{{url('mail-chimp/mapping')}}" class="btn btn-secondary btn-sm">{{Lang::get('message.mapping')}}</a>
                                    <p><i> {{Lang::get('message.map-the-mailchimp-field-with-agora')}}</i> </p>
                                </div>



                            </div>
                        </td>

                    </tr>
                    @endif

                    {!! html()->closeModelForm() !!}

                </table>


                <button type="submit" class="btn btn-primary pull-right" id="submit" style="margin-top:-40px;"><i class="fa fa-sync-alt">&nbsp;</i>{!!Lang::get('message.update')!!}</button>

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