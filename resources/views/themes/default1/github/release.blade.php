@extends('themes.default1.layouts.master')
@section('title') {{ __('message.create_release') }} @stop
@section('content-header')
    <div class="col-sm-6"><h1>{{ __('message.create_release') }}</h1></div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ url('settings') }}">{{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.create_release') }}</li>
        </ol>
    </div>
@stop
@section('content')
<div class="card card-secondary card-outline">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fab fa-github mr-1"></i>
            <span id="formModeTitle">{{ __('message.create_release') }}</span>
        </h3>
        <div class="card-tools">
            <a href="{{ route('github.manage-releases') }}" class="btn btn-sm btn-secondary mr-1">
                <i class="fas fa-list mr-1"></i> {{ __('message.manage_releases') }}
            </a>
            <a href="{{ route('github.repos.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-cogs mr-1"></i> {{ __('message.manage_repos') }}
            </a>
        </div>
    </div>

    <div class="card-body">
        <div id="alertMessage"></div>

        @if ($repos->isEmpty())
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                No repositories configured.
                <a href="{{ route('github.repos.index') }}" class="alert-link">Add a repo first</a>.
            </div>
        @else

        {{-- Row 1: Repo selector + Tag name --}}
        <div class="row">
            <div class="col-md-5 form-group">
                <label class="required">Repository</label>
                <select id="repo_id" class="form-control">
                    @foreach ($repos as $r)
                        <option value="{{ $r->id }}">{{ $r->display_name }} &nbsp;({{ $r->owner }}/{{ $r->repo }})</option>
                    @endforeach
                </select>
                <small class="form-text text-muted" id="latestTagHint"></small>
            </div>

            <div class="col-md-4 form-group">
                <label class="required" for="tag_name">{{ __('message.tag_name') }}</label>
                <div class="input-group">
                    <input type="text" id="tag_name" class="form-control"
                        placeholder="e.g. v4.0.2.7 or v4.0.2.7.RC.1" autocomplete="off" />
                    <div class="input-group-append">
                        <span class="input-group-text px-2" id="tagCheckSpinner" style="display:none;">
                            <i class="fas fa-circle-notch fa-spin"></i>
                        </span>
                    </div>
                </div>
                <small class="form-text text-muted">{{ __('message.tag_name_hint') }}</small>
                <div id="tagStatusBadge" class="mt-1"></div>
            </div>

            <div class="col-md-3 form-group">
                <label class="required" for="release_title">{{ __('message.release_title') }}</label>
                <input type="text" id="release_title" class="form-control"
                    placeholder="e.g. Faveo Helpdesk Advance v4.0.2.7" />
            </div>
        </div>

        {{-- Target branch (only for new tags) --}}
        <div class="row" id="branchRow">
            <div class="col-md-5 form-group">
                <label class="required" for="target_branch">{{ __('message.target_branch') }}</label>
                <input type="text" id="target_branch" class="form-control" value="development" />
                <small class="form-text text-muted">{{ __('message.target_branch_hint') }}</small>
            </div>
            <div class="col-md-7 form-group d-flex align-items-center mt-3">
                <div class="alert alert-info w-100 mb-0 py-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    GitHub Actions will rebase <code id="infoRepo">...</code>:
                    <code>development</code> → <code>master</code>, create tag and publish release.
                </div>
            </div>
        </div>

        {{-- Release notes --}}
        <div class="row">
            <div class="col-md-12 form-group">
                <label class="required" for="release_notes">{{ __('message.release_notes') }}</label>
                <textarea id="release_notes" class="form-control" rows="9"
                    placeholder="{{ __('message.release_notes_placeholder') }}"></textarea>
            </div>
        </div>

        {{-- Pre-release / Draft --}}
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="custom-control custom-checkbox custom-control-inline">
                    <input type="checkbox" class="custom-control-input" id="prerelease">
                    <label class="custom-control-label" for="prerelease">
                        {{ __('message.pre_release') }}
                        <small class="text-muted">&mdash; {{ __('message.pre_release_hint') }}</small>
                    </label>
                </div>
                <div class="custom-control custom-checkbox custom-control-inline ml-3">
                    <input type="checkbox" class="custom-control-input" id="draft">
                    <label class="custom-control-label" for="draft">
                        {{ __('message.draft') }}
                        <small class="text-muted">&mdash; {{ __('message.draft_hint') }}</small>
                    </label>
                </div>
            </div>
        </div>

        {{-- Action buttons --}}
        <div class="row">
            <div class="col-md-12">
                <button type="button" id="btnTriggerWorkflow" class="btn btn-primary" style="display:none;">
                    <i class="fas fa-rocket mr-1"></i> Trigger Release Pipeline
                </button>
                <button type="button" id="btnCreateRelease" class="btn btn-success" style="display:none;">
                    <i class="fab fa-github mr-1"></i> {{ __('message.publish_release') }}
                </button>
                <button type="button" id="btnUpdateRelease" class="btn btn-warning" style="display:none;">
                    <i class="fas fa-edit mr-1"></i> Update Release Notes
                </button>
                <button type="button" id="btnPromoteRelease" class="btn btn-danger ml-2" style="display:none;">
                    <i class="fas fa-crown mr-1"></i> Promote to Official Release
                </button>
                <a id="btnViewOnGithub" href="#" target="_blank" class="btn btn-secondary ml-2" style="display:none;">
                    <i class="fab fa-github mr-1"></i> View on GitHub
                </a>
            </div>
        </div>

        <input type="hidden" id="release_id" value="" />
        @endif
    </div>
