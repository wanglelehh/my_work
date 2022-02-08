<template>
	<view class="page-body " :class="[app.setCStyle()]">
		<view class="qb-box" :style="{'background-image':'url('+img_points_top_bg+')'}">
		</view>
		<view class="menu_box ">
			<view class="flex">
				<view class="flex_bd">
					<view class="grid_price ff">{{account.use_integral}}</view>
					<view class="grid_text mt20">{{app.langReplace('我的积分')}}</view>
				</view>
				
			</view>
		</view>

		<view class="p20">
			<view class="list-cell mt20" @click="app.goPage('myLog?pageType=points')">
				<u-icon :name="baseUrl+'/a_static/menuimg/integral_icon_detailed.png'" :size="48" class="mr20"></u-icon>
				<view class="cell-tit fs28">积分明细</view>
				<view class="cell-more">
					<u-icon name="arrow-right"></u-icon>
				</view>
			</view>
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
				img_points_top_bg:''
			}
		},
		onLoad() {
			let title = this.app.langReplace('我的积分');
			uni.setNavigationBarTitle({
				title
			})
			this.setting = uni.getStorageSync("setting");
			if (this.setting.img_points_top_bg){
				this.img_points_top_bg = this.config.baseUrl + this.setting.img_points_top_bg;
			}
		},
		onShow() {
			this.app.isLogin(this); //强制登陆
			this.$u.post('member/api.wallet/getBalanceInfo').then(res => {
				this.account = res.data.account;
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
		background: linear-gradient(180deg, #FFEEC5 0%, #FFFFFF 100%);
		box-shadow: 0px 10px 15px 0px rgba(188, 188, 188, 0.1);
		border-radius: 20rpx;
		margin: 30rpx;
		margin-top: -230rpx;
		padding: 60rpx 0 30rpx 0;
		text-align: center;
		height: 300rpx;
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
	.list-cell{
		background: #FFFFFF;
		border-radius: 20rpx;
		padding: 30rpx 30rpx;
	}
</style>
