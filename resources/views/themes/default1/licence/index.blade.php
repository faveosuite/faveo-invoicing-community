@extends('themes.default1.layouts.master')
@section('title')
    {{ __('message.license_types') }}
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.license-type') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> {{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.license-type') }}</li>
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
                            <th>{{ __('message.name_page') }}</th>
                            <th>{{ __('message.action') }}</th>
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
                name:@json(trans('message.lic_details.new_license_type')),


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
                name:@json(trans('message.lic_details.new_license_type')),


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
                alert('{{ __('message.session_expired') }}')
                window.location.href = '/login';
               }
            }

            },
            "oLanguage": {
                "sLengthMenu": "_MENU_ Records per page",
                "sSearch"    : "Search: ",
                "sProcessing": ' <div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">{{ __('message.loading') }}</div></div>'
            },
            language: {
                paginate: {
                    first:      "{{ __('message.paginate_first') }}",
                    last:       "{{ __('message.paginate_last') }}",
                    next:       "{{ __('message.paginate_next') }}",
                    previous:   "{{ __('message.paginate_previous') }}"
                },
                emptyTable:     "{{ __('message.empty_table') }}",
                info:           "{{ __('message.datatable_info') }}",
                search:         "{{ __('message.datatable_search') }} ",
                zeroRecords:    "{{ __('message.no_matching_records_found') }} ",
                infoEmpty:      "{{ __('message.info_empty') }}",
                infoFiltered:   "{{ __('message.info_filtered') }}",
                lengthMenu:     "{{ __('message.sLengthMenu') }}",
                loadingRecords: "{{ __('message.loading_records') }}",
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
          $('.type_checkbox:checked').each(function(){
              id.push($(this).val())
          });
          if(id.length<=0){
              swal.fire({
                  title: "<h2 class='swal2-title custom-title'>{{Lang::get('message.Select')}}</h2>",
                  html: "<div class='swal2-html-container custom-content'>" +
                      "<div class='section-sa'>" +
                      "<p>{{Lang::get('message.sweet_license')}}</p>" + "</div>" +
                      "</div>",
                  position: 'top',
                  confirmButtonText: "{{ __('message.ok') }}",
                  showCloseButton: true,
                  confirmButtonColor: "#007bff",
                  width: "600px",
              })
          }
          else {
              var swl = swal.fire({
                  title: "<h2 class='swal2-title custom-title'>{{Lang::get('message.Delete')}}</h2>",
                  html: "<div class='swal2-html-container custom-content'>" +
                      "<div class='section-sa'>" +
                      "<p>{{Lang::get('message.license_type')}}</p>" + "</div>" +
                      "</div>",
                  showCancelButton: true,
                  showCloseButton: true,
                  position: "top",
                  width: "600px",

                  confirmButtonText: @json(trans('message.Delete')),
                  cancelButtonText: "{{ __('message.cancel') }}",
                  confirmButtonColor: "#007bff",
              }).then((result) => {
                  if (result.isConfirmed) {
                      $('.type_checkbox:checked').each(function () {
                          id.push($(this).val())
                      });
                      if (id.length > 0) {
                          $.ajax({
                              url: "{!! route('license-type-delete') !!}",
                              method: "delete",
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
                      } else {
                          swal.fire({
                              title: "<h2 class='swal2-title custom-title'>{{Lang::get('message.Select')}}</h2>",
                              html: "<div class='swal2-html-container custom-content'>" +
                                  "<div class='section-sa'>" +
                                  "<p>{{Lang::get('message.sweet_license')}}</p>" + "</div>" +
                                  "</div>",
                              position: 'top',
                              confirmButtonText: "{{ __('message.ok') }}",
                              showCloseButton: true,
                              confirmButtonColor: "#007bff",
                              width: "600px",
                          })
                      }
                  } else if (result.dismiss === Swal.DismissReason.cancel) {
                      window.close();
                  }
              })
              return false;
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


