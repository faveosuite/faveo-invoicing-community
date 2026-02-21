import axios from 'axios'

const el = document.getElementById('app-root')

const http = axios.create({
    baseURL: '/api',
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    }
})

http.interceptors.request.use(config => {
    const token = el?.dataset?.token
    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }
    return config
})

http.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) window.location.href = '/login'
        return Promise.reject(error)
    }
)

export default http
