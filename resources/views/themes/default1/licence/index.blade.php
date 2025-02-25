@extends('themes.default1.layouts.master')
@section('title')
License Types
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>License Types</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> Settings</a></li>
            <li class="breadcrumb-item active">License Types</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')

    <div class="card card-secondary card-outline">
        <div class="card-header">
            <h3 class="card-title">{{Lang::get('message.types')}}</h3>

            <div class="card-tools">
                <a href="#create-type" data-toggle="modal" data-target="#create-type" class="btn btn-default btn-sm"><span class="fa fa-plus"></span>&nbsp;&nbsp;{{Lang::get('message.create')}}</a>


            </div>
        </div>

       @include('themes.default1.licence.create')
        @include('themes.default1.licence.edit')
        <div id="response"></div>
       <div class="card-body table-responsive">
             
             <div class="row">
            
            <div class="col-md-12">
               
                 <table id="products-table" class="table display" cellspacing="0" width="100%" styleClass="borderless">
                     <button  value="" class="btn btn-secondary btn-sm btn-alldell" id="bulk_delete"><i class="fa fa-trash"></i>&nbsp;&nbsp;{{Lang::get('message.delmultiple')}}</button><br /><br />
                    <thead><tr>
                        <th class="no-sort" style="width:20px"><input type="checkbox" name="select_all" onchange="checking(this)"></th>
                            <th>Name</th>
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
                name:@json(trans('message.license_details.new_license_type')),


            };

            $('#licenseForm').on('submit', function (e) {
                const userFields = {
                    name:$('#name'),

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
            ['name'].forEach(id => {

                document.getElementById(id).addEventListener('input', function () {
                    removeErrorMessage(this);

                });
            });
        });

    </script>


    <script>

        $(document).ready(function() {
            const userRequiredFields = {
                name:@json(trans('message.license_details.new_license_type')),


            };

            $('#type-edit-form').on('submit', function (e) {
                const userFields = {
                    name:$('#tname'),

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
            ['name'].forEach(id => {

                document.getElementById(id).addEventListener('input', function () {
                    removeErrorMessage(this);

                });
            });
        });

    </script>

<script type="text/javascript">
        $('#products-table').DataTable({
            processing: true,
            serverSide: true,
            stateSave: false,
            ordering: true,
            searching:true,
            select: true,
            order: [[ 1, "desc" ]],
               ajax: {
            "url":  '{!! route('get-license-type') !!}',
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
             { targets: 'no-sort', 
                    orderable: false,
                    order: []
                }
          ],
            columns: [
                {data: 'checkbox', name: 'checkbox'},
                {data: 'type_name', name: 'type_name'},
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
    </script>
 <script>
    function checking(e){
      $('#products-table').find("td input[type='checkbox']").prop('checked',$(e).prop('checked'));
    }


    function bindEditButton() {
        $('[data-toggle="tooltip"]').tooltip({
            container : 'body'
        });
        $('.editType').click(function(){
           var typeName = $(this).attr('data-name');
           var typeId   = $(this).attr('data-id');
            $("#edit-type").modal('show');
            $('#tname').val(typeName);
             var url = "{{url('license-type/')}}"+"/"+typeId
        $("#type-edit-form").attr('action', url)
        })
    }

      $(document).on('click','#bulk_delete',function(){
      var id=[];
      if (confirm("Are you sure you want to delete this? All the Products and Permissions related to this License Type would be deleted"))
        {
            $('.type_checkbox:checked').each(function(){
              id.push($(this).val())
            });
            if(id.length >0)
            {
               $.ajax({
                      url:"{!! route('license-type-delete') !!}",
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


