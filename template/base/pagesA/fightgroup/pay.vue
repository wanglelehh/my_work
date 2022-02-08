<template>
	<view class="page-body" :class="[app.setCStyle()]">
		<view class="top-box" >
			<view class="price_box ff base-color">￥<text class="price ">{{paySetting.order_amount}}</text></view>
			<view class="mt10 text-center fs24 color-99">
				待支付订单金额
			</view>
			<view @click="app.goPage('order?order_id='+order_id,-1)" class="mt20 text-center fs26 color-66">查看订单</view>
		</view>
		<scroll-view class="scroll_view"  scroll-y>
			<payBox :payType="payType" :from="from" :order_id="order_id" :paySetting="paySetting"></payBox>
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
				from:'shop',
				payType: 'fgorder', //支付类型
				order_id: 0,
				headerPosition: "fixed",
				headerTop: "0px",
				paySetting: {
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
								url: '/pages/center/index'
							});
						}
					}
				});
				return false;
			}
			this.order_id = parseInt(options.order_id);
			uni.setNavigationBarTitle({
			　　title:'商城订单支付'
			})
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
				this.$u.post('shop/api.order/getPayOrder', {
					order_id: order_id,
					platform: platform
				}).then(res => {
					this.paySetting = res.data;
				}).catch(res => {
					setTimeout(() => {
						uni.redirectTo({
							url: '/pages/shop/fightgroup/order?order_id=' + order_id
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
		padding: 60rpx;
		width: 100%;
		z-index: 1;
		z-index: 99;
		font-weight: 500;
		.price_box {
			font-size: 40rpx;
			.price {
				margin-left: 5rpx;
				font-size: 66rpx;
			}
		}
	}

	.scroll_view{
		height: calc(100% - 280rpx);
	}

</style>
