import './bootstrap';

import { createApp, h } from 'vue';

import router from './router';

import store from './store';

import LicenseManagerRenderer from "./Layouts/LicenseManagerRenderer.vue";

let app = createApp({});

app.component('license-manager-renderer', LicenseManagerRenderer);

import Tooltip from "./components/Reusable/Tooltip.vue";

import VTooltip from "v-tooltip";

app.use(VTooltip);

import "v-tooltip/dist/v-tooltip.css";
import "../css/cropper.css";

app.component('tool-tip', Tooltip);

import {ServerTable, ClientTable, EventBus} from 'v-tables-3';

app.use(ClientTable)
app.use(ServerTable)

import mitt from 'mitt';
const emitter = mitt();
app.config.globalProperties.emitter = emitter;
window.emitter = emitter;

import Alert from "./components/Reusable/Alert.vue";
import Loader from "./components/Reusable/Loader.vue";
import CustomLoader from "./components/Reusable/CustomLoader.vue";
import DatatableActions from "./components/Reusable/DatatableActions.vue";
import Modal from './components/Reusable/Modal.vue';

app.component('alert', Alert);
app.component('loader', Loader);
app.component('custom-loader', CustomLoader);
app.component('table-actions', DatatableActions);
app.component('modal', Modal);

import globalMixins from './globalMixins.js'

app.mixin(globalMixins)

app.use(router)

app.use(store)

app.mount('#app');
