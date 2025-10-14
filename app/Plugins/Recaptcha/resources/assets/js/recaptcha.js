/**
 * Enhanced reCAPTCHA Manager - Singleton for handling v2, v2-invisible and v3 per form
 *
 * Usage Examples:
 *
 * 1. Initialize on specific elements with different modes:
 * const captcha1 = await RecaptchaManager.init(document.getElementById('captcha-container-1'), {
 *   mode: 'v2',
 *   v2SiteKey: 'YOUR_V2_SITE_KEY'
 * });
 * const captcha2 = await RecaptchaManager.init(document.getElementById('captcha-container-2'), {
 *   mode: 'v2-invisible',
 *   v2SiteKey: 'YOUR_V2_SITE_KEY'
 * });
 * const captcha3 = await RecaptchaManager.init(document.getElementById('captcha-container-3'), {
 *   mode: 'v3',
 *   v3SiteKey: 'YOUR_V3_SITE_KEY'
 * });
 *
 * 2. Disable reCAPTCHA entirely:
 * const disabledCaptcha = await RecaptchaManager.init(document.getElementById('captcha-container'), {
 *   disabled: true
 * });
 *
 * 3. Get tokens when needed:
 * const token1 = await captcha1.getToken(); // v2 token
 * const token2 = await captcha2.getToken(); // v2 invisible token (auto-executes)
 * const token3 = await captcha3.getToken('login_form'); // v3 token
 * const disabledToken = await disabledCaptcha.getToken(); // returns null immediately
 *
 * 4. Handle failed verification (reset v2 widget):
 * captcha1.reset();
 * captcha2.reset();
 *
 * 5. Global configuration with disabled option:
 * RecaptchaManager.setGlobalConfig({
 *   v2SiteKey: 'YOUR_V2_SITE_KEY',
 *   v3SiteKey: 'YOUR_V3_SITE_KEY',
 *   defaultMode: 'v2-invisible',
 *   disabled: true, // Globally disable all reCAPTCHA
 *   theme: 'dark',
 *   size: 'invisible',
 *   badge: 'bottomright',
 *   tabindex: 0,
 *   isolated: false,
 *   debug: true
 * });
 */

