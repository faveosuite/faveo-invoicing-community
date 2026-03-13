@extends('themes.default1.layouts.master')
@section('title') {{ __('message.manage_releases') }} @stop
@section('content-header')
    <div class="col-sm-6"><h1>{{ __('message.manage_releases') }}</h1></div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ url('settings') }}">{{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.manage_releases') }}</li>
        </ol>
    </div>
@stop
@section('content')
<div class="card card-secondary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fab fa-github mr-1"></i> {{ __('message.manage_releases') }}</h3>
        <div class="card-tools">
            <a href="{{ route('github.create-release') }}" class="btn btn-sm btn-primary mr-1">
                <i class="fas fa-rocket mr-1"></i> {{ __('message.create_release') }}
            </a>
            <a href="{{ route('github.repos.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-cogs mr-1"></i> {{ __('message.manage_repos') }}
            </a>
        </div>
    </div>
    <div class="card-body">
        <div id="alertMessage"></div>

        {{-- Repo selector --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <label><strong>{{ __('message.select_repo') }}</strong></label>
                <select id="repoSelector" class="form-control">
                    @forelse ($repos as $r)
                        <option value="{{ $r->id }}" {{ $selected && $selected->id == $r->id ? 'selected' : '' }}>
                            {{ $r->display_name }} ({{ $r->owner }}/{{ $r->repo }})
                        </option>
                    @empty
                        <option value="">-- No repos configured --</option>
                    @endforelse
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button id="btnLoadReleases" class="btn btn-primary">
                    <i class="fas fa-sync-alt mr-1"></i> Load Releases
                </button>
            </div>
        </div>

        {{-- Releases table --}}
        <div id="releasesContainer" style="display:none;">
            <table class="table table-bordered table-hover table-sm">
                <thead class="thead-light">
                    <tr>
                        <th>Tag</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="releasesTbody"></tbody>
            </table>
        </div>
        <div id="releasesLoading" style="display:none;" class="text-center py-4">
            <i class="fas fa-circle-notch fa-spin fa-2x text-muted"></i>
            <p class="text-muted mt-2">Loading releases...</p>
        </div>
        <div id="releasesEmpty" style="display:none;" class="text-center py-4 text-muted">
            <i class="fab fa-github fa-3x mb-2"></i>
            <p>No releases found for this repo.</p>
        </div>
    </div>
</div>

{{-- Edit release modal --}}
<div class="modal fade" id="editReleaseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit mr-1"></i> Update Release</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="modalAlert"></div>
                <input type="hidden" id="modal_release_id" />
                <input type="hidden" id="modal_repo_id" />
                <div class="form-group">
                    <label class="required">{{ __('message.release_title') }}</label>
                    <input type="text" id="modal_release_title" class="form-control" />
                </div>
                <div class="form-group">
                    <label class="required">{{ __('message.release_notes') }}</label>
                    <textarea id="modal_release_notes" class="form-control" rows="10"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="modal_prerelease">
                            <label class="custom-control-label" for="modal_prerelease">{{ __('message.pre_release') }}</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="modal_draft">
                            <label class="custom-control-label" for="modal_draft">{{ __('message.draft') }}</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" id="btnSaveRelease" class="btn btn-warning">
                    <i class="fas fa-save mr-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {

    var currentRepoId = {{ $selected ? $selected->id : 'null' }};

    function showAlert(type, msg) {
        var html = '<div class="alert alert-' + type + ' alert-dismissable">'
            + '<button type="button" class="close" data-dismiss="alert">&times;</button>'
            + msg + '</div>';
        $('#alertMessage').html(html).show();
        $('html,body').animate({ scrollTop: 0 }, 300);
    }

    function typeBadge(release) {
        if (release.draft)      return '<span class="badge badge-secondary">Draft</span>';
        if (release.prerelease) return '<span class="badge badge-warning">Pre-release</span>';
        return '<span class="badge badge-success">Official</span>';
    }

    function loadReleases(repoId) {
        currentRepoId = repoId;
        $('#releasesContainer, #releasesEmpty').hide();
        $('#releasesLoading').show();

        $.get('{{ url("github-fetch-releases") }}/' + repoId)
            .done(function (data) {
                if (data.error) {
                    showAlert('danger', data.error);
                    return;
                }
                var releases = data.releases;
                if (!releases || releases.length === 0) {
                    $('#releasesEmpty').show();
                    return;
                }
                var rows = '';
                releases.forEach(function (r) {
                    rows += '<tr>'
                        + '<td><code>' + r.tag_name + '</code></td>'
                        + '<td>' + (r.name || '—') + '</td>'
                        + '<td>' + typeBadge(r) + '</td>'
                        + '<td>' + new Date(r.created_at).toLocaleDateString() + '</td>'
                        + '<td>'
                        +   '<button class="btn btn-xs btn-warning btn-edit-release mr-1" '
                        +       'data-id="' + r.id + '" data-title="' + r.name + '" '
                        +       'data-body="' + encodeURIComponent(r.body || '') + '" '
                        +       'data-prerelease="' + r.prerelease + '" data-draft="' + r.draft + '">'
                        +       '<i class="fas fa-edit"></i> Edit</button>'
                        + (r.prerelease
                            ? '<button class="btn btn-xs btn-success btn-promote-release mr-1" data-id="' + r.id + '">'
                              + '<i class="fas fa-crown"></i> Promote</button>'
                            : '')
                        +   '<a href="' + r.html_url + '" target="_blank" class="btn btn-xs btn-secondary mr-1">'
                        +       '<i class="fab fa-github"></i></a>'
                        +   '<button class="btn btn-xs btn-danger btn-delete-release" data-id="' + r.id + '" data-tag="' + r.tag_name + '">'
                        +       '<i class="fas fa-trash"></i></button>'
                        + '</td>'
                        + '</tr>';
                });
                $('#releasesTbody').html(rows);
                $('#releasesContainer').show();
            })
            .fail(function () { showAlert('danger', 'Failed to load releases.'); })
            .always(function () { $('#releasesLoading').hide(); });
    }

    // Auto-load on page open if a repo is selected
    if (currentRepoId) loadReleases(currentRepoId);

    $('#btnLoadReleases').on('click', function () {
        var id = $('#repoSelector').val();
        if (id) loadReleases(id);
    });

    // ── Edit release ──────────────────────────────────────────────────────
    $(document).on('click', '.btn-edit-release', function () {
        var btn = $(this);
        $('#modal_release_id').val(btn.data('id'));
        $('#modal_repo_id').val(currentRepoId);
        $('#modal_release_title').val(btn.data('title'));
        $('#modal_release_notes').val(decodeURIComponent(btn.data('body')));
        $('#modal_prerelease').prop('checked', btn.data('prerelease') == true || btn.data('prerelease') == 'true');
        $('#modal_draft').prop('checked', btn.data('draft') == true || btn.data('draft') == 'true');
        $('#modalAlert').html('');
        $('#editReleaseModal').modal('show');
    });

    $('#btnSaveRelease').on('click', function () {
        var btn = $(this);
        btn.html('<i class="fas fa-circle-notch fa-spin"></i>').prop('disabled', true);
        $.ajax({
            url: '{{ route("github.update-release") }}',
            type: 'POST',
            data: {
                _token:        '{{ csrf_token() }}',
                repo_id:       $('#modal_repo_id').val(),
                release_id:    $('#modal_release_id').val(),
                release_title: $('#modal_release_title').val().trim(),
                release_notes: $('#modal_release_notes').val().trim(),
                prerelease:    $('#modal_prerelease').is(':checked') ? 1 : 0,
                draft:         $('#modal_draft').is(':checked') ? 1 : 0,
            },
            success: function (res) {
                $('#editReleaseModal').modal('hide');
                showAlert('success', '<i class="fa fa-check mr-1"></i> ' + (res.update || res.message));
                loadReleases(currentRepoId);
            },
            error: function (xhr) {
                var msg = xhr.responseJSON ? (xhr.responseJSON.message || xhr.responseJSON.error) : 'Error occurred.';
                $('#modalAlert').html('<div class="alert alert-danger">' + msg + '</div>');
            },
            complete: function () { btn.html('<i class="fas fa-save mr-1"></i> Save Changes').prop('disabled', false); }
        });
    });

    // ── Promote release ───────────────────────────────────────────────────
    $(document).on('click', '.btn-promote-release', function () {
        if (!confirm('Promote this pre-release to an official release?')) return;
        var btn       = $(this);
        var releaseId = btn.data('id');
        btn.html('<i class="fas fa-circle-notch fa-spin"></i>').prop('disabled', true);
        $.ajax({
            url: '{{ route("github.promote-release") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', repo_id: currentRepoId, release_id: releaseId },
            success: function (res) {
                showAlert('success', '<i class="fa fa-check mr-1"></i> ' + (res.update || res.message));
                loadReleases(currentRepoId);
            },
            error: function (xhr) {
                var msg = xhr.responseJSON ? (xhr.responseJSON.message || xhr.responseJSON.error) : 'Error.';
                showAlert('danger', msg);
                btn.html('<i class="fas fa-crown"></i> Promote').prop('disabled', false);
            }
        });
    });

    // ── Delete release ────────────────────────────────────────────────────
    $(document).on('click', '.btn-delete-release', function () {
        var tag = $(this).data('tag');
        if (!confirm('Delete release "' + tag + '"? The tag will remain on GitHub.')) return;
        var btn       = $(this);
        var releaseId = btn.data('id');
        btn.html('<i class="fas fa-circle-notch fa-spin"></i>').prop('disabled', true);
        $.ajax({
            url: '{{ route("github.delete-release") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', _method: 'DELETE', repo_id: currentRepoId, release_id: releaseId },
            success: function (res) {
                showAlert('success', '<i class="fa fa-check mr-1"></i> ' + (res.update || res.message));
                loadReleases(currentRepoId);
            },
            error: function (xhr) {
                var msg = xhr.responseJSON ? (xhr.responseJSON.message || xhr.responseJSON.error) : 'Error.';
                showAlert('danger', msg);
                btn.html('<i class="fas fa-trash"></i>').prop('disabled', false);
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
