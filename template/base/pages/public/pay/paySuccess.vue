<template>
	<view class="page-body" :class="[app.setCStyle(from)]">
		<view class="success-icon">
			<u-icon name="checkmark" color="#FFFFFF" size="88"></u-icon>
		</view>
		<text class="tip">{{tip}}</text>
		<view v-if="from == 'channel'" class="btn-group">
			<view v-if="type == 'order'" @click="app.goPage('/pagesB/channel/order/info?order_id='+order_id,-1)" class="mix-btn primarybtn" >查看订单</view>
			<view @click="app.goPage('/pagesB/channel/center/index',-1)" class="mix-btn primarybtnB" >返回代理中心</view>
		</view>
		<view v-else>
			<view v-if="from == 'shop'" class="btn-group">
				<view v-if="type == 'order'" @click="app.goPage('/pages/shop/order/info?order_id='+order_id,-1)" class="mix-btn  primarybtn" >查看订单</view>
				<view v-if="type == 'fgorder'" @click="app.goPage('/pagesA/fightgroup/order?order_id='+order_id,-1)" class="mix-btn  primarybtn" >查看拼团订单</view>
			</view>
			<view @click="app.goPage('/pages/member/center/index',-1)" class="mix-btn primarybtnB" >返回会员中心</view>
			<view @click="app.goPage('/pages/shop/index/index',-1)" class="mix-btn primarybtnB" >返回首页</view>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				from:'',
				baseBgColor:'',
				type:'',
				order_id:0,
				tip:'支付成功'
			}
		},
		onLoad(options){
			this.from = options.from;
			this.type = options.payType;
			this.order_id = options.order_id;
			
			if (options.pay_code == 'offline'){
				this.tip = '提交成功，等待审核.';
			}
		},
		methods: {
			
		}
	}
</script>

<style lang='scss'>
	.page-body{
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
	}
	.success-icon{
		background-color: #18B566;
		padding:40rpx 40rpx;
		border-radius: 50%;
		margin-bottom: 20rpx;
	}
	.tit{
		font-size: 38rpx;
		color: #303133;
	}
	.btn-group{
		padding-top: 100rpx;
	}
	.mix-btn {
		margin-top: 30rpx;
		display: flex;
		align-items: center;
		justify-content: center;
		width: 600rpx;
		height: 80rpx;
		font-size: $font-lg;
		color: #fff;
		border-radius: 10rpx;
		
	}
</style>
