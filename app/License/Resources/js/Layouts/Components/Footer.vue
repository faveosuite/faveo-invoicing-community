<template>

	<footer class="app-footer">

		<div class="float-end d-none d-sm-inline">

			<strong>{{trans('version')}}&nbsp;</strong> {{versioning}}
		</div>

		<strong> {{trans('copyright')}} &copy; {{new Date().getFullYear()}}

			<a class="text-decoration-none" href="https://www.faveohelpdesk.com" target="_blank">Ladybird Web Solution.</a>

		</strong>

		{{ trans('all_rights_reserved') }}. {{ trans('powered_by') }}

		<a href="https://www.faveohelpdesk.com" target="_blank">Faveo</a>
	</footer>
</template>

<script>

	export default {

		name : 'license-footer',

        props:{
            versioning : { type : String , default : ''},
        },
	};
</script>