var RecaptchaManager = (function() {
    var instance = null;

    function getInstance() {
        if (!instance) {
            instance = new RecaptchaManagerClass();
        }
        return instance;
    }

    // Constructor
    function RecaptchaManagerClass() {
        if (instance) {
            throw new Error('RecaptchaManager is a singleton. Use RecaptchaManager.getInstance() instead.');
        }

        this.defaultConfig = {
            theme: 'light',
            size: 'normal',
            badge: 'bottomright', // for invisible: bottomright, bottomleft, inline
            tabindex: 0,
            isolated: false,
            debug: false,
            fallback: false,
            disabled: false, // NEW: disable reCAPTCHA entirely
            lang: 'en', // NEW: language configuration
            validationErrorMessage: 'reCAPTCHA validation failed. Please try again.',
        };

        this.globalSiteKeys = {
            v2: null,
            v3: null
        };

        this.defaultMode = null;
        this.globalDisabled = false; // NEW: global disable flag

        this.scripts = {
            v2: { loaded: false, loading: null },
            v3: { loaded: false, loading: null }
        };

        this.instances = new Map(); // Track all instances
        this.instanceCounter = 0;
    }

    /**
     * Set global configuration including default mode and site keys
     */
    function setGlobalConfig(config) {
        var manager = getInstance();

        if (config.v2SiteKey !== undefined) { manager.globalSiteKeys.v2 = config.v2SiteKey; }
        if (config.v3SiteKey !== undefined) { manager.globalSiteKeys.v3 = config.v3SiteKey; }
        if (config.defaultMode !== undefined) { manager.defaultMode = config.defaultMode; }
        if (config.disabled !== undefined) { manager.globalDisabled = config.disabled; } // NEW
        if (config.theme !== undefined) { manager.defaultConfig.theme = config.theme; }
        if (config.size !== undefined) { manager.defaultConfig.size = config.size; }
        if (config.badge !== undefined) { manager.defaultConfig.badge = config.badge; }
        if (config.tabindex !== undefined) { manager.defaultConfig.tabindex = config.tabindex; }
        if (config.isolated !== undefined) { manager.defaultConfig.isolated = config.isolated; }
        if (config.debug !== undefined) { manager.defaultConfig.debug = config.debug; }
        if (config.fallback !== undefined) { manager.defaultConfig.fallback = config.fallback; }
        if (config.lang !== undefined) { manager.defaultConfig.lang = config.lang; } // NEW
        if (config.validationErrorMessage !== undefined) { manager.defaultConfig.validationErrorMessage = config.validationErrorMessage; }

        manager.log('Global configuration updated', config);
    }

    /**
     * Legacy method for backward compatibility
     */
    function setGlobalSiteKeys(v2SiteKey, v3SiteKey) {
        var manager = getInstance();
        manager.globalSiteKeys.v2 = v2SiteKey || null;
        manager.globalSiteKeys.v3 = v3SiteKey || null;
        manager.log('Global site keys updated');
    }

    /**
     * Initialize reCAPTCHA on a container element
     */
    function init(containerElement, config) {
        var manager = getInstance();

        if (!containerElement || !(containerElement instanceof HTMLElement)) {
            return Promise.reject(new Error('A valid containerElement must be provided.'));
        }

        config = config || {};
        var instanceConfig = {};
        for (var key in manager.defaultConfig) {
            if (manager.defaultConfig.hasOwnProperty(key)) {
                instanceConfig[key] = manager.defaultConfig[key];
            }
        }
        for (var key2 in config) {
            if (config.hasOwnProperty(key2)) {
                instanceConfig[key2] = config[key2];
            }
        }

        var instanceId = ++manager.instanceCounter;

        // NEW: Check if reCAPTCHA is disabled globally or for this instance
        var isDisabled = manager.globalDisabled || instanceConfig.disabled;

        if (isDisabled) {
            manager.log('Instance ' + instanceId + ' created in disabled mode - no scripts will be loaded');

            var disabledInstance = {
                id: instanceId,
                container: containerElement,
                config: instanceConfig,
                mode: 'disabled',
                v2WidgetId: null,
                v2Token: null,
                resolveV2: null,
                rejectV2: null,
                disabled: true
            };

            // Clear container and optionally show disabled message
            containerElement.innerHTML = instanceConfig.showDisabledMessage ?
                '<div class="recaptcha-disabled-message">reCAPTCHA is disabled</div>' : '';

            var recaptchaInstance = new RecaptchaInstance(disabledInstance, manager);
            manager.instances.set(instanceId, recaptchaInstance);

            return Promise.resolve(recaptchaInstance);
        }

        // Use global default mode if not specified in config
        var mode = config.mode || manager.defaultMode;

        if (!mode || !['v2', 'v2-invisible', 'v3'].includes(mode)) {
            return Promise.reject(new Error('Mode must be specified either in config or as global default ("v2", "v2-invisible", or "v3")'));
        }

        instanceConfig.mode = mode;

        var instance = {
            id: instanceId,
            container: containerElement,
            config: instanceConfig,
            mode: instanceConfig.mode,
            v2WidgetId: null,
            v2Token: null,
            resolveV2: null,
            rejectV2: null,
            disabled: false
        };

        return manager.loadRequiredScript(instanceConfig.mode, instanceConfig, config.forceReload)
            .then(function() {
                return manager.initializeInstance(instance);
            })
            .then(function() {
                var recaptchaInstance = new RecaptchaInstance(instance, manager);
                manager.instances.set(instanceId, recaptchaInstance);

                manager.log('Instance ' + instanceId + ' initialized successfully in ' + instance.mode + ' mode');
                return recaptchaInstance;
            })
            .catch(function(error) {
                manager.logError('Failed to initialize instance ' + instanceId + ':', error);
                throw error;
            });
    }

    /**
     * Load required script based on mode
     */
    RecaptchaManagerClass.prototype.loadRequiredScript = function(mode, config) {
        if (mode === 'v2' || mode === 'v2-invisible') {
            var v2SiteKey = config.v2SiteKey || this.globalSiteKeys.v2;
            if (!v2SiteKey) {
                return Promise.reject(new Error('v2SiteKey is required either in config or as global setting'));
            }
            return this.loadV2Script(config.lang); // Pass language parameter
        } else if (mode === 'v3') {
            var v3SiteKey = config.v3SiteKey || this.globalSiteKeys.v3;
            var batch = config.badge === 'inline' ? 'inline' : (config.badge === 'bottomleft' ? 'bottomleft' : 'bottomright');
            if (!v3SiteKey) {
                return Promise.reject(new Error('v3SiteKey is required either in config or as global setting'));
            }
            return this.loadV3Script(v3SiteKey, batch, config.forceReload, config.lang); // Pass language parameter
        }
    };

    /**
     * Load v2 script
     */
    RecaptchaManagerClass.prototype.loadV2Script = function(lang) {
        lang = lang || 'en';

        if (this.scripts.v2.loaded) {
            return Promise.resolve();
        }
        if (this.scripts.v2.loading) {
            return this.scripts.v2.loading;
        }

        var self = this;
        this.log('Loading reCAPTCHA v2 script...');

        this.scripts.v2.loading = new Promise(function(resolve, reject) {
            // Set up global callback before loading script
            window.initRecaptchaV2Callback = function() {
                self.log('reCAPTCHA v2 ready');
                self.scripts.v2.loaded = true;
                resolve();
            };

            var script = document.createElement('script');
            script.src = 'https://www.google.com/recaptcha/api.js?onload=initRecaptchaV2Callback&render=explicit&hl=' + lang;
            script.async = true;
            script.defer = true;

            script.onload = function() {
                // Fallback if callback wasn't called
                setTimeout(function() {
                    if (!self.scripts.v2.loaded && window.grecaptcha && window.grecaptcha.render) {
                        self.scripts.v2.loaded = true;
                        resolve();
                    }
                }, 1000);
            };

            script.onerror = function() {
                reject(new Error('Failed to load reCAPTCHA v2 script'));
            };

            document.head.appendChild(script);
        });

        return this.scripts.v2.loading;
    };

    /**
     * Load v3 script
     */
    RecaptchaManagerClass.prototype.loadV3Script = function(siteKey, batch, forceReload, lang) {
        lang = lang || 'en';

        if (this.scripts.v3.loaded && !forceReload) {
            return Promise.resolve();
        }
        if (this.scripts.v3.loading && !forceReload) {
            return this.scripts.v3.loading;
        }

        var self = this;
        this.log('Loading reCAPTCHA v3 script...');

        if (forceReload) {
            var existingScript = document.querySelector('script[src^="https://www.google.com/recaptcha/api.js?render="]');
            if (existingScript) {
                existingScript.remove();
                this.scripts.v3.loaded = false;
                this.scripts.v3.loading = null;
            }
        }

        this.scripts.v3.loading = new Promise(function(resolve, reject) {
            var script = document.createElement('script');
            script.src = 'https://www.google.com/recaptcha/api.js?render=' + siteKey + '&badge=' + batch + '&hl=' + lang;
            script.async = true;
            script.defer = true;

            script.onload = function() {
                var checkReady = function() {
                    if (window.grecaptcha && window.grecaptcha.ready) {
                        window.grecaptcha.ready(function() {
                            self.log('reCAPTCHA v3 ready');
                            self.scripts.v3.loaded = true;
                            resolve();
                        });
                    } else {
                        setTimeout(checkReady, 100);
                    }
                };
                checkReady();
            };

            script.onerror = function() {
                reject(new Error('Failed to load reCAPTCHA v3 script'));
            };

            document.head.appendChild(script);
        });

        return this.scripts.v3.loading;
    };

    /**
     * Initialize a specific instance
     */
    RecaptchaManagerClass.prototype.initializeInstance = function(instance) {
        if (instance.mode === 'v2') {
            return this.renderV2Widget(instance);
        } else if (instance.mode === 'v2-invisible') {
            return this.renderV2InvisibleWidget(instance);
        } else if (instance.mode === 'v3') {
            return this.renderV3Widget(instance);
        }
    };

    /**
     * Render v2 widget
     */
    RecaptchaManagerClass.prototype.renderV2Widget = function(instance) {
        var self = this;
        var config = instance.config;
        var container = instance.container;

        return this.waitForV2Ready().then(function() {
            var widgetId = 'recaptcha-widget-' + instance.id;
            container.innerHTML = '<div id="' + widgetId + '"></div>';

            var widgetContainer = container.querySelector('#' + widgetId);
            var siteKey = config.v2SiteKey || self.globalSiteKeys.v2;

            try {
                var renderParams = {
                    sitekey: siteKey,
                    theme: config.theme,
                    size: config.size,
                    tabindex: config.tabindex,
                    isolated: config.isolated,
                    callback: function(token) {
                        instance.v2Token = token;
                        if (instance.resolveV2) {
                            instance.resolveV2(token);
                            instance.resolveV2 = null;
                        }
                    },
                    'expired-callback': function() {
                        instance.v2Token = null;
                        if (instance.rejectV2) {
                            instance.rejectV2(new Error('reCAPTCHA expired'));
                            instance.rejectV2 = null;
                        }
                    },
                    'error-callback': function() {
                        instance.v2Token = null;
                        if (instance.rejectV2) {
                            instance.rejectV2(new Error('reCAPTCHA error'));
                            instance.rejectV2 = null;
                        }
                    }
                };

                instance.v2WidgetId = window.grecaptcha.render(widgetContainer, renderParams);

                self.log('v2 widget rendered for instance ' + instance.id);
            } catch (error) {
                self.logError('Error rendering v2 widget:', error);
                throw error;
            }
        });
    };

    /**
     * Render v2 invisible widget
     */
    RecaptchaManagerClass.prototype.renderV2InvisibleWidget = function(instance) {
        var self = this;
        var config = instance.config;
        var container = instance.container;

        return this.waitForV2Ready().then(function() {

            var globalBadge = document.querySelector('.grecaptcha-badge[data-hidden-by]');
            if (globalBadge) {
                globalBadge.style.display = '';
                globalBadge.removeAttribute('data-hidden-by');
            }

            var siteKey = config.v2SiteKey || self.globalSiteKeys.v2;
            var renderTarget;

            if (config.badge === 'bottomright' || config.badge === 'bottomleft') {
                var dummyContainerId = 'recaptcha-dummy-container-' + instance.id;
                var dummyEl = document.getElementById(dummyContainerId);
                if (!dummyEl) {
                    dummyEl = document.createElement('div');
                    dummyEl.id = dummyContainerId;
                    document.body.appendChild(dummyEl);
                }
                renderTarget = dummyEl;
                container.innerHTML = '';
            } else {
                var widgetId = 'recaptcha-invisible-widget-' + instance.id;
                container.innerHTML = '<div id="' + widgetId + '"></div>';
                renderTarget = container.querySelector('#' + widgetId);
            }

            try {
                // --- Start of fix ---
                var cleanupCallbacks = function() {
                    instance.resolveV2 = null;
                    instance.rejectV2 = null;
                };

                var renderParams = {
                    sitekey: siteKey,
                    size: 'invisible',
                    theme: config.theme,
                    badge: config.badge, // bottomright, bottomleft, inline
                    tabindex: config.tabindex,
                    isolated: config.isolated,
                    callback: function(token) {
                        instance.v2Token = token;
                        if (instance.resolveV2) {
                            instance.resolveV2(token);
                        }
                        cleanupCallbacks();
                    },
                    'expired-callback': function() {
                        instance.v2Token = null;
                        if (instance.rejectV2) {
                            instance.rejectV2(new Error('reCAPTCHA challenge expired. Please try again.'));
                        }
                        cleanupCallbacks();
                    },
                    'error-callback': function() {
                        instance.v2Token = null;
                        if (instance.rejectV2) {
                            // This handles the "Uncaught (in promise) null" case by ensuring a proper Error is always sent.
                            instance.rejectV2(new Error('reCAPTCHA challenge failed or was closed by the user.'));
                        }
                        cleanupCallbacks();
                    }
                };

                instance.v2WidgetId = window.grecaptcha.render(renderTarget, renderParams);
                self.log('v2 invisible widget rendered for instance ' + instance.id);
            } catch (error) {
                self.logError('Error rendering v2 invisible widget:', error);
                throw error;
            }
        });
    };

    /**
     * Render v3 widget
     */
    RecaptchaManagerClass.prototype.renderV3Widget = function(instance) {
        var self = this;
        var container = instance.container;
        var config = instance.config;

        return this.waitForV3Ready().then(function() {
            if (config.badge === 'inline') {
                // Hide the global badge (only while inline mode is active)
                var globalBadge = document.querySelector('.grecaptcha-badge');
                if (globalBadge) {
                    globalBadge.style.display = 'none';
                    globalBadge.setAttribute('data-hidden-by', instance.id); // track who hid it
                }

                // Delay clone
                setTimeout(function() {
                    var originalBadge = document.querySelector('.grecaptcha-badge');
                    if (originalBadge) {
                        var badgeClone = originalBadge.cloneNode(true);
                        badgeClone.style.position = 'static';
                        badgeClone.style.marginTop = '10px';
                        badgeClone.style.display = 'inline-block';
                        badgeClone.style.transform = 'none';
                        badgeClone.style.visibility = 'visible';
                        badgeClone.classList.add('custom-recaptcha-badge');
                        container.appendChild(badgeClone);

                        self.log('v3 inline badge rendered for instance ' + instance.id);
                    }
                }, 500);
            }

            self.log('v3 widget rendered for instance ' + instance.id);
        });
    };

    /**
     * Wait for v2 to be ready
     */
    RecaptchaManagerClass.prototype.waitForV2Ready = function() {
        var self = this;
        return new Promise(function(resolve) {
            var check = function() {
                if (window.grecaptcha && window.grecaptcha.render) {
                    resolve();
                } else {
                    setTimeout(check, 100);
                }
            };
            check();
        });
    };

    /**
     * Wait for v3 to be ready
     */
    RecaptchaManagerClass.prototype.waitForV3Ready = function() {
        var self = this;
        return new Promise(function(resolve) {
            var check = function() {
                if (window.grecaptcha && window.grecaptcha.ready) {
                    window.grecaptcha.ready(resolve);
                } else {
                    setTimeout(check, 100);
                }
            };
            check();
        });
    };

    /**
     * Logging utilities
     */
    RecaptchaManagerClass.prototype.log = function() {
        if (this.defaultConfig.debug) {
            var args = Array.prototype.slice.call(arguments);
            console.log.apply(console, ['[RecaptchaManager]'].concat(args));
        }
    };

    RecaptchaManagerClass.prototype.logError = function() {
        var args = Array.prototype.slice.call(arguments);
        console.error.apply(console, ['[RecaptchaManager]'].concat(args));
    };

    // Public API
    return {
        getInstance: getInstance,
        setGlobalConfig: setGlobalConfig,
        setGlobalSiteKeys: setGlobalSiteKeys,
        init: init
    };
})();

