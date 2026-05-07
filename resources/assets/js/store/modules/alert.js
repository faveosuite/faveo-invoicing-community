/**
 * Handles all the alerts displayed on the screen.
 * It contains all the messages for 5 seconds which can be used to display those messages on the screen
 *
 * NOTE: there cannot be multiple alerts in the store
 */

 const state = {
	message: '',
	type: '',//can be success, danger, warning or info,
	component_name: '',
	validation_errors: {},//validation errors,
	duration : ''
};


const getters = {

	getAlertType: state => state.type,

	getAlertMessage: state => state.message,

	getAlertComponentName: state => state.component_name,

	getAlertDuration: state => state.duration,

	getValidationErrors: state => state.validation_errors

};


const mutations = {

	alert(state, payload) {
		state.message = payload.message;
		state.type = payload.type;
		state.duration = payload.duration;
		state.component_name = payload.component_name;
	},


	validationErrors(state, payload) {
		state.validation_errors = payload
	}


};


const actions = {

	setAlert({ commit }, payload) {
		commit('alert', payload)
	},

	unsetAlert({ commit }) {
		commit('alert', { message: '', type: '' })
	},

	setValidationError({ commit }, payload) {
		commit('validationErrors', payload)
	},

	unsetValidationError({ commit }) {
		commit('validationErrors', {}) // setting validation error to empty
	},

}


export default { state, getters, mutations, actions }
