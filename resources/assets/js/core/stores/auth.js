import { defineStore } from 'pinia'
import { useDateTimeStore } from './dateTimeStore'
import http from '@/plugins/axios'

export const useAuthStore = defineStore('auth', {
    state: () => {
        const el = document.getElementById('app-client') ?? document.getElementById('app-root')
        const isAuth = el?.dataset?.authenticated === 'true'
        return {
            user: isAuth ? {
                id: el?.dataset?.userId ?? '',
                first_name: el?.dataset?.userFirstName ?? '',
                last_name: el?.dataset?.userLastName ?? '',
                user_name: el?.dataset?.userUsername ?? '',
                email: el?.dataset?.userEmail ?? '',
                role: el?.dataset?.userRole ?? '',
                timezone: { name: el?.dataset?.userTimezone ?? '' }
            } : null,
        }
    },

    getters: {
        isAuthenticated: (s) => s.user !== null,
        isAdmin: (s) => s.user?.role === 'admin',
    },

    actions: {
        async hydrate() {
            try {
                const { data } = await http.get('/api/user', { _skipAuthRedirect: true })
                this.user = data.data
                const tz = this.user?.timezone?.name ?? null
                if (tz) useDateTimeStore().setUserTimezone(tz)
            } catch (err) {
                // Only a real 401 means "not actually logged in" — a network
                // blip or 5xx here shouldn't log out a genuinely authenticated
                // user on their next click (router guard reads isAuthenticated
                // live on every navigation).
                if (err.response?.status === 401) this.user = null
            }
        },

        clear() {
            this.user = null
        },

        // Called after profile update to patch local user fields without re-hydrating
        patchUser(payload) {
            if (this.user) Object.assign(this.user, payload)
        },
    },
})
