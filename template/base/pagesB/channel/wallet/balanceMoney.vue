<template>
	<view class="page-body">
		<view class="menu_box b-tottom">
			<view class="grid_price"><text>￥</text>{{account.balance_money}}</view>
			<view class="grid_text">钱包余额</view>
		</view>
		<view class="grid_menu p10">
			<u-grid :col="2"  :border="false">
				<u-grid-item>
					<view class="money">￥{{withdrawing}}</view>
					<view class="titel">提现中</view>
				</u-grid-item>
				<u-grid-item >
					<view class="money">￥{{account.withdraw_money_total}}</view>
					<view class="titel">累计提现</view>
				</u-grid-item>
			</u-grid>
		</view>
		<view class="row-menu" @click="app.goPage('withdrawLog')">提现记录 <view class="icon">查看 <u-icon name="arrow-right "></u-icon></view></view>
		<view class="row-menu" @click="app.goPage('balanceLog')">余额明细 <view class="icon">查看 <u-icon name="arrow-right "></u-icon></view></view>
		<view class="p30">
			<u-button size="default" shape="circle" type="primary" class="mt40" @click="app.goPage('withdraw')" >去提现</u-button>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			let that = this;
			return {
				account:{},
				withdrawing:0,
			}
		},
		onLoad() {
			
		},
		onShow() {
			this.$u.post('channel/api.wallet/getBalanceInfo').then(res => {
				this.account = res.data.account;
				this.withdrawing = res.data.withdrawing;
			});
		},
		onReady() {},
		methods: {
		}
	}
</script>
	
<style lang="scss">
	@import "~@/pagesB/static/channel/css/wallet.scss";

	.grid_menu{
		.u-grid-item{
			border: 10rpx solid $page-color-base;
			line-height: 55rpx;
			
			.img {
				margin: 0rpx auto;
				margin-bottom: 30rpx;
				background-color: #f9f9f9 !important;
				padding: 30rpx;
			}
			.titel{
				color: $font-color-light;
			}
			
			.money{
				height: 55rpx;
				margin-top: 20rpx;
				font-weight: 700;
				font-size: 36rpx;
			}
		}
	}
</style>
