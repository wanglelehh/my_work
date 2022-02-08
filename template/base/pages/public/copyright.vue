<template>
	<view class="copyright-box">
		<view v-if="setting.copyright">{{setting.copyright}}</view>
		<view v-if="setting.ipc_no" @click="app.toOutLink('https://beian.miit.gov.cn/');">{{setting.ipc_no}}</view>
		<view v-if="setting.tel">联系电话：{{setting.tel}}</view>
		<view v-if="setting.address">联系地址：{{setting.address}}</view>
	</view>
</template>

<script>
	export default {
		name: "copyright",
		props: {
			
		},
		data() {
			return { setting : uni.getStorageSync('setting')}
		},
		computed:{
		},
		methods: {
			
		}
	}
</script>

<style lang="scss">
	.copyright-box{
		font-size: 26rpx;
		text-align: center;
		margin: 80rpx 0rpx 40rpx 0rpx;
		color: #cccccc;
		line-height: 50rpx;
	}
</style>
