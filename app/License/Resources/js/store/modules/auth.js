
const state = {

    user_data : '',

    api_key : '',

    admin_data: '',

    clientTimezone: ''
 };


 const getters = {

    getUserData: state => state.user_data,

    getAdminData: state => state.admin_data,

    getApiKey : state => state.api_key,

    getClientTimezone: (state) => state.clientTimezone,
 };

 const mutations = {

    updateUserInfo(state,payload) {

        state.user_data = payload
    },

    updateApiKey(state,payload) {

        state.api_key = payload
    },

     updateUserData(state, payload)  {

         state.user_data.client_profile_pic = payload.profile_pic
         state.user_data.client_mobile_code = payload.client_mobile_code
         state.user_data.client_iso2 = payload.client_iso2
         state.user_data.client_fname= payload.client_fname
         state.user_data.client_lname= payload.client_lname
         state.user_data.client_email= payload.client_email
         state.user_data.client_timezone_id= payload.client_timezone_id
     },

     updateAdminData(state,payload) {

         state.admin_data = payload
     },

     setClientTimezone(state, timezone) {
         state.clientTimezone = timezone;
     },
 }

 const actions = {

    setUserInfo({commit},payload) {
        commit('updateUserInfo',payload)
    },

    setUserData({commit}, payload) {
        commit('updateUserData', payload)
    },

    setApiKey({commit}) {

        commit('updateApiKey','')
    },

     setAdminData({commit}, payload) {

         commit('updateAdminData', payload)
     },

     setClientTimezone({ commit }, timezone) {
         commit('setClientTimezone', timezone);
     },
 }

 export default {state, getters, mutations, actions}
