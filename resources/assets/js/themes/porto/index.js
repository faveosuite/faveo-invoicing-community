import { defineAsyncComponent } from 'vue'

// Required exports — same contract as themes/adminlte/index.js
// Layout + always-needed components loaded eagerly
import DefaultLayout from './layouts/DefaultLayout.vue'
import Sidebar       from './components/common/Sidebar.vue'
import Navbar        from './components/common/Navbar.vue'
import AppFooter     from './components/common/Footer.vue'

const ActionButton = defineAsyncComponent(() => import('./components/common/ClientActionButton.vue'))
// Heavy / page-specific components — loaded on demand
const AppCard          = defineAsyncComponent(() => import('./components/common/Card.vue'))
const AppModal         = defineAsyncComponent(() => import('./components/common/Modal.vue'))
const DeleteModal      = defineAsyncComponent(() => import('@/components/Reusable/DeleteModal.vue'))
const DataTable        = defineAsyncComponent(() => import('@/components/Reusable/DataTable.vue'))
const AppPagination    = defineAsyncComponent(() => import('./components/common/Pagination.vue'))
const AppBreadcrumb    = defineAsyncComponent(() => import('./components/common/Breadcrumb.vue'))
const AppAlert         = defineAsyncComponent(() => import('@/components/Reusable/Alert.vue'))
const AppButton        = defineAsyncComponent(() => import('./components/common/Button.vue'))
const AppBadge         = defineAsyncComponent(() => import('./components/common/Badge.vue'))

const ClientField    = defineAsyncComponent(() => import('./components/forms/ClientField.vue'))
const ClientCheckbox = defineAsyncComponent(() => import('./components/forms/ClientCheckbox.vue'))
const TextField     = defineAsyncComponent(() => import('@/components/Reusable/FormField/TextField.vue'))
const TextareaField = defineAsyncComponent(() => import('@/components/Reusable/FormField/TextareaField.vue'))
const DatePicker    = defineAsyncComponent(() => import('@/components/Reusable/FormField/DatePicker.vue'))
const FileUpload    = defineAsyncComponent(() => import('@/components/Reusable/FormField/FileUpload.vue'))
const ToggleSwitch  = defineAsyncComponent(() => import('@/components/Reusable/FormField/ToggleSwitch.vue'))
const DynamicSelect = defineAsyncComponent(() => import('@/components/Reusable/FormField/DynamicSelect.vue'))
const PhoneField    = defineAsyncComponent(() => import('@/components/Reusable/FormField/PhoneField.vue'))

const PricingTable = defineAsyncComponent(() => import('./components/store/PricingTable.vue'))
const PlanCard     = defineAsyncComponent(() => import('./components/store/PlanCard.vue'))

export const components = {
    ActionButton,
    ClientField,
    ClientCheckbox,
    DefaultLayout,
    Sidebar,
    Navbar,
    AppFooter,
    AppCard,
    AppModal,
    DeleteModal,
    DataTable,
    AppPagination,
    AppBreadcrumb,
    AppAlert,
    AppButton,
    AppBadge,
    TextField,
    TextareaField,
    DatePicker,
    FileUpload,
    ToggleSwitch,
    DynamicSelect,
    PhoneField,
    PricingTable,
    PlanCard,
}