/**
 * Represents a single reCAPTCHA instance
 */
function RecaptchaInstance(internalState, manager) {
    this.state = internalState;
    this.manager = manager;
    this.state.forceFallback = false;
}

/**
 * Toggle fallback (switch to v2)
 */
RecaptchaInstance.prototype.useFallback = function(force = true) {
    if (this.state.disabled) {
        this.manager.log(`Instance ${this.state.id} is disabled - fallback skipped`);
        return Promise.resolve();
    }

    if (!this.state.config.fallback) return Promise.resolve();

    const v2SiteKey = this.state.config.v2SiteKey || this.manager.globalSiteKeys.v2;
    if (!v2SiteKey) return Promise.resolve();

    this.state.forceFallback = force;

    const self = this;

    if (force && this.state.mode === 'v3') {
        return this.manager.loadV2Script(this.state.config.lang)
            .then(() => self.manager.renderV2Widget(self.state))
            .then(() => self.manager.log(`Instance ${self.state.id} switched to fallback (v2) mode`));
    } else if (!force) {
        return this.manager.renderV3Widget(this.state)
            .then(() => self.manager.log(`Instance ${self.state.id} switched back to v3 mode`));
    }

    return Promise.resolve();
};

/**
 * Uniform token retrieval
 */
RecaptchaInstance.prototype.getToken = function(action = 'default') {
    if (this.state.disabled) {
        this.manager.log(`Instance ${this.state.id} is disabled - returning null token`);
        return Promise.resolve(null);
    }

    if (this.state.forceFallback || this.state.mode === 'v2') {
        return this._getV2TokenSafe();
    }

    if (this.state.mode === 'v2-invisible') {
        return this._getV2InvisibleTokenSafe();
    }

    if (this.state.mode === 'v3') {
        return this._getV3TokenSafe(action);
    }

    this.manager.logError(`Invalid reCAPTCHA mode for instance ${this.state.id}`);
    return Promise.resolve(null);
};

