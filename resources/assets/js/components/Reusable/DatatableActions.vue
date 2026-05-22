<template>
    <div class="user-table-actions">

        <router-link v-if="data.edit_url" class="btn btn-light table_btn"
                     :to="data.edit_url" v-tooltip="trans('edit')">
            <i class="fas fa-edit"></i>
        </router-link>

        <div v-if="data.view_url || data.agent_view_url" class="btn-group">
            <template v-if="data.view_url && data.agent_view_url">
                <button
                    ref="dropdownBtn"
                    type="button"
                    class="btn btn-default table_btn dropdown-toggle"
                    :class="{ active: isOpen }"
                    @click.stop="toggleDropdown"
                    v-tooltip="trans('view')">
                    <i class="fas fa-eye"></i>
                </button>

                <Teleport to="body">
                    <div
                        v-if="isOpen"
                        ref="dropdownMenu"
                        class="dropdown-menu show shadow-sm"
                        :style="menuStyle">

                        <router-link
                            class="dropdown-item pointer"
                            :to="data.view_url"
                            @click="isOpen = false">
                            {{ trans('view') }}
                        </router-link>

                        <router-link
                            v-if="data.agent_view_url"
                            class="dropdown-item pointer"
                            :to="data.agent_view_url"
                            @click="isOpen = false">
                            {{ trans('view_agent') }}
                        </router-link>
                    </div>
                </Teleport>
            </template>

            <router-link v-else class="btn btn-default table_btn"
                         :to="data.view_url || data.agent_view_url"
                         v-tooltip="trans(data.agent_view_url ? 'view_agent' : 'view')">
                <i class="fas fa-eye"></i>
            </router-link>
        </div>

        <span v-tooltip="disabled ? trans('default_field_is_not_restore') : trans('restore')">
            <button v-if="data.restore_url" class="btn btn-light table_btn"
                    @click="showRestoreModalMethod" :disabled="disabled">
                <i class="fas fa-sync-alt"></i>
            </button>
        </span>

        <span v-tooltip="disabled ? trans('default_field_is_not_deletable') : data.tooltip ? trans(data.tooltip) : trans('delete')">
            <button v-if="data.delete_url" class="btn btn-light table_btn"
                    @click="showModalMethod" :disabled="disabled">
                <i class="fas fa-trash"></i>
            </button>
        </span>

        <transition name="modal">
            <DeleteModal v-if="showModal" :onClose="onClose" :showModal="showModal"
                         :deleteUrl="data.delete_url" :alertComponentName="alert"
                         :keyVal="data.keyVal" :idVal="data.idVal"
                         :modalMessage="data.modalMessage" :btnTitle="data.btnTitle"
                         :softDelete="data.softDelete" :modalTitle="data.modalTitle" />
        </transition>

        <transition name="modal">
            <DeleteModal v-if="showRestoreModal" :onClose="onClose" :showModal="showRestoreModal"
                         :deleteUrl="data.restore_url" :alertComponentName="alert"
                         :keyVal="data.keyVal" :idVal="data.idVal"
                         :modalMessage="data.restoreModalMessage" :btnTitle="data.restoreBtnTitle"
                         :modalTitle="data.restoreModalTitle" />
        </transition>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { boolean, lang } from '@/helpers/extraLogics'
import { useAlertStore } from '@/core/stores/alert'
import DeleteModal from './DeleteModal.vue'

const props = defineProps({
    data: { type: Object, required: true },
})

const alertStore = useAlertStore()

const showModal = ref(false)
const showRestoreModal = ref(false)
const alert = ref(props.data.alertComponentName ?? 'dataTableModal')

const disabled = computed(() => boolean(props.data.is_default))

const trans = (string) => lang(string)

function showModalMethod() {
    showModal.value = !props.data.is_default
}

function showRestoreModalMethod() {
    showRestoreModal.value = !props.data.is_default
}

function onClose() {
    showModal.value = false
    showRestoreModal.value = false
}

/* ── Dropdown positioning (favMer pattern) ── */
const dropdownBtn = ref(null)
const dropdownMenu = ref(null)
const isOpen = ref(false)
const menuStyle = ref({})

function updateMenuPosition() {
    if (!dropdownBtn.value) return
    const rect = dropdownBtn.value.getBoundingClientRect()
    menuStyle.value = {
        position: 'fixed',
        top: `${rect.bottom + 4}px`,
        left: `${rect.right}px`,
        transform: 'translateX(-100%)',
        zIndex: 9999,
        minWidth: '160px',
    }
}

async function toggleDropdown() {
    isOpen.value = !isOpen.value
    if (isOpen.value) {
        await nextTick()
        updateMenuPosition()
    }
}

function closeDropdown(e) {
    if (
        dropdownBtn.value && !dropdownBtn.value.contains(e.target) &&
        dropdownMenu.value && !dropdownMenu.value.contains(e.target)
    ) {
        isOpen.value = false
    }
}

onMounted(() => {
    document.addEventListener('click', closeDropdown)
    window.addEventListener('scroll', () => { if (isOpen.value) updateMenuPosition() }, true)
    window.addEventListener('resize', () => { if (isOpen.value) updateMenuPosition() })
})

onBeforeUnmount(() => {
    document.removeEventListener('click', closeDropdown)
})
</script>
