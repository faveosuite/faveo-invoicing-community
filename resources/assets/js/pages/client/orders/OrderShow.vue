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

            <!-- Terminated because of a plan/agent change: explain why this
                 order stopped working and point at what replaced it. Someone
                 can land here directly (bookmark, old link) without ever
                 seeing the "Terminated" badge on the orders list. -->
            <div v-if="order.status === 'Terminated' && order.replacement_order" class="row justify-content-center mb-4">
                <div class="col-lg-12 alert alert-warning mb-0">
                    <strong>{{ __('message.order_terminated_notice_title') }}</strong>
                    <p class="mb-0 mt-1">{{ __('message.order_terminated_notice_body') }}</p>
                    <p class="mb-0 mt-1">
                        {{ __('message.order_terminated_replacement') }}
                        <RouterLink :to="'/my-order/' + order.replacement_order.id">#{{ order.replacement_order.number }}</RouterLink>
                    </p>
                </div>
            </div>

            <!-- The reverse case: this order exists because an older one was
                 terminated (a plan/agent change created it automatically) —
                 say so plainly instead of leaving an order the client doesn't
                 remember placing themselves unexplained. -->
            <div v-else-if="order.predecessor_order" class="row justify-content-center mb-4">
                <div class="col-lg-12 alert alert-info mb-0">
                    {{ __('message.order_created_from_predecessor') }}
                    <RouterLink :to="'/my-order/' + order.predecessor_order.id">#{{ order.predecessor_order.number }}</RouterLink>
                </div>
            </div>

            <!-- Two-column layout -->
            <div class="row pt-2">

                <!-- Left mini-nav -->
                <div class="col-lg-3 mt-4 mt-lg-0">
                    <aside class="sidebar mt-2 mb-5">
                        <ul class="nav nav-list flex-column">
                            <!-- Nothing to show for a terminated order — the notice
                                 above already covers it, and this tab held nothing
                                 but the (no longer reachable) license itself. -->
                            <li v-if="order.status !== 'Terminated'" class="nav-item">
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
                    <!-- Nav item above hides this tab for a terminated order, and
                         onMounted() redirects activeTab away from it in that case,
                         so nothing here needs its own Terminated check any more. -->
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
                                        class="btn btn-light btn-sm ms-2 table_btn"
                                        v-tooltip="copied ? __('message.copied') : __('message.copy')"
                                        @click="copyLicense">
                                    <i :class="copied ? 'fas fa-check text-success' : 'fas fa-copy'"></i>
                                </button>
                                <button v-if="order.serial_key && !order.is_cloud"
                                        class="btn btn-light btn-sm table_btn"
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
                                <button v-if="order.license_ends_at"
                                        class="btn btn-light btn-sm ms-2 table_btn"
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
                            <div class="col-sm-7 d-flex align-items-center gap-2">
                                <span>{{ formatDate(order.update_ends_at) }}</span>
                                <!-- One-time (lifetime license) products have no license expiry to renew,
                                     so the renew action lives here instead, since updates/support are what run out.
                                     But if updates never expire either (product has no expiring permissions at all),
                                     there's nothing to renew. -->
                                <button v-if="!order.license_ends_at && order.update_ends_at"
                                        class="btn btn-light btn-sm ms-2 table_btn"
                                        v-tooltip="__('message.renew')"
                                        @click="showRenewModal = true">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>

                        <template v-if="order.license_mode === 'File'">
                            <div class="row"><div class="col"><hr class="solid my-3"></div></div>

                            <div class="row align-items-center">
                                <div class="col-sm-5">
                                    <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">
                                        <span class="fw-bold">{{ __('message.localized_license') }}</span>
                                    </div>
                                </div>
                                <div class="col-sm-7">
                                    <button class="btn btn-light btn-sm table_btn" @click="handleDownloadClick">
                                        <i class="fas fa-download me-1"></i>{{ __('message.download_license_file') }}
                                    </button>
                                </div>
                            </div>
                        </template>

                        <div class="row"><div class="col"><hr class="solid my-3"></div></div>

                        <!-- Installations table -->
                        <DataTable :key="`${orderId}-${installKey}`" :url="installationsUrl" :dataColumns="installColumns" :option="installOptions">
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
                        <DataTable :key="orderId" :url="invoicesUrl" :dataColumns="invoiceColumns" :option="invoiceOptions">
                            <template #number="{ row }">
                                <div class="d-flex flex-column">
                                    <RouterLink :to="'/my-invoice/' + row.id" class="fw-semibold">{{ row.number || '—' }}</RouterLink>
                                    <span v-if="row.is_renewed" class="badge bg-primary mt-1 w-auto">
                                        {{ __('message.renewed') }}
                                    </span>
                                </div>
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
                        <DataTable :key="orderId" :url="paymentsUrl" :dataColumns="paymentColumns" :option="paymentOptions">
                            <template #invoice_number="{ row }">
                                <RouterLink v-if="row.invoice_id" :to="'/my-invoice/' + row.invoice_id" class="fw-semibold">{{ row.invoice_number || '—' }}</RouterLink>
                                <span v-else>{{ row.invoice_number || '—' }}</span>
                            </template>
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
                        <AppCard>
                            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="auto-renewal-icon"><i class="fas fa-sync-alt"></i></div>
                                    <div>
                                        <span class="fw-bold">
                                            {{ __('message.auto_renewal') }}
                                            <span class="badge rounded-pill badge-soft-success ms-1">
                                                <template v-if="order.auto_renew_state === 'active'">{{ __('message.active') }}</template>
                                                <template v-else-if="order.auto_renew_state === 'pending'">{{ __('message.pending_authorization') }}</template>
                                                <template v-else-if="order.auto_renew_state === 'enabled'">{{ __('message.enabled') }}</template>
                                                <template v-else>{{ __('message.inactive') }}</template>
                                            </span>
                                        </span>
                                        <p class="text-muted text-2 mb-0">{{ __('message.auto_renewal_description') }}</p>
                                        <template v-if="order.is_subscribed && order.autorenew_log">
                                            <small class="text-muted text-capitalize">
                                                {{ __('message.payment_gateway') }}: {{ order.autorenew_log.payment_method }}
                                                &nbsp;&middot;&nbsp;
                                                {{ __('message.subscription_enabled_date') }} {{ formatDate(order.autorenew_log.date) }}
                                            </small>
                                        </template>
                                    </div>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           :checked="order.is_subscribed" :disabled="renewalBusy"
                                           @click.prevent="onAutoRenewToggleClick"
                                           v-tooltip="order.is_subscribed ? __('message.disable') : __('message.enable')">
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
                    <div class="bg-light rounded px-3 py-3 mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">{{ __('message.current_no_agents') }}</span>
                            <span class="fw-bold text-dark">{{ cloud?.current_agents || '—' }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">{{ __('message.price_per_agent') }}</span>
                            <span class="fw-bold text-dark">{{ cloud?.price_per_agent || '—' }}</span>
                        </div>
                    </div>

                    <div class="text-center mb-4">
                        <div class="download-section-label mb-2">{{ __('message.choose_no_desired_agents') }}</div>
                        <div class="d-flex align-items-center justify-content-center gap-3">
                            <button type="button" class="btn btn-light agent-stepper-btn" :disabled="agentAtMinimum" @click="stepAgents(-1)">
                                <i class="fas fa-minus"></i>
                            </button>
                            <span class="agent-count-display">{{ agentForm.number || 0 }}</span>
                            <button type="button" class="btn btn-light agent-stepper-btn" @click="stepAgents(1)">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <!-- Plain sentence, not a bare "+1" symbol — says what's
                             about to happen, not just a number. -->
                        <div v-if="agentChanged" class="fw-bold mt-2" :class="agentIncreasing ? 'text-success' : 'text-danger'">
                            {{ agentSubmitLabel }}
                        </div>
                    </div>

                    <!-- Laid out like a receipt on purpose: a cost, a credit for
                         what's unused, and the total — so it's obvious the two
                         big numbers above aren't extra charges, they're what
                         the final number below is calculated from. -->
                    <div v-if="agentChanged" class="cost-breakdown rounded p-3 mb-1">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-color-grey">{{ __('message.new_agent_count_cost', { count: agentForm.number }) }}</span>
                            <span class="fw-bold text-dark">{{ agentNewCost }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pb-2 mb-2 border-bottom">
                            <span class="text-color-grey">{{ __('message.unused_current_agents_credit', { count: cloud?.current_agents }) }}</span>
                            <span class="fw-bold text-success">−{{ agentCurrentCost }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark">{{ __('message.price_due_today') }}</span>
                            <span class="fw-bold text-dark fs-6">{{ agentCost }}</span>
                        </div>
                    </div>

                    <p v-if="agentChanged" class="text-color-grey text-2 mb-0">
                        {{ __('message.agent_proration_note', { count: agentForm.number }) }}
                    </p>
                </template>
                <template #controls>
                    <action-button action="confirm"
                                   :label="agentSubmitLabel"
                                   :loading="agentBusy"
                                   :disabled="!agentChanged"
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

                    <!-- Reads top to bottom like a real deduction: how much
                         credit you have, what the new plan costs, how much
                         of that credit gets used, then what's left to pay.
                         The second, distinctly-styled box is money going the
                         OTHER way (into your account) — kept apart so it's
                         never confused with the subtraction above it. -->
                    <template v-if="planCost">
                        <div class="cost-breakdown rounded p-3 mt-2 mb-1">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-color-grey">{{ __('message.total_credits_remaining') }}</span>
                                <span class="fw-bold text-dark">{{ planCost.currency_symbol }}{{ planCost.priceoldplan }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pb-2 mb-2 border-bottom">
                                <span class="text-color-grey">{{ __('message.price_for_new_plan') }}</span>
                                <span class="fw-bold text-dark">{{ planCost.currency_symbol }}{{ planCost.pricenewplan }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pb-2 mb-2 border-bottom">
                                <span class="text-color-grey">{{ __('message.credit_deducted_label') }}</span>
                                <span class="fw-bold text-success">−{{ planCost.currency_symbol }}{{ planCreditApplied.toFixed(2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-dark">{{ __('message.price_due_today') }}</span>
                                <span class="fw-bold text-dark fs-6">{{ planCost.currency_symbol }}{{ planCost.price_to_be_paid }}</span>
                            </div>
                        </div>

                        <!-- Leftover credit (old plan worth more than the new
                             one costs) doesn't just vanish — called out here,
                             clearly apart from the payment math above. -->
                        <div v-if="planFutureCredit > 0" class="future-credit-callout rounded p-3 mb-1 d-flex justify-content-between align-items-center">
                            <span class="text-dark">{{ __('message.future_plan_credit_label') }}</span>
                            <span class="fw-bold text-success fs-6">+{{ planCost.currency_symbol }}{{ planFutureCredit.toFixed(2) }}</span>
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

        <!-- ── Bind license (domain + machine ID) before first download ── -->
        <Modal :showModal="showBindingModal" :onClose="closeBindingModal" :showCloseBtn="false">
            <template #title>
                <h5 class="modal-title fw-bold">{{ __('message.localized_license') }}</h5>
            </template>
            <template #fields>
                <AppAlert componentName="license-binding-modal" />
                <p class="text-muted mb-3">{{ __('message.machine_id_tooltip') }}</p>
                <ClientField type="text" name="bindingDomain" required
                             :label="__('message.domain')"
                             v-model="bindingForm.domain"
                             placeholder="example.com" autocomplete="off" />
                <ClientField type="text" name="bindingMachineId" required
                             :label="__('message.machine_id')"
                             v-model="bindingForm.machineId"
                             :placeholder="__('message.enter_machine_id')" autocomplete="off" />
            </template>
            <template #controls>
                <action-button action="confirm"
                               :label="__('message.save_and_download')"
                               :loading="bindingBusy"
                               :disabled="!bindingForm.domain || !bindingForm.machineId"
                               @click="submitBinding" />
            </template>
        </Modal>

        <!-- ── Pick which license file to download (main product + entitled add-ons) ── -->
        <Modal :showModal="showDownloadModal" :onClose="closeDownloadModal" classname="modal-dialog-scrollable download-license-modal">
            <template #title>
                <h5 class="modal-title fw-bold">{{ __('message.localized_license') }}</h5>
            </template>
            <template #fields>
                <div class="download-section-label">{{ __('message.product') }}</div>
                <ul class="list-group mb-3">
                    <li class="list-group-item d-flex align-items-center justify-content-between">
                        <span>{{ order?.product_name || __('message.product') }}</span>
                        <button class="btn btn-light btn-sm table_btn" @click="selectDownload(null)">
                            <i class="fas fa-download"></i>
                        </button>
                    </li>
                </ul>

                <template v-if="pluginLicenses.length">
                    <div class="download-section-label">{{ __('message.addons') }}</div>
                    <ul class="list-group">
                        <li
                            v-for="plugin in pluginLicenses"
                            :key="plugin.id"
                            class="list-group-item d-flex align-items-center justify-content-between"
                        >
                            <span>{{ plugin.name }}</span>
                            <button class="btn btn-light btn-sm table_btn" @click="selectDownload(plugin.id)">
                                <i class="fas fa-download"></i>
                            </button>
                        </li>
                    </ul>
                </template>
            </template>
        </Modal>

        <!-- ── Renew Order modal (shared with the orders list page) ── -->
        <RenewModal v-model:show="showRenewModal" :order="order" />

        </template>
    </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { errorHandler, successHandler } from '@/helpers/responseHandler.js'
import { useAlertStore } from '@/core/stores/alert'
import { useLoaderStore } from '@/core/stores/loader'
import Modal from '@/themes/porto/components/common/Modal.vue'
import AppAlert from '@/components/Reusable/Alert.vue'
import RenewModal from './components/RenewModal.vue'
import WhatsappPanel from './components/WhatsappPanel.vue'
import DeployWizard from './components/DeployWizard.vue'
import { useDateTime } from '@/core/composables/useDateTime'
import { useBaseUrl } from '@/core/composables/useBaseUrl'

const { formatDate } = useDateTime()
const baseUrl = useBaseUrl()
const el      = document.getElementById('app-client')
const userId  = el?.dataset?.userId  ?? ''

const route   = useRoute()
const router  = useRouter()
// Reactive, not a one-time snapshot — the SPA reuses this component instance
// when navigating from one order's page straight to another's (e.g. the
// termination notice's "replaces order #X" link), so a plain `const` here
// would freeze on whichever order loaded first and never update.
const orderId = computed(() => route.params.id)

const loading   = ref(true)
const copied    = ref(false)
const activeTab = ref('license')
const order     = ref(null)

const installationsUrl = computed(() => `/get-my-installations/${orderId.value}`)
const invoicesUrl      = computed(() => `/get-my-invoices/${orderId.value}/${userId}`)
const paymentsUrl      = computed(() => `/get-my-payment-client/${orderId.value}/${userId}`)


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
// A single "desired total" field — simpler than an increase/decrease action
// picker plus a delta amount. Direction and delta are worked out (here and
// server-side) by comparing to the current count, not asked for directly.
const agentForm  = reactive({ number: '' })
const planForm   = reactive({ planId: '' })

// Nothing to submit until the desired total actually differs from what's
// already provisioned. Direction/delta are plain counts (not money), so
// computing them here is fine — the one money figure shown (agentCost
// below) always comes straight from the server, never computed here.
const agentChanged = computed(() =>
    !!agentForm.number && Number(agentForm.number) !== Number(cloud.value?.current_agents)
)
const agentIncreasing = computed(() => Number(agentForm.number || 0) > Number(cloud.value?.current_agents || 0))
const agentCountDelta = computed(() => Math.abs(Number(agentForm.number || 0) - Number(cloud.value?.current_agents || 0)))
const agentAtMinimum = computed(() => Number(agentForm.number || 0) <= 1)
// "Add 1 agent" vs "Add 3 agents" — the __() helper has no pluralization, so
// pick the right key by hand rather than showing "Add 1 agents". Used both
// as the button label and as the plain-English line under the stepper, so
// the two always say exactly the same thing.
const agentSubmitLabel = computed(() => {
    if (!agentChanged.value) return __('message.update_agents')
    const count = agentCountDelta.value
    if (agentIncreasing.value) return count === 1 ? __('message.add_one_agent') : __('message.add_agents_count', { count })
    return count === 1 ? __('message.remove_one_agent') : __('message.remove_agents_count', { count })
})

function stepAgents(delta) {
    const next = Number(agentForm.number || cloud.value?.current_agents || 1) + delta
    agentForm.number = Math.max(1, next)
    fetchAgentCost()
}

function onPlanChange(v) {
    planForm.planId = v?.id ?? ''
    fetchPlanCost()
}

const agentCost = ref('')
// The two halves priceToPay (agentCost) is the difference of — see fetchAgentCost.
const agentCurrentCost = ref('')
const agentNewCost = ref('')
const planCost  = ref(null)

// Money fields from the backend are currency-formatted for display (e.g.
// "3,000.00") — strip thousands separators before treating them as numbers.
function toNumber(v) {
    return parseFloat(String(v ?? '').replace(/,/g, '')) || 0
}

// How much of the old plan's remaining value is actually going toward this
// purchase — never more than the new plan costs. planCost.discount (if any)
// is the leftover beyond that, banked as credit for later (see below), not
// spent here, so it's subtracted out rather than shown as part of this credit.
const planCreditApplied = computed(() => planCost.value
    ? toNumber(planCost.value.priceoldplan) - toNumber(planCost.value.discount)
    : 0)
// Set only when the old plan was worth more than the new one costs — the
// difference doesn't just vanish, it's added to the account as credit.
const planFutureCredit = computed(() => planCost.value ? toNumber(planCost.value.discount) : 0)

const domainBusy = ref(false)
const agentBusy  = ref(false)
const planBusy   = ref(false)

const alertStore = useAlertStore()
const loaderStore = useLoaderStore()

const showCloudTab      = computed(() => !!order.value?.is_cloud && order.value?.status !== 'Terminated')
const showAutoRenewTab  = computed(() => !!order.value?.autorenewal_enabled && order.value?.status !== 'Terminated')

/* ── Auto Renewal ─────────────────────────────────────────── */
const showRenewalModal = ref(false)
const renewalBusy      = ref(false)
const selectedGateway  = ref('')

// The click is prevented so the native checkbox never flips on its own —
// enabling needs a gateway pick + card verification, disabling needs
// confirmation, so the switch only visually moves once order.is_subscribed
// itself changes (on modal success), not just because it was clicked.
function onAutoRenewToggleClick() {
    if (order.value?.is_subscribed) {
        showDisableRenewalModal.value = true
    } else {
        showRenewalModal.value = true
    }
}

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
        const res = await http.post(`/auto-renewal/${orderId.value}/disable`)
        order.value.is_subscribed    = false
        order.value.autorenew_status = false
        order.value.auto_renew_state = 'inactive'
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
            const { data } = await http.post(`/auto-renewal/${orderId.value}/razorpay/order`)
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
    // Opens directly against the already-created subscription (config.subscription_id)
    // instead of a one-time order — the customer authorizes the recurring mandate
    // right here, in one payment, instead of a separate verification charge now
    // plus a second authorization step later via an emailed link.
    const options = { ...config }
    options.handler = async (response) => {
        renewalBusy.value = true
        loaderStore.startLoader('renewal-verify')
        try {
            const res = await http.post(`/auto-renewal/${orderId.value}/razorpay/confirm`, {
                razorpay_subscription_id: response.razorpay_subscription_id,
                razorpay_payment_id:      response.razorpay_payment_id,
                razorpay_signature:       response.razorpay_signature,
            })
            order.value.is_subscribed    = true
            // This popup *is* the authorization — no further step needed.
            order.value.auto_renew_state = 'active'
            successHandler(res, 'client-page')
        } catch (e) {
            errorHandler(e, 'client-page')
        } finally {
            renewalBusy.value = false
            loaderStore.stopLoader('renewal-verify')
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
    const { data } = await http.post(`/auto-renewal/${orderId.value}/stripe/session`)
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
    loaderStore.startLoader('renewal-verify')
    try {
        const res = await http.post(`/auto-renewal/${orderId.value}/stripe/confirm`, {
            payment_intent: renewalPaymentIntentId,
        })
        order.value.is_subscribed    = true
        // The real Stripe subscription isn't created until renewal:cron runs
        // near the actual expiry date — not truly active yet.
        order.value.auto_renew_state = 'pending'
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
        loaderStore.stopLoader('renewal-verify')
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
        const res = await http.patch(`/reissue-license`, { id: orderId.value })
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

/* ── License binding (domain + machine ID) before first download ─── */
const showBindingModal = ref(false)
const bindingBusy      = ref(false)
const bindingForm      = reactive({ domain: '', machineId: '' })
const pluginLicenses   = ref([])
// null = main product's license file; otherwise the plugin product_id whose
// download triggered the binding modal, so it resumes the right one after.
const pendingDownloadProductId = ref(null)

function isLicenseBound() {
    return !!(order.value?.license_domain && order.value?.license_machine_id)
}

function triggerDownload(productId = null) {
    const suffix = productId ? `&productId=${productId}` : ''
    window.location.href = `${baseUrl}/downloadLicenseFile?orderNo=${order.value.number}${suffix}`
}

function requestDownload(productId = null) {
    if (isLicenseBound()) {
        triggerDownload(productId)
        return
    }
    pendingDownloadProductId.value = productId
    bindingForm.domain    = order.value?.license_domain ?? ''
    bindingForm.machineId = order.value?.license_machine_id ?? ''
    alertStore.unsetAlert()
    showBindingModal.value = true
}

/* ── Pick which license file to download (main product + entitled add-ons) ── */
const showDownloadModal = ref(false)

function handleDownloadClick() {
    showDownloadModal.value = true
}

function closeDownloadModal() {
    showDownloadModal.value = false
}

function selectDownload(productId) {
    // Only hide the picker if binding is about to be needed - otherwise its
    // overlay would sit on top of the binding modal. It's restored in
    // submitBinding() once binding's done. If already bound, leave it open
    // so multiple items can be downloaded without reopening it each time.
    if (!isLicenseBound()) {
        showDownloadModal.value = false
    }
    requestDownload(productId)
}

async function loadPluginLicenses() {
    if (!order.value?.number) return
    try {
        const res = await http.get(`/LocalizedLicense/${order.value.number}/plugins`)
        pluginLicenses.value = res.data?.data ?? res.data ?? []
    } catch (e) {
        pluginLicenses.value = []
    }
}

function closeBindingModal() {
    showBindingModal.value = false
    alertStore.unsetAlert()
}

async function submitBinding() {
    if (!bindingForm.domain || !bindingForm.machineId) return
    bindingBusy.value = true
    try {
        const res = await http.post('/license-binding', {
            orderNo: order.value.number,
            domain: bindingForm.domain,
            machine_id: bindingForm.machineId,
        })
        order.value.license_domain     = bindingForm.domain
        order.value.license_machine_id = bindingForm.machineId
        successHandler(res, 'client-page')
        closeBindingModal()
        triggerDownload(pendingDownloadProductId.value)
        showDownloadModal.value = true
    } catch (e) {
        errorHandler(e, 'license-binding-modal')
    } finally {
        bindingBusy.value = false
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
        const res = await http.get(`/get-cloud-settings/${orderId.value}`)
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
    agentForm.number = cloud.value?.current_agents ?? ''
    agentCost.value = ''
    agentCurrentCost.value = ''
    agentNewCost.value = ''
    showAgentsModal.value = true
}
function closeAgentsModal() { showAgentsModal.value = false; alertStore.unsetAlert() }

function resetAgentCost() {
    agentCost.value = ''
    agentCurrentCost.value = ''
    agentNewCost.value = ''
}

async function fetchAgentCost() {
    if (!cloud.value || !agentChanged.value) { resetAgentCost(); return }
    try {
        const res = await http.post(`/get-agent-inc-dec-cost`, {
            orderId:       cloud.value.order_id,
            desiredAgents: agentForm.number,
        })
        // raw (un-wrapped) array response: { pricePerAgent, totalPrice, priceToPay, currentAgentsCost, newAgentsCost }
        const symbol = res.data?.currency_symbol ?? ''
        agentCost.value = symbol + (res.data?.priceToPay ?? '')
        agentCurrentCost.value = symbol + (res.data?.currentAgentsCost ?? '')
        agentNewCost.value = symbol + (res.data?.newAgentsCost ?? '')
    } catch (e) {
        resetAgentCost()
        errorHandler(e, 'agents-modal')
    }
}

async function submitAgents() {
    if (!cloud.value || !agentChanged.value) return
    agentBusy.value = true
    try {
        const res = await http.post(`/changeAgents`, {
            orderId:       cloud.value.order_id,
            subId:         cloud.value.sub_id,
            desiredAgents: agentForm.number,
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

// Re-runs whenever orderId changes — including navigating from one order's
// page straight to another's (e.g. the termination notice's "replaces order
// #X" link) — since Vue Router reuses this component instance rather than
// remounting it for a same-route param change.
watch(orderId, async () => {
    loading.value = true
    activeTab.value = 'license'
    // Cloud tab data belongs to whichever order was open before — drop it so
    // openCloudTab() re-fetches for the new order instead of reusing it.
    cloud.value = null
    cloudLoaded.value = false
    try {
        const res = await http.get(`/get-my-order/${orderId.value}`)
        order.value = res.data?.data ?? null
        // License Details isn't offered for a terminated order (no nav item
        // for it), so land somewhere that is instead of defaulting to a tab
        // that's invisible with nothing selected.
        if (order.value?.status === 'Terminated') activeTab.value = 'invoice'
        await loadPluginLicenses()
    } catch (e) {
        errorHandler(e, 'client-page')
    } finally {
        loading.value = false
    }
}, { immediate: true })
</script>

<style scoped>
.auto-renewal-icon {
    width: 40px;
    height: 40px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: #e7f4fb;
    color: #17a2e0;
    font-size: 16px;
}
.form-switch .form-check-input {
    width: 2.5em;
    height: 1.4em;
    cursor: pointer;
}
.badge-soft-success { background-color: #e6f9ef; color: #1a9d5c; }
.download-section-label {
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #6c757d;
    margin-bottom: 0.5rem;
}
.agent-stepper-btn {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #dee2e6;
    border-radius: 10px;
}
.agent-stepper-btn:disabled { opacity: 0.4; }
.agent-count-display {
    min-width: 2.5rem;
    text-align: center;
    font-size: 2rem;
    font-weight: 800;
    color: #212529;
}
.cost-breakdown { background-color: #e7f4fb; }
.future-credit-callout { background-color: #e6f9ef; border: 1px dashed #1a9d5c; }
</style>

<!-- Modal.vue teleports to <body>, so scoped styles can't reach its internals -
     this rule targets the unique classname passed to that one modal instance,
     rather than being scoped (which teleport would silently defeat anyway). -->
<style>
.download-license-modal .modal-content {
    max-height: 60vh;
}
</style>
