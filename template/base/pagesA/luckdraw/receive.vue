<template>
	<view class="page-body" :class="[app.setCStyle()]">
		<!-- 地址 -->
		<view v-if="addressData.address_id>0">
			<navigator url="/pages/member/address/list?source=1&from=luckdraw" class="address-section">
				<view class="address-content">
					<u-icon name="map-fill" class="ml20 mr20 base-color" size="42" ></u-icon>
					<view class="cen">
						<view class="top">
							<text class="name">{{addressData.consignee}}</text>
							<text class="mobile fs30 font-w600">{{addressData.mobile}}</text>
						</view>
						<text class="address fs28">{{addressData.merger_name}} {{addressData.address}}</text>
					</view>
					<u-icon name="arrow-right" class="arrow-right" color="#333333" size="20"></u-icon>
				</view>
			</navigator>
		</view>
		<view v-else>
			<navigator url="/pages/member/address/manage?source=1&from=luckdraw" class="address-section">
				<view class="empty-address">
					<view class="smll">
						<u-icon name="/static/public/images/icon_live.png" class="ml20 mr20" size="42"></u-icon>
						<text>选择收货地址</text>
					</view>
					<u-icon name="arrow-right" class="arrow-right" color="#333333" size="20"></u-icon>
				</view>
			</navigator>
		</view>
		<view class="goods-section">
			<!-- 商品列表 -->
			<view class="g-item" >
				<image :src="baseUrl+prize.goods.goods_thumb"></image>
				<view class="ml10 flex">
					<view class="flex_bd">
						<view class="goods_name">{{prize.goods.goods_name}}</view>
					</view>
					<view class="text-right price-box">
						<view class="fs30 mt10 color-99">x1</view>
					</view>
					
				</view>
			</view>
		</view>

		<!-- 金额明细 -->
		<view class="yt-list">
			<view class="yt-list-cell">
				<text class="cell-tit clamp">兑换奖品</text>
				<text class="cell-tip ff base-color">{{prize.prize_name}}</text>
			</view>
			<!-- 运费 -->
			<view class="yt-list-cell">
				<text class="cell-tit clamp">运费</text>
				<text v-if="shipping_fee_ing == 0" class="cell-tip ff base-color">{{shipping_fee}}</text>
				<text v-else class="cell-tip base-color">运费计算中...</text>
			</view>
			<view class="yt-list-cell desc-cell">
				<text class="cell-tit clamp">备注</text>
				<input class="desc" type="text" v-model="desc" placeholder="请输入备注内容" placeholder-class="placeholder" />
			</view>
		</view>

		<!-- 底部 -->
		<view class="footer">
			<view class="price-content base-color">
				<text class="fs28">合计:</text>
				<text class="price ff">{{order_total}}</text>
			</view>
			<text class="submit primarybtn" :loading="isLoading" @click="submit">确认兑换</text>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				log_id:0,
				prize:[],
				shipping_fee_ing: 1,
				setting: {},
				baseUrl: this.config.baseUrl,
				isLoading: false,
				desc: '', //备注
				address_id: 0,
				addressData: {},
				goods_total: 0,
				shipping_fee: 0,
				order_total: 0,
			}
		},
		onLoad(options) {
			this.app.isLogin(this); //强制登陆
			this.log_id = options.log_id
			this.loadData();
			this.getAddress();
		},
		onShow() {

		},
		methods: {
			//请求数据
			async loadData() {
				this.$u.post('luckdraw/api.receive/getInfo',{log_id:this.log_id}).then(res => {
					this.prize = res.data;
				}).catch(res=>{
					this.app.showModal(res.msg,-1);
				})
			},
			//请求数据
			async getAddress() {
				let address_id = uni.getStorageSync("address_id");
				this.$u.post('member/api.address/getAddress', {
					address_id: address_id
				}).then(res => {
					this.addressData = res.data;
					if (this.addressData.address_id > 0) {
						this.address_id = this.addressData.address_id;
					} else {
						this.address_id = 0;
					}
					this.evalShippingFee();
				})
			},
			//计算运费
			async evalShippingFee() {
				let data = {};
				data.address_id = this.address_id;
				data.log_id = this.log_id;
				this.shipping_fee_ing = 1;
				this.$u.post('luckdraw/api.receive/evalShippingFee', data).then(res => {
					this.shipping_fee_ing = 0;
					this.shipping_fee = res.data.shippingFee.shipping_fee;
					this.supplyerShippingFee = res.data.shippingFee.supplyerShippingFee;
					this.calcTotal();
				})
			},
			//计算总价
			calcTotal() {
				let total = 0;
				total = parseFloat(total) + parseFloat(this.shipping_fee);
				this.order_total = total.toFixed(2);
			},
			
			//提交订单
			submit() {
				if (this.isLoading == true) {
					return false;
				}
				var data = {};
				if (this.address_id < 1) {
					uni.showToast({
						title: '请选择收货地址.',
						duration: 2000,
						icon: 'none'
					});
					return false;
				}
				data.address_id = this.address_id;
				data.log_id = this.log_id;
				data.buy_msg = this.desc;
				this.isLoading = true;
				this.$u.post('luckdraw/api.receive/addOrder', data).then(res => {
					let order_id = res.data.order_id;
					uni.redirectTo({
						url: `/pages/shop/order/pay?order_id=${order_id}`
					});
				}).catch(res => {
					this.isLoading = false;
				})

			},
			stopPrevent() {}
		}
	}
