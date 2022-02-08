<template>
	<view class="page-body">
		<view class="menu_box b-tottom">
			<view class="grid_price"><text>￥</text>{{account.total_income}}</view>
			<view class="grid_text">累计收入</view>
		</view>
		<u-grid :col="2" :border="false">
			<u-grid-item class="relative">
				<view class="top_static_box ">
					<u-circle-progress class="circle_progress"  :width="120" active-color="#2979ff" :percent="income.day_pre">
						<view class="u-progress-content">
							<text class='u-progress-info fs18'>{{income.day_pre}}%</text>
						</view>
					</u-circle-progress>
					<view class="price"><text>￥</text>{{income.day}}</view>
					<view class="text">今天收入</view>
				</view>
				<view class="b-right"></view>
			</u-grid-item>
			<u-grid-item>
				<view class="top_static_box">
				<u-circle-progress class="circle_progress" :width="120" active-color="#ce6c60" :percent="income.month_pre">
					<view class="u-progress-content">
						<text class='u-progress-info fs18'>{{income.month_pre}}%</text>
					</view>
				</u-circle-progress>
				
				<view class="price"><text>￥</text>{{income.month}}</view>
				<view class="text">本月收入</view>
				</view>
			</u-grid-item>
		</u-grid>
		<view class="grid_menu p10">
			<u-grid :col="2"  :border="false">
				<u-grid-item @click="app.goPage('balanceMoney')" >
					<view class="img">
						<u-image shape="circle"  width="120rpx" height="120rpx" src="/pagesB/static/channel/images/menu_10.png"></u-image>
					</view>
					<view class="titel">钱包余额</view>
					<view class="tip">可提取积分</view>
					<view class="money">￥{{account.balance_money}}</view>
				</u-grid-item>
				<u-grid-item @click="app.goPage('goodsMoney')">
					<view class="img">
						<u-image shape="circle" width="120rpx" height="120rpx" src="/pagesB/static/channel/images/menu_04.png"></u-image>
					</view>
					<view class="titel">货款余额</view>
					<view class="tip">资金安全保障</view>
					<view class="money">￥{{account.goods_money}}</view>
				</u-grid-item>
				<u-grid-item @click="app.goPage('uplevelGoodsMoney')">
					<view class="img">
						<u-image shape="circle" width="120rpx" height="120rpx" src="/pagesB/static/channel/images/menu_13.png"></u-image>
					</view>
					<view class="titel">升级货款</view>
					<view class="tip">不可提取积分</view>
					<view class="money">￥{{account.uplevel_goods_money}}</view>
				</u-grid-item>
				<u-grid-item  @click="app.goPage('rewardLog')">
					<view class="img">
						<u-image shape="circle" width="120rpx" height="120rpx" src="/pagesB/static/channel/images/menu_11.png"></u-image>
					</view>
					<view class="titel">奖励中心</view>
					<view class="tip">对帐与核销</view>
					<view class="money color-ff">_</view>
				</u-grid-item>
			</u-grid>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			let that = this;
			return {
				account:{},
				income:{},
			}
		},
		onLoad() {
			this.$u.post('channel/api.wallet/getWalletInfo').then(res => {
				this.account = res.data.account;
				this.income = res.data.income;
				this.income.day_pre = Number(this.income.day_pre);
				this.income.month_pre = Number(this.income.month_pre);
			});
		},
		onReady() {},
		methods: {
		}
	}
</script>
<style lang="scss">
	@import "~@/pagesB/static/channel/css/wallet.scss";
	.top_static_box{
		padding-left: 40rpx;
		.circle_progress {
			float: left;
			margin-right: 20rpx;
		}
		.price {
			float: left;
			padding-top: 10rpx;
			font-size: 33rpx;
			font-weight: 500;
			text {
				font-size: 28rpx;
			}
		}
		
		.text {
			float: left;
			color: #979598;
			font-size: 28rpx;
		}
	}
	
	
	.grid_menu{
		.u-grid-item{
			border: 10rpx solid $page-color-base;
			.img {
				margin: 0rpx auto;
				margin-bottom: 30rpx;
				margin-top: 30rpx;
				background-color: #f9f9f9 !important;
				padding: 20rpx;
				border-radius: 50%;
			}
			.titel{
				font-weight: 700;
				font-size: 36rpx;
			}
			.tip{
				color: $font-color-light;
			}
			.money{
				height: 55rpx;
				margin-top: 20rpx;
				font-weight: 700;
				font-size: 32rpx;
			}
		}
	}
</style>
