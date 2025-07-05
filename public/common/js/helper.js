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
                           refresh = false,
                       }) {
        if (!message){
            return;
        }

        const container = document.querySelector(containerSelector);
        if (!container){
            return;
        }

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

        // Create icon element
        const iconElement = document.createElement('i');
        iconElement.className = `fa ${iconClass} mr-1`;

        // Create message text node to prevent HTML injection
        const messageText = document.createTextNode(` ${message}`);

        // Append icon and message safely
        alertDiv.appendChild(iconElement);
        alertDiv.appendChild(messageText);

        // Dismiss button
        if (dismissible) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'close';
            btn.setAttribute('data-dismiss', 'alert');
            btn.setAttribute('aria-hidden', 'true');
            btn.innerHTML = '&times;';
            btn.onclick = () => {
                alertDiv.remove();
                if (refresh) {
                    window.location.reload(true);
                }
            };
            alertDiv.appendChild(btn);
        }

        container.prepend(alertDiv);

        // Auto-dismiss after timeout
        if (autoDismiss !== null && !isNaN(autoDismiss) && autoDismiss > 0) {
            alertTimeouts[id] = setTimeout(() => {
                alertDiv.classList.remove('show');
                alertDiv.classList.add('hide');
                setTimeout(() => {
                    alertDiv.remove();
                    if (refresh) {
                        window.location.reload(true);
                    }
                }, 200);
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

    /**
     * Input restrictions
     */
    const restrictions = {
        "only-numbers": "0-9",
        "only-letters": "a-zA-Z",
        "only-alphanum": "a-zA-Z0-9",
        "only-decimal": "0-9\\.",       // numbers + dot
        "only-hex": "0-9a-fA-F",        // hexadecimal
        "only-username": "a-zA-Z0-9_-", // username chars
    };

    function applyRestriction(el, pattern) {
        if (!pattern) return;
        el.value = el.value.replace(new RegExp(`[^${pattern}]`, 'g'), '');
    }

    function getPattern(el) {
        // Priority: class restriction → data-allow attribute
        for (let cls in restrictions) {
            if (el.classList.contains(cls)) return restrictions[cls];
        }
        return el.getAttribute("data-allow");
    }

    function initRestrictions() {
        ["input", "paste"].forEach(evt => {
            document.addEventListener(evt, (e) => {
                const el = e.target;
                if (!(el instanceof HTMLInputElement || el instanceof HTMLTextAreaElement)) return;

                const pattern = getPattern(el);
                if (pattern) {
                    // Use setTimeout for paste to allow value update
                    evt === "paste" ? setTimeout(() => applyRestriction(el, pattern), 0) : applyRestriction(el, pattern);
                }
            });
        });
    }

    // Run immediately
    initRestrictions();

    return {
        showAlert,
        globalLoader,
        addRestriction: (className, regex) => { restrictions[className] = regex; },
    };
})();