// --- Internal safe wrappers ---

RecaptchaInstance.prototype._getV2TokenSafe = function() {
    if (this.state.v2Token) return Promise.resolve(this.state.v2Token);
    this.manager.log(`No v2 token available for instance ${this.state.id}`);
    return Promise.resolve(null);
};

RecaptchaInstance.prototype._getV2InvisibleTokenSafe = function() {
    const self = this;
    return new Promise(resolve => {
        self.getV2InvisibleToken()
            .then(token => resolve(token))
            .catch(error => {
                self.manager.logError(`v2-invisible token failed for instance ${self.state.id}`, error);
                resolve(null);
            });
    });
};

RecaptchaInstance.prototype._getV3TokenSafe = function(action) {
    const self = this;
    return new Promise(resolve => {
        self.getV3Token(action)
            .then(token => resolve(token))
            .catch(error => {
                self.manager.logError(`v3 token generation failed for instance ${self.state.id}`, error);
                resolve(null);
            });
    });
};

/**
 * Get v2 token (requires user interaction)
 */
RecaptchaInstance.prototype.getV2Token = function() {
    return this.state.v2Token ? Promise.resolve(this.state.v2Token) : Promise.reject(new Error('No v2 token available'));
};

/**
 * Get v2 invisible token (programmatically execute)
 */
