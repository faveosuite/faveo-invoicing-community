import { defineAsyncComponent } from 'vue';
import 'flag-icons/css/flag-icons.min.css';

// Layout shell — loaded eagerly (always needed on first render)
import DefaultLayout from './layouts/DefaultLayout.vue';
import Sidebar from './components/common/Sidebar.vue';
import Navbar from './components/common/Navbar.vue';
import AppFooter from './components/common/Footer.vue';
import AppButton from './components/common/Button.vue';
import AppBadge from './components/common/Badge.vue';
import AppAlert from './components/common/Alert.vue'
import DeleteModal from './components/common/DeleteModal.vue';
import AppBreadcrumb from './components/common/Breadcrumb.vue';

// Heavy / page-specific components — split into their own chunks, loaded on demand
const AppTable      = defineAsyncComponent(() => import('./components/common/Table.vue'));
const DataTable        = defineAsyncComponent(() => import('./components/common/DataTable.vue'));
const SimplePagination = defineAsyncComponent(() => import('./components/common/SimplePagination.vue'));
const AppModal      = defineAsyncComponent(() => import('./components/common/Modal.vue'));
const AppPagination = defineAsyncComponent(() => import('./components/common/Pagination.vue'));
const AppCard       = defineAsyncComponent(() => import('./components/common/Card.vue'));
const TextField     = defineAsyncComponent(() => import('./components/forms/TextField.vue'));
const SelectField   = defineAsyncComponent(() => import('./components/forms/SelectField.vue'));
const TextareaField = defineAsyncComponent(() => import('./components/forms/TextareaField.vue'));
const DatePicker    = defineAsyncComponent(() => import('./components/forms/DatePicker.vue'));
const FileUpload    = defineAsyncComponent(() => import('./components/forms/FileUpload.vue'));
const ToggleSwitch  = defineAsyncComponent(() => import('./components/forms/ToggleSwitch.vue'));
const DynamicSelect = defineAsyncComponent(() => import('./components/forms/DynamicSelect.vue'));
const PhoneField    = defineAsyncComponent(() => import('./components/forms/PhoneField.vue'));
const TinyMCE       = defineAsyncComponent(() => import('./components/forms/TinyMCE.vue'));

export const components = {
    DefaultLayout,
    Sidebar,
    Navbar,
    AppFooter,
    AppButton,
    AppBadge,
    AppAlert,
    DeleteModal,
    AppBreadcrumb,
    AppTable,
    DataTable,
    SimplePagination,
    AppModal,
    AppPagination,
    AppCard,
    TextField,
    SelectField,
    TextareaField,
    DatePicker,
    FileUpload,
    ToggleSwitch,
    DynamicSelect,
    PhoneField,
    TinyMCE,
};
