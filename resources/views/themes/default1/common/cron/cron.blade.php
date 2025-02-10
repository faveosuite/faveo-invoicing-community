@extends('themes.default1.layouts.master')
@section('title')
Cron Setting
@stop
@section('content-header')
<style>
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: lightgray;
        color: black;
        border: none;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: black; 
    }
</style>
    <div class="col-sm-6">
        <h1>{!! Lang::get('message.cron-setting') !!}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> {{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item active">{!! Lang::get('message.cron-setting') !!}</li>
        </ol>
    </div><!-- /.col -->
@stop


@section('content')



    <div class="card card-secondary card-outline">
      <div class="row">
    <div class="col-md-12">
        <!-- Custom Tabs -->
  
               
                    @include('themes.default1.common.cron.cron-new')
               
                <!-- /.tab-pane -->


        <!-- nav-tabs-custom -->
    </div>
    <!-- /.col -->
</div>

</div>




<div class="row">
    <div class="col-md-12">
        <!-- Custom Tabs -->
  
     <div class="card card-secondary card-outline">
        
        <!-- /.box-header -->
       
              {!! Form::open(['url' => 'cron-days', 'method' => 'PATCH','id' => 'cronForm']) !!}
              <?php 
                   $mailStatus = \App\Model\Common\StatusSetting::pluck('expiry_mail')->first();
                   $activityStatus =\App\Model\Common\StatusSetting::pluck('activity_log_delete')->first();
                   $Autorenewal_status = \App\Model\Common\StatusSetting::pluck('subs_expirymail')->first();
                   $postExpiry_status = \App\Model\Common\StatusSetting::pluck('post_expirymail')->first();
                   $cloudStatus = \App\Model\Common\StatusSetting::pluck('cloud_mail_status')->first();
                   $invoiceStatus = \App\Model\Common\StatusSetting::pluck('invoice_deletion_status')->first();
                  ?>
         <div class="card-header">
             <h3 class="card-title">{{Lang::get('message.set_cron_period')}}  </h3>


         </div>

      <div class="card-body">
          <div class="row">
           
            <!-- /.col -->
            <div class="col-md-6">
             
              <!-- /.form-group -->
              <div class="form-group select2">
                <label >{{Lang::get('message.expiry_mail_sent')}}</label> 
                <i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="{{ __('message.cron_trigger') }}"></i>

                <?php 
                 if (count($selectedDays) > 0) {
                foreach ($selectedDays as $selectedDay) {
                    $saved[$selectedDay] = 'true';
                }
               }  else {
                    $saved=[];
                }
                 if (count($saved) > 0) {
                   foreach ($saved as $key => $value) {
                     $savedkey[]=$key;
                   }
                   $saved1=$savedkey?$savedkey:[''];
                       }
                       else{
                        $saved1=[];
                       }
                 ?>
                  
                   @if ($mailStatus == 0)
                    <select id ="days" name="expiryday[]" class="form-control selectpicker"   style="width: 100%; color:black;" disabled>
                      <option value="">{{Lang::get('message.enable_mail_cron')}}</option>
                    </select>
                      @else
                <select id ="days" name="expiryday[]" class="form-control selectpicker"  data-live-search="true" data-live-search-placeholder="Search" multiple="true" style="width: 100%; color:black;">

                    @foreach ($expiryDays as $key=>$value)

                  <option value="{{$key}}" <?php echo (in_array($key, $selectedDays)) ?  "selected" : "" ;  ?>>
                  {{$value}}
                </option>
                   @endforeach
                   
                </select>
                @endif
              </div>
              <!-- /.form-group -->
            </div>

             <div class="col-md-6">
              <div class="form-group">
                <label>{{Lang::get('message.log_del_days')}}</label>
                  @if ($activityStatus == 0)
                    <select id ="days" name="expiryday" class="form-control selectpicker"   style="width: 100%; color:black;" disabled>
                      <option value="">{{Lang::get('message.enable_activityLog_cron')}}</option>
                    </select>
                      @else
                <select name="logdelday" class="form-control selectpicker" data-live-search="true" data-live-search-placeholder="Search" style="width: 100%;">
                    @foreach ($delLogDays as $key=>$value)
                  <option value="{{$key}}" <?php echo (in_array($key, $beforeLogDay)) ?  "selected" : "" ;  ?>>{{$value}}</option>
                  @endforeach
                </select>
                @endif
              </div>
              <!-- /.form-group -->

              <!-- /.form-group -->
            </div>
            <!-- /.col -->

           <div class="col-md-6">
          <div class="form-group select2">
              <label>{{ Lang::get('Subscription renewal reminder - Auto payment') }}</label>
              <i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="{{ __('message.cron_trigger_enabled') }}"></i>

              @if ($Autorenewal_status == 0)
                  <select id="subdays" name="subexpiryday[]" class="form-control selectpicker" style="width: 100%; color: black;" disabled>
                      <option value="">{{ Lang::get('message.enable_mail_cron') }}</option>
                  </select>
              @else
                  <select id="subdays" name="subexpiryday[]" class="form-control selectpicker" data-live-search="true" data-live-search-placeholder="Search" multiple="true" style="width: 100%; color: black;">
                      @foreach ($Subs_expiry as $key => $value)
                          <option value="{{ $key }}" {{ in_array($key, $Auto_expiryday[0]) ? 'selected' : '' }}>{{ $value }}</option>
                      @endforeach
                  </select>
              @endif
          </div>
          <!-- /.form-group -->
      </div>
  

                <div class="col-md-6">
              <div class="form-group">
                <label>{{Lang::get('Cloud subscription deletion')}}</label>  <i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="{{ __('message.cron_trigger_cloud') }}"></i>
                  @if ($cloudStatus == 0)
                    <select id ="days" name="cloud_days" class="form-control selectpicker"   style="width: 100%; color:black;" disabled>
                      <option value="">{{Lang::get('Please Enable the Faveo cloud cron')}}</option>
                    </select>
                      @else
                <select name="cloud_days" class="form-control selectpicker" data-live-search="true" data-live-search-placeholder="Search" style="width: 100%;">
                    @foreach ($cloudDays as $key=>$value)
                  <option value="{{$key}}" <?php echo (in_array($key, $beforeCloudDay)) ?  "selected" : "" ;  ?>>{{$value}}</option>
                  @endforeach
                </select>
                @endif
              </div>
            </div>



    <div class="col-md-6">
        <div class="form-group select2">
            <label>{{ Lang::get('Subscription expired') }}</label>
            <i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="{{ __('message.cron_trigger_cloud_both') }}"></i>

            @if ($postExpiry_status == 0)
                <select id="postdays" name="postsubexpiry_days[]" class="form-control selectpicker" style="width: 100%; color: black;" disabled>
                    <option value="">{{ Lang::get('message.enable_mail_cron') }}</option>
                </select>
            @else
                <select id="postdays" name="postsubexpiry_days[]" class="form-control selectpicker" data-live-search="true" data-live-search-placeholder="Search" multiple="true" style="width: 100%; color: black;">
                    @foreach ($post_expiry as $key => $value)
                        <option value="{{ $key }}" {{ in_array($key, $post_expiryday[0]) ? 'selected' : '' }}>{{ $value }}</option>
                    @endforeach
                </select>
            @endif
        </div>
        <!-- /.form-group -->
    </div>

       <div class="col-md-6">
              <div class="form-group">
                <label>{{Lang::get('Invoice deletion')}}</label>  <i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="{{ __('message.cron_trigger_deletion_old') }}"></i>
                  @if ($invoiceStatus == 0)
                    <select id ="days" name="invoice_days[]" class="form-control selectpicker"   style="width: 100%; color:black;" disabled>
                      <option value="">{{Lang::get('Please enable the invoice deletion cron')}}</option>
                    </select>
                      @else
                <select name="invoice_days" class="form-control selectpicker" data-live-search="true" data-live-search-placeholder="Search" style="width: 100%;">
                    @foreach ($invoiceDays as $key=>$value)
                  <option value="{{$key}}" <?php echo (in_array($key, $invoiceDeletionDay)) ?  "selected" : "" ;  ?>>{{$value}}</option>
                  @endforeach
                </select>
                @endif
              </div>
            </div>





              <!-- /.form-group -->
            </div>

          
          </div>
          <!-- /.row -->
          @if ( $mailStatus || $activityStatus || $cloudStatus ==1)
              <button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Saving..."><i class="fa fa-sync-alt">&nbsp;</i>{!!Lang::get('message.update')!!}</button>
          @else
              <button type="submit" class="btn btn-primary pull-right disabled" id="submit"><i class="fa fa-sync-alt">&nbsp;</i>{!!Lang::get('message.update')!!}</button>
          @endif
            {!! Form::close() !!}
        </div>
        <!-- /.box-body -->
       
      </div>
                <!-- /.tab-pane -->
            
         
        <!-- nav-tabs-custom -->
    </div>
    <!-- /.col -->
</div>



<script>
  $(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();
});

     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'setting';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'setting';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
</script>

<script>
  function configureSelect2(selector) {
    $(selector).select2({
      templateSelection: function(selected, total) {
        return selected.text;
      }
    });
  }

  $(document).ready(function() {
    configureSelect2('#days');
    configureSelect2('#subdays');
    configureSelect2('#postdays');
  });
</script>
<!-- Include jQuery library -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Include jQuery Validation Plugin -->
<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
    $('#cronForm').validate({
        rules: {
            'expiryday[]': {
                required: true
            },
            'logdelday': {
                required: true
            },
            'subexpiryday[]': { 
                required: true
            },
            'cloud_days': {
                required: true
            },
            'postsubexpiry_days[]': {
                required: true
            }
        },
        messages: {
            'expiryday[]': {
                required: "{{ __('message.select_atleast_one_option') }}"
            },
            'logdelday': {
                required: "{{ __('message.select_option') }}"
            },
            'subexpiryday[]': {
                required: "{{ __('message.select_atleast_one_option') }}"
            },
            'cloud_days': {
                required: "{{ __('message.select_atleast_one_option') }}"
            },
            'postsubexpiry_days[]': {
                required: "{{ __('message.select_atleast_one_option') }}"
            }
        },
        errorElement: 'span',
        errorPlacement: function(error, element) {
            error.addClass('invalid-feedback');
            element.closest('.form-group').append(error);
        },
        highlight: function(element, errorClass, validClass) {
            $(element).addClass('is-invalid').removeClass('is-valid');
        },
        unhighlight: function(element, errorClass, validClass) {
            $(element).removeClass('is-invalid').addClass('is-valid');
        }
    });
});

</script>

@stop


