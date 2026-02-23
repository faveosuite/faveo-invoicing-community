<template>
    <nav class="app-header navbar navbar-expand bg-body" role="banner">
        <div class="container-fluid">

            <!-- ── Left ───────────────────────────────────────────────────── -->
            <ul class="navbar-nav" role="navigation" aria-label="Primary navigation">

                <!-- Sidebar toggle -->
                <li class="nav-item">
                    <button type="button"
                            class="nav-link btn btn-link"
                            :aria-expanded="isOpen"
                            aria-controls="app-sidebar"
                            aria-label="Toggle sidebar"
                            @click="toggle">
                        <i class="bi bi-list" aria-hidden="true"></i>
                    </button>
                </li>

                <!-- Go to client panel -->
                <li class="nav-item d-none d-md-block">
                    <a :href="baseUrl"
                       class="nav-link"
                       target="_blank"
                       rel="noopener noreferrer"
                       aria-label="Go to client panel (opens in new tab)">
                        <i class="bi bi-box-arrow-up-right me-1" aria-hidden="true"></i>
                        Go to Client Panel
                    </a>
                </li>
            </ul>

            <!-- ── Right ──────────────────────────────────────────────────── -->
            <ul class="navbar-nav ms-auto" role="navigation" aria-label="User navigation">

                <!-- Language -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-1"
                       data-bs-toggle="dropdown"
                       href="#"
                       role="button"
                       aria-label="Change language, current: {{ currentLang }}"
                       aria-haspopup="true">
                        <i class="bi bi-translate" aria-hidden="true"></i>
                        <span class="d-none d-md-inline">{{ currentLang }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" role="listbox" aria-label="Languages">
                        <li v-for="lang in languages" :key="lang.code" role="option">
                            <a class="dropdown-item d-flex align-items-center gap-2"
                               href="#"
                               :class="{ active: lang.code === currentLang }"
                               :aria-current="lang.code === currentLang ? 'true' : undefined"
                               @click.prevent="currentLang = lang.code">
                                <span aria-hidden="true">{{ lang.flag }}</span>
                                <span>{{ lang.label }}</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Profile -->
                <li class="nav-item dropdown user-menu">
                    <a href="#"
                       class="nav-link dropdown-toggle d-flex align-items-center gap-2"
                       data-bs-toggle="dropdown"
                       role="button"
                       :aria-label="`User menu for ${userName}`"
                       aria-haspopup="true">
                        <img :src="avatarUrl"
                             class="user-image rounded-circle shadow"
                             alt=""
                             @error="e => e.target.src = fallbackAvatar" />
                        <span class="d-none d-md-inline">{{ userName }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-label="User menu">
                        <!-- Header -->
                        <li class="user-header text-bg-primary">
                            <img :src="avatarUrl"
                                 class="rounded-circle shadow"
                                 :alt="`${userName} avatar`"
                                 @error="e => e.target.src = fallbackAvatar" />
                            <p>
                                {{ userName }}
                                <small v-if="userEmail">{{ userEmail }}</small>
                            </p>
                        </li>
                        <!-- Actions -->
                        <li class="user-footer">
                            <a href="#" class="btn btn-default btn-flat">
                                <i class="bi bi-person me-1" aria-hidden="true"></i>
                                Profile
                            </a>
                            <a href="#" class="btn btn-default btn-flat float-end text-danger">
                                <i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i>
                                Sign out
                            </a>
                        </li>
                    </ul>
                </li>

            </ul>
        </div>
    </nav>
</template>

<script setup>
import { ref } from 'vue'
import { asset } from '@/core/utils/asset.js'
import { useSidebar } from '@/core/composables/useSidebar.js'

const { isOpen, toggle } = useSidebar()

const el = document.getElementById('app-root')

// ── URLs ──────────────────────────────────────────────────────────────────────
const baseUrl = el?.dataset?.baseUrl ?? '/'

// ── User ──────────────────────────────────────────────────────────────────────
const userName      = el?.dataset?.userName  ?? 'Admin'
const userEmail     = el?.dataset?.userEmail ?? ''
const avatarUrl     = el?.dataset?.userAvatar || asset('themes/adminlte/assets/img/avatar.png')
const fallbackAvatar = asset('themes/adminlte/assets/img/avatar.png')

// ── Language ──────────────────────────────────────────────────────────────────
const currentLang = ref(el?.dataset?.locale ?? 'EN')

const languages = [
    { code: 'EN', flag: '🇬🇧', label: 'English'  },
    { code: 'DE', flag: '🇩🇪', label: 'Deutsch'  },
    { code: 'FR', flag: '🇫🇷', label: 'Français' },
    { code: 'ES', flag: '🇪🇸', label: 'Español'  },
    { code: 'AR', flag: '🇸🇦', label: 'العربية'  },
]
</script>