RecaptchaInstance.prototype.getV2InvisibleToken = function() {
    const self = this;
    return new Promise((resolve, reject) => {
        self.state.resolveV2 = resolve;
        self.state.rejectV2 = reject;

        try {
            window.grecaptcha.execute(self.state.v2WidgetId)
                .then(() => self.state.resolveV2())
                .catch(error => {
                    self.state.resolveV2 = null;
                    self.state.rejectV2 = null;
                    reject(error);
                });
            self.manager.log(`v2 invisible execution triggered for instance ${self.state.id}`);
        } catch (error) {
            self.state.resolveV2 = null;
            self.state.rejectV2 = null;
            reject(error);
        }
    });
};

/**
 * Get v3 token (automatic execution)
 */
RecaptchaInstance.prototype.getV3Token = function(action) {
    const siteKey = this.state.config.v3SiteKey || this.manager.globalSiteKeys.v3;
    const self = this;

    return window.grecaptcha.execute(siteKey, { action })
        .then(token => {
            self.manager.log(`v3 token generated for action: ${action}`);
            return token;
        })
        .catch(error => {
            self.manager.logError('v3 execution failed:', error);
            throw error;
        });
};

/**
 * Validate token
 */
RecaptchaInstance.prototype.tokenValidation = function(action = 'default') {
    const container = this.state.container || document.getElementById('recaptcha-container');

    if (container && container.querySelector('.invalid-feedback')) {
        container.querySelector('.invalid-feedback').remove();
    }

    if (this.state.disabled) return Promise.resolve(true);

    const errorMessage = this.state.config.validationErrorMessage || 'reCAPTCHA validation failed. Please try again.';

    return this.getToken(action)
        .then(token => {
            if (!token) throw new Error('No token received');
            return token;
        })
        .catch(error => {
            if (container) {
                const errorEl = document.createElement('div');
                errorEl.className = 'invalid-feedback d-block';
                errorEl.textContent = errorMessage;
                container.appendChild(errorEl);
            }
            return false;
        });
};

