import { defineAsyncComponent } from 'vue';
import 'flag-icons/css/flag-icons.min.css';

// Layout shell — loaded eagerly (always needed on first render)
import DefaultLayout from './layouts/DefaultLayout.vue';
import Sidebar from './components/common/Sidebar.vue';
import Navbar from './components/common/Navbar.vue';
import AppFooter from './components/common/Footer.vue';
import AppButton from './components/common/Button.vue';
import AppAlert        from '@/components/Reusable/Alert.vue'
import DeleteModal     from '@/components/Reusable/DeleteModal.vue'
import StaticAlert     from './components/common/StaticAlert.vue'
import AppBreadcrumb   from './components/common/Breadcrumb.vue';

// Heavy / page-specific components — split into their own chunks, loaded on demand
const DataTable        = defineAsyncComponent(() => import('@/components/Reusable/DataTable.vue'));
const AppModal         = defineAsyncComponent(() => import('./components/common/Modal.vue'));
const TextField        = defineAsyncComponent(() => import('@/components/Reusable/FormField/TextField.vue'));
const TextareaField    = defineAsyncComponent(() => import('@/components/Reusable/FormField/TextareaField.vue'));
const DatePicker       = defineAsyncComponent(() => import('@/components/Reusable/FormField/DatePicker.vue'));
const FileUpload       = defineAsyncComponent(() => import('@/components/Reusable/FormField/FileUpload.vue'));
const ToggleSwitch     = defineAsyncComponent(() => import('@/components/Reusable/FormField/ToggleSwitch.vue'));
const DynamicSelect    = defineAsyncComponent(() => import('@/components/Reusable/FormField/DynamicSelect.vue'));
const PhoneField       = defineAsyncComponent(() => import('@/components/Reusable/FormField/PhoneField.vue'));
const TinyMCE          = defineAsyncComponent(() => import('./components/forms/TinyMCE.vue'));

export const components = {
    DefaultLayout,
    Sidebar,
    Navbar,
    AppFooter,
    AppButton,
    AppAlert,
    StaticAlert,
    DeleteModal,
    AppBreadcrumb,
    DataTable,
    AppModal,
    TextField,
    TextareaField,
    DatePicker,
    FileUpload,
    ToggleSwitch,
    DynamicSelect,
    PhoneField,
    TinyMCE,
};
