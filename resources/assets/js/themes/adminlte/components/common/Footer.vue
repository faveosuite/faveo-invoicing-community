<template>
    <footer class="app-footer">
        <!-- Right: version -->
        <div class="float-end d-none d-sm-inline">
            <strong>Version</strong> {{ appVersion }}
        </div>

        <!-- Left: copyright -->
        <strong>Copyright &copy; {{ year }}&nbsp;
            <a :href="website" class="text-decoration-none fw-semibold" target="_blank" rel="noopener noreferrer">{{ companyName }}</a>.
        </strong>

        All rights reserved. Powered by
        <a :href="website" class="text-decoration-none fw-semibold" target="_blank" rel="noopener noreferrer">Faveo</a>
    </footer>
</template>

<script setup>

const el = document.getElementById('app-root')

const year        = new Date().getFullYear()
const appVersion  = el?.dataset?.appVersion  ?? ''
const companyName = el?.dataset?.company || 'Ladybird Web Solution Pvt Ltd'
const website = el?.dataset?.website || 'https://www.faveohelpdesk.com'
</script>
