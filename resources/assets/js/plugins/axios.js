import axios from 'axios'

const el = document.getElementById('app-root')

// Base URL is the Laravel app root (e.g. https://domain/faveo/public)
// so all route paths can be used as-is without a /api prefix.
const baseURL = el?.dataset?.baseUrl ?? ''

const http = axios.create({
    baseURL,
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    },
    withCredentials: true, // send session cookie on every request
})

http.interceptors.request.use(config => {
    // Attach CSRF token for Laravel web routes
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content
    if (csrf) {
        config.headers['X-CSRF-TOKEN'] = csrf
    }
    return config
})

http.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            // Session expired — redirect to client panel login
            window.location.href = (el?.dataset?.baseUrl ?? '') + '/auth/login'
        }
        return Promise.reject(error)
    }
)

export default http
