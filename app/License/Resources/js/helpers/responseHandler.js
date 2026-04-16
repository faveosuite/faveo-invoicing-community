/**
 * This file contains all the common procedures that will be related to response handling
 *
 */


import store from "../store";

/**
 * TODO: if error response is 400, write to vuex for components to read
 * TODO: decide on status code for validation errors and message errors
 * If error message has a key and a value, we write to vuex as validation errors
 * If error message is a string, we store that as alert
 *
 * It redirects to home page if user is logged out from backend(if status code is 422)
 * else write to vuex the error message
 * @param  {object } err axios error object
 * @param {string} componentName refers to the component name from which it was called
 * @return {void}
 */
export const errorHandler = (err, componentName = '') => {
    /**
     * return if error is thrown (in case duplicate API request is made)
     * Error is thrown from AXIOS interceptor
     * Check bootstrap.js
    */
    if (err.duplicateRequestRejection) {
        return;
    }

    // checking if status is any of 400 and 422
    if ([400, 422, 401, 429, 500].includes(err.response.status)) {

        //check if it has a key called message, if yes, write the message to vuex store
        if (err.response.data.message !== undefined) {
            store.dispatch('setAlert', { type: 'danger', message: err.response.data.message, component_name: componentName });
        }

        // for handling cases when response comes is old format (  {result : {fails : "error messsage"}} )
        if (err.response.data.result !== undefined && err.response.data.result.fails !== undefined) {
            store.dispatch('setAlert', { type: 'danger', message: err.response.data.result.fails, component_name: componentName });
        }
    }

    if (err.response.status === 412) {
        if (err.response.data.message !== undefined) {

            store.dispatch('setValidationError', err.response.data.message);
        }
    }

    if (err.response.status === 404) {
      window.location = window.axios.defaults.baseURL + '/404';
    }
};

/**
 *
 * writes success message to the vuex store.
 * @param  {object} res axios success response object
 * @param componentName Name of the component in which alert has to be displayed
 * @return {void}
 */
export const successHandler = (res, componentName = '') => {
    if (res.status === 200 || res.status === 201) {
        //dispatch a job which writes success message to vuex store
        if (res.data.message !== undefined) {
            store.dispatch('setAlert', { type: 'success', message: res.data.message, component_name: componentName, data : res.data.data });
        }
    }
};