/**
 * Reset widget
 */
RecaptchaInstance.prototype.reset = function() {
    if (this.state.disabled) {
        this.manager.log(`Instance ${this.state.id} is disabled - reset skipped`);
        return;
    }

    const isV2Mode = this.state.mode === 'v2' || this.state.mode === 'v2-invisible';
    const isFallbackV2 = this.state.forceFallback && this.state.v2WidgetId !== null;

    if ((isV2Mode || isFallbackV2) && this.state.v2WidgetId !== null) {
        try {
            window.grecaptcha.reset(this.state.v2WidgetId);
            this.state.v2Token = null;
            this.manager.log(`${isFallbackV2 ? 'fallback v2' : this.state.mode} widget ${this.state.id} reset`);
        } catch (error) {
            this.manager.logError(`Error resetting widget for instance ${this.state.id}:`, error);
        }
    }

    this.state.resolveV2 = null;
    this.state.rejectV2 = null;
};

/**
 * Execute v2-invisible manually
 */
RecaptchaInstance.prototype.execute = function() {
    if (this.state.disabled) {
        this.manager.log(`Instance ${this.state.id} is disabled - execute returning null`);
        return Promise.resolve(null);
    }

    if (this.state.mode !== 'v2-invisible') {
        return Promise.reject(new Error('Execute method is only available for v2-invisible mode'));
    }

    return this._getV2InvisibleTokenSafe();
};

