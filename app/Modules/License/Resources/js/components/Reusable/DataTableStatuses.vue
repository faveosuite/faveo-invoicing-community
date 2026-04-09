<template>

    <div class="btn-group">

<!--         Functionalities where not implemented yet for email and mobile verification.-->
<!--        <i class="fas fa-envelope" :class="{'success':data.email_verified == 1,'danger':data.email_verified != 1}"
           id="email" v-tooltip="data.email_verified == 1 ? lang('user_email_is_verified') : lang('user_email_not_verified')">

        </i>&nbsp;&nbsp;

        <i :class="{'success':data.mobile_verified == 1,'danger':data.mobile_verified != 1}"
           id="mobile" class="fas fa-mobile-alt" v-tooltip="data.mobile_verified == 1 ? lang('user_mobile_is_verified') : lang('user_mobile_not_verified')">

        </i>&nbsp;&nbsp;-->

        <i :class="{'success':data.is_2fa_enabled == 1,'danger':data.is_2fa_enabled != 1}"
           :id="'2fa_status_user__' + data.id" class="fas fa-shield-alt" v-tooltip="data.is_2fa_enabled == 1 ? lang('user_enabled_2fa') : lang('user_not_enabled_2fa')">

        </i>&nbsp;&nbsp;

    </div>
</template>

<script type="text/javascript">

import axios from 'axios';

export default {

    name:"data-table-statuses",

    description: "Contains active,ban,email,mobile verify and inactive status",

    props: {

        data : { type : Object, required : true }
    },

    methods:{

    }
};

</script>

<style type="text/css">
.success{
    color : #017701 !important;
}
.danger{
    color : red !important;
}
</style>
