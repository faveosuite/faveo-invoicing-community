<template>
    <div>
        <div v-if="shouldShowProgressBar" class="progress color-shift-progress-bar">
            <div class="progress-bar" role="progressbar" :style="{ width: progressBarWidth }"></div>
        </div>

        <router-view :versioning="versioning"></router-view>
    </div>
</template>

<script>

import axios from 'axios';

export default {
    props:{
        versioning : { type : String , default : ''},
        userData : { type : Object, default : () => ({}) },
    },
    data() {
        return {
            shouldShowProgressBar: false,
        };
    },
    watch: {
        $route(to, from) {
            this.$store.dispatch('unsetAlert');
            this.$store.dispatch('unsetValidationError');
        }
    },
    beforeMount() {
        this.$store.dispatch('setApiKey');

        if (this.userData && Object.keys(this.userData).length) {
            this.$store.dispatch('setUserInfo', this.userData);
        }

        // Immediately show the progress bar with a starting value of 0%
        this.shouldShowProgressBar = true;
        this.$store.dispatch('updateProgressBar', 0);
    },
    computed: {
        progressBarWidth() {
            return this.$store.state.progressBarValue + '%';
        },
    },
    created() {
        const store = this.$store;
        let activeRequests = 0;

        const toggleProgressBar = (show, progress) => {
            this.shouldShowProgressBar = show;
            store.dispatch('updateProgressBar', progress);
        };

        const showProgressBar = () => {
            activeRequests++;
            if (activeRequests === 1) {
                toggleProgressBar(true, 0);
            }
        };

        const hideProgressBar = () => {
            activeRequests--;
            if (activeRequests === 0) {
                toggleProgressBar(false, 0);
                setTimeout(() => {
                    store.dispatch('updateProgressBar', 0);
                    // Ensure shouldShowProgressBar is set to false after hiding
                    this.shouldShowProgressBar = false;
                }, 500);
            }
        };

        const onRequestSuccess = (response) => {
            hideProgressBar();
            return response;
        };

        const onRequestError = (error) => {
            hideProgressBar();
            return Promise.reject(error);
        };

        axios.interceptors.request.use((config) => {
            showProgressBar();
            return config;
        });

        axios.interceptors.response.use(onRequestSuccess, onRequestError);
    },
}

</script>
<style scoped>
.color-shift-progress-bar {
    width: 100%;
    height: 3px;
    background: linear-gradient(90deg, transparent 0, #0077b6 50%, #00e1ff 50%, transparent 0);
    background-size: 200% 10px;
    animation: color-move-animation 2s linear infinite;
}

@keyframes color-move-animation {
    0% {
        background-position: 100% 0;
    }
    100% {
        background-position: -100% 0;
    }
}
</style>

