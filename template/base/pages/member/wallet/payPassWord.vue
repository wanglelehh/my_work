<template>
	<!-- 规格-模态层弹窗 -->
	<view class="popup " :class="payPassWordClass"  @touchmove.stop.prevent="stopPrevent" >
		<!-- 遮罩层 -->
		<view class="mask"></view>
		<view class="pay_main" @click.stop="stopPrevent">
			<view class="pay_box p20">
				<u-icon name="close" size="28" class="close"  @click="open"></u-icon>
				<view class=" text-center">请输入支付密码</view>
				<view class="text-center mt30 fs32">{{payTip}}</view>
				<view v-if="payMoney > 0" class="text-center fs60 mt10"><text class="fs28">￥</text>{{payMoney}}</view>
				<view class="mt20"><u-message-input :maxlength="6"  @finish="finish" :focus="true" :dot-fill="true"></u-message-input></view>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		name: "payPassWord",
		props: {
			payTip:{
				type: String,
				default:''
			},
			payMoney:{
				type: String,
				default:'0'
			},
			payPassWordClass: {
				type: String,
				default: 'none',
			},
		},
		data() {
			return {
			}
		},
		computed:{
			
		},
		methods: {
			open(){
				this.$emit("openPayPassWord");
			},
			finish(e) {
				this.$emit("finishPayPassWord",e);
			},
			stopPrevent() {}
		},
	}
</script>

<style>
	.pay_main{
		position: absolute;
		top:20%;
		left: 0px;
		width: 100%;
		z-index: 999;
	}
	
	.pay_main .pay_box{
		width: 650rpx;
		min-height: 180rpx;
		background-color: #FFFFFF;
		border-radius: 20rpx;
		margin: 0rpx auto;
		position: relative;
	}
	.pay_main .pay_box .close{
		position: absolute;
		top: 20rpx;
		right: 20rpx;
	}
</style>
