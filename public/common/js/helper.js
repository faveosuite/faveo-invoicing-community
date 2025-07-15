window.helper = (() => {
    const alertTimeouts = {};

    function showAlert({
                           message,
                           type = 'info',
                           autoDismiss = 5000,
                           containerSelector = 'body',
                           dismissible = true,
                           additionalClasses = '',
                           id = 'custom-alert',
                       }) {
        if (!message) return;

        const container = document.querySelector(containerSelector);
        if (!container) return;

        const isSuccess = type === 'success';
        const iconClass = isSuccess ? 'fa-check-circle' : 'fa-ban';
        const alertClass = isSuccess ? 'alert-success' : 'alert-danger';

        // Remove existing alert if present
        const existingAlert = document.getElementById(id);
        if (existingAlert) {
            clearTimeout(alertTimeouts[id]);
            existingAlert.remove();
        }

        const alertDiv = document.createElement('div');
        alertDiv.id = id;
        alertDiv.className = `alert ${alertClass} ${dismissible ? 'alert-dismissible' : ''} ${additionalClasses}`;
        alertDiv.setAttribute('role', 'alert');

        // Icon + message
        alertDiv.innerHTML = `<i class="fa ${iconClass} mr-1"></i> ${message}`;

        // Dismiss button
        if (dismissible) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'close';
            btn.setAttribute('data-dismiss', 'alert');
            btn.setAttribute('aria-hidden', 'true');
            btn.innerHTML = '&times;';
            alertDiv.appendChild(btn);
        }

        container.prepend(alertDiv);

        // Auto-dismiss after timeout
        if (autoDismiss !== null && !isNaN(autoDismiss) && autoDismiss > 0) {
            alertTimeouts[id] = setTimeout(() => {
                alertDiv.classList.remove('show');
                alertDiv.classList.add('hide');
                setTimeout(() => alertDiv.remove(), 200);
            }, autoDismiss);
        }
    }

    const globalLoader = {
        show: function () {
            const loader = document.getElementById('global-loader');
            if (loader) {
                loader.style.display = 'flex';
            }
        },

        hide: function () {
            const loader = document.getElementById('global-loader');
            if (loader) {
                loader.style.display = 'none';
            }
        }
    };

    return {
        showAlert,
        globalLoader,
    };
})();
