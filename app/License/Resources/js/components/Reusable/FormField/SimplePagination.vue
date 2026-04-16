<template>

    <div class="simple-pagination">

        <button class="btn m-2 btn-primary" @click="onPagination('previous')" :disabled="!prev_page">

            <i class="fas fa-arrow-left"></i> &nbsp; {{ trans('previous') }}
        </button>

        <button class="btn btn-primary" @click="onPagination('next')" :disabled="!next_page">

            {{ trans('next') }} &nbsp;<i class="fas fa-arrow-right"></i>
        </button>
    </div>
</template>

<script>
export default {
    name : 'simple-pagination',
    props : {
        prev_page : { type : [String,null], default : '' },
        next_page : { type : [String,null], default : '' },
        onPagination : { type : Function, default : ()=>{} }
    }
}
</script>
