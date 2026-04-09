// store.js

import { createStore } from "vuex";
import VuexPersist from 'vuex-persist';
import auth from './modules/auth';
import alert from './modules/alert';
import setting from './modules/setting';

const vuexLocalStorage = new VuexPersist({
    // storage: window.localStorage,
    reducer: state => ({
        auth: state.auth,
        progressBarValue: state.progressBarValue // Adding progressBarValue to the persisted state
    })
})

const store = createStore({
    state: {
        progressBarValue: 0, // Initial value for the progress bar in the root state
    },
    mutations: {
        setProgressBarValue(state, value) {
            state.progressBarValue = value;
        },
    },
    actions: {
        updateProgressBar({ commit }, value) {
            commit('setProgressBarValue', value);
        },
    },
    modules : {
        auth,
        alert,
        setting
    },
    plugins: [vuexLocalStorage.plugin]
});

export default store;
