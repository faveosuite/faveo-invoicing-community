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

class RecaptchaManager {
    static instance = null;

    static getInstance() {
        if (!RecaptchaManager.instance) {
            RecaptchaManager.instance = new RecaptchaManager();
        }
        return RecaptchaManager.instance;
    }

    constructor() {
        if (RecaptchaManager.instance) {
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
    static setGlobalConfig(config = {}) {
        const manager = RecaptchaManager.getInstance();

        if (config.v2SiteKey !== undefined) manager.globalSiteKeys.v2 = config.v2SiteKey;
        if (config.v3SiteKey !== undefined) manager.globalSiteKeys.v3 = config.v3SiteKey;
        if (config.defaultMode !== undefined) manager.defaultMode = config.defaultMode;
        if (config.disabled !== undefined) manager.globalDisabled = config.disabled; // NEW
        if (config.theme !== undefined) manager.defaultConfig.theme = config.theme;
        if (config.size !== undefined) manager.defaultConfig.size = config.size;
        if (config.badge !== undefined) manager.defaultConfig.badge = config.badge;
        if (config.tabindex !== undefined) manager.defaultConfig.tabindex = config.tabindex;
        if (config.isolated !== undefined) manager.defaultConfig.isolated = config.isolated;
        if (config.debug !== undefined) manager.defaultConfig.debug = config.debug;
        if (config.fallback !== undefined) manager.defaultConfig.fallback = config.fallback;

        manager.log('Global configuration updated', config);
    }

    /**
     * Legacy method for backward compatibility
     */
    static setGlobalSiteKeys(v2SiteKey = null, v3SiteKey = null) {
        const manager = RecaptchaManager.getInstance();
        manager.globalSiteKeys.v2 = v2SiteKey;
        manager.globalSiteKeys.v3 = v3SiteKey;
        manager.log('Global site keys updated');
    }

    /**
     * Initialize reCAPTCHA on a container element
     */
    static async init(containerElement, config = {}) {
        const manager = RecaptchaManager.getInstance();

        if (!containerElement || !(containerElement instanceof HTMLElement)) {
            throw new Error('A valid containerElement must be provided.');
        }

        const instanceConfig = { ...manager.defaultConfig, ...config };
        const instanceId = ++manager.instanceCounter;

        // NEW: Check if reCAPTCHA is disabled globally or for this instance
        const isDisabled = manager.globalDisabled || instanceConfig.disabled;

        if (isDisabled) {
            manager.log(`Instance ${instanceId} created in disabled mode - no scripts will be loaded`);

            const disabledInstance = {
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

            const recaptchaInstance = new RecaptchaInstance(disabledInstance, manager);
            manager.instances.set(instanceId, recaptchaInstance);

            return recaptchaInstance;
        }

        // Use global default mode if not specified in config
        const mode = config.mode || manager.defaultMode;

        if (!mode || !['v2', 'v2-invisible', 'v3'].includes(mode)) {
            throw new Error('Mode must be specified either in config or as global default ("v2", "v2-invisible", or "v3")');
        }

        instanceConfig.mode = mode;

        const instance = {
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

        try {
            await manager.loadRequiredScript(instanceConfig.mode, instanceConfig, config.forceReload);
            await manager.initializeInstance(instance);

            const recaptchaInstance = new RecaptchaInstance(instance, manager);
            manager.instances.set(instanceId, recaptchaInstance);

            manager.log(`Instance ${instanceId} initialized successfully in ${instance.mode} mode`);
            return recaptchaInstance;
        } catch (error) {
            manager.logError(`Failed to initialize instance ${instanceId}:`, error);
            throw error;
        }
    }

    /**
     * Load required script based on mode
     */
    async loadRequiredScript(mode, config) {
        if (mode === 'v2' || mode === 'v2-invisible') {
            const siteKey = config.v2SiteKey || this.globalSiteKeys.v2;
            if (!siteKey) {
                throw new Error('v2SiteKey is required either in config or as global setting');
            }
            await this.loadV2Script();
        } else if (mode === 'v3') {
            const siteKey = config.v3SiteKey || this.globalSiteKeys.v3;
            const batch = config.badge === 'inline' ? 'inline' : (config.badge === 'bottomleft' ? 'bottomleft' : 'bottomright');
            if (!siteKey) {
                throw new Error('v3SiteKey is required either in config or as global setting');
            }
            await this.loadV3Script(siteKey, batch, config.forceReload);
        }
    }

    /**
     * Load v2 script
     */
    async loadV2Script() {
        if (this.scripts.v2.loaded) return;
        if (this.scripts.v2.loading) return this.scripts.v2.loading;

        this.log('Loading reCAPTCHA v2 script...');

        this.scripts.v2.loading = new Promise((resolve, reject) => {
            // Set up global callback before loading script
            window.initRecaptchaV2Callback = () => {
                this.log('reCAPTCHA v2 ready');
                this.scripts.v2.loaded = true;
                resolve();
            };

            const script = document.createElement('script');
            script.src = `https://www.google.com/recaptcha/api.js?onload=initRecaptchaV2Callback&render=explicit`;
            script.async = true;
            script.defer = true;

            script.onload = () => {
                // Fallback if callback wasn't called
                setTimeout(() => {
                    if (!this.scripts.v2.loaded && window.grecaptcha && window.grecaptcha.render) {
                        this.scripts.v2.loaded = true;
                        resolve();
                    }
                }, 1000);
            };

            script.onerror = () => {
                reject(new Error('Failed to load reCAPTCHA v2 script'));
            };

            document.head.appendChild(script);
        });

        return this.scripts.v2.loading;
    }

    /**
     * Load v3 script
     */
    async loadV3Script(siteKey, batch, forceReload = false) {
        if (this.scripts.v3.loaded && !forceReload) return;
        if (this.scripts.v3.loading && !forceReload) return this.scripts.v3.loading;

        this.log('Loading reCAPTCHA v3 script...');

        if (forceReload) {
            const existingScript = document.querySelector('script[src^="https://www.google.com/recaptcha/api.js?render="]');
            if (existingScript) {
                existingScript.remove();
                this.scripts.v3.loaded = false;
                this.scripts.v3.loading = null;
            }
        }

        this.scripts.v3.loading = new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = `https://www.google.com/recaptcha/api.js?render=${siteKey}&badge=${batch}`;
            script.async = true;
            script.defer = true;

            script.onload = () => {
                const checkReady = () => {
                    if (window.grecaptcha && window.grecaptcha.ready) {
                        window.grecaptcha.ready(() => {
                            this.log('reCAPTCHA v3 ready');
                            this.scripts.v3.loaded = true;
                            resolve();
                        });
                    } else {
                        setTimeout(checkReady, 100);
                    }
                };
                checkReady();
            };

            script.onerror = () => {
                reject(new Error('Failed to load reCAPTCHA v3 script'));
            };

            document.head.appendChild(script);
        });

        return this.scripts.v3.loading;
    }

    /**
     * Initialize a specific instance
     */
    async initializeInstance(instance) {
        if (instance.mode === 'v2') {
            await this.renderV2Widget(instance);
        } else if (instance.mode === 'v2-invisible') {
            await this.renderV2InvisibleWidget(instance);
        } else if (instance.mode === 'v3') {
            await this.renderV3Widget(instance);
        }
    }

    /**
     * Render v2 widget
     */
    async renderV2Widget(instance) {
        const { config, container } = instance;

        await this.waitForV2Ready();

        const widgetId = `recaptcha-widget-${instance.id}`;
        container.innerHTML = `<div id="${widgetId}"></div>`;

        const widgetContainer = container.querySelector(`#${widgetId}`);
        const siteKey = config.v2SiteKey || this.globalSiteKeys.v2;

        try {
            const renderParams = {
                sitekey: siteKey,
                theme: config.theme,
                size: config.size,
                tabindex: config.tabindex,
                isolated: config.isolated,
                callback: (token) => {
                    instance.v2Token = token;
                    if (instance.resolveV2) {
                        instance.resolveV2(token);
                        instance.resolveV2 = null;
                    }
                },
                'expired-callback': () => {
                    instance.v2Token = null;
                    if (instance.rejectV2) {
                        instance.rejectV2(new Error('reCAPTCHA expired'));
                        instance.rejectV2 = null;
                    }
                },
                'error-callback': () => {
                    instance.v2Token = null;
                    if (instance.rejectV2) {
                        instance.rejectV2(new Error('reCAPTCHA error'));
                        instance.rejectV2 = null;
                    }
                }
            };

            instance.v2WidgetId = window.grecaptcha.render(widgetContainer, renderParams);

            this.log(`v2 widget rendered for instance ${instance.id}`);
        } catch (error) {
            this.logError('Error rendering v2 widget:', error);
            throw error;
        }
    }

    /**
     * Render v2 invisible widget
     */
    async renderV2InvisibleWidget(instance) {
        const { config, container } = instance;

        await this.waitForV2Ready();

        const siteKey = config.v2SiteKey || this.globalSiteKeys.v2;
        let renderTarget;

        // --- Start of fix ---

        // Conditionally select the render target based on the badge position.
        if (config.badge === 'bottomright' || config.badge === 'bottomleft') {
            // For corner positions, create a dedicated dummy container in the body.
            // This allows the reCAPTCHA script to position the badge correctly.
            const dummyContainerId = `recaptcha-dummy-container-${instance.id}`;
            let dummyEl = document.getElementById(dummyContainerId);
            if (!dummyEl) {
                dummyEl = document.createElement('div');
                dummyEl.id = dummyContainerId;
                document.body.appendChild(dummyEl);
            }
            renderTarget = dummyEl;

            // Clear the original container since it's not used for the badge itself.
            container.innerHTML = '';
        } else {
            // For 'inline' badges, render directly inside the provided container.
            const widgetId = `recaptcha-invisible-widget-${instance.id}`;
            container.innerHTML = `<div id="${widgetId}"></div>`;
            renderTarget = container.querySelector(`#${widgetId}`);
        }

        // --- End of fix ---

        try {
            const renderParams = {
                sitekey: siteKey,
                size: 'invisible',
                theme: config.theme,
                badge: config.badge, // bottomright, bottomleft, inline
                tabindex: config.tabindex,
                isolated: config.isolated,
                callback: (token) => {
                    instance.v2Token = token;
                    if (instance.resolveV2) {
                        instance.resolveV2(token);
                        instance.resolveV2 = null;
                    }
                },
                'expired-callback': () => {
                    instance.v2Token = null;
                    if (instance.rejectV2) {
                        instance.rejectV2(new Error('reCAPTCHA expired'));
                        instance.rejectV2 = null;
                    }
                },
                'error-callback': () => {
                    instance.v2Token = null;
                    if (instance.rejectV2) {
                        instance.rejectV2(new Error('reCAPTCHA error'));
                        instance.rejectV2 = null;
                    }
                }
            };

            // Pass the correctly chosen renderTarget to the reCAPTCHA API.
            instance.v2WidgetId = window.grecaptcha.render(renderTarget, renderParams);

            this.log(`v2 invisible widget rendered for instance ${instance.id}`);
        } catch (error) {
            this.logError('Error rendering v2 invisible widget:', error);
            throw error;
        }
    }

    /**
     * Render v3 widget
     */
    async renderV3Widget(instance) {
        const { container, config } = instance;

        await this.waitForV3Ready();

        // Hide global badge with CSS (only once)
        if (config.badge === 'inline' && !document.querySelector('#hide-global-recaptcha-style')) {
            const style = document.createElement('style');
            style.id = 'hide-global-recaptcha-style';
            style.textContent = `
            .grecaptcha-badge:not(.custom-recaptcha-badge) {
                display: none !important;
                visibility: hidden !important;
            }
        `;
            document.head.appendChild(style);
        }

        if (config.badge === 'inline') {
            setTimeout(() => {
                const originalBadge = document.querySelector('.grecaptcha-badge');
                if (originalBadge) {
                    // Clone badge for this container
                    const badgeClone = originalBadge.cloneNode(true);
                    badgeClone.style.position = 'static';
                    badgeClone.style.marginTop = '10px';
                    badgeClone.style.display = 'inline-block';
                    badgeClone.style.transform = 'none';
                    badgeClone.style.visibility = 'visible';

                    // Add a class to identify our custom badges (this makes it visible due to CSS rule above)
                    badgeClone.classList.add('custom-recaptcha-badge');

                    container.appendChild(badgeClone);

                    this.log(`v3 inline badge rendered for instance ${instance.id}`);
                }
            }, 500);
        }

        this.log(`v3 widget rendered for instance ${instance.id}`);
    }

    /**
     * Wait for v2 to be ready
     */
    async waitForV2Ready() {
        return new Promise((resolve) => {
            const check = () => {
                if (window.grecaptcha && window.grecaptcha.render) {
                    resolve();
                } else {
                    setTimeout(check, 100);
                }
            };
            check();
        });
    }

    /**
     * Wait for v3 to be ready
     */
    async waitForV3Ready() {
        return new Promise((resolve) => {
            const check = () => {
                if (window.grecaptcha && window.grecaptcha.ready) {
                    window.grecaptcha.ready(resolve);
                } else {
                    setTimeout(check, 100);
                }
            };
            check();
        });
    }

    /**
     * Logging utilities
     */
    log(...args) {
        if (this.defaultConfig.debug) {
            console.log('[RecaptchaManager]', ...args);
        }
    }

    logError(...args) {
        console.error('[RecaptchaManager]', ...args);
    }
}

/**
 * Represents a single reCAPTCHA instance
 */
class RecaptchaInstance {
    constructor(internalState, manager) {
        this.state = internalState;
        this.manager = manager;
        this.state.forceFallback = false;
    }

    /**
     * Toggle fallback (switch to v2 for this instance)
     */
    async useFallback(force = true) {
        // NEW: Skip fallback if disabled
        if (this.state.disabled) {
            this.manager.log(`Instance ${this.state.id} is disabled - fallback skipped`);
            return;
        }

        if (!this.state.config.fallback) {
            return;
        }

        // Check if both site keys are available
        const v2SiteKey = this.state.config.v2SiteKey || this.manager.globalSiteKeys.v2;

        if (!v2SiteKey) {
            return;
        }

        this.state.forceFallback = force;

        if (force && this.state.mode === 'v3') {
            // Load v2 script if not loaded
            await this.manager.loadV2Script();

            // Render v2 widget
            await this.manager.renderV2Widget(this.state);

            this.manager.log(`Instance ${this.state.id} switched to fallback (v2) mode`);
        } else if (!force) {
            // Switch back to v3 if needed
            await this.manager.renderV3Widget(this.state);
            this.manager.log(`Instance ${this.state.id} switched back to v3 mode`);
        }
    }

    /**
     * Get reCAPTCHA token
     */
    async getToken(action = 'default') {
        // NEW: Return null immediately if disabled
        if (this.state.disabled) {
            this.manager.log(`Instance ${this.state.id} is disabled - returning null token`);
            return null;
        }

        const { config } = this.state;

        // Forced fallback → always v2
        if (this.state.forceFallback) {
            return this.getV2Token();
        }

        if (this.state.mode === 'v2') {
            return this.getV2Token();
        } else if (this.state.mode === 'v2-invisible') {
            return this.getV2InvisibleToken();
        } else if (this.state.mode === 'v3') {
            const finalAction = config.action || action;
            return this.getV3Token(finalAction);
        }

        throw new Error('Invalid reCAPTCHA mode');
    }

    /**
     * Get v2 token (waits for user interaction)
     */
    async getV2Token() {
        if (this.state.v2Token) {
            return this.state.v2Token; // token exists, return it
        } else {
            throw new Error('No reCAPTCHA v2 token available'); // immediately throw error
        }
    }

    /**
     * Get v2 invisible token (programmatically execute)
     */
    async getV2InvisibleToken() {
        return new Promise((resolve, reject) => {
            if (this.state.v2Token) {
                // Token already exists
                resolve(this.state.v2Token);
                return;
            }

            // Set up promise resolvers
            this.state.resolveV2 = resolve;
            this.state.rejectV2 = reject;

            // Execute the invisible reCAPTCHA
            try {
                window.grecaptcha.execute(this.state.v2WidgetId);
                this.manager.log(`v2 invisible execution triggered for instance ${this.state.id}`);
            } catch (error) {
                this.state.resolveV2 = null;
                this.state.rejectV2 = null;
                reject(error);
            }
        });
    }

    async tokenValidation(instance, action = 'default') {
        const container = instance.state?.container || document.getElementById('recaptcha-container');

        // Remove any previous error
        container?.querySelector('.invalid-feedback')?.remove();

        try {
            // NEW: Accept null token for disabled instances
            if (instance.state.disabled) {
                return true; // Consider disabled reCAPTCHA as valid
            }

            const token = await instance.getToken(action);

            if (!token) throw new Error("No token received");
            return token;
        } catch (error) {
            if (container) {
                const errorEl = document.createElement('div');
                errorEl.className = 'invalid-feedback d-block';
                errorEl.textContent = 'reCAPTCHA validation failed. Please try again.';
                container.appendChild(errorEl);
            }
            return false;
        }
    }

    /**
     * Get v3 token (automatic execution)
     */
    async getV3Token(action) {
        const { config } = this.state;
        const siteKey = config.v3SiteKey || this.manager.globalSiteKeys.v3;
        try {
            const token = await window.grecaptcha.execute(siteKey, { action });
            this.manager.log(`v3 token generated for action: ${action}`);
            return token;
        } catch (error) {
            this.manager.logError('v3 execution failed:', error);
            throw error;
        }
    }

    /**
     * Reset the widget (works for v2 and v2-invisible)
     */
    reset() {
        // NEW: Skip reset if disabled
        if (this.state.disabled) {
            this.manager.log(`Instance ${this.state.id} is disabled - reset skipped`);
            return;
        }

        const isV2Mode = (this.state.mode === 'v2' || this.state.mode === 'v2-invisible');
        const isFallbackV2 = (this.state.forceFallback && this.state.v2WidgetId !== null);

        if ((isV2Mode || isFallbackV2) && this.state.v2WidgetId !== null) {
            try {
                window.grecaptcha.reset(this.state.v2WidgetId);
                this.state.v2Token = null;
                this.manager.log(`${isFallbackV2 ? 'fallback v2' : this.state.mode} widget ${this.state.id} reset`);
            } catch (error) {
                this.manager.logError(`Error resetting widget for instance ${this.state.id}:`, error);
            }
        }

        // Clear any pending v2 promises/callbacks
        if (this.state.resolveV2) {
            this.state.resolveV2 = null;
        }
        if (this.state.rejectV2) {
            this.state.rejectV2 = null;
        }
    }

    /**
     * Execute invisible reCAPTCHA manually (for v2-invisible mode)
     */
    async execute() {
        // NEW: Return null if disabled
        if (this.state.disabled) {
            this.manager.log(`Instance ${this.state.id} is disabled - execute returning null`);
            return null;
        }

        if (this.state.mode !== 'v2-invisible') {
            throw new Error('Execute method is only available for v2-invisible mode');
        }

        return this.getV2InvisibleToken();
    }

    /**
     * Get current mode
     */
    getCurrentMode() {
        return this.state.disabled ? 'disabled' : this.state.mode;
    }

    /**
     * Check if token is available
     */
    hasToken() {
        // NEW: Always return true for disabled instances
        if (this.state.disabled) {
            return true;
        }
        return this.state.v2Token !== null;
    }

    /**
     * Check if instance is disabled
     */
    isDisabled() {
        return this.state.disabled || false;
    }

    /**
     * Get detailed state information
     */
    getState() {
        return {
            id: this.state.id,
            mode: this.state.mode,
            disabled: this.state.disabled || false, // NEW
            hasV2Widget: this.state.v2WidgetId !== null,
            v2Token: this.state.v2Token,
            hasToken: this.hasToken(),
            config: { ...this.state.config }
        };
    }

    /**
     * Destroy the instance
     */
    destroy() {
        // NEW: Skip cleanup if disabled (no widgets to clean up)
        if (this.state.disabled) {
            this.state.container.innerHTML = '';
            this.manager.instances.delete(this.state.id);
            this.manager.log(`Disabled instance ${this.state.id} destroyed`);
            return;
        }

        if ((this.state.mode === 'v2' || this.state.mode === 'v2-invisible') && this.state.v2WidgetId !== null) {
            try {
                this.reset();
            } catch (error) {
                this.manager.logError('Error during cleanup:', error);
            }
        }

        // Clear container
        this.state.container.innerHTML = '';

        // Remove instance-specific styles
        const inlineStyles = document.querySelector(`#recaptcha-inline-styles-${this.state.id}`);
        if (inlineStyles) {
            inlineStyles.remove();
        }

        // Remove from manager's instance map
        this.manager.instances.delete(this.state.id);

        this.manager.log(`Instance ${this.state.id} destroyed`);
    }
}

// Export for use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = RecaptchaManager;
} else if (typeof window !== 'undefined') {
    window.RecaptchaManager = RecaptchaManager;
}