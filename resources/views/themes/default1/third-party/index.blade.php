@extends('themes.default1.layouts.master')
@section('title')
Third party Apps
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>Third party apps</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> Settings</a></li>
            <li class="breadcrumb-item active">Third party apps</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')

    <div class="card card-secondary card-outline">
        <div class="card-header">
            <h3 class="card-title">Third party apps</h3>

            <div class="card-tools">
                <a href="#create-third-party-app" data-toggle="modal" data-target="#create-third-party-app" class="btn btn-default btn-sm"><span class="fa fa-plus"></span>&nbsp;&nbsp;{{Lang::get('message.create')}}</a>


            </div>
        </div>

       @include('themes.default1.third-party.create')
       @include('themes.default1.third-party.edit')
        <div id="response"></div>
       <div class="card-body table-responsive">
             
             <div class="row">
            
            <div class="col-md-12">
               
                 <table id="third-party-app-table" class="table display" cellspacing="0" width="100%" styleClass="borderless">
                     <button  value="" class="btn btn-secondary btn-sm btn-alldell" id="bulk_delete"><i class="fa fa-trash"></i>&nbsp;&nbsp;{{Lang::get('message.delmultiple')}}</button><br /><br />
                    <thead><tr>
                        <th class="no-sort" style="width:20px"><input type="checkbox" name="select_all" onchange="checking(this)"></th>
                            <th>App name</th>
                            <th>App key</th>
                            <th>App secret</th>
                             <th>Action</th>
                        </tr></thead>

                   </table>
            </div>
        </div>

    </div>

</div>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">

    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>


    <script>

        $(document).ready(function() {
            const userRequiredFields = {
                app_key:@json(trans('message.app_details.app_key')),
                app_name:@json(trans('message.app_details.app_name')),
                app_secret:@json(trans('message.app_details.app_secret')),


            };

            $('#appForm').on('submit', function (e) {
                const userFields = {
                    app_name:$('#app-name'),
                    app_key:$('#app-key'),
                    app_secret:$('#app-secret'),
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
            ['app-name','app-key','app-secret'].forEach(id => {

                document.getElementById(id).addEventListener('input', function () {
                    removeErrorMessage(this);

                });
            });
        });



        $(document).ready(function() {
            const userRequiredFields = {
                app_key:@json(trans('message.app_details.app_key')),
                app_name:@json(trans('message.app_details.app_name')),
                app_secret:@json(trans('message.app_details.app_secret')),


            };

            $('#app-edit-form').on('submit', function (e) {
                const userFields = {
                    app_name:$('#name'),
                    app_key:$('#key'),
                    app_secret:$('#secret'),
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
            ['name','key','secret'].forEach(id => {

                document.getElementById(id).addEventListener('input', function () {
                    removeErrorMessage(this);

                });
            });
        });


        $('#third-party-app-table').DataTable({
            processing: true,
            serverSide: true,
            stateSave: false,
            ordering: true,
            searching:true,
            select: true,
            order: [[ 1, "desc" ]],
               ajax: {
            "url":  '{!! route('get-third-party-app') !!}',
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
             { orderable: false, targets:0 }
          ],
            columns: [
                {data: 'checkbox', name: 'checkbox'},
                {data: 'app_name', name: 'app_name'},
                {data: 'app_key', name: 'app_key'},
                {data: 'app_secret', name: 'app_secret'},
                {data: 'action', name: 'action'}
            ],
            "fnDrawCallback": function( oSettings ) {
              bindEditButton();
                $('.loader').css('display', 'none');
            },
            "fnPreDrawCallback": function(oSettings, json) {
                $('.loader').css('display', 'block');
            },
        });

    function checking(e){
      $('#third-party-app-table').find("td input[type='checkbox']").prop('checked',$(e).prop('checked'));
    }


    function bindEditButton() {
        $('[data-toggle="tooltip"]').tooltip({
            container : 'body'
        });
        $('.editThirdPartyApp').on('click',function(){
           var appName = $(this).attr('data-appName');
           var appKey = $(this).attr('data-appKey');
           var appId   = $(this).attr('data-id');
           var appSecret   = $(this).attr('data-secret');
           console.log(appName,appKey,appSecret)
            $("#edit-app").modal('show');
            $("#name").val(appName);
            $("#key").val(appKey);
            $("#secret").val(appSecret);
             var url = "{{url('third-party-keys/')}}"+"/"+appId
        $("#app-edit-form").attr('action', url)
        })
    }

      $(document).on('click','#bulk_delete',function(){
      var id=[];
      if (confirm("Are you sure you want to delete this?"))
        {
            $('.type_checkbox:checked').each(function(){
              id.push($(this).val())
            });
            if(id.length >0)
            {
               $.ajax({
                      url:"{!! route('third-party-delete') !!}",
                      method:"delete",
                      data: $('#check:checked').serialize(),
                      beforeSend: function () {
                $('#gif').show();
                },
                success: function (data) {
                $('#gif').hide();
                $('#response').html(data);
                location.reload();
                }
               })
            }
            else
            {
                alert("Please select at least one checkbox");
            }
        }  

     });

      $('.get-app-key').on('click',function(){
            $.ajax({
            type: "GET",
            url: "{{url('get-app-key')}}",
            success: function (data) {
                $(".app-key").val(data)
            }
        });

        })

      $('.closebutton').on('click',function(){
        location.reload();
      })


     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'setting';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'setting';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
</script>
@stop


