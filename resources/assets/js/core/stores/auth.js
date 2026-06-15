import { defineStore } from 'pinia'
import { useDateTimeStore } from './dateTimeStore'
import http from '@/plugins/axios'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,  // null = guest, object = authenticated user
    }),

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
            } catch {
                this.user = null
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
