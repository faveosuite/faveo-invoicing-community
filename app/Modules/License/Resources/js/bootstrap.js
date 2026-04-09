// import _ from 'lodash';
// window._ = _;
//
// import '../css/app.scss';
//
// import '../css/dynamicSelectCommon.css';
//
// import '../css/tooltip.css';
//
// import "vue-select/src/scss/vue-select.scss";
//
// window.Vue = require('vue').default;
//
// window.eventHub = new Vue();
//
// import {lang} from 'helpers/extraLogics';
//
// import {store} from 'store'
//
// /**
//  * We'll load jQuery and the Bootstrap jQuery plugin which provides support
//  * for JavaScript based Bootstrap features such as modals and tabs. This
//  * code may be modified to fit the specific needs of your application.
//  */
//
// try {
//
//     window.$ = window.jQuery = require('jquery');
//
// } catch (e) {}
//
// /**
//  * We'll load the axios HTTP library which allows us to easily issue requests
//  * to our Laravel back-end. This library automatically handles sending the
//  * CSRF token as a header based on the value of the "XSRF" token cookie.
//  */
//
// import axios from 'axios';
// window.axios = axios;
//
// window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
// window.axios.defaults.baseURL = document.head.querySelector('meta[name="api-base-url"]').content;
// window.axios.defaults.headers.common['Authorization'] = 'Bearer'+' '+ store.getters.getUserToken;
// window.axios.interceptors.response.use((response) => {
//
//     return response
//
// },function (error) {
//
//     if (error.response.status === 401) {
//
//         store.dispatch('setAlert', { type: 'danger', message: 'Unauthorized!'});
//         store.dispatch('setLoggedInUserToken', '');
//
//         setTimeout(()=>{
//
//             window.location = window.axios.defaults.baseURL;
//         },2000);
//
//         return Promise.reject(error);
//     }
//
//     return Promise.reject(error);
//
// });
// //fetching language file from server and declaring that as global prop
// //if file doesn't have the passed key, it is going to return string
// Vue.prototype.lang = lang;
//
// // gives basePath
// Vue.prototype.basePath = () => (window.axios.defaults.baseURL)
//
// Vue.mixin({
//
//     methods: {
//
//       basePath : () => (window.axios.defaults.baseURL),
//
//       trans: (string) => lang(string)
//     },
//
//     data: () =>({})
// });
//

import store from "./store";

import '../css/dynamicSelectCommon.css';

import 'vue-select/dist/vue-select.css';

import 'vue-datepicker-next/index.css';

import 'intl-tel-input/build/css/intlTelInput.css';

import _ from 'lodash';

window._ = _;

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

axios.interceptors.request.use(
    function (config) {
        // Get the user token from the store
        const userToken = store.getters.getUserToken;

        // If the user token is available, set the Authorization header
        if (userToken) {
            config.headers.Authorization = `Bearer ${userToken}`;
        }

        return config;
    },
    function (error) {
        return Promise.reject(error);
    }
);

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.baseURL = document.head.querySelector('meta[name="api-base-url"]').content;
window.axios.interceptors.response.use((response) => {

    return response

},function (error) {

    if (error.response.status === 401) {

        store.dispatch('setAlert', { type: 'danger', message: 'Unauthorized!'});

        store.dispatch('setLoggedInUserToken', '');

        setTimeout(()=>{

            window.location = window.axios.defaults.baseURL;
        },2000);

        return Promise.reject(error);
    }

    return Promise.reject(error);

});

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// import Pusher from 'pusher-js';
// window.Pusher = Pusher;

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: import.meta.env.VITE_PUSHER_APP_KEY,
//     wsHost: import.meta.env.VITE_PUSHER_HOST ?? `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
//     wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
//     wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
//     forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
//     enabledTransports: ['ws', 'wss'],
// });
