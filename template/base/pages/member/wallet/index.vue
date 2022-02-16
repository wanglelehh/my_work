<template>
	<view class="page-body bg-white" :class="[app.setCStyle()]">
		<view class="qb-box" :style="{'background-image':'url('+img_wallet_top_bg+')'}">
		</view>
		<view class="menu_box ">
			<view class="flex">
				<view class="flex_bd">
					<view class="grid_price ff"><text class="fs40">￥</text>{{account.balance_money}}</view>
					<view class="grid_text">{{app.langReplace('钱包余额')}}</view>
				</view>
				
			</view>
			<view class="grid_menu flex">
				<view class="w50">
					<view class="money ff">{{withdrawing}}</view>
					<view class="title fs28">{{app.langReplace('提现中')}}</view>
				</view>
				<view class="w50">
					<view class="money ff">{{wait_returned}}</view>
					<view class="title fs28">{{app.langReplace('待返')}}</view>
				</view>
			</view>
			
			
		</view>

		<view class="p20">
			<view class="list-cell mt20" @click="app.goPage('withdrawLog')">
				<u-icon :name="baseUrl+'/a_static/menuimg/wallet_icon_withdrawal.png'" :size="48" class="mr20"></u-icon>
				<view class="cell-tit fs28">提现记录</view>
				<view class="cell-more">
					<u-icon name="arrow-right"></u-icon>
				</view>
			</view>
			<view class="list-cell mt20" @click="app.goPage('myLog?pageType=balance')">
				<u-icon :name="baseUrl+'/a_static/menuimg/wallet_icon_balance.png'" :size="48" class="mr20"></u-icon>
				<view class="cell-tit fs28">余额明细</view>
				<view class="cell-more">
					<u-icon name="arrow-right"></u-icon>
				</view>
			</view>
			<view class="list-cell mt20" @click="app.goPage('dividendlog')">
				<u-icon :name="baseUrl+'/a_static/menuimg/wallet_icon_accountbook.png'" :size="48" class="mr20"></u-icon>
				<view class="cell-tit fs28">我的帐本</view>
				<view class="cell-more">
					<u-icon name="arrow-right"></u-icon>
				</view>
			</view>
		</view>

		<view class="p30">
			<button v-if="setting.withdraw_status == 1" size="default" shape="circle" type="primary" class="mt40 primarybtn" @click="app.goPage('withdraw')">{{app.langReplace('去提现')}}</button>
			<!-- <button size="default" shape="circle" type="primary" class="mt40 primarybtnB" @click="app.goPage('recharge')">{{app.langReplace('去充值')}}</button> -->
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			let that = this;
			return {
				baseUrl: this.config.baseUrl,
				setting: {},
				account: {},
				withdrawing: 0,
				wait_returned:0,
				img_wallet_top_bg:''
			}
		},
		onLoad() {
			let title = this.app.langReplace('我的钱包');
			uni.setNavigationBarTitle({
				title
			})
			this.setting = uni.getStorageSync("setting");
			if (this.setting.img_wallet_top_bg){
				this.img_wallet_top_bg = this.config.baseUrl + this.setting.img_wallet_top_bg;
			}
		},
		onShow() {
			this.app.isLogin(this); //强制登陆
			this.$u.post('member/api.wallet/getBalanceInfo').then(res => {
				this.account = res.data.account;
				this.withdrawing = res.data.withdrawing;
				this.wait_returned = res.data.wait_returned;
			});
		},
		onReady() {},
		methods: {}
	}
</script>

<style lang="scss">
	.qb-box{
		height: 445rpx;
		overflow: hidden;
		background-size: 100%;
		background-repeat: no-repeat;
	}
	.menu_box {
		background: linear-gradient(180deg, #FEDADE 0%, #FFFFFF 100%);
		box-shadow: 0rpx 10rpx 15rpx 0rpx rgba(188, 188, 188, 0.1);
		border-radius: 20rpx;
		margin: 30rpx;
		margin-top: -230rpx;
		padding: 60rpx 0 30rpx 0;
		text-align: center;
	}
	
	.menu_box {
		.grid_price {
			color: $font-color-dark;
			font-size: 70rpx;
			font-weight: 500;
			text {
				font-size: 40rpx;
			}
		}
		.grid_text {
			font-size: 28rpx;
			color: $font-color-light;
		}
	}


	.img44 {
		image {
			width: 44rpx;
			height: 44rpx;
		}
	}

	.gridbox {
		margin: 20rpx 15rpx;

		.card-item {
			margin: 0rpx 10rpx;
			background-color: #ffffff;
			box-shadow: 0px 10rpx 15rpx 0px rgba(188, 188, 188, 0.1);
			border-radius: 20rpx;
			text-align: center;
			padding: 60rpx 0;
		}
	}

	
	.grid_menu {
		margin-top: 40rpx;
		line-height: 55rpx;
		.money {
			height: 55rpx;
			margin-top: 30rpx;
			font-weight: 600;
			font-size: 40rpx;
			&::before {
				content: '￥';
				font-size: 24rpx;
			}
		}
		.title{
			color: #999999;
		}
	}
</style>
