import store from "../index";

const state = {

    settings : {},
 };


 const getters = {

    getSettings: state => state.settings,

    getGeneralSettings(state){
        return state.settings && state.settings.SETTING_ID ? {
            SETTING_ID: state.settings.SETTING_ID,
            TIMEZONE: state.settings.TIMEZONE,
            RECORDS_ARCHIVE_DAYS: state.settings.RECORDS_ARCHIVE_DAYS,
            RECORDS_ON_ADMIN_PAGE: state.settings.RECORDS_ON_ADMIN_PAGE,
            RECORDS_ON_INDEX_PAGE: state.settings.RECORDS_ON_INDEX_PAGE,
            RECORDS_ON_SEARCH_PAGE: state.settings.RECORDS_ON_SEARCH_PAGE,
            SMART_REPORTS: state.settings.SMART_REPORTS,
            SMART_TABLES: state.settings.SMART_TABLES,
        }: null
    },
    getAdvancedSettings(state){
        return state.settings && state.settings.SETTING_ID ? {
            SETTING_ID: state.settings.SETTING_ID,
            API_STATUS: state.settings.API_STATUS,
            ENVATO_API_TOKEN: state.settings.ENVATO_API_TOKEN,

        }: null
    },
    getSecuritySettings(state){
        return state.settings && state.settings.SETTING_ID ? {
            SETTING_ID: state.settings.SETTING_ID,
            WHITELISTED_ACCESS: state.settings.WHITELISTED_ACCESS,
            WHITELISTED_IP: state.settings.WHITELISTED_IP,
            BANNED_HOSTS: state.settings.BANNED_HOSTS,
            BANNED_HOST_MESSAGE: state.settings.BANNED_HOST_MESSAGE,
            FAILED_LICENSINGS_LIMIT: state.settings.FAILED_LICENSINGS_LIMIT,
            FAILED_HOSTS_FORGET: state.settings.FAILED_HOSTS_FORGET,
            FAILED_LOGINS_LIMIT: state.settings.FAILED_LOGINS_LIMIT,
            MIN_PASSWORD_LENGTH: state.settings.MIN_PASSWORD_LENGTH,
            FAILED_FORGET_LIMIT: state.settings.FAILED_FORGET_LIMIT,

        }: null
    },
    getEmailSettings(state){
        return state.settings && state.settings.SETTING_ID ? {
            SETTING_ID: state.settings.SETTING_ID,
            EMAIL_FROM_NAME: state.settings.EMAIL_FROM_NAME,
            EMAIL_FROM_ADDRESS: state.settings.EMAIL_FROM_ADDRESS,
            EMAIL_CC_SENDER: state.settings.EMAIL_CC_ADMIN,
            EMAIL_EXPIRING_LICENSE_DAYS: state.settings.EMAIL_EXPIRING_LICENSE_DAYS,
            EMAIL_EXPIRING_UPDATES_DAYS: state.settings.EMAIL_EXPIRING_UPDATES_DAYS,
            EMAIL_EXPIRING_SUPPORT_DAYS: state.settings.EMAIL_EXPIRING_SUPPORT_DAYS,
            EMAIL_DRIVER: state.settings.EMAIL_DRIVER,
            EMAIL_ENCRYPTION: state.settings.EMAIL_ENCRYPTION,
            EMAIL_PORT: state.settings.EMAIL_PORT,
            EMAIL_PASSWORD: state.settings.EMAIL_PASSWORD,
            EMAIL_HOST: state.settings.EMAIL_HOST,

        }: null
    },
    getCleanUpSettings(state){
        return state.settings && state.settings.SETTING_ID ? {
            SETTING_ID: state.settings.SETTING_ID,
            DATABASE_CLEANUP_ENABLED: state.settings.DATABASE_CLEANUP_ENABLED,
            DATABASE_CLEANUP_CALLBACKS: state.settings.DATABASE_CLEANUP_CALLBACKS,
            DATABASE_CLEANUP_REPORTS_MAIN: state.settings.DATABASE_CLEANUP_REPORTS_MAIN,
            DATABASE_CLEANUP_REPORTS_SYSTEM: state.settings.DATABASE_CLEANUP_REPORTS_SYSTEM,
            DATABASE_CLEANUP_REPORTS_LICENSES: state.settings.DATABASE_CLEANUP_REPORTS_LICENSES,

        }: null
    }
 };

 const mutations = {


    setSettings(state,payload) {

        state.settings = payload
    },
 }

 const actions = {


    fetchSettings({commit}) {
        return axios.get((document.getElementById('app-root')?.dataset?.baseUrl || '') + '/api/admin/viewSettings').then(res => {
            if(res && res.data && res.data.data && Array.isArray(res.data.data) && res.data.data.length >0 ) {
                commit('setSettings',res.data.data[0])
            }
        }).catch(err => {

            commit('setSettings',{})

           return err;
        });
    },

 }

 export default {state, getters, mutations, actions}
