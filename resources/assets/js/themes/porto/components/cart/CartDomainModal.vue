<template>
  <Modal :showModal="show" :onClose="() => emit('close')" :showCloseBtn="false" classname="modal-md">
    <template #title>
      <h5 class="modal-title fw-bold">{{ __('message.enter_domain_new') }}</h5>
    </template>

    <template #fields>
      <p class="text-muted mb-3">{{ __('message.enter_domain_name') }}</p>
      <ClientField
        v-for="item in items"
        :key="item.id"
        :name="`domain_${item.id}`"
        :label="item.name"
        placeholder="support.example.com…"
        autocomplete="off"
        :spellcheck="false"
        :modelValue="domains[`domain_${item.id}`]"
        :error="errors[`domain_${item.id}`]"
        :required="true"
        @update:modelValue="domains[`domain_${item.id}`] = $event; setFieldError(`domain_${item.id}`, undefined)"
        @keyup.enter="submit"
      />
    </template>

    <template #controls>
      <action-button action="cancel" @click="emit('close')" />
      <action-button action="confirm" :label="submitting ? __('message.please_wait') : __('message.proceed_checkout')" :loading="submitting" @click="submit" />
    </template>
  </Modal>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useForm } from 'vee-validate'
import { __ } from '@/plugins/i18n'
import { scrollToFirstError } from '@/helpers/formUtils.js'
import { buildCartDomainSchema } from '@/validations/client/cartDomainValidations'
import Modal from '../common/Modal.vue'

// items: cart items with require_domain = true. Emits `save` with { [itemId]: domain }.
const props = defineProps({
  show:  { type: Boolean, default: false },
  items: { type: Array, default: () => [] },
})
const emit = defineEmits(['close', 'save'])

// No validationSchema on useForm: it would validate as soon as the fields are
// prefilled and show "required" errors on open. Validate on submit only.
const { errors, setErrors, setFieldError } = useForm()
const domains = ref({})
const submitting = ref(false)

// Prefill with whatever is already saved on each item every time the modal opens.
watch(() => props.show, (open) => {
  if (!open) return
  submitting.value = false
  setErrors({})
  domains.value = Object.fromEntries(props.items.map(i => [`domain_${i.id}`, i.domain || '']))
})

function submit() {
  try {
    buildCartDomainSchema(props.items).validateSync(domains.value, { abortEarly: false })
  } catch (err) {
    const errMap = {}
    err.inner?.forEach(e => { if (e.path && !errMap[e.path]) errMap[e.path] = e.message })
    setErrors(errMap)
    scrollToFirstError()
    return
  }
  submitting.value = true
  emit('save', Object.fromEntries(props.items.map(i => [i.id, domains.value[`domain_${i.id}`].trim()])))
}
</script>
