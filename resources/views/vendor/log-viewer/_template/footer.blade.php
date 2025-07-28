<footer class="main-footer">
    <div class="container">
        <p class="text-muted pull-left">
            {{ __('message.log_viewer') }} <span class="label label-info">{{ __('message.version') }} {{ log_viewer()->version() }}</span>
        </p>
        <p class="text-muted pull-right">
            {{ __('message.created_with') }} <i class="fa fa-heart"></i> {{ __('message.by_arcanedev') }} <sup>&copy;</sup>
        </p>
    </div>
</footer>