</div>

<script>
$(document).ready(function () {

    var checkTimer   = null;
    var currentState = null;

    // ── Repo changed: load latest tag ─────────────────────────────────────
    function loadLatestTag() {
        var repoId = $('#repo_id').val();
        var repoText = $('#repo_id option:selected').text();
        $('#infoRepo').text(repoText);
        $('#latestTagHint').text('');
        $.get('{{ route("github.latest-tag") }}', { repo_id: repoId })
            .done(function (data) {
                if (data.latest_tag) {
                    $('#latestTagHint').html('Latest: <strong>' + data.latest_tag + '</strong>');
                }
            });
    }

    $('#repo_id').on('change', function () {
        loadLatestTag();
        resetState();
        var tag = $('#tag_name').val().trim();
        if (tag.length >= 3) {
            clearTimeout(checkTimer);
            checkTimer = setTimeout(function () { checkTag(tag); }, 300);
        }
    });

    loadLatestTag();

    // ── Tag name input: auto-fill title + debounced check ─────────────────
    $('#tag_name').on('input', function () {
        var tag = $(this).val().trim();
        if (tag && !$('#release_title').data('manually-edited')) {
            var repoName = $('#repo_id option:selected').text().split('(')[0].trim();
            $('#release_title').val(repoName + ' ' + tag);
        }
        clearTimeout(checkTimer);
        resetState();
        if (tag.length < 3) return;
        checkTimer = setTimeout(function () { checkTag(tag); }, 500);
    });

    $('#release_title').on('input', function () { $(this).data('manually-edited', true); });

    // ── Check tag on GitHub ───────────────────────────────────────────────
    function checkTag(tag) {
        $('#tagCheckSpinner').show();
        $('#tagStatusBadge').html('');
        $.get('{{ route("github.check-tag") }}', { tag: tag, repo_id: $('#repo_id').val() })
            .done(function (data) {
                if (data.error) { showBadge('danger', '<i class="fas fa-times-circle mr-1"></i>' + data.error); return; }
                if (!data.tag_exists)           setState('new');
                else if (!data.release_exists)  setState('tag_only');
                else                            setState('has_release', data.release);
            })
            .fail(function () { showBadge('danger', 'Could not reach GitHub API.'); })
            .always(function () { $('#tagCheckSpinner').hide(); });
    }

    // ── State machine ─────────────────────────────────────────────────────
    function setState(state, release) {
        currentState = state;
        hideAllButtons();

        if (state === 'new') {
            showBadge('success', '<i class="fas fa-tag mr-1"></i> New tag — full pipeline will run');
            $('#branchRow').show();
            $('#btnTriggerWorkflow').show();
            $('#formModeTitle').text('{{ __("message.create_release") }}');
        }
        if (state === 'tag_only') {
            showBadge('warning', '<i class="fas fa-exclamation-circle mr-1"></i> Tag exists — no release yet');
            $('#branchRow').hide();
            $('#btnCreateRelease').show();
            $('#formModeTitle').text('Publish Release for Existing Tag');
        }
        if (state === 'has_release') {
            showBadge('info',
                '<i class="fab fa-github mr-1"></i> Release exists'
                + (release.prerelease ? ' &nbsp;<span class="badge badge-warning">Pre-release</span>' : ' &nbsp;<span class="badge badge-success">Official</span>')
                + (release.draft ? ' &nbsp;<span class="badge badge-secondary">Draft</span>' : '')
            );
            $('#branchRow').hide();
            $('#release_title').val(release.name).data('manually-edited', true);
            $('#release_notes').val(release.body);
            $('#prerelease').prop('checked', release.prerelease);
            $('#draft').prop('checked', release.draft);
            $('#release_id').val(release.id);
            $('#btnUpdateRelease').show();
            if (release.prerelease) $('#btnPromoteRelease').show();
            if (release.html_url) $('#btnViewOnGithub').attr('href', release.html_url).show();
            $('#formModeTitle').text('Update Release');
        }
    }

    function resetState() {
        currentState = null;
        hideAllButtons();
        $('#tagStatusBadge').html('');
        $('#branchRow').show();
        $('#release_id').val('');
        $('#prerelease, #draft').prop('checked', false);
        $('#formModeTitle').text('{{ __("message.create_release") }}');
    }

    function hideAllButtons() {
        $('#btnTriggerWorkflow, #btnCreateRelease, #btnUpdateRelease, #btnPromoteRelease, #btnViewOnGithub').hide();
    }

    function showBadge(type, html) {
        $('#tagStatusBadge').html('<span class="badge badge-' + type + ' p-2">' + html + '</span>');
    }

    // ── Validation ────────────────────────────────────────────────────────
    function validate(fields) {
        var ok = true;
        $('.is-invalid').removeClass('is-invalid');
        $('.error.invalid-feedback').remove();
        fields.forEach(function (f) {
            if (!$(f.el).val().trim()) {
                $(f.el).addClass('is-invalid')
                    .after('<span class="error invalid-feedback">' + f.label + ' is required.</span>');
                ok = false;
            }
        });
        return ok;
    }

    // ── POST helper ───────────────────────────────────────────────────────
    function submitAjax(url, data, btn, originalHtml) {
        btn.html("<i class='fas fa-circle-notch fa-spin'></i> {{ __('message.please_wait') }}").prop('disabled', true);
        $.ajax({
            url: url, type: 'POST',
            data: $.extend({ _token: '{{ csrf_token() }}' }, data),
            success: function (res) {
                var html = '<div class="alert alert-success alert-dismissable">'
                    + '<button type="button" class="close" data-dismiss="alert">&times;</button>'
                    + '<strong><i class="fa fa-check"></i> Success!</strong> ' + (res.update || res.message) + '</div>';
                if (res.html_url) html += '<a href="' + res.html_url + '" target="_blank" class="btn btn-sm btn-outline-secondary mt-1"><i class="fab fa-github mr-1"></i>View on GitHub</a>';
                $('#alertMessage').html(html).show();
                $('html,body').animate({ scrollTop: 0 }, 400);
                if (res.html_url) $('#btnViewOnGithub').attr('href', res.html_url).show();
            },
            error: function (xhr) {
                var msg = xhr.responseJSON ? (xhr.responseJSON.message || xhr.responseJSON.error || 'Something went wrong.') : 'Something went wrong.';
                $('#alertMessage').html('<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert">&times;</button><strong><i class="fa fa-times"></i> Failed!</strong> ' + msg + '</div>').show();
                $('html,body').animate({ scrollTop: 0 }, 400);
            },
            complete: function () { btn.html(originalHtml).prop('disabled', false); }
        });
    }

    function commonData() {
        return {
            repo_id:       $('#repo_id').val(),
            tag_name:      $('#tag_name').val().trim(),
            release_title: $('#release_title').val().trim(),
            release_notes: $('#release_notes').val().trim(),
            prerelease:    $('#prerelease').is(':checked') ? 1 : 0,
            draft:         $('#draft').is(':checked') ? 1 : 0,
        };
    }

    var baseFields = [
        { el: '#tag_name',      label: '{{ __("message.tag_name") }}' },
        { el: '#release_title', label: '{{ __("message.release_title") }}' },
        { el: '#release_notes', label: '{{ __("message.release_notes") }}' },
    ];

    $('#btnTriggerWorkflow').on('click', function () {
        if (!validate(baseFields)) return;
        submitAjax('{{ route("github.trigger-workflow") }}', commonData(), $(this), $(this).html());
    });

    $('#btnCreateRelease').on('click', function () {
        if (!validate(baseFields)) return;
        submitAjax('{{ route("github.post-create-release") }}', commonData(), $(this), $(this).html());
    });

    $('#btnUpdateRelease').on('click', function () {
        if (!validate([baseFields[1], baseFields[2]])) return;
        submitAjax('{{ route("github.update-release") }}',
            $.extend(commonData(), { release_id: $('#release_id').val() }),
            $(this), $(this).html());
    });

    $('#btnPromoteRelease').on('click', function () {
        if (!confirm('Promote this to an official release?')) return;
        submitAjax('{{ route("github.promote-release") }}',
            { repo_id: $('#repo_id').val(), release_id: $('#release_id').val() },
            $(this), $(this).html());
    });
});
</script>
<script>
    $('ul.nav-sidebar a').filter(function () { return this.id == 'setting'; }).addClass('active');
    $('ul.nav-treeview a').filter(function () { return this.id == 'setting'; })
        .parentsUntil('.nav-sidebar > .nav-treeview').addClass('menu-open').prev('a').addClass('active');
</script>
@stop
