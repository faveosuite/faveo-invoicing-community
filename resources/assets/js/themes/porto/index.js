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
const DeleteModal      = defineAsyncComponent(() => import('./components/common/DeleteModal.vue'))
const AppTable         = defineAsyncComponent(() => import('./components/common/Table.vue'))
const DataTable        = defineAsyncComponent(() => import('./components/common/DataTable.vue'))
const AppPagination    = defineAsyncComponent(() => import('./components/common/Pagination.vue'))
const SimplePagination = defineAsyncComponent(() => import('./components/common/SimplePagination.vue'))
const AppBreadcrumb    = defineAsyncComponent(() => import('./components/common/Breadcrumb.vue'))
const AppAlert         = defineAsyncComponent(() => import('./components/common/Alert.vue'))
const AppButton        = defineAsyncComponent(() => import('./components/common/Button.vue'))
const AppBadge         = defineAsyncComponent(() => import('./components/common/Badge.vue'))

const ClientField   = defineAsyncComponent(() => import('./components/forms/ClientField.vue'))
const TextField     = defineAsyncComponent(() => import('./components/forms/TextField.vue'))
const SelectField   = defineAsyncComponent(() => import('./components/forms/SelectField.vue'))
const TextareaField = defineAsyncComponent(() => import('./components/forms/TextareaField.vue'))
const DatePicker    = defineAsyncComponent(() => import('./components/forms/DatePicker.vue'))
const FileUpload    = defineAsyncComponent(() => import('./components/forms/FileUpload.vue'))
const ToggleSwitch  = defineAsyncComponent(() => import('./components/forms/ToggleSwitch.vue'))
const DynamicSelect = defineAsyncComponent(() => import('./components/forms/DynamicSelect.vue'))
const PhoneField    = defineAsyncComponent(() => import('./components/forms/PhoneField.vue'))

const PricingTable = defineAsyncComponent(() => import('./components/store/PricingTable.vue'))
const PlanCard     = defineAsyncComponent(() => import('./components/store/PlanCard.vue'))

export const components = {
    ActionButton,
    ClientField,
    DefaultLayout,
    Sidebar,
    Navbar,
    AppFooter,
    AppCard,
    AppModal,
    DeleteModal,
    AppTable,
    DataTable,
    AppPagination,
    SimplePagination,
    AppBreadcrumb,
    AppAlert,
    AppButton,
    AppBadge,
    TextField,
    SelectField,
    TextareaField,
    DatePicker,
    FileUpload,
    ToggleSwitch,
    DynamicSelect,
    PhoneField,
    PricingTable,
    PlanCard,
}
