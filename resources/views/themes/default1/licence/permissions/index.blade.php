@extends('themes.default1.layouts.master')
@section('title')
License Permission
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>License Permissions</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
             <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> Settings</a></li>
            <li class="breadcrumb-item active">License Permissions</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')
    <link rel="stylesheet" href="{{asset('admin/plugins/iCheck/all.css')}}">
    <div class="card card-secondary card-outline">


       @include('themes.default1.licence.permissions.create')
       <div class="card-body table-responsive">
             
             <div class="row">
            
            <div class="col-md-12">
               
                 <table id="permissions-table" class="table display" cellspacing="0" width="100%" styleClass="borderless">
                    <thead><tr>
                            <th>License Type</th>
                            <th>License Permissions</th>
                            <th>Action</th>
                        </tr></thead>

                   </table>
            </div>
        </div>

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

    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">

    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">

        $('#permissions-table').DataTable({
            processing: true,
            serverSide: true,
            stateSave: false,
            order: [[ 0, "desc" ]],
              ajax: {
            "url":  '{!! route('get-license-permission') !!}',
               error: function(xhr) {
               if(xhr.status == 401) {
                alert('Your session has expired. Please login again to continue.')
                window.location.href = '/login';
               }
            }

            },
            "oLanguage": {
                "sLengthMenu": "_MENU_ Records per page",
                "sSearch"    : "Search: ",
                "sProcessing": ' <div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">Loading...</div></div>'
            },
            columnDefs: [
                { 
                     targets: 'no-sort', 
                    orderable: false,
                    order: []
                }
            ],
            columns: [
                {data: 'license_type', name: 'license_type'},
                {data: 'permissions', name: 'permissions'},
                {data: 'action', name: 'action',orderable:'false'}
            ],
            "fnDrawCallback": function( oSettings ) {
                bindEditButton();
                $('.loader').css('display', 'none');
            },
            "fnPreDrawCallback": function(oSettings, json) {
                $('.loader').css('display', 'block');
            },
        });
    </script>
 <script>
      $(function () {
    //iCheck for checkbox and radio inputs
    $('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
      checkboxClass: 'icheckbox_minimal-blue',
      radioClass   : 'iradio_minimal-blue'
  })
  })
    

    //Add Permissions for a License


      function bindEditButton() {
          $('.addPermission').on('click', function() {
              var licenseTypeId = $(this).attr('data-id');
              var permissions = $(this).attr('data-permission'); // All Permission for a particular License

              $.ajax({
                  url: "{!! route('tick-permission') !!}",
                  data: { 'license': licenseTypeId },
                  beforeSend: function() {
                      $('input[type="checkbox"].minimal').iCheck('uncheck');
                    },
                success: function (data) {
                    if (data.message =='success') {
                      $('.permission_checkbox').each(function() {
                        var permission = $(this);
                        $.each(data.permissions, function(index, el) {
                          if (el == permission.val()) {
                            permission.iCheck('check');
                          }
                        });
                      });
                    $("#all-permissions").modal('show');
                    }               
                }
              });

              $('#licenseTypeId').val(licenseTypeId);

              $('#permissionssubmit').off('click').on('click', function() {
                  var permissionid = [];
                  $('.permission_checkbox:checked').each(function() {
                      permissionid.push($(this).val());
                  });

                  if (permissionid.length > 0) {
                      $.ajax({
                          url: "{!! route('add-permission') !!}",
                          method: "delete",
                          data: { 'licenseId': licenseTypeId, 'permissionid': permissionid },
                          beforeSend: function() {
                              $('#permissionresponse').html('<div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">Loading...</div></div>');
                          },
                          success: function(data) {
                              showAlert('success', data);
                              setTimeout(function (){
                                  window.location.reload();
                              }, 3000);
                          },
                          error: function(xhr) {
                              showAlert('error', xhr.responseJSON);
                          }
                      });
                  } else {
                      showAlert('error', 'Please select at least one permission');
                  }
              });
          });
      }

      let alertTimeout;

      function showAlert(type, messageOrResponse) {
          // Generate appropriate HTML
          var html = generateAlertHtml(type, messageOrResponse);

          // Clear any existing alerts and remove the timeout
          $('#permissionresponse').html(html);
          clearTimeout(alertTimeout); // Clear the previous timeout if it exists

          // Display alert
          window.scrollTo(0, 0);

          // Auto-dismiss after 5 seconds
          alertTimeout = setTimeout(function() {
              $('#permissionresponse .alert').fadeOut('slow');
          }, 5000);
      }

      function generateAlertHtml(type, response) {
          // Determine alert styling based on type
          const isSuccess = type === 'success';
          const iconClass = isSuccess ? 'fa-check-circle' : 'fa-ban';
          const alertClass = isSuccess ? 'alert-success' : 'alert-danger';

          // Extract message and errors
          const message = response.message || response || 'An error occurred. Please try again.';
          const errors = response.errors || null;

          // Build base HTML
          let html = `<div class="alert ${alertClass} alert-dismissible">` +
              `<i class="fa ${iconClass}"></i> ` +
              `${message}` +
              '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';

          html += '</div>';

          return html;
      }

   </script>

@stop


