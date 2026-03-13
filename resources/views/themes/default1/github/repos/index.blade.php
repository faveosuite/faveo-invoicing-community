@extends('themes.default1.layouts.master')
@section('title') {{ __('message.manage_repos') }} @stop
@section('content-header')
    <div class="col-sm-6"><h1>{{ __('message.manage_repos') }}</h1></div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ url('settings') }}">{{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.manage_repos') }}</li>
        </ol>
    </div>
@stop
@section('content')
<div class="card card-secondary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fab fa-github mr-1"></i> {{ __('message.manage_repos') }}</h3>
        <div class="card-tools">
            <a href="{{ route('github.create-release') }}" class="btn btn-sm btn-primary mr-1">
                <i class="fas fa-rocket mr-1"></i> {{ __('message.create_release') }}
            </a>
            <a href="{{ route('github.manage-releases') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-list mr-1"></i> {{ __('message.manage_releases') }}
            </a>
        </div>
    </div>
    <div class="card-body">
        <div id="alertMessage"></div>

        {{-- Add repo form --}}
        <div class="card card-default card-outline mb-4">
            <div class="card-header"><h5 class="card-title mb-0">{{ __('message.add_repo') }}</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label class="required">{{ __('message.display_name') }}</label>
                        <input type="text" id="new_display_name" class="form-control" placeholder="Faveo Helpdesk Advance" />
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="required">{{ __('message.github_owner') }}</label>
                        <input type="text" id="new_owner" class="form-control" placeholder="faveosuite" />
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="required">{{ __('message.repo_name') }}</label>
                        <input type="text" id="new_repo" class="form-control" placeholder="faveo-helpdesk-advance" />
                    </div>
                    <div class="col-md-2 form-group">
                        <label>{{ __('message.workflow_file') }}</label>
                        <input type="text" id="new_workflow_file" class="form-control" placeholder="release.yml" />
                    </div>
                    <div class="col-md-1 form-group d-flex align-items-end">
                        <button id="btnAddRepo" class="btn btn-primary btn-block">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Repos table --}}
        <table class="table table-bordered table-hover" id="reposTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('message.display_name') }}</th>
                    <th>{{ __('message.github_owner') }}</th>
                    <th>{{ __('message.repo_name') }}</th>
                    <th>{{ __('message.workflow_file') }}</th>
                    <th>{{ __('message.action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($repos as $r)
                <tr id="repo-row-{{ $r->id }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <span class="view-mode">{{ $r->display_name }}</span>
                        <input type="text" class="form-control edit-mode" value="{{ $r->display_name }}" name="display_name" style="display:none;" />
                    </td>
                    <td>
                        <span class="view-mode">{{ $r->owner }}</span>
                        <input type="text" class="form-control edit-mode" value="{{ $r->owner }}" name="owner" style="display:none;" />
                    </td>
                    <td>
                        <span class="view-mode"><code>{{ $r->repo }}</code></span>
                        <input type="text" class="form-control edit-mode" value="{{ $r->repo }}" name="repo" style="display:none;" />
                    </td>
                    <td>
                        <span class="view-mode">{{ $r->workflow_file }}</span>
                        <input type="text" class="form-control edit-mode" value="{{ $r->workflow_file }}" name="workflow_file" style="display:none;" />
                    </td>
                    <td>
                        <span class="view-mode">
                            <button class="btn btn-sm btn-warning btn-edit" data-id="{{ $r->id }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger btn-delete ml-1" data-id="{{ $r->id }}" data-name="{{ $r->display_name }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </span>
                        <span class="edit-mode" style="display:none;">
                            <button class="btn btn-sm btn-success btn-save" data-id="{{ $r->id }}">
                                <i class="fas fa-check"></i> Save
                            </button>
                            <button class="btn btn-sm btn-secondary btn-cancel ml-1" data-id="{{ $r->id }}">
                                <i class="fas fa-times"></i>
                            </button>
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted">No repos added yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function () {

    function showAlert(type, msg) {
        var html = '<div class="alert alert-' + type + ' alert-dismissable">'
            + '<button type="button" class="close" data-dismiss="alert">&times;</button>'
            + msg + '</div>';
        $('#alertMessage').html(html).show();
        $('html,body').animate({ scrollTop: 0 }, 300);
    }

    // ── Add repo ──────────────────────────────────────────────────────────
    $('#btnAddRepo').on('click', function () {
        var btn = $(this);
        btn.html('<i class="fas fa-circle-notch fa-spin"></i>').prop('disabled', true);
        $.ajax({
            url: '{{ route("github.repos.store") }}',
            type: 'POST',
            data: {
                _token:        '{{ csrf_token() }}',
                display_name:  $('#new_display_name').val().trim(),
                owner:         $('#new_owner').val().trim(),
                repo:          $('#new_repo').val().trim(),
                workflow_file: $('#new_workflow_file').val().trim(),
            },
            success: function (res) {
                showAlert('success', '<i class="fa fa-check mr-1"></i> ' + (res.update || res.message));
                setTimeout(function () { location.reload(); }, 1000);
            },
            error: function (xhr) {
                var msg = xhr.responseJSON ? (xhr.responseJSON.message || xhr.responseJSON.error) : 'Error occurred.';
                showAlert('danger', '<i class="fa fa-times mr-1"></i> ' + msg);
            },
            complete: function () { btn.html('<i class="fas fa-plus"></i>').prop('disabled', false); }
        });
    });

    // ── Inline edit ───────────────────────────────────────────────────────
    $(document).on('click', '.btn-edit', function () {
        var id = $(this).data('id');
        $('#repo-row-' + id + ' .view-mode').hide();
        $('#repo-row-' + id + ' .edit-mode').show();
    });

    $(document).on('click', '.btn-cancel', function () {
        var id = $(this).data('id');
        $('#repo-row-' + id + ' .view-mode').show();
        $('#repo-row-' + id + ' .edit-mode').hide();
    });

    $(document).on('click', '.btn-save', function () {
        var id  = $(this).data('id');
        var row = $('#repo-row-' + id);
        var btn = $(this);
        btn.html('<i class="fas fa-circle-notch fa-spin"></i>').prop('disabled', true);
        $.ajax({
            url: '{{ url("github-repos") }}/' + id,
            type: 'POST',
            data: {
                _token:        '{{ csrf_token() }}',
                _method:       'PUT',
                display_name:  row.find('input[name=display_name]').val().trim(),
                owner:         row.find('input[name=owner]').val().trim(),
                repo:          row.find('input[name=repo]').val().trim(),
                workflow_file: row.find('input[name=workflow_file]').val().trim(),
            },
            success: function (res) {
                showAlert('success', '<i class="fa fa-check mr-1"></i> ' + (res.update || res.message));
                setTimeout(function () { location.reload(); }, 800);
            },
            error: function (xhr) {
                var msg = xhr.responseJSON ? (xhr.responseJSON.message || xhr.responseJSON.error) : 'Error occurred.';
                showAlert('danger', '<i class="fa fa-times mr-1"></i> ' + msg);
                btn.html('<i class="fas fa-check"></i> Save').prop('disabled', false);
            }
        });
    });

    // ── Delete repo ───────────────────────────────────────────────────────
    $(document).on('click', '.btn-delete', function () {
        var id   = $(this).data('id');
        var name = $(this).data('name');
        if (!confirm('Remove "' + name + '" from the list? This will not delete the GitHub repo.')) return;
        $.ajax({
            url: '{{ url("github-repos") }}/' + id,
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
            success: function (res) {
                showAlert('success', '<i class="fa fa-check mr-1"></i> ' + (res.update || res.message));
                $('#repo-row-' + id).fadeOut(400, function () { $(this).remove(); });
            },
            error: function (xhr) {
                var msg = xhr.responseJSON ? (xhr.responseJSON.message || xhr.responseJSON.error) : 'Error occurred.';
                showAlert('danger', '<i class="fa fa-times mr-1"></i> ' + msg);
            }
        });
    });
});
</script>
<script>
    $('ul.nav-sidebar a').filter(function () { return this.id == 'setting'; }).addClass('active');
    $('ul.nav-treeview a').filter(function () { return this.id == 'setting'; })
        .parentsUntil('.nav-sidebar > .nav-treeview').addClass('menu-open').prev('a').addClass('active');
</script>
@stop