</script>

<style lang="scss">
	page {
		background: $page-color-base;
		padding-bottom: 100rpx;
	}


	.address-section {
		padding: 30rpx 0;
		background: #fff;
		position: relative;
		margin: 20rpx;
		border-radius: 10px;

		.empty-address {
			display: flex;
			justify-content: space-between;
			align-items: center;
			font-size: 28rpx;
			font-weight: 600;
			padding: 20rpx 0;
		}

		.address-content {
			display: flex;
			align-items: center;
		}

		.icon-shouhuodizhi {
			flex-shrink: 0;
			display: flex;
			align-items: center;
			justify-content: center;
			width: 90rpx;
			color: #888;
			font-size: 44rpx;
		}

		.cen {
			display: flex;
			flex-direction: column;
			flex: 1;
			font-size: 28rpx;
			color: $font-color-dark;
		}

		.name {
			font-size: 30rpx;
			margin-right: 24rpx;
		}

		.address {
			margin-top: 16rpx;
			margin-right: 20rpx;
			color: $font-color-light;
		}

		.icon-you {
			font-size: 32rpx;
			color: $font-color-light;
			margin-right: 30rpx;
		}

		.arrow-right {
			position: absolute;
			right: 10rpx;
			top: 40%;
		}
	}

	.goods-section {
		margin: 20rpx;
		border-radius: 10px;
		background: #fff;

		.g-header {
			display: flex;
			align-items: center;
			height: 84rpx;
			padding: 0 30rpx;
			position: relative;
		}

		.logo {
			display: block;
			width: 50rpx;
			height: 50rpx;
			border-radius: 100px;
		}

		.name {
			font-size: 30rpx;
			color: $font-color-base;
			margin-left: 24rpx;
		}

		.g-item {
			display: flex;
			padding: 20rpx;

			image {
				flex-shrink: 0;
				display: block;
				width: 140rpx;
				height: 140rpx;
				border-radius: 10rpx;
			}

			.goods_name {
				display: block;
				font-size: 28rpx;
				color: $font-color-dark;
				line-height: 40rpx;
				height: 80rpx;
				overflow: hidden;
				text-overflow: ellipsis;
				display: -webkit-box;
				-webkit-line-clamp: 2;
				-webkit-box-orient: vertical;
			}


			.price-box {
				font-size: 40rpx;
				color: $font-color-dark;
				line-height: 1;

				.price {
					font-size: 40rpx;
					line-height: 1;

					&:before {
						content: '￥';
						font-size: 24rpx;
					}
				}
			}

			.step-box {
				position: relative;
			}
		}
	}

	.yt-list {
		margin: 20rpx;
		border-radius: 10px;
		background: #fff;
	}

	.yt-list-cell {
		display: flex;
		align-items: center;
		padding: 30rpx 20rpx;
		position: relative;

		&.cell-hover {
			background: #fafafa;
		}

		&.b-b:after {
			left: 30rpx;
		}

		.cell-icon {
			height: 32rpx;
			width: 32rpx;
			font-size: 22rpx;
			color: #fff;
			text-align: center;
			line-height: 32rpx;
			background: #f85e52;
			border-radius: 4rpx;
			margin-right: 12rpx;

			&.hb {
				background: #ffaa0e;
			}

			&.lpk {
				background: #3ab54a;
			}

		}

		.cell-more {
			align-self: center;
			font-size: 24rpx;
			color: $font-color-light;
			margin-left: 8rpx;
			margin-right: -10rpx;
		}

		.cell-tit {
			flex: 1;
			font-size: 28rpx;
			color: $font-color-dark;
			margin-right: 10rpx;
		}

		.cell-tip {

			&.disabled {
				color: $font-color-light;
			}

			&.active {
				color: $font-color-base;
			}

			&.red {
				color: $font-color-base;
			}
		}

		&.desc-cell {
			.cell-tit {
				max-width: 90rpx;
			}
		}

		.desc {
			flex: 1;
			font-size: $font-base;
			color: $font-color-dark;
		}
	}

	.footer {
		position: fixed;
		left: 0;
		bottom: 0;
		z-index: 995;
		display: flex;
		align-items: center;
		width: 100%;
		height: 98rpx;
		justify-content: flex-end;
		font-size: 30rpx;
		background-color: #fff;
		z-index: 998;
		color: $font-color-base;
		padding: 0 20rpx;

		.price-content {
			padding-right: 30rpx;
		}

		.price-tip {
			color: $font-color-red;
			margin-left: 8rpx;
		}

		.price {
			font-size: 40rpx;
			&:before {
				content: '￥';
				font-size: 24rpx;
			}
		}

		.submit {
			display: flex;
			align-items: center;
			justify-content: center;
			width: 200rpx;
			height: 70rpx;
			color: #fff;
			border-radius: 40rpx;
			font-size: 28rpx;
		}
	}
</style>
