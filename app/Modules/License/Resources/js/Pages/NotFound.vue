<template>

    <div class="d-flex align-items-center justify-content-center min-vh-80">

        <div class="error-page">

            <h2 class="headline text-warning lock_mt_25"> 404</h2>

            <div class="error-content">

                <h3><i class="fas fa-exclamation-triangle text-warning"></i> Oops! Page not found.</h3>

                <p>
                    We could not find the page you were looking for.
                    Meanwhile, you may <router-link to="/">return to dashboard.</router-link>
                </p>
            </div>
        </div>
    </div>


</template>

<script setup>
</script>

<style>

body { background: #e9ecef !important; }

.lock_mt_25 { margin-top: -25px !important; }

.lockscreen-wraper { margin-top: 16% !important; margin-left: 29% !important; }

.min-vh-80 { min-height: 70vh; }
</style>
