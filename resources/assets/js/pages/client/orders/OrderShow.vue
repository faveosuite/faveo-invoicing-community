<template>
    <div>

        <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

        <template v-else-if="!order">
            <div class="alert alert-warning">{{ __('message.no_records_found') }}</div>
        </template>

        <template v-else>

            <!-- Summary Bar -->
            <div class="row justify-content-center mb-4">
                <div class="col-lg-12 alert bg-color-grey">
                    <div class="d-flex flex-column flex-md-row justify-content-between plan-features text-center">
                        <div>
                            <strong>{{ __('message.order_number') }}</strong><br>
                            #{{ order.number }}
                        </div>
                        <div class="mt-3 mt-md-0">
                            <strong>{{ __('message.date') }}</strong><br>
                            {{ formatDate(order.order_date) }}
                        </div>
                        <div class="mt-3 mt-md-0">
                            <strong>{{ __('message.status') }}</strong><br>
                            {{ order.status || '—' }}
                        </div>
                        <div class="mt-3 mt-md-0">
                            <strong>{{ __('message.expiry_date') }}</strong><br>
                            {{ formatDate(order.update_ends_at) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two-column layout -->
            <div class="row pt-2">

                <!-- Left mini-nav -->
                <div class="col-lg-3 mt-4 mt-lg-0">
                    <aside class="sidebar mt-2 mb-5">
                        <ul class="nav nav-list flex-column">
                            <li class="nav-item">
                                <a class="nav-link text-3" :class="{ active: activeTab === 'license' }"
                                   href="javascript:;" @click="activeTab = 'license'">
                                    {{ __('message.license_details') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-3" :class="{ active: activeTab === 'users' }"
                                   href="javascript:;" @click="activeTab = 'users'">
                                    {{ __('message.user_details') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-3" :class="{ active: activeTab === 'invoice' }"
                                   href="javascript:;" @click="activeTab = 'invoice'">
                                    {{ __('message.invoice_list') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-3" :class="{ active: activeTab === 'receipt' }"
                                   href="javascript:;" @click="activeTab = 'receipt'">
                                    {{ __('message.payment_receipts') }}
                                </a>
                            </li>
                            <li v-if="showCloudTab" class="nav-item">
                                <a class="nav-link text-3" :class="{ active: activeTab === 'cloud' }"
                                   href="javascript:;" @click="openCloudTab">
                                    {{ __('message.cloud_settings') }}
                                </a>
                            </li>
                            <li v-if="showAutoRenewTab" class="nav-item">
                                <a class="nav-link text-3" :class="{ active: activeTab === 'auto-renew' }"
                                   href="javascript:;" @click="activeTab = 'auto-renew'">
                                    {{ __('message.auto_renewal') }}
                                </a>
                            </li>
                            <li v-if="order.whatsapp_enabled" class="nav-item">
                                <a class="nav-link text-3" :class="{ active: activeTab === 'whatsapp' }"
                                   href="javascript:;" @click="activeTab = 'whatsapp'">
                                    {{ __('message.whatsapp_signup') }}
                                </a>
                            </li>
                            <li v-if="order.deploy_enabled && order.has_deployable_uploads && order.status !== 'Terminated'" class="nav-item">
                                <a class="nav-link text-3" :class="{ active: activeTab === 'deploy' }"
                                   href="javascript:;" @click="activeTab = 'deploy'">
                                    Deploy
                                </a>
                            </li>
                        </ul>
                    </aside>
                </div>

                <!-- Right content -->
                <div class="col-lg-9 mt-2">

                    <!-- ── License Details ──────────────────────────────── -->
                    <div v-show="activeTab === 'license'">

                        <div class="row align-items-center">
                            <div class="col-sm-5">
                                <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">
                                    <span class="fw-bold">{{ __('message.license_code') }}</span>
                                </div>
                            </div>
                            <div class="col-sm-7 d-flex align-items-center gap-2">
                                <span>{{ order.serial_key || '—' }}</span>
                                <button v-if="order.serial_key"
                                        class="btn btn-light btn-sm ms-2"
                                        v-tooltip="copied ? __('message.copied') : __('message.copy')"
                                        @click="copyLicense">
                                    <i :class="copied ? 'fas fa-check text-success' : 'fas fa-copy'"></i>
                                </button>
                                <button v-if="order.serial_key && !order.is_cloud"
                                        class="btn btn-light btn-sm"
                                        :disabled="reissuing"
                                        v-tooltip="__('message.reissue_license')"
                                        @click="reissueLicense">
                                    <i v-if="reissuing" class="fas fa-circle-notch fa-spin"></i>
                                    <i v-else class="fas fa-id-card-alt"></i>
                                </button>
                            </div>
                        </div>

                        <div class="row"><div class="col"><hr class="solid my-3"></div></div>

                        <div class="row align-items-center">
                            <div class="col-sm-5">
                                <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">
                                    <span class="fw-bold">{{ __('message.license_expiry_date') }}</span>
                                </div>
                            </div>
                            <div class="col-sm-7 d-flex align-items-center gap-2">
                                <span>{{ formatDate(order.license_ends_at) }}</span>
                                <button v-if="order.status !== 'Terminated'"
                                        class="btn btn-light btn-sm ms-2"
                                        v-tooltip="__('message.renew')"
                                        @click="showRenewModal = true">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>

                        <div class="row"><div class="col"><hr class="solid my-3"></div></div>

                        <div class="row align-items-center">
                            <div class="col-sm-5">
                                <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">
                                    <span class="fw-bold">{{ __('message.update_expiry_date') }}</span>
                                </div>
                            </div>
                            <div class="col-sm-7">{{ formatDate(order.update_ends_at) }}</div>
                        </div>

                        <div class="row"><div class="col"><hr class="solid my-3"></div></div>

                        <!-- Installations table -->
                        <DataTable :key="installKey" :url="installationsUrl" :dataColumns="installColumns" :option="installOptions">
                            <template #last_active="{ row }">{{ formatDate(row.last_active) }}</template>
                            <template #version="{ row }">{{ row.version || '—' }}</template>
                        </DataTable>
                    </div>

                    <!-- ── User Details ─────────────────────────────────── -->
                    <div v-show="activeTab === 'users'">
                        <template v-if="order.user">
                            <div class="row align-items-center">
                                <div class="col-sm-5">
                                    <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">
                                        <span class="fw-bold">{{ __('message.client_name') }}</span>
                                    </div>
                                </div>
                                <div class="col-sm-7">{{ order.user.name || '—' }}</div>
                            </div>
                            <div class="row"><div class="col"><hr class="solid my-3"></div></div>

                            <div class="row align-items-center">
                                <div class="col-sm-5">
                                    <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">
                                        <span class="fw-bold">{{ __('message.email') }}</span>
                                    </div>
                                </div>
                                <div class="col-sm-7">{{ order.user.email || '—' }}</div>
                            </div>
                            <div class="row"><div class="col"><hr class="solid my-3"></div></div>

                            <div class="row align-items-center">
                                <div class="col-sm-5">
                                    <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">
                                        <span class="fw-bold">{{ __('message.mobile') }}</span>
                                    </div>
                                </div>
                                <div class="col-sm-7">{{ order.user.mobile ? (order.user.mobile_code ? `+${order.user.mobile_code} ${order.user.mobile}` : order.user.mobile) : '—' }}</div>
                            </div>
                            <div class="row"><div class="col"><hr class="solid my-3"></div></div>

                            <div class="row align-items-center">
                                <div class="col-sm-5">
                                    <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">
                                        <span class="fw-bold">{{ __('message.address') }}</span>
                                    </div>
                                </div>
                                <div class="col-sm-7">{{ order.user.address || '—' }}</div>
                            </div>
                        </template>
                    </div>

                    <!-- ── Invoice List ─────────────────────────────────── -->
                    <div v-if="activeTab === 'invoice'">
                        <DataTable :url="invoicesUrl" :dataColumns="invoiceColumns" :option="invoiceOptions">
                            <template #number="{ row }">
                                <RouterLink :to="'/my-invoice/' + row.id" class="fw-semibold">{{ row.number || '—' }}</RouterLink>
                            </template>
                            <template #date="{ row }">{{ formatDate(row.date) }}</template>
                            <template #status="{ row }">
                                <span class="badge" :class="invoiceBadge(row.status)">{{ row.status || '—' }}</span>
                            </template>
                            <template #action="{ row }">
                                <RouterLink :to="'/my-invoice/' + row.id" class="btn btn-sm btn-light"
                                            v-tooltip="__('message.view')">
                                    <i class="fas fa-eye"></i>
                                </RouterLink>
                            </template>
                        </DataTable>
                    </div>

                    <!-- ── Payment Receipts ─────────────────────────────── -->
                    <div v-if="activeTab === 'receipt'">
                        <DataTable :url="paymentsUrl" :dataColumns="paymentColumns" :option="paymentOptions">
                            <template #payment_status="{ row }">
                                <span :class="paymentBadge(row.payment_status)">{{ row.payment_status || '—' }}</span>
                            </template>
                            <template #created_at="{ row }">{{ formatDate(row.created_at) }}</template>
                        </DataTable>
                    </div>

                    <!-- ── Cloud Settings ───────────────────────────────── -->
                    <div v-if="showCloudTab" v-show="activeTab === 'cloud'">

                        <div v-if="cloudLoading" class="row justify-content-center py-3"><loader /></div>

                        <template v-else-if="cloud">
                            <div class="row">
                                <!-- Change cloud domain -->
                                <div class="col-lg-6 mb-4">
                                    <div class="card border-radius-1 bg-color-light box-shadow-6 box-shadow-hover cur-pointer h-100"
                                         @click="openDomainModal">
                                        <div class="card-body p-relative zindex-1 p-3">
                                            <div class="feature-box feature-box-style-6 text-center d-block">
                                                <div class="feature-box-icon justify-content-center">
                                                    <i class="fas fa-globe text-primary"></i>
                                                </div>
                                                <div class="feature-box-info">
                                                    <h4 class="text-4 mt-3 mb-2 text-color-grey">{{ __('message.change_cloud_domain') }}</h4>
                                                    <p class="mb-2"><strong class="text-black text-2">{{ __('message.current_domain_name') }}</strong> {{ cloud.installation_path || '—' }}</p>
                                                    <p class="mb-0 text-2">{{ __('message.click_customising_domain') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Increase / decrease agents -->
                                <div class="col-lg-6 mb-4">
                                    <div class="card border-radius-1 bg-color-light box-shadow-6 box-shadow-hover cur-pointer h-100"
                                         @click="openAgentsModal">
                                        <div class="card-body p-relative zindex-1 p-3">
                                            <div class="feature-box feature-box-style-6 text-center d-block">
                                                <div class="feature-box-icon justify-content-center">
                                                    <i class="fas fa-users text-primary"></i>
                                                </div>
                                                <div class="feature-box-info">
                                                    <h4 class="text-4 mt-3 mb-2 text-color-grey">{{ __('message.increase_decrease_agents') }}</h4>
                                                    <p class="mb-2"><strong class="text-black text-2">{{ __('message.current_no_agents') }} </strong>{{ cloud.current_agents }}</p>
                                                    <p class="mb-0 text-2">{{ __('message.update_agent_count') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Upgrade / downgrade plan -->
                                <div v-if="!cloud.is_free_plan" class="col-lg-6 mb-4">
                                    <div class="card border-radius-1 bg-color-light box-shadow-6 box-shadow-hover cur-pointer h-100"
                                         @click="openPlanModal">
                                        <div class="card-body p-relative zindex-1 p-3">
                                            <div class="feature-box feature-box-style-6 text-center d-block">
                                                <div class="feature-box-icon justify-content-center">
                                                    <i class="fas fa-cloud-upload-alt text-primary"></i>
                                                </div>
                                                <div class="feature-box-info">
                                                    <h4 class="text-4 mt-3 mb-2 text-color-grey">{{ __('message.upgrade_downgrade_cloud') }}</h4>
                                                    <p class="mb-2"><strong class="text-black text-2">{{ __('message.current_plan') }}</strong> {{ cloud.current_plan_name }}</p>
                                                    <p class="mb-0 text-2">{{ __('message.change_cloud_plan') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="col-12">
                                    <h6 class="mb-1"><i>{{ __('message.current_plan') }} {{ cloud.current_plan_name }}</i></h6>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- ── Auto Renewal ─────────────────────────────────── -->
                    <div v-if="showAutoRenewTab && activeTab === 'auto-renew'">
                        <AppCard :title="__('message.auto_renewal')">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-sync-alt icon-lg"></i>
                                    <div>
                                        <span class="text-2">
                                            {{ __('message.auto_renewal') }}
                                            <span v-if="order.is_subscribed" class="badge bg-success ms-1">{{ __('message.active') }}</span>
                                            <span v-else class="badge bg-secondary ms-1">{{ __('message.inactive') }}</span>
                                        </span>
                                        <template v-if="order.is_subscribed && order.autorenew_log">
                                            <br>
                                            <small class="text-muted text-capitalize">
                                                {{ __('message.payment_gateway') }}: {{ order.autorenew_log.payment_method }}
                                                &nbsp;&middot;&nbsp;
                                                {{ __('message.subscription_enabled_date') }} {{ formatDate(order.autorenew_log.date) }}
                                            </small>
                                        </template>
                                    </div>
                                </div>
                                <div>
                                    <button v-if="!order.is_subscribed"
                                            type="button"
                                            class="btn btn-primary btn-sm btn-modern"
                                            @click="showRenewalModal = true">
                                        <i class="fas fa-toggle-on me-1"></i>{{ __('message.enable') }}
                                    </button>
                                    <button v-else
                                            type="button"
                                            class="btn btn-outline-secondary btn-sm btn-modern"
                                            :disabled="renewalBusy"
                                            @click="showDisableRenewalModal = true">
                                        <i v-if="renewalBusy" class="fas fa-circle-notch fa-spin me-1"></i>
                                        <i v-else class="fas fa-toggle-off me-1"></i>
                                        {{ __('message.disable') }}
                                    </button>
                                </div>
                            </div>
                        </AppCard>
                    </div>

                    <!-- ── WhatsApp SignUp ──────────────────────────────── -->
                    <div v-if="order.whatsapp_enabled" v-show="activeTab === 'whatsapp'">
                        <WhatsappPanel :order="order" :active="activeTab === 'whatsapp'" />
                    </div>

                    <!-- ── Deploy ──────────────────────────────────────────── -->
                    <div v-show="activeTab === 'deploy'">
                        <DeployWizard
                            v-if="order.deploy_enabled && order.has_deployable_uploads"
                            :orderId="order.id"
                            :serialKey="order.serial_key ?? ''"
                            :orderNumber="order.number ?? ''"
                            :manualGuideUrl="order.manual_install_guide_url"
                        />
                    </div>

                </div>
            </div>

            <!-- ── Change Cloud Domain modal ────────────────────────── -->
            <Modal :showModal="showDomainModal" :onClose="closeDomainModal" :showCloseBtn="false">
                <template #title>
                    <h5 class="modal-title fw-bold">{{ __('message.change_cloud_domain') }}</h5>
                </template>
                <template #fields>
                    <AppAlert componentName="domain-modal" />
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                        <span class="text-muted">{{ __('message.current_cloud_domain') }}</span>
                        <span class="fw-bold text-dark">{{ cloud?.installation_path || '—' }}</span>
                    </div>
                    <ClientField type="text" name="newDomain" required
                                 :label="__('message.enter_domain_new_name')"
                                 v-model="domainForm.newDomain"
                                 placeholder="https://billing.custom.com" autocomplete="off" />
                </template>
                <template #controls>
                    <action-button action="confirm"
                                   :label="__('message.chg_domain')"
                                   :loading="domainBusy"
                                   :disabled="!domainForm.newDomain"
                                   @click="submitDomain" />
                </template>
            </Modal>

            <!-- ── Change Number of Agents modal ────────────────────── -->
            <Modal :showModal="showAgentsModal" :onClose="closeAgentsModal" :showCloseBtn="false">
                <template #title>
                    <h5 class="modal-title fw-bold">{{ __('message.change_no_of_agents') }}</h5>
                </template>
                <template #fields>
                    <AppAlert componentName="agents-modal" />
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">{{ __('message.current_no_agents') }}</span>
                        <span class="fw-bold text-dark">{{ cloud?.current_agents || '—' }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                        <span class="text-muted">{{ __('message.price_per_agent') }}</span>
                        <span class="fw-bold text-dark">{{ cloud?.price_per_agent || '—' }}</span>
                    </div>

                    <SelectField name="action" required
                                 :label="__('message.action')"
                                 :elements="actionOptions"
                                 :value="actionOptions.find(o => o.id === agentForm.action) ?? null"
                                 :onChange="onActionChange"
                                 :clearable="false" />

                    <ClientField type="number" name="number" required
                                 :label="__('message.choose_no_desired_agents')"
                                 v-model="agentForm.number" @update:modelValue="fetchAgentCost" />

                    <div v-if="agentCost" class="d-flex justify-content-between align-items-center border-top pt-3 mt-1">
                        <span class="text-muted">{{ __('message.price_to_be_paid') }}</span>
                        <span class="fw-bold text-dark fs-6">{{ agentCost }}</span>
                    </div>
                </template>
                <template #controls>
                    <action-button action="confirm"
                                   :label="__('message.update_agents')"
                                   :loading="agentBusy"
                                   :disabled="!agentForm.number"
                                   @click="submitAgents" />
                </template>
            </Modal>

            <!-- ── Upgrade / Downgrade Plan modal ───────────────────── -->
            <Modal :showModal="showPlanModal" :onClose="closePlanModal" :showCloseBtn="false">
                <template #title>
                    <h5 class="modal-title fw-bold">{{ __('message.upgrade_downgrade_cloud_plan') }}</h5>
                </template>
                <template #fields>
                    <AppAlert componentName="plan-modal" />
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                        <span class="text-muted">{{ __('message.current_plan') }}</span>
                        <span class="fw-bold text-dark">{{ cloud?.current_plan_name || '—' }}</span>
                    </div>

                    <SelectField name="planId" required
                                 :label="__('message.select_new_plan')"
                                 :elements="cloud?.plans || []"
                                 :value="(cloud?.plans || []).find(p => p.id === planForm.planId) ?? null"
                                 :onChange="onPlanChange"
                                 :placeholder="__('message.select')" />

                    <template v-if="planCost">
                        <div class="d-flex justify-content-between align-items-center mt-2 mb-1">
                            <span class="text-muted">{{ __('message.total_credits_remaining') }}</span>
                            <span class="fw-bold text-dark">{{ planCost.currency_symbol }}{{ planCost.priceoldplan }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted">{{ __('message.price_for_new_plan') }}</span>
                            <span class="fw-bold text-dark">{{ planCost.currency_symbol }}{{ planCost.pricenewplan }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-2">
                            <span class="text-muted">{{ __('message.price_to_be_paid') }}</span>
                            <span class="fw-bold text-dark fs-6">{{ planCost.currency_symbol }}{{ planCost.price_to_be_paid }}</span>
                        </div>
                    </template>
                </template>
                <template #controls>
                    <action-button action="confirm"
                                   :label="__('message.change_plan')"
                                   :loading="planBusy"
                                   :disabled="!planForm.planId"
                                   @click="submitPlan" />
                </template>
            </Modal>

        <!-- ── Enable Auto Renewal: gateway selection modal ──── -->
        <Modal :showModal="showRenewalModal" :onClose="closeRenewalModal" :showCloseBtn="false">
            <template #title>
                <h5 class="modal-title">{{ __('message.auto_renewal') }}</h5>
            </template>
            <template #fields>
                <AppAlert componentName="auto-renew-modal" />
                <SelectField
                    name="gateway"
                    :label="__('message.select_payment')"
                    :elements="gatewayOptions"
                    :value="gatewayOptions.find(g => g.id === selectedGateway) ?? null"
                    :onChange="(v) => selectedGateway = v?.id ?? ''"
                    :clearable="false"
                    :required="true"
                />
            </template>
            <template #controls>
                <button type="button" class="btn btn-primary" :disabled="renewalBusy || !selectedGateway" @click="enableAutoRenewal">
                    <i v-if="renewalBusy" class="fas fa-circle-notch fa-spin me-1"></i>
                    <i v-else class="fas fa-toggle-on me-1"></i>
                    {{ __('message.enable') }}
                </button>
            </template>
        </Modal>

        <!-- ── Stripe card modal for renewal ──────────────────── -->
        <Modal :showModal="showStripeRenewalModal" :onClose="closeStripeRenewalModal" :showCloseBtn="true" :showControls="false" classname="modal-md">
            <template #title>
                <h5 class="modal-title fw-bold">{{ __('message.enter_card_details') }}</h5>
            </template>
            <template #fields>
                <div class="px-2 pb-3">
                    <AppAlert componentName="stripe-renewal-modal" />

                    <div class="mb-4">
                        <label class="form-label text-color-grey mb-1">{{ __('message.card_number') }}</label>
                        <div id="renewal-card-number" class="form-control h-auto py-3" :class="{ 'is-invalid': renewalCardErrors.number }"></div>
                        <div v-if="renewalCardErrors.number" class="invalid-feedback d-block">{{ renewalCardErrors.number }}</div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <label class="form-label text-color-grey mb-1">{{ __('message.expiry_date') }}</label>
                            <div id="renewal-card-expiry" class="form-control h-auto py-3" :class="{ 'is-invalid': renewalCardErrors.expiry }"></div>
                            <div v-if="renewalCardErrors.expiry" class="invalid-feedback d-block">{{ renewalCardErrors.expiry }}</div>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-color-grey mb-1">CVC</label>
                            <div id="renewal-card-cvc" class="form-control h-auto py-3" :class="{ 'is-invalid': renewalCardErrors.cvc }"></div>
                            <div v-if="renewalCardErrors.cvc" class="invalid-feedback d-block">{{ renewalCardErrors.cvc }}</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center border rounded px-2 py-2 mb-3">
                        <span class="fw-bold text-color-dark">{{ __('message.total') }}</span>
                        <span class="fw-bold text-color-dark">{{ renewalSymbol }}{{ renewalAmount }}</span>
                    </div>

                    <button class="btn btn-primary w-100 py-2 fw-bold text-uppercase"
                            :disabled="stripeRenewalBusy" @click="payRenewalStripe">
                        <span v-if="stripeRenewalBusy" class="spinner-border spinner-border-sm me-1"></span>
                        {{ stripeRenewalBusy ? __('message.please_wait') : __('message.pay_now') }}
                    </button>
                </div>
            </template>
        </Modal>

        <!-- ── Disable Auto-Renewal confirmation ─────────────────── -->
        <Modal :showModal="showDisableRenewalModal" :onClose="closeDisableRenewalModal" :showCloseBtn="false">
            <template #title>
                <h5 class="modal-title">{{ __('message.disable_auto_renewal') }}</h5>
            </template>
            <template #fields>
                <p class="mb-0">{{ __('message.disable_auto_renewal_confirm') }}</p>
            </template>
            <template #controls>
                <button type="button" class="btn btn-light me-2" @click="closeDisableRenewalModal">
                    {{ __('message.cancel') }}
                </button>
                <button type="button" class="btn btn-danger" :disabled="renewalBusy" @click="confirmDisableRenewal">
                    <i v-if="renewalBusy" class="fas fa-circle-notch fa-spin me-1"></i>
                    <i v-else class="fas fa-toggle-off me-1"></i>
                    {{ __('message.disable') }}
                </button>
            </template>
        </Modal>

        <!-- ── Renew Order modal (shared with the orders list page) ── -->
        <RenewModal v-model:show="showRenewModal" :order="order" />

        </template>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { errorHandler, successHandler } from '@/helpers/responseHandler.js'
import { useAlertStore } from '@/core/stores/alert'
import Modal from '@/themes/porto/components/common/Modal.vue'
import AppAlert from '@/components/Reusable/Alert.vue'
import RenewModal from './components/RenewModal.vue'
import WhatsappPanel from './components/WhatsappPanel.vue'
import DeployWizard from './components/DeployWizard.vue'
import { useDateTime } from '@/core/composables/useDateTime'

const { formatDate } = useDateTime()
const el      = document.getElementById('app-client')
const userId  = el?.dataset?.userId  ?? ''

const route   = useRoute()
const router  = useRouter()
const orderId = route.params.id

const loading   = ref(true)
const copied    = ref(false)
const activeTab = ref('license')
const order     = ref(null)

const installationsUrl = `/get-my-installations/${orderId}`
const invoicesUrl      = `/get-my-invoices/${orderId}/${userId}`
const paymentsUrl      = `/get-my-payment-client/${orderId}/${userId}`


const installColumns = ['installation_path', 'installation_ip', 'version', 'last_active']
const installOptions = reactive({
    headings: {
        installation_path: () => __('message.installation_path'),
        installation_ip:   () => __('message.installation_ip'),
        version:           () => __('message.version'),
        last_active:       () => __('message.last_active'),
    },
    sortable:   ['installation_path', 'installation_ip', 'last_active'],
    filterable: true,
})

const invoiceColumns = ['number', 'date', 'grand_total', 'status', 'action']
const invoiceOptions = reactive({
    headings: {
        number:      () => __('message.invoice_no'),
        date:        () => __('message.date'),
        grand_total: () => __('message.grand_total'),
        status:      () => __('message.status'),
        action:      () => __('message.actions'),
    },
    sortable:   ['number', 'date'],
    filterable: true,
})

const paymentColumns = ['invoice_number', 'amount', 'payment_method', 'payment_status', 'created_at']
const paymentOptions = reactive({
    headings: {
        invoice_number: () => __('message.invoice_no'),
        amount:         () => __('message.total'),
        payment_method: () => __('message.method'),
        payment_status: () => __('message.status'),
        created_at:     () => __('message.payment_date'),
    },
    sortable:   ['payment_status', 'created_at'],
    filterable: true,
})

/* ── Cloud settings state ─────────────────────────────────── */
const cloud        = ref(null)
const cloudLoading = ref(false)
const cloudLoaded  = ref(false)

const showDomainModal = ref(false)
const showAgentsModal = ref(false)
const showPlanModal   = ref(false)

const domainForm = reactive({ newDomain: '' })
const agentForm  = reactive({ action: 'increase', number: '' })
const planForm   = reactive({ planId: '' })

// Increase/Decrease options for the SelectField (vue-select expects objects).
const actionOptions = computed(() => [
    { id: 'increase', name: __('message.increase') },
    { id: 'decrease', name: __('message.decrease') },
])

function onActionChange(v) {
    agentForm.action = v?.id ?? 'increase'
    fetchAgentCost()
}

function onPlanChange(v) {
    planForm.planId = v?.id ?? ''
    fetchPlanCost()
}

const agentCost = ref('')
const planCost  = ref(null)

const domainBusy = ref(false)
const agentBusy  = ref(false)
const planBusy   = ref(false)

const alertStore = useAlertStore()

const showCloudTab      = computed(() => !!order.value?.is_cloud && order.value?.status !== 'Terminated')
const showAutoRenewTab  = computed(() => !!order.value?.autorenewal_enabled)

/* ── Auto Renewal ─────────────────────────────────────────── */
const showRenewalModal = ref(false)
const renewalBusy      = ref(false)
const selectedGateway  = ref('')

const gatewayOptions = computed(() => {
    const active = (order.value?.available_gateways ?? []).map(g => g.toLowerCase())
    const all = [
        { id: 'stripe',   name: __('message.stripe') },
        { id: 'razorpay', name: __('message.razorpay') },
    ]
    return active.length ? all.filter(g => active.includes(g.id)) : all
})

// Stripe card renewal state
const showStripeRenewalModal = ref(false)
const stripeRenewalBusy      = ref(false)
const renewalCardErrors      = reactive({ number: '', expiry: '', cvc: '' })
const renewalCardComplete    = reactive({ number: false, expiry: false, cvc: false })
let stripeRenewal    = null
let renewalCardNumber = null

function loadScript(src) {
    return new Promise((resolve, reject) => {
        if (document.querySelector(`script[src="${src}"]`)) return resolve()
        const s = document.createElement('script')
        s.src = src
        s.onload = resolve
        s.onerror = () => reject(new Error('Failed to load ' + src))
        document.head.appendChild(s)
    })
}

function closeRenewalModal() {
    alertStore.unsetAlert()
    showRenewalModal.value = false
    selectedGateway.value  = ''
}

function closeStripeRenewalModal() {
    alertStore.unsetAlert()
    showStripeRenewalModal.value = false
    stripeRenewalBusy.value = false
}

const showDisableRenewalModal = ref(false)

function closeDisableRenewalModal() {
    showDisableRenewalModal.value = false
}

async function confirmDisableRenewal() {
    renewalBusy.value = true
    closeDisableRenewalModal()
    try {
        const res = await http.post(`/auto-renewal/${orderId}/disable`)
        order.value.is_subscribed    = false
        order.value.autorenew_status = false
        successHandler(res, 'client-page')
    } catch (e) {
        errorHandler(e, 'client-page')
    } finally {
        renewalBusy.value = false
    }
}

async function enableAutoRenewal() {
    if (!selectedGateway.value) return
    renewalBusy.value = true
    try {
        if (selectedGateway.value === 'razorpay') {
            const { data } = await http.post(`/auto-renewal/${orderId}/razorpay/order`)
            closeRenewalModal()
            await openRenewalRazorpayPopup(data.data)
        } else {
            closeRenewalModal()
            await openRenewalStripeModal()
        }
    } catch (e) {
        alertStore.setAlert({
            message: e?.response?.data?.message ?? __('message.something_went_wrong'),
            type: 'danger',
            component_name: 'auto-renew-modal',
        })
        showRenewalModal.value = true
    } finally {
        renewalBusy.value = false
    }
}

async function openRenewalRazorpayPopup(config) {
    await loadScript('https://checkout.razorpay.com/v1/checkout.js')
    const options = { ...config }
    options.handler = async (response) => {
        try {
            renewalBusy.value = true
            const res = await http.post(`/auto-renewal/${orderId}/razorpay/confirm`, {
                razorpay_order_id:   response.razorpay_order_id,
                razorpay_payment_id: response.razorpay_payment_id,
                razorpay_signature:  response.razorpay_signature,
            })
            order.value.is_subscribed    = true
            order.value.autorenew_status = true
            successHandler(res, 'client-page')
        } catch (e) {
            errorHandler(e, 'client-page')
        } finally {
            renewalBusy.value = false
        }
    }
    options.modal = { ondismiss: () => { renewalBusy.value = false } }
    new globalThis.Razorpay(options).open()
}

let renewalClientSecret    = null
let renewalPaymentIntentId = null
const renewalAmount        = ref('')
const renewalSymbol        = ref('')

async function openRenewalStripeModal() {
    const { data } = await http.post(`/auto-renewal/${orderId}/stripe/session`)
    renewalClientSecret    = data.data.client_secret
    renewalPaymentIntentId = data.data.payment_intent_id
    renewalAmount.value    = data.data.display_amount ?? ''
    renewalSymbol.value    = data.data.currency_symbol ?? ''

    // Intent already succeeded on a prior idempotent attempt — skip card entry.
    if (data.data.status === 'succeeded') {
        await finalizeRenewalStripe()
        return
    }

    await loadScript('https://js.stripe.com/v3/')
    stripeRenewal = globalThis.Stripe(data.data.publishable_key)
    const elements = stripeRenewal.elements()

    Object.assign(renewalCardErrors,   { number: '', expiry: '', cvc: '' })
    Object.assign(renewalCardComplete, { number: false, expiry: false, cvc: false })
    alertStore.unsetAlert()
    showStripeRenewalModal.value = true

    await new Promise(r => setTimeout(r, 100))

    const style = {
        base: { fontSize: '15px', color: '#32325d', fontFamily: 'inherit', '::placeholder': { color: '#aab7c4' } },
        invalid: { color: '#dc3545' },
    }
    renewalCardNumber = elements.create('cardNumber', { showIcon: true, style })
    const cardExpiry  = elements.create('cardExpiry', { style })
    const cardCvc     = elements.create('cardCvc', { style })

    renewalCardNumber.mount('#renewal-card-number')
    cardExpiry.mount('#renewal-card-expiry')
    cardCvc.mount('#renewal-card-cvc')

    const bind = (el, key) => el.on('change', e => {
        renewalCardErrors[key]   = e.error ? e.error.message : ''
        renewalCardComplete[key] = e.complete
    })
    bind(renewalCardNumber, 'number')
    bind(cardExpiry, 'expiry')
    bind(cardCvc, 'cvc')
}

async function payRenewalStripe() {
    if (!renewalCardComplete.number) renewalCardErrors.number = renewalCardErrors.number || __('message.card_number_required')
    if (!renewalCardComplete.expiry) renewalCardErrors.expiry = renewalCardErrors.expiry || __('message.expiry_required')
    if (!renewalCardComplete.cvc)    renewalCardErrors.cvc    = renewalCardErrors.cvc    || __('message.cvc_required')
    if (!renewalCardComplete.number || !renewalCardComplete.expiry || !renewalCardComplete.cvc) return

    stripeRenewalBusy.value = true
    alertStore.unsetAlert()
    try {
        const { paymentIntent, error: confirmError } = await stripeRenewal.confirmCardPayment(
            renewalClientSecret,
            { payment_method: { card: renewalCardNumber } },
        )

        if (confirmError) {
            if (
                confirmError.code === 'payment_intent_unexpected_state' ||
                confirmError.payment_intent?.status === 'succeeded'
            ) {
                await finalizeRenewalStripe()
                return
            }
            closeStripeRenewalModal()
            alertStore.setAlert({ message: confirmError.message, type: 'danger', component_name: 'client-page' })
            stripeRenewalBusy.value = false
            return
        }

        if (paymentIntent?.status === 'succeeded') {
            await finalizeRenewalStripe()
        } else {
            closeStripeRenewalModal()
            alertStore.setAlert({ message: __('message.err_msg'), type: 'danger', component_name: 'client-page' })
            stripeRenewalBusy.value = false
        }
    } catch (e) {
        closeStripeRenewalModal()
        alertStore.setAlert({
            message: e?.response?.data?.message ?? __('message.something_went_wrong'),
            type: 'danger',
            component_name: 'client-page',
        })
        stripeRenewalBusy.value = false
    }
}

async function finalizeRenewalStripe() {
    try {
        const res = await http.post(`/auto-renewal/${orderId}/stripe/confirm`, {
            payment_intent: renewalPaymentIntentId,
        })
        order.value.is_subscribed    = true
        order.value.autorenew_status = true
        closeStripeRenewalModal()
        successHandler(res, 'client-page')
    } catch (e) {
        closeStripeRenewalModal()
        alertStore.setAlert({
            message: e?.response?.data?.message ?? __('message.something_went_wrong'),
            type: 'danger',
            component_name: 'client-page',
        })
    } finally {
        stripeRenewalBusy.value = false
    }
}


function invoiceBadge(status) {
    const s = (status ?? '').toLowerCase()
    if (s === 'paid' || s === 'success')   return 'bg-success'
    if (s === 'pending' || s === 'unpaid') return 'bg-warning text-dark'
    if (s === 'partially paid')            return 'bg-info text-dark'
    if (s === 'cancelled')                 return 'bg-danger'
    if (s === 'overdue')                   return 'bg-danger'
    return 'bg-secondary'
}

function paymentBadge(status) {
    const s = (status ?? '').toLowerCase()
    if (s === 'success') return 'badge bg-success'
    if (s === 'pending') return 'badge bg-warning text-dark'
    if (s === 'failed')  return 'badge bg-danger'
    return 'badge bg-secondary'
}

async function copyLicense() {
    const code = order.value?.serial_key
    if (!code) return
    try {
        await navigator.clipboard.writeText(code)
        copied.value = true
        setTimeout(() => { copied.value = false }, 2000)
    } catch { /* ignore */ }
}

/* ── Reissue license ──────────────────────────────────────── */
const reissuing  = ref(false)
const installKey = ref(0)

async function reissueLicense() {
    if (reissuing.value) return
    reissuing.value = true
    try {
        const res = await http.patch(`/reissue-license`, { id: orderId })
        alertStore.setAlert({
            message: res.data?.message ?? __('message.license_reissued'),
            type: 'success',
            component_name: 'client-page',
        })
        installKey.value++   // refresh installations table
    } catch (e) {
        errorHandler(e, 'client-page')
    } finally {
        reissuing.value = false
    }
}

/* ── Renew order (shared modal) ───────────────────────────── */
const showRenewModal = ref(false)

/* ── Cloud settings: lazy load on first tab open ──────────── */
async function openCloudTab() {
    activeTab.value = 'cloud'
    if (cloudLoaded.value) return
    cloudLoading.value = true
    try {
        const res = await http.get(`/get-cloud-settings/${orderId}`)
        cloud.value = res.data?.data ?? null
        cloudLoaded.value = true
    } catch (e) {
        errorHandler(e, 'client-page')
    } finally {
        cloudLoading.value = false
    }
}

/* ── Change domain ────────────────────────────────────────── */
function openDomainModal() {
    domainForm.newDomain = ''
    showDomainModal.value = true
}
function closeDomainModal() { showDomainModal.value = false; alertStore.unsetAlert() }

async function submitDomain() {
    if (!cloud.value) return
    domainBusy.value = true
    try {
        const res = await http.post(`/change/domain`, {
            newDomain:     domainForm.newDomain,
            currentDomain: cloud.value.installation_path,
            lic_code:      cloud.value.serial_key,
            product_id:    cloud.value.product_id,
            order_id:      cloud.value.order_id,
        })
        successHandler(res, 'client-page')
        closeDomainModal()
    } catch (e) {
        errorHandler(e, 'domain-modal')
    } finally {
        domainBusy.value = false
    }
}

/* ── Change agents ────────────────────────────────────────── */
function openAgentsModal() {
    agentForm.action = 'increase'
    agentForm.number = ''
    agentCost.value  = ''
    showAgentsModal.value = true
}
function closeAgentsModal() { showAgentsModal.value = false; alertStore.unsetAlert() }

async function fetchAgentCost() {
    if (!cloud.value || !agentForm.number) { agentCost.value = ''; return }
    try {
        const res = await http.post(`/get-agent-inc-dec-cost`, {
            number:      agentForm.number,
            oldAgents:   cloud.value.current_agents,
            orderId:     cloud.value.order_id,
            agentAction: agentForm.action,
        })
        // raw (un-wrapped) array response: { pricePerAgent, totalPrice, priceToPay }
        agentCost.value = (res.data?.currency_symbol ?? '') + (res.data?.priceToPay ?? '')
    } catch (e) {
        agentCost.value = ''
        errorHandler(e, 'agents-modal')
    }
}

async function submitAgents() {
    if (!cloud.value || !agentForm.number) return
    agentBusy.value = true
    try {
        const res = await http.post(`/changeAgents`, {
            newAgents:   agentForm.number,
            orderId:     cloud.value.order_id,
            product_id:  cloud.value.product_id,
            subId:       cloud.value.sub_id,
            agentAction: agentForm.action,
        })
        const invoiceId = res.data?.data?.invoice_id
        if (invoiceId) router.push({ path: '/checkout', query: { invoice: invoiceId } })
    } catch (e) {
        errorHandler(e, 'agents-modal')
    } finally {
        agentBusy.value = false
    }
}

/* ── Upgrade / downgrade plan ─────────────────────────────── */
function openPlanModal() {
    planForm.planId = ''
    planCost.value  = null
    showPlanModal.value = true
}
function closePlanModal() { showPlanModal.value = false; alertStore.unsetAlert() }

async function fetchPlanCost() {
    if (!cloud.value || !planForm.planId) { planCost.value = null; return }
    try {
        const res = await http.post(`/get-cloud-upgrade-cost`, {
            plan:    planForm.planId,
            agents:  cloud.value.current_agents,
            orderId: cloud.value.order_id,
        })
        // raw (un-wrapped) array response
        planCost.value = res.data ?? null
    } catch (e) {
        planCost.value = null
        errorHandler(e, 'plan-modal')
    }
}

async function submitPlan() {
    if (!cloud.value || !planForm.planId) return
    planBusy.value = true
    try {
        const res = await http.post(`/upgradeDowngradeCloud`, {
            id:      planForm.planId,
            agents:  cloud.value.current_agents,
            userId:  userId,
            orderId: cloud.value.order_id,
        })
        const invoiceId = res.data?.data?.invoice_id
        if (invoiceId) router.push({ path: '/checkout', query: { invoice: invoiceId } })
    } catch (e) {
        errorHandler(e, 'plan-modal')
    } finally {
        planBusy.value = false
    }
}

onMounted(async () => {
    try {
        const res = await http.get(`/get-my-orders`, { params: { id: orderId } })
        order.value = res.data?.data ?? null
    } catch (e) {
        errorHandler(e, 'client-page')
    } finally {
        loading.value = false
    }
})
</script>

<style scoped>
.icon-lg { font-size: 20px; }
</style>
