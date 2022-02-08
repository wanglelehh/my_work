<template>
	<view class="page-body" :class="[app.setCStyle()]">
		<view class="top-box" >
			<view class="price_box ff base-color">￥<text class="price ">{{paySetting.order_amount}}</text></view>
			<view class="mt10 text-center fs24 color-99">
				{{app.langReplace('待支付金额')}}
			</view>
			<view @click="app.goPage('info?order_id='+order_id,-1)" class="mt20 text-center fs26 color-66">{{app.langReplace('查看订单')}}</view>
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
				payType: 'order', //支付类型
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
					title: this.app.langReplace('提示'),
					content: this.app.langReplace('ID传值错误'),
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
			let title = this.app.langReplace('商城订单支付');
			uni.setNavigationBarTitle({
			　　title:title
			})
			this.getOrder(options);
		},
		computed: {},
		onReady() {},
		methods: {
			getOrder(options) { //获取订单信息
				let order_id = this.order_id;
				let platform = this.app.getPlatform();
				this.$u.post('shop/api.order/getPayOrder', {
					order_id: order_id,
					platform: platform
				}).then(res => {
					this.paySetting = res.data;
				}).catch(res => {
					setTimeout(() => {
						uni.redirectTo({
							url: '/pages/shop/order/info?order_id=' + order_id
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
