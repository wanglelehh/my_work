<template>
	<view :style="{background:diyitem.style.background,height:app.pxToRpx(diyitem.style.height)+'rpx'}"></view>
</template>
<script>
	export default {
		name: "tpl_blank",
		props: {
			diyitemid: {
				type: String,
				default: ''
			},
			diyitem: {
				type: Object,
				default: function() {
					return {};
				}
			},

		},
		data() {
			return {};
		},
		watch: {},
		computed: {},
		methods: {}
	}
</script>

<style lang='scss'>
</style>
