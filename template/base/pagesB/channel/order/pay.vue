<template>
	<view class="page-body">
		<view class="top-box">
			<view class="price_box">￥<text class="price">{{paySetting.order_amount}}</text></view>
			<text class="tip">您需要支付的金额</text>
		</view>
		<scroll-view class="scroll_view" scroll-y>
			<view v-if="paySetting.is_stock == 1">
				<payBox :payType="payType" :from="from" :order_id="order_id" :paySetting="paySetting"></payBox>
			</view>
			<view v-else-if="paySetting.is_stock == 0" class="mt20 bg-white lack-box">
				<view class="lack-tip">{{paySetting.lack_tip}}</view>
				<view class="top-title">缺货商品</view>
				<view class="g-item" v-for="(item, index) in paySetting.lackGoods" :key="item.rec_id">
					<image :src="config.baseUrl+item.pic"></image>
					<view class="right">
						<text class="title clamp">{{item.goods_name}}</text>
						<text class="spec">{{item.sku_name}}</text>
						<view class="price-box">
							<text class="price">￥{{item.sale_price}}</text>
							<text class="number">x {{item.goods_number}}{{item.goods_unit_name}}</text>
						</view>
					</view>
				</view>
				<u-button class="mt50" type="error" @click="app.goPage('/pagesB/channel/center/index')">返回会员中心</u-button>
			</view>
			<view style="height: 50rpx;"></view>
		</scroll-view>
	</view>
</template>

<script>
	import payBox from '@/pages/public/pay/payBox';
	export default {
		components: {
			payBox
		},
		data() {
			return {
				order_id: 0,
				from: 'channel',
				payType: 'order',
				headerPosition: "fixed",
				headerTop: "0px",
				paySetting: {
					lackGoods: []
				},
			}
		},
		onLoad(options) {
			if (options.order_id < 1) {
				uni.showModal({
					title: '提示',
					content: 'ID传值错误.',
					showCancel: false,
					success: function(res) {
						if (res.confirm) {
							uni.switchTab({
								url: '/pagesB/channel/center/index'
							});
						}
					}
				});
				return false;
			}
			uni.setNavigationBarTitle({
				title: '代理订单支付'
			})
			this.order_id = parseInt(options.order_id);
			this.getOrder(options);
		},
		computed: {},
		onReady() {},
		methods: {
			getOrder(options) { //获取订单信息
				let order_id = this.order_id;
				let platform = this.app.getPlatform();
				if (uni.getStorageSync("source") == 'APP'){
					platform = 'APP';
				}
				this.$u.post('channel/api.order/getPayOrder', {
					order_id: order_id,
					platform: platform
				}).then(res => {
					this.paySetting = res.data;
				}).catch(res => {
					setTimeout(() => {
						uni.redirectTo({
							url: '/pagesB/channel/order/info?order_id=' + order_id
						});
					}, 1000);
				})
			}
		}
	}
</script>

<style lang="scss">
	.top-box {
		text-align: center;
		background-color: #FFFFFF;
		padding: 50rpx;
		width: 100%;
		z-index: 1;
		border-bottom: 1rpx solid $page-color-base;
		z-index: 99;

		.price_box {
			color: $font-color-base;
			font-size: 45rpx;
			font-weight: 600;

			.price {
				margin-left: 5rpx;
				font-size: 55rpx;
			}
		}
	}

	.scroll_view {
		height: calc(100% - 200rpx);
	}

	.lack-box {
		padding: 20rpx;

		.lack-tip {
			padding: 50rpx 0rpx;
			text-align: center;
		}

		.top-title {
			padding-bottom: 20rpx;
			border-bottom: 1rpx dashed #cccccc;
		}

		.g-item {
			display: flex;
			margin: 20rpx 30rpx;

			image {
				flex-shrink: 0;
				display: block;
				width: 140rpx;
				height: 140rpx;
				border-radius: 4rpx;
			}

			.right {
				flex: 1;
				padding-left: 24rpx;
				overflow: hidden;
			}

			.title {
				display: block;
				font-size: 30rpx;
				color: $font-color-dark;
				height: 100rpx;
			}

			.spec {
				font-size: 26rpx;
				color: $font-color-light;
			}

			.price-box {
				float: right;
				align-items: center;
				font-size: 32rpx;
				color: $font-color-dark;
				padding-top: 10rpx;

				.price {
					margin-bottom: 4rpx;
				}

				.number {
					font-size: 26rpx;
					color: $font-color-base;
					margin-left: 20rpx;
				}
			}

			.step-box {
				position: relative;
			}
		}
	}
</style>
