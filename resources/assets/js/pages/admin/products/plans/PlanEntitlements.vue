<template>
    <div>
        <div v-if="loading" class="card card-light"><div class="card-body text-center py-3"><loader /></div></div>
        <div v-else class="card card-light">
            <div class="card-header">
                <h4 class="card-title">
                    {{ __('message.features') }}
                    <ToolTip :message="`${__('message.plan_mode_help_included')} ${__('message.plan_mode_help_optional', { period: periodText })}`" size="small" />
                </h4>
                <div v-if="addable.length || otherPlans.length" class="card-tools">
                    <button v-if="otherPlans.length" type="button" class="btn btn-tool" v-tooltip="__('message.plan_copy_title')" :aria-label="__('message.plan_copy_title')"
                            @click="openCopy">
                        <i class="fas fa-copy" aria-hidden="true"></i>
                    </button>
                    <button v-if="addable.length" type="button" class="btn btn-tool" v-tooltip="__('message.plan_add_features')" :aria-label="__('message.plan_add_features')" @click="openPicker">
                        <i class="fas fa-plus" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Toolbar as on every DataTable: bulk actions (once rows are ticked), then search -->
                <div v-if="rows.length" class="d-flex justify-content-end mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <BulkActionIcons v-if="selectedRows.length" :actions="[
                            { key: 'included', icon: 'fas fa-check', label: __('message.plan_make_included'), onClick: bulkIncluded },
                            { key: 'addon', icon: 'fas fa-tag', label: __('message.plan_make_addon'), onClick: openBulkAddon },
                            { key: 'remove', icon: 'fas fa-trash', label: __('message.plan_feature_remove'), onClick: bulkRemove },
                        ]" />
                        <input v-model="searchInput" type="text" class="form-control globe-search" name="plan_feature_search" @keyup.enter="search = searchInput"
                               :placeholder="__('message.search_placeholder')" :aria-label="__('message.entitlement_search')"
                               autocomplete="off" spellcheck="false" />
                    </div>
                </div>

                <!-- Narrow by category -->
                <div v-if="categories.length > 1" class="d-flex flex-wrap gap-2 mb-3" role="group" :aria-label="__('message.entitlement_category')">
                    <action-button v-for="c in ['', ...categories]" :key="c" variant="light" class="btn-xs" :class="{ active: category === c }"
                                   :label="c || __('message.entitlement_all')" :aria-pressed="category === c" @click="category = c" />
                </div>

                <!-- Just removed: a way back (nothing is saved until the plan is) -->
                <div v-if="removed" class="alert alert-light border d-flex align-items-center flex-wrap gap-2 py-2" role="status" aria-live="polite">
                    <span>{{ removed.text }}</span>
                    <button type="button" class="btn btn-link btn-sm p-0" @click="undoRemove">{{ __('message.plan_undo') }}</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered align-middle">
                        <thead>
                            <tr>
                                <th scope="col">
                                    <input v-if="pageRows.length" type="checkbox" name="select_all_rows" :checked="allSelected"
                                           :aria-label="__('message.select_all')" @change="selectAll($event.target.checked)" />
                                </th>
                                <th scope="col">{{ __('message.entitlement_name') }}</th>
                                <th scope="col">{{ __('message.entitlement_category') }}</th>
                                <th scope="col">{{ __('message.plan_mode') }}</th>
                                <th scope="col">{{ __('message.plan_mode_value') }}</th>
                                <th scope="col">{{ __('message.price') }}</th>
                                <th scope="col">{{ __('message.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in pageRows" :key="row.id">
                                <td>
                                    <input type="checkbox" :name="`select_${row.id}`" :checked="selected.includes(row.id)"
                                           :aria-label="__('message.plan_select_row', { name: row.label })" @change="toggleRow(row.id, $event.target.checked)" />
                                </td>
                                <td class="text-break">
                                    {{ row.label }}
                                    <span v-if="!row.is_active" class="badge bg-secondary ms-1">{{ __('message.entitlement_archived') }}</span>
                                    <small class="d-block text-muted" translate="no">{{ row.code }}</small>
                                </td>
                                <td>{{ categoryOf(row) }}</td>
                                <td>
                                    <span class="badge" :class="row.mode === 'optional' ? 'bg-info' : 'bg-success'">
                                        {{ row.mode === 'optional' ? __('message.plan_mode_optional_short') : __('message.plan_mode_included') }}
                                    </span>
                                </td>
                                <td :class="{ 'text-muted': row.mode === 'optional' }">{{ givesText(row) }}</td>
                                <td>
                                    <template v-if="priceText(row)">
                                        <span class="tabular-nums">{{ priceText(row) }}</span>
                                        <small class="d-block text-muted">{{ priceLabel(row) }}</small>
                                    </template>
                                    <span v-else class="text-muted">—</span>
                                </td>
                                <td class="text-nowrap">
                                    <action-button action="edit" :label="`${__('message.edit')} ${row.label}`" v-tooltip="__('message.edit')" @click="openEditor(row)" />
                                    <action-button variant="light" class="table_btn ms-1" icon="fas fa-trash" :iconOnly="true"
                                                   :label="`${__('message.plan_feature_remove')}: ${row.label}`" v-tooltip="__('message.plan_feature_remove')"
                                                   @click="removeRows([row])" />
                                </td>
                            </tr>
                            <tr v-if="!total">
                                <td colspan="7" class="text-center text-muted py-4">
                                    {{ rows.length ? __('message.entitlement_no_match') : __('message.plan_features_none_yet') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="total" class="pagination-container d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <template v-if="total === 1">{{ __('message.one_record') }}</template>
                        <template v-else-if="total <= PER_PAGE">{{ __('message.x_records', { count: total }) }}</template>
                        <template v-else>{{ __('message.datatable_info').replace('_START_', from).replace('_END_', to).replace('_TOTAL_', total) }}</template>
                    </div>
                    <Pagination :currentPage="page" :totalPages="pages" @change="p => (page = p)" />
                </div>
            </div>
        </div>

        <!-- Copy another plan's features: this plan's list becomes the same (saved with the plan) -->
        <AppModal :showModal="copying" :onClose="() => (copying = false)" classname="modal-md">
            <template #title><h4>{{ __('message.plan_copy_title') }}</h4></template>
            <template #fields>
                <template v-if="copying">
                    <p class="text-muted small">{{ __('message.plan_copy_help') }}</p>
                    <DynamicSelect name="copy_from" :label="__('message.plan_copy_from')" :required="true" :elements="otherPlans"
                                   :value="otherPlans.find(p => p.id === copyFrom) ?? null" :onChange="(o) => (copyFrom = o?.id ?? null)" :error="copyError" />
                </template>
            </template>
            <template #controls>
                <action-button action="confirm" icon="fas fa-copy" :label="__('message.plan_copy')" :loading="copyBusy" @click="copyFeatures" />
            </template>
        </AppModal>

        <!-- Make the ticked rows add-ons, all at one price -->
        <AppModal :showModal="bulkAddon" :onClose="() => (bulkAddon = false)" classname="modal-md">
            <template #title><h4>{{ __('message.plan_bulk_addon_title', { count: selectedRows.length }) }}</h4></template>
            <template #fields>
                <template v-if="bulkAddon">
                    <p class="text-muted small">{{ __('message.plan_bulk_addon_help') }}</p>
                    <p v-if="!currencies.length" class="text-muted mb-0">{{ __('message.plan_no_currencies') }}</p>
                    <div v-else class="row g-2">
                        <div v-for="cur in currencies" :key="cur" class="col-sm-6">
                            <TextField :name="`bulk_${cur}`" :id="`bulk_${cur}`" type="number" inputmode="decimal" autocomplete="off" :label="cur" classname="mb-0"
                                       :value="String(bulkPrices[cur] ?? '')" :placeholder="__('message.entitlement_not_sold')"
                                       :onChange="(v) => (bulkPrices[cur] = v)" />
                        </div>
                    </div>
                    <div v-if="bulkError" class="text-danger small mt-2" role="alert">{{ bulkError }}</div>
                </template>
            </template>
            <template #controls>
                <action-button action="apply" @click="bulkMakeAddon" />
            </template>
        </AppModal>

        <!-- Add several features at once, laid out like the feature editor: the choices and what's ticked on
             top, the list below. Explanations sit in tooltips. -->
        <AppModal :showModal="picking" :onClose="() => (picking = false)" :containerStyle="{ maxWidth: '760px' }" modalBodyClass="plan-feature-body">
            <template #title><h4>{{ __('message.plan_add_features') }}</h4></template>
            <template #fields>
                <template v-if="picking">
                    <!-- What's ticked, removable, so the choice is visible without scrolling the list -->
                    <div class="bg-light border rounded p-3 mb-3" role="status" aria-live="polite">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="text-uppercase text-muted small fw-bold mb-0">{{ __('message.plan_selected', { count: picked.length }) }}</h6>
                            <button v-if="picked.length" type="button" class="btn btn-link btn-sm p-0" @click="picked = []">{{ __('message.clear') }}</button>
                        </div>
                        <div v-if="picked.length" class="d-flex flex-wrap gap-1">
                            <span v-for="i in pickedItems.slice(0, 12)" :key="i.id" class="badge text-bg-light border fw-normal d-inline-flex align-items-center gap-1">
                                {{ i.label }}
                                <button type="button" class="btn btn-link btn-sm p-0 lh-1 text-muted" :aria-label="`${__('message.remove')} ${i.label}`" @click="togglePick(i.id, false)">
                                    <i class="fas fa-xmark" aria-hidden="true"></i>
                                </button>
                            </span>
                            <span v-if="pickedItems.length > 12" class="badge text-bg-light border fw-normal">+{{ pickedItems.length - 12 }}</span>
                        </div>
                        <p v-else class="small text-muted mb-0">{{ __('message.plan_add_none_ticked') }}</p>
                    </div>

                    <!-- 1. How they're added -->
                    <section class="mb-3">
                        <h6 class="text-uppercase text-muted small fw-bold mb-2">
                            {{ __('message.plan_section_availability') }} <ToolTip :message="__('message.plan_add_as_help')" size="small" />
                        </h6>
                        <div class="btn-group w-100" role="radiogroup" :aria-label="__('message.plan_add_as')">
                            <template v-for="m in [{ id: 'included', name: __('message.plan_mode_included'), desc: __('message.plan_feature_included_desc') }, { id: 'optional', name: __('message.plan_mode_addon'), desc: __('message.plan_feature_addon_desc') }]" :key="m.id">
                                <input :id="`add-as-${m.id}`" v-model="addAs" type="radio" class="btn-check" name="add_as" :value="m.id" autocomplete="off" />
                                <label class="btn" :class="addAs === m.id ? 'btn-primary' : 'btn-light border'" :for="`add-as-${m.id}`" v-tooltip="m.desc">{{ m.name }}</label>
                            </template>
                        </div>
                    </section>

                    <!-- 2. The add-ons' price, typed once for all of them -->
                    <section v-if="addAs === 'optional'" class="border-top pt-3 mb-3">
                        <h6 class="text-uppercase text-muted small fw-bold mb-2">
                            {{ __('message.plan_add_price') }} <ToolTip :message="__('message.plan_add_price_help')" size="small" />
                        </h6>
                        <p v-if="!currencies.length" class="text-muted mb-0">{{ __('message.plan_no_currencies') }}</p>
                        <div v-else class="row g-2">
                            <div v-for="cur in currencies" :key="cur" class="col-sm-6 col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text" translate="no">{{ cur }}</span>
                                    <input :id="`batch_${cur}`" v-model="batchPrices[cur]" :name="`batch_${cur}`" type="number" min="0" step="any" inputmode="decimal"
                                           autocomplete="off" class="form-control" :placeholder="__('message.entitlement_not_sold')" :aria-label="`${__('message.plan_add_price')} (${cur})`" />
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- 3. The features: search, then one folding section per category -->
                    <section class="border-top pt-3">
                        <input v-model="pickSearch" type="text" class="form-control mb-3" name="add_features_search" :placeholder="__('message.plan_find_feature')"
                               :aria-label="__('message.entitlement_search')" autocomplete="off" spellcheck="false" />

                        <div v-for="group in pickGroups" :key="group.name" class="mb-3">
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-1 mb-2">
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-reset" :aria-expanded="groupOpen(group)"
                                        @click="toggleGroup(group)">
                                    <i :class="groupOpen(group) ? 'fas fa-chevron-down' : 'fas fa-chevron-right'" class="fa-fw me-1 text-muted" aria-hidden="true"></i>
                                    <span class="text-uppercase small fw-bold">{{ group.name }}</span>
                                    <span class="text-muted small ms-1">{{ group.items.filter(i => picked.includes(i.id)).length }}/{{ group.items.length }}</span>
                                </button>
                                <button type="button" class="btn btn-link btn-sm p-0" @click="pickAll(group, !allPicked(group))"
                                        :aria-label="`${allPicked(group) ? __('message.clear') : __('message.select_all')}: ${group.name}`">
                                    {{ allPicked(group) ? __('message.clear') : __('message.select_all') }}
                                </button>
                            </div>
                            <div v-if="groupOpen(group)" class="row">
                                <div v-for="i in group.items" :key="i.id" class="col-md-6">
                                    <div class="form-check">
                                        <input :id="`pick_${i.id}`" class="form-check-input" type="checkbox" :name="`pick_${i.id}`" :checked="picked.includes(i.id)"
                                               @change="togglePick(i.id, $event.target.checked)" />
                                        <label class="form-check-label fw-normal" :for="`pick_${i.id}`" :title="i.code">
                                            {{ i.label }}
                                            <!-- On/off is the usual kind; only the others are named -->
                                            <span v-if="i.type !== 'boolean'" class="badge text-bg-light border fw-normal ms-1">{{ __(`message.entitlement_type_${i.type}`) }}</span>
                                            <small v-if="sameName(i)" class="text-muted ms-1" translate="no">{{ i.code }}</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p v-if="!pickGroups.length" class="text-muted mb-0">{{ __('message.entitlement_no_match') }}</p>
                    </section>
                    <div v-if="pickError" class="text-danger small mt-2" role="alert">{{ pickError }}</div>
                </template>
            </template>
            <template #controls>
                <action-button action="add" :label="`${__('message.add')} (${picked.length})`" @click="addPicked" />
            </template>
        </AppModal>

        <!-- One editor per feature: as little text as possible; the explanations sit in tooltips and the
             summary line at the bottom says what the customer gets. -->
        <AppModal :showModal="!!editing" :onClose="() => (editing = null)" :containerStyle="{ maxWidth: '680px' }" modalBodyClass="plan-feature-body">
            <template #title>
                <h4>{{ editing?.label }} <small v-if="editing?.code" class="text-muted fw-normal" translate="no">{{ editing.code }}</small></h4>
            </template>
            <template #fields>
                <template v-if="editing">
                    <!-- The result first, as a short list that updates while the admin edits below -->
                    <div v-if="summaryLines.length" class="bg-light border rounded p-3 mb-3" role="status" aria-live="polite">
                        <h6 class="text-uppercase text-muted small fw-bold mb-2">{{ __('message.plan_customers_get') }}</h6>
                        <ul class="list-unstyled mb-0">
                            <li v-for="line in summaryLines" :key="line.text" class="d-flex align-items-baseline gap-2" :class="line.cls">
                                <i :class="line.icon" class="fa-fw" aria-hidden="true"></i><span>{{ line.text }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Three short sections, each under a small heading: availability, what the plan gives, pricing -->
                    <!-- 1. Included or Add-on: one choice, explained on hover -->
                    <section v-if="modes(editing).length > 1" class="mb-3">
                    <h6 class="text-uppercase text-muted small fw-bold mb-2">{{ __('message.plan_section_availability') }}</h6>
                    <div class="btn-group w-100" role="radiogroup" :aria-label="__('message.plan_feature_how')">
                        <template v-for="m in modes(editing)" :key="m.id">
                            <input :id="`mode-${m.id}`" v-model="editing.mode" type="radio" class="btn-check" name="mode" :value="m.id" autocomplete="off" />
                            <label class="btn" :class="editing.mode === m.id ? 'btn-primary' : 'btn-light border'" :for="`mode-${m.id}`" v-tooltip="m.desc">
                                {{ m.name }}
                            </label>
                        </template>
                    </div>
                    </section>

                    <!-- 2. What the plan gives (an on/off feature is simply on) -->
                    <section v-if="editing.mode === 'included' && editing.type !== 'boolean'" :class="{ 'border-top pt-3': modes(editing).length > 1 }">
                    <h6 class="text-uppercase text-muted small fw-bold mb-2">{{ __('message.plan_mode_value') }}</h6>
                    <div class="row">
                        <template v-if="editing.type === 'number'">
                            <div class="col-md-6">
                                <RadioButton name="value_kind" :label="editing.code === 'agents' ? __('message.plan_agents_included') : __('message.entitlement_limit')"
                                             :value="editing.unlimited ? 'unlimited' : 'limited'" :options="LIMIT_KINDS"
                                             :onChange="(k) => setLimit(editing.value, k === 'unlimited')" />
                            </div>
                            <div v-if="!editing.unlimited" class="col-md-6">
                                <TextField name="value" type="number" inputmode="numeric" autocomplete="off" :required="true" :value="String(editing.value ?? '')"
                                           :error="valueError" :label="__('message.plan_feature_amount')" :onChange="(v) => (editing.value = v)" />
                            </div>
                        </template>
                        <div v-else class="col-md-6">
                            <DynamicSelect v-if="editing.type === 'select'" name="value" :label="__('message.plan_feature_value')" :elements="choices(editing)"
                                           :value="choices(editing).find(o => o.id === editing.value) ?? null" :clearable="false"
                                           :onChange="(o) => (editing.value = o?.id ?? '')" />
                            <DynamicSelect v-else-if="editing.type === 'date'" name="ends" :label="__('message.plan_date_ends')" :elements="endOptions"
                                           :value="endOptions.find(o => o.id === (editing.value?.ends ?? 'subscription')) ?? null" :clearable="false"
                                           :onChange="(o) => o && setEnds(editing, { ends: o.id })" />
                            <TextField v-else name="value" :label="__('message.plan_feature_value')" :value="String(editing.value ?? '')"
                                       :onChange="(v) => (editing.value = v)" />
                        </div>
                        <!-- A date on its own schedule: every N days / months / years, or a fixed date -->
                        <template v-if="editing.type === 'date' && editing.value?.ends === 'custom'">
                            <div class="col-md-3">
                                <TextField name="every" type="number" inputmode="numeric" :label="__('message.plan_date_every')" :value="String(editing.value.every ?? '')"
                                           :onChange="(v) => setEnds(editing, { every: v })" />
                            </div>
                            <div class="col-md-3">
                                <DynamicSelect name="unit" :label="__('message.plan_date_unit')" :elements="unitOptions" :clearable="false" :searchable="false"
                                               :value="unitOptions.find(o => o.id === (editing.value.unit ?? 'day')) ?? null"
                                               :onChange="(o) => o && setEnds(editing, { unit: o.id })" />
                            </div>
                        </template>
                        <div v-if="editing.type === 'date' && editing.value?.ends === 'fixed'" class="col-md-6">
                            <TextField name="date" type="date" :label="__('message.date')" :value="editing.value.date ?? ''"
                                       :onChange="(v) => setEnds(editing, { date: v })" />
                        </div>
                    </div>
                    </section>

                    <!-- 3. Pricing: sell extra units of a limited number, or the add-on's price -->
                    <section v-if="canBuyMore(editing) || pricesShown(editing)" class="border-top pt-3">
                    <h6 class="text-uppercase text-muted small fw-bold mb-2">{{ __('message.plan_section_pricing') }}</h6>
                    <Checkbox v-if="canBuyMore(editing)" name="buy_more" classname="mb-3" :label="__('message.plan_feature_buy_more')"
                              :hint="__('message.plan_feature_buy_more_help')" :value="!!editing.buyMore" :onChange="(v) => (editing.buyMore = v)" />

                    <!-- One row per currency; what each column means is on its header -->
                    <template v-if="pricesShown(editing)">
                        <p v-if="agentsAll(editing)" class="small text-muted mb-2">{{ __('message.plan_price_agents_all') }}</p>
                        <p v-if="!currencies.length" class="text-muted mb-0">{{ __('message.plan_no_currencies') }}</p>
                        <div v-else class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col">{{ __('message.currency') }}</th>
                                        <th scope="col">{{ priceTitle(editing) }} <ToolTip :message="priceHelp(editing)" size="small" /></th>
                                        <th scope="col">{{ __('message.renew-price') }} <ToolTip :message="__('message.plan_renew_help')" size="small" /></th>
                                        <th scope="col">{{ __('message.plan_offer_percent') }} <ToolTip :message="__('message.plan_offer_help')" size="small" /></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="cur in currencies" :key="cur">
                                        <th scope="row" translate="no">{{ cur }}</th>
                                        <td><TextField :name="`unit_${cur}`" :id="`unit_${cur}`" type="number" inputmode="decimal" autocomplete="off" :ariaLabel="`${priceTitle(editing)} (${cur})`" classname="mb-0" :value="String(editing.prices[cur].unit_price ?? '')" :placeholder="__('message.entitlement_not_sold')"
                                                       :onChange="(v) => (editing.prices[cur].unit_price = v)" /></td>
                                        <td><TextField :name="`renew_${cur}`" type="number" inputmode="decimal" autocomplete="off" :ariaLabel="`${__('message.renew-price')} (${cur})`" classname="mb-0" :value="String(editing.prices[cur].renew_price ?? '')" :placeholder="__('message.entitlement_same_as_price')"
                                                       :onChange="(v) => (editing.prices[cur].renew_price = v)" /></td>
                                        <td><TextField :name="`offer_${cur}`" type="number" inputmode="decimal" autocomplete="off" :ariaLabel="`${__('message.plan_offer_percent')} (${cur})`" classname="mb-0" :value="String(editing.prices[cur].offer_percent ?? '')" :placeholder="__('message.plan_no_offer')"
                                                       :onChange="(v) => (editing.prices[cur].offer_percent = v)" /></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-if="priceError" class="text-danger small mt-2" role="alert">{{ priceError }}</div>
                    </template>
                    </section>

                </template>
            </template>
            <template #controls>
                <action-button action="apply" @click="applyEditor" />
            </template>
        </AppModal>
    </div>
</template>
<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import http from '@/plugins/axios'
import { errorHandler } from '@/helpers/responseHandler.js'
import TextField from '@/components/Reusable/FormField/TextField.vue'
import DynamicSelect from '@/components/Reusable/FormField/DynamicSelect.vue'
import RadioButton from '@/components/Reusable/FormField/RadioButton.vue'
import Checkbox from '@/components/Reusable/FormField/Checkbox.vue'
import BulkActionIcons from '@/components/Reusable/BulkActionIcons.vue'
import Pagination from '@/components/Reusable/Pagination.vue'
import ToolTip from '@/components/Reusable/Tooltip.vue'

/**
 * The plan's features, shown as a section of the plan form. The form's Save calls
 * save() after the plan itself is saved, so prices follow the plan's currencies.
 */
const props = defineProps({
    planId:     { type: [String, Number], required: true },
    currencies: { type: Array, default: () => [] }, // the plan's currency codes, live from the Pricing table
    period:     { type: String, default: '' },      // the plan's period name
})

const COMPONENT = 'plans-edit'

const loading      = ref(true)
const items        = ref([])
const liveLicenses = ref(0)
const editing      = ref(null) // a copy of the feature in the editor; written back on Apply
const priceError   = ref('')
const valueError   = ref('')

const periodText = computed(() => props.period || __('message.entitlement_billing_period'))
// Only the features this plan uses are listed; the rest are picked from "Add feature".
const shown      = computed(() => items.value.filter(i => i.mode !== 'none'))
// Features not on the plan yet, which can be added.
const addable    = computed(() => items.value.filter(i => i.mode === 'none' && i.is_active))

// ── The table: the plan's features; search, category and pages as on the order page ──
const rows       = shown
const DATES      = __('message.order_dates')
const categoryOf = row => (row.type === 'date' ? DATES : (row.category || __('message.order_features')))
const categories = computed(() => [...new Set(rows.value.map(categoryOf))])

// Search, category and page live in the URL (?fq=, ?fcat=, ?fpage=) so a reload keeps them.
const route       = useRoute()
const router      = useRouter()
const search      = ref(route?.query?.fq ?? '') // applied on Enter, like DataTable
const searchInput = ref(search.value)
const category    = ref(route?.query?.fcat ?? '')
const filtered    = computed(() => {
    const q = search.value.trim().toLowerCase()
    return rows.value.filter(r => (!category.value || categoryOf(r) === category.value)
        && (!q || r.label.toLowerCase().includes(q) || String(r.code ?? '').includes(q)))
})

const PER_PAGE = 10
const page     = ref(Math.max(1, Number(route?.query?.fpage) || 1))
const total    = computed(() => filtered.value.length)
const pages    = computed(() => Math.max(1, Math.ceil(total.value / PER_PAGE)))
const from     = computed(() => (page.value - 1) * PER_PAGE + 1)
const to       = computed(() => Math.min(page.value * PER_PAGE, total.value))
const pageRows = computed(() => filtered.value.slice(from.value - 1, to.value))
watch([search, category], () => (page.value = 1))
watch(pages, n => { if (page.value > n) page.value = n }) // removing the last row of a page
watch([search, category, page], ([q, cat, p]) => {
    router?.replace({ query: { ...route?.query, fq: q.trim() || undefined, fcat: cat || undefined, fpage: p > 1 ? p : undefined } })
})

const isEmpty = value => value === '' || value === null || value === undefined

// What the plan gives, in words: "On", "10", "Unlimited", "Ends with the subscription"…
function givesText(item) {
    if (item.mode === 'optional') return __('message.plan_gives_nothing')
    if (item.type === 'date') return endsText(item.value)
    if (item.type === 'boolean') return __('message.entitlement_on')
    if (item.type === 'number') return isEmpty(item.value) ? __('message.unlimited') : String(item.value)
    return String(item.value || item.default_value || '—')
}

// A date feature's value is how the plan ends it: { ends, every?, unit?, date? }.
const END_KINDS = ['subscription', 'custom', 'month_end', 'year_end', 'fixed', 'never']
const endOptions = END_KINDS.map(k => ({ id: k, name: __(`message.plan_date_ends_${k}`) }))
const unitOptions = ['day', 'month', 'year'].map(u => ({ id: u, name: __(`message.plan_date_unit_${u}`) }))

// Changes the end rule on the editor's copy; Apply writes it to the feature.
function setEnds(target, patch) {
    const next = { ...(target.value ?? {}), ...patch }
    if (patch.ends === 'custom') next.unit ??= 'day'
    target.value = next
}

function endsText(value) {
    const rule = value ?? { ends: 'subscription' }
    if (rule.ends === 'custom') return `${rule.every ?? '?'} ${__(`message.plan_date_unit_${rule.unit ?? 'day'}`)}`
    if (rule.ends === 'fixed') return rule.date ?? '—'
    return __(`message.plan_date_ends_${rule.ends}`)
}

// One line per thing the customer gets, for the plan summary.
const summary = computed(() => {
    const lines = []
    // The features as counts: listing each one made the sticky summary taller than the screen
    const included = shown.value.filter(i => i.mode === 'included').length
    const optional = shown.value.filter(i => i.mode === 'optional').length
    if (included || optional) {
        lines.push({
            label: __('message.features'),
            value: __('message.plan_summary_included', { count: included }),
            extra: optional ? __('message.plan_summary_optional', { count: optional }) : '',
        })
    }
    return lines
})

// ── Add features: a checklist by category, added as Included or as Add-ons with one price ──
const picking     = ref(false)
const pickSearch  = ref('')
const picked      = ref([])
const addAs       = ref('included')
const batchPrices = ref({})
const pickError   = ref('')

function openPicker() {
    pickSearch.value = ''
    picked.value = []
    addAs.value = 'included'
    batchPrices.value = {}
    pickError.value = ''
    groupOpenState.value = {}
    picking.value = true
}

// The addable features matching the search, grouped as the table groups them.
const pickGroups = computed(() => {
    const q = pickSearch.value.trim().toLowerCase()
    const groups = new Map()
    for (const i of addable.value) {
        if (q && !i.label.toLowerCase().includes(q) && !String(i.code ?? '').includes(q)) continue
        const name = categoryOf(i)
        groups.set(name, [...(groups.get(name) ?? []), i])
    }
    return [...groups].map(([name, list]) => ({ name, items: list }))
})

const pickedItems = computed(() => picked.value.map(id => items.value.find(i => i.id === id)).filter(Boolean))
// Two features with the same name (e.g. a plugin and its Pro edition named alike) show their code to tell them apart.
const sameName = item => addable.value.some(o => o.id !== item.id && o.label === item.label)
// Long categories (the ~100 plugins) start folded; a search opens everything that matches.
const groupOpenState = ref({})
const groupOpen = group => pickSearch.value.trim() !== '' || (groupOpenState.value[group.name] ?? group.items.length <= 25)
const toggleGroup = group => { groupOpenState.value = { ...groupOpenState.value, [group.name]: !groupOpen(group) } }

const allPicked = group => group.items.every(i => picked.value.includes(i.id))
const pickAll = (group, on) => {
    const ids = group.items.map(i => i.id)
    picked.value = on ? [...new Set([...picked.value, ...ids])] : picked.value.filter(id => !ids.includes(id))
}
const togglePick = (id, on) => {
    if (on !== picked.value.includes(id)) picked.value = on ? [...picked.value, id] : picked.value.filter(x => x !== id)
}

// Prices typed once for several features: the currencies with a price, or an error.
function batchPriced(prices, required) {
    const priced = props.currencies.filter(c => !isEmpty(prices[c]))
    const error = required && !priced.length ? __('message.plan_price_needs_one_currency')
        : priced.some(c => Number(prices[c]) < 0) ? __('message.entitlement_offer_invalid') : ''
    return { priced, error }
}

// Only on/off and number features can be add-ons (not Agents).
const canBeAddon = item => item.sellable && item.code !== 'agents'

function makeAddon(item, prices, priced) {
    item.mode = 'optional'
    item.prices = { ...item.prices, ...Object.fromEntries(priced.map(c => [c, { unit_price: prices[c], renew_price: '', offer_percent: '' }])) }
}

function makeIncluded(item) {
    if (item.mode !== 'included' && item.type === 'boolean') {
        Object.values(item.prices ?? {}).forEach(p => Object.assign(p, { unit_price: '', renew_price: '', offer_percent: '' })) // nothing to buy
    }
    item.mode = 'included'
    item.value ??= startValue(item)
}

// ── Change ticked rows together ──
const selected     = ref([])
const selectedRows = computed(() => rows.value.filter(r => selected.value.includes(r.id)))
// As on every DataTable: "select all" means the rows on screen, and ticks on rows that leave the screen drop.
const allSelected  = computed(() => pageRows.value.length > 0 && pageRows.value.every(r => selected.value.includes(r.id)))
const selectAll    = on => {
    const ids = pageRows.value.map(r => r.id)
    selected.value = on ? [...new Set([...selected.value, ...ids])] : selected.value.filter(id => !ids.includes(id))
}
watch(pageRows, list => {
    const shown = new Set(list.map(r => r.id))
    if (selected.value.some(id => !shown.has(id))) selected.value = selected.value.filter(id => shown.has(id))
})
const toggleRow    = (id, on) => { selected.value = on ? [...new Set([...selected.value, id])] : selected.value.filter(x => x !== id) }
const itemOf       = row => items.value.find(i => i.id === row.id)

function bulkIncluded() {
    selectedRows.value.forEach(row => makeIncluded(itemOf(row)))
    selected.value = []
}

function bulkRemove() {
    removeRows(selectedRows.value)
    selected.value = []
}

// Removing is undoable until the next removal (and nothing is saved until the plan is).
const removed = ref(null)

function removeRows(list) {
    const before = list.map(row => itemOf(row)).map(item => ({ item, mode: item.mode, value: item.value, prices: JSON.parse(JSON.stringify(item.prices ?? {})) }))
    before.forEach(({ item }) => { item.mode = 'none' })
    removed.value = {
        before,
        text: before.length === 1 ? __('message.plan_removed_one', { name: before[0].item.label }) : __('message.plan_removed_many', { count: before.length }),
    }
}

function undoRemove() {
    removed.value?.before.forEach(({ item, mode, value, prices }) => Object.assign(item, { mode, value, prices }))
    removed.value = null
}

const bulkAddon  = ref(false)
const bulkPrices = ref({})
const bulkError  = ref('')

function openBulkAddon() {
    bulkPrices.value = {}
    bulkError.value = ''
    bulkAddon.value = true
}

function bulkMakeAddon() {
    const { priced, error } = batchPriced(bulkPrices.value, true)
    bulkError.value = error
    if (error) return
    selectedRows.value.map(itemOf).filter(canBeAddon).forEach(item => makeAddon(item, bulkPrices.value, priced))
    bulkAddon.value = false
    selected.value = []
}

// ── Copy another plan's features ──
const otherPlans = ref([])
const copying    = ref(false)
const copyFrom   = ref(null)
const copyBusy   = ref(false)
const copyError  = ref('')

function openCopy() {
    copyFrom.value = null
    copyError.value = ''
    copying.value = true
}

async function copyFeatures() {
    if (!copyFrom.value) {
        copyError.value = __('message.plan_copy_pick')
        return
    }
    copyBusy.value = true
    try {
        const res = await http.get(`/plan/${copyFrom.value}/entitlements`)
        const source = new Map((res.data?.data?.entitlements ?? []).map(i => [i.id, i]))
        for (const item of items.value) {
            const from = source.get(item.id)
            if (!from || from.mode === 'none') {
                item.mode = 'none'
                continue
            }
            Object.assign(item, {
                mode:   from.mode,
                value:  from.value && typeof from.value === 'object' ? { ...from.value } : from.value,
                prices: JSON.parse(JSON.stringify(from.prices ?? {})),
            })
        }
        selected.value = []
        copying.value = false
    } catch (e) {
        copyError.value = e?.response?.data?.message ?? __('message.something_went_wrong')
    } finally {
        copyBusy.value = false
    }
}

// What a newly included feature starts with: on, its default, or (agents) one agent.
function startValue(item) {
    // Agents start at one: empty would be unlimited, 0000 on every license.
    return item.type === 'boolean' || item.code === 'agents' ? '1' : item.type === 'date' ? { ends: 'subscription' } : (item.default_value ?? '')
}

function addPicked() {
    if (!picked.value.length) {
        pickError.value = __('message.plan_add_pick_one')
        return
    }
    const { priced, error } = batchPriced(batchPrices.value, addAs.value === 'optional')
    pickError.value = error
    if (error) return
    for (const id of picked.value) {
        const item = items.value.find(i => i.id === id)
        // The rest (agents, choices, text, dates) can't be add-ons: they're included.
        if (addAs.value === 'optional' && canBeAddon(item)) makeAddon(item, batchPrices.value, priced)
        else makeIncluded(item)
    }
    picking.value = false
}

// Adds the feature as included, then opens it so its value and price can be set.
function addFeature(id) {
    const item = id && items.value.find(i => String(i.id) === String(id))
    if (!item) return
    item.mode = 'included'
    item.value ??= startValue(item)
    openEditor(item)
}

function choices(item) {
    return (item.options ?? []).map(o => ({ id: o, name: o }))
}

// Only numbers and on/off features can be bought, so only they can be optional. Not Agents: a plan
// sold by agents needs a count on the license.
function modes(item) {
    const included = { id: 'included', name: __('message.plan_mode_included'), desc: __('message.plan_feature_included_desc') }
    if (item.code === 'agents') return [included]
    return [included, ...(item.sellable ? [{ id: 'optional', name: __('message.plan_mode_addon'), desc: __('message.plan_feature_addon_desc') }] : [])]
}

// What the price column means for this row, or null when there is nothing to price.
function priceLabel(item) {
    if (item.mode === 'optional') return item.type === 'number' ? __('message.plan_price_per_unit') : __('message.price')
    // Unlimited: nothing more to buy
    if (item.mode === 'included' && item.type === 'number') return isEmpty(item.value) ? null : __('message.plan_price_extra_unit')
    return null
}

function priceText(item) {
    if (!priceLabel(item)) return ''
    const set = Object.entries(item.prices ?? {}).filter(([cur, p]) => props.currencies.includes(cur) && p.unit_price !== '' && p.unit_price !== null && p.unit_price !== undefined)
    return set.map(([cur, p]) => `${cur} ${p.unit_price}`).join(', ')
}

// One price row per currency the plan is sold in (kept for currencies added later).
function withAllCurrencies(prices) {
    return Object.fromEntries(props.currencies.map(cur => [cur, {
        unit_price:    prices?.[cur]?.unit_price ?? '',
        renew_price:   prices?.[cur]?.renew_price ?? '',
        offer_percent: prices?.[cur]?.offer_percent ?? '',
    }]))
}

function openEditor(row) {
    priceError.value = ''
    valueError.value = ''
    const item = items.value.find(i => i.id === row.id)
    editing.value = {
        ...item,
        value: item.value && typeof item.value === 'object' ? { ...item.value } : item.value,
        unlimited: item.type === 'number' && isEmpty(item.value), // unlimited is stored as an empty value
        prices: withAllCurrencies(item.prices),
    }
    const prices = Object.values(editing.value.prices)
    editing.value.buyMore = prices.some(p => !isEmpty(p.unit_price)) // a price sells more
}

// A number is Limited (with its limit beside it) or Unlimited, saved as an empty value.
const LIMIT_KINDS = [{ value: 'limited', name: __('message.limited') }, { value: 'unlimited', name: __('message.unlimited') }]

function setLimit(value, unlimited) {
    editing.value.unlimited = unlimited
    editing.value.value = unlimited ? null : value
}

// ── Step 3, what extra costs ──
const isLimited = e => e.type === 'number' && !e.unlimited
// A plan including 0 agents sells every agent, so its price can't be switched off.
const agentsAll = e => e.code === 'agents' && e.mode === 'included' && isLimited(e) && !isEmpty(e.value) && Number(e.value) === 0
// "Let customers buy more" is offered for an included, limited number.
const canBuyMore = e => e.mode === 'included' && isLimited(e) && !agentsAll(e)
const pricesShown = e => e.mode === 'optional' || agentsAll(e) || (canBuyMore(e) && !!e.buyMore)
const pricedIn = e => props.currencies.filter(c => !isEmpty(e.prices[c]?.unit_price))

function priceTitle(e) {
    if (e.mode === 'optional') return e.type === 'number' ? __('message.plan_price_per_unit') : __('message.plan_price_addon')
    return e.code === 'agents' ? __('message.plan_price_per_agent') : __('message.plan_price_extra_unit')
}

function priceHelp(e) {
    if (agentsAll(e)) return __('message.plan_price_agents_all')
    if (e.mode === 'optional') return e.type === 'number' ? __('message.plan_price_unit_help') : __('message.plan_price_addon_help')
    return __('message.plan_price_extra_help')
}

// What customers end up with, in plain words, e.g. "Every customer on this plan gets 10 Storage. They can buy more at USD 2 each."
// What customers get, as short lines with an icon each: included (✓), extra to buy (+), not offered (−),
// and what still needs filling in (!), e.g. ["✓ 10 Storage", "+ Extra: USD 2 each", "− Not sold in INR"].
const LINE = {
    gets:  { icon: 'fas fa-check', cls: 'text-success' },
    buy:   { icon: 'fas fa-plus', cls: 'text-primary' },
    not:   { icon: 'fas fa-minus', cls: 'text-muted' },
    todo:  { icon: 'fas fa-triangle-exclamation', cls: 'text-warning' },
}
const summaryLines = computed(() => {
    const e = editing.value
    if (!e) return []
    const name = e.label
    const priced = pricedIn(e)
    const prices = priced.map(c => `${c} ${e.prices[c].unit_price}`).join(', ')
    const lines = []
    const add = (kind, text) => lines.push({ ...LINE[kind], text })
    const needsPrice = () => add('todo', __('message.plan_get_set_price'))

    if (e.mode === 'optional') {
        add('not', __('message.plan_get_not_included'))
        if (priced.length) add('buy', __(e.type === 'number' ? 'message.plan_get_addon_units' : 'message.plan_get_addon', { prices }))
        else needsPrice()
    } else if (agentsAll(e)) {
        priced.length ? add('buy', __('message.plan_get_agents_all', { prices })) : needsPrice()
    } else {
        if (e.type === 'boolean') add('gets', name)
        else if (e.type === 'date') add('gets', __('message.plan_get_ends', { name, when: endsText(e.value) }))
        else if (e.type === 'number' && e.unlimited) add('gets', __('message.plan_get_unlimited', { name }))
        else if (isEmpty(e.value)) add('todo', __('message.plan_get_set_amount'))
        else add('gets', e.type === 'number' ? `${e.value} ${name}` : `${name}: ${e.value}`)
        if (canBuyMore(e)) {
            if (!pricesShown(e)) add('not', __('message.plan_get_no_extra'))
            else priced.length ? add('buy', __('message.plan_get_extra', { name, prices })) : needsPrice()
        }
    }
    const missing = props.currencies.filter(c => !priced.includes(c))
    if (pricesShown(e) && priced.length && missing.length) add('not', __('message.plan_get_not_sold', { currencies: missing.join(', ') }))
    return lines
})

function applyEditor() {
    const e = editing.value
    if (e.mode === 'included' && e.type === 'number' && !e.unlimited) {
        const n = Number(e.value)
        valueError.value = isEmpty(e.value) ? __('message.entitlement_limit_required')
            : (Number.isInteger(n) && n >= 0 ? '' : __('message.entitlement_number_invalid'))
        if (valueError.value) return
    }
    if (!pricesShown(e)) {
        // Nothing to sell (included on/off, unlimited, "buy more" off): no prices kept.
        Object.values(e.prices).forEach(p => Object.assign(p, { unit_price: '', renew_price: '', offer_percent: '' }))
    } else {
        const bad = Object.values(e.prices).some(p =>
            (!isEmpty(p.unit_price) && Number(p.unit_price) < 0)
            || (!isEmpty(p.offer_percent) && (Number(p.offer_percent) < 0 || Number(p.offer_percent) > 100)))
        const priced = pricedIn(e).length
        priceError.value = bad ? __('message.entitlement_offer_invalid')
            : agentsAll(e) && priced < props.currencies.length ? __('message.plan_price_every_currency')
            // Sold with no price anywhere would be sold nowhere.
            : e.mode === 'optional' && !priced ? __('message.entitlement_optional_needs_price', { key: e.label })
            : !priced ? __('message.plan_price_needs_one') : ''
        if (priceError.value) return
    }
    const item = items.value.find(i => i.id === editing.value.id)
    Object.assign(item, {
        mode:      editing.value.mode,
        value:     editing.value.value,
        prices:    { ...item.prices, ...editing.value.prices },
    })
    editing.value = null
}

// What would be saved, to tell whether anything changed since load or the last save.
const snapshot = () => JSON.stringify(items.value.map(i => [i.id, i.mode, i.value, i.prices]))
const baseline = ref('')
const dirty    = computed(() => !loading.value && baseline.value !== '' && snapshot() !== baseline.value)

async function load() {
    try {
        const res = await http.get(`/plan/${props.planId}/entitlements`)
        liveLicenses.value = res.data?.data?.live_licenses ?? 0
        otherPlans.value = res.data?.data?.other_plans ?? []
        items.value = (res.data?.data?.entitlements ?? []).map(i => ({
            ...i,
            // Show what the plan actually includes, not a placeholder (an empty number is unlimited).
            value: i.mode === 'included' && !['date', 'number'].includes(i.type) && isEmpty(i.value) ? (i.default_value ?? '') : i.value,
        }))
        baseline.value = snapshot()
        removed.value = null
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
}

/** Saves the plan's features. Throws on failure, so the form shows the error. */
async function save() {
    const entitlements = items.value.map(i => ({
        id:        i.id,
        mode:      i.mode,
        value:     i.type === 'boolean' && i.mode === 'included' ? '1'
            : i.type === 'date' ? (i.mode === 'included' ? i.value : null)
            : (isEmpty(i.value) ? null : String(i.value)),
        prices:    Object.fromEntries(Object.entries(i.prices ?? {}).filter(([cur]) => props.currencies.includes(cur))),
    }))
    const res = await http.post(`/plan/${props.planId}/entitlements`, { entitlements })
    await load()
    return res
}

onMounted(load)

defineExpose({ save, dirty, liveLicenses, summary, rows, pageRows, categories, category, search, searchInput, page, editing, openEditor, applyEditor, addFeature, givesText, priceText })
</script>

<style scoped>
/* Digits line up in the price column */
.tabular-nums { font-variant-numeric: tabular-nums; }
/* A long editor scrolls inside the popup instead of running off the screen */
:deep(.plan-feature-body) { max-height: 70vh; overflow-y: auto; overscroll-behavior: contain; }
</style>