/**
 * Get current mode
 */
RecaptchaInstance.prototype.getCurrentMode = function() {
    return this.state.disabled ? 'disabled' : this.state.mode;
};

/**
 * Check token availability
 */
RecaptchaInstance.prototype.hasToken = function() {
    return this.state.disabled || this.state.v2Token !== null;
};

/**
 * Check if disabled
 */
RecaptchaInstance.prototype.isDisabled = function() {
    return !!this.state.disabled;
};

/**
 * Get state info
 */
RecaptchaInstance.prototype.getState = function() {
    return {
        id: this.state.id,
        mode: this.state.mode,
        disabled: !!this.state.disabled,
        hasV2Widget: this.state.v2WidgetId !== null,
        v2Token: this.state.v2Token,
        hasToken: this.hasToken(),
        config: { ...this.state.config }
    };
};

/**
 * Destroy instance
 */
RecaptchaInstance.prototype.destroy = function() {
    if (this.state.disabled) {
        this.state.container.innerHTML = '';
        this.manager.instances.delete(this.state.id);
        this.manager.log(`Disabled instance ${this.state.id} destroyed`);
        return;
    }

    if ((this.state.mode === 'v2' || this.state.mode === 'v2-invisible') && this.state.v2WidgetId !== null) {
        try { this.reset(); } catch (error) { this.manager.logError('Error during cleanup:', error); }
    }

    this.state.container.innerHTML = '';

    const inlineStyles = document.querySelector(`#recaptcha-inline-styles-${this.state.id}`);
    if (inlineStyles) inlineStyles.remove();

    this.manager.instances.delete(this.state.id);
    this.manager.log(`Instance ${this.state.id} destroyed`);
};

// Export for use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = RecaptchaManager;
} else if (typeof window !== 'undefined') {
    window.RecaptchaManager = RecaptchaManager;
}