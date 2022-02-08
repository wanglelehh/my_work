<template>
	<view :style="{background:diyitem.style.background,padding:diyitem.style.padding+'rpx 0'}">
	    <view :style="{'border-top':diyitem.style.height+'rpx '+diyitem.style.linestyle+' '+diyitem.style.border}"></view>
	</view>
</template>


<script>
	export default {
		name: "tpl_line",
		props: {
			diyitem: {
				type: Object,
				default:function(){
					return {};
				}
			},
		},
		data() {
			return {
			};
		},
		watch: {
		},
		computed: {},
		methods: {
			
		}
	}
</script>

<style lang='scss'>
</style>

