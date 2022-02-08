<template>
	<view>
		<view v-if="cartList.length > 0" class="page-body" :class="[app.setCStyle()]">
			<!-- 地址 -->
			<view v-if="addressData.address_id>0">
				<navigator url="/pages/member/address/list?source=1&from=shop" class="address-section">
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
				<navigator url="/pages/member/address/manage?source=1&from=shop" class="address-section">
					<view class="empty-address">
						<view class="smll">
							<u-icon name="/static/public/images/icon_live.png" class="ml20 mr20" size="42"></u-icon>
							<text>{{app.langReplace('选择收货地址')}}</text>
						</view>
						<u-icon name="arrow-right" class="arrow-right" color="#333333" size="20"></u-icon>
					</view>
				</navigator>
			</view>
			<view class="goods-section">
				<!-- 商品列表 -->
				<view class="g-item" v-for="(item, index) in cartList" :key="item.rec_id">
					<image :src="baseUrl+item.pic"></image>
					<view class="ml10 flex">
						<view class="flex_bd">
							<view class="goods_name">{{item.goods_name}}</view>
							<view class="color-cc fs26 mt20">{{item.sku_name}}</view>
						</view>
						<view class="text-right price-box">
							<view class="price ff base-color">{{item.sale_price}}</view>
							<view v-if="item.use_integral > 0" class="fs26 mt10 base-color">+{{item.use_integral}}{{app.langReplace('积分')}}</view>
							<view class="fs30 mt10 color-99">x{{item.goods_number}}</view>
						</view>
						
					</view>
				</view>
			</view>

			<!-- 金额明细 -->
			<view class="yt-list">
				<view class="yt-list-cell">
					<text class="cell-tit clamp">{{app.langReplace('商品金额')}}</text>
					<text class="cell-tip ff base-color">{{goods_total}}</text>
				</view>
				<!-- 优惠明细 -->
				<view v-if="showBonus == true" class="yt-list" @click="toggleBonusSelect()">
					<view class="yt-list-cell" style="padding: 0rpx;">
						<view class="cell-icon">
							券
						</view>
						<text class="cell-tit clamp ">{{app.langReplace('优惠券')}}</text>
						<view v-if="bonusMoney > 0">
							<text class="cell-tip ff base-color">- {{bonusMoney}}</text>
						</view>
						<view v-else class="cell-tip">
							<text class=" color-99 fs30">
								{{bonusTip}}
							</text>
							<u-icon name="arrow-right" class="arrow-right" color="#999999" size="22"></u-icon>
						</view>
					</view>
				</view>
				<!-- 运费 -->
				<view class="yt-list-cell">
					<text class="cell-tit clamp">{{app.langReplace('运费')}}</text>
					<text v-if="shipping_fee_ing == 0" class="cell-tip ff base-color">{{shipping_fee}}</text>
					<text v-else class="cell-tip base-color">{{app.langReplace('运费计算中')}}...</text>
				</view>
				<view class="yt-list-cell desc-cell">
					<text class="cell-tit clamp">{{app.langReplace('备注')}}</text>
					<input class="desc" type="text" v-model="desc" :placeholder="app.langReplace('请输入备注内容')" placeholder-class="placeholder" />
				</view>
			</view>

			<!-- 底部 -->
			<view class="footer">
				<view class="price-content base-color">
					<text class="fs28">{{app.langReplace('合计')}}:</text>
					<text class="price ff">{{order_total}}</text>
					<text v-if="integralTotal > 0" class="fs30 ml10">+{{integralTotal}}{{app.langReplace('积分')}}</text>
				</view>
				<text class="submit primarybtn" :loading="isLoading" @click="submit">{{app.langReplace('确认提交')}}</text>
			</view>
			<bonusSelect  @toggleBonusSelect="toggleBonusSelect"  :bonusSelectClass="bonusSelectClass" :bonusList="bonusList"></bonusSelect>
		</view>
	</view>
</template>

<script>
	import bonusSelect from '@/pages/shop/bonus/select';
	export default {
		components: {
			bonusSelect
		},
		data() {
			return {
				shipping_fee_ing: 1,
				setting: {},
				baseUrl: this.config.baseUrl,
				isLoading: false,
				desc: '', //备注
				address_id: 0,
				addressData: {},
				recids: '',
				cartList: [],
				goods_total: 0,
				shipping_fee: 0,
				order_total: 0,
				integralTotal: 0,
				bonusSelectClass: 'none',
				used_bonus_id:0,
				showBonus:false,//setting.sys_model_shop_bonus
				bonusList:{},
				bonusMoney:0,
				bonusTip:this.app.langReplace('选择优惠券'),
			}
		},
		onLoad(options) {
			let title = this.app.langReplace('结算页');
			uni.setNavigationBarTitle({
				title
			})
			this.app.isLogin(this); //强制登陆
			this.setting = uni.getStorageSync("setting");
			this.recids = options.recids;
			this.getAddress();
			this.loadData();
			this.getBonusList();
		},
		onShow() {

		},
		methods: {
			//请求数据
			async loadData() {
				this.$u.post('shop/api.flow/getList', {
					is_sel: 1,
					recids: this.recids
				}).then(res => {
					if (res.data.goodsList.length < 1) {
						uni.showModal({
							title: this.app.langReplace('提示'),
							content: this.app.langReplace('请选择下单商品.'),
							confirmText: this.app.langReplace('确认'),
							showCancel: false,
							success: function(res) {
								if (res.confirm) {
									this.app.goPage(-1);
								}
							}
						});
						return false;
					}
					let cartList = [];
					let goodsList = res.data.goodsList;
					Object.keys(goodsList).forEach(function(key) {
						cartList.push(goodsList[key]);
					})
					this.cartList = cartList;
					this.integralTotal = res.data.integralTotal;
					this.calcTotal(); //计算总价
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
				data.recids = this.recids;
				this.shipping_fee_ing = 1;
				this.$u.post('shop/api.flow/evalShippingFee', data).then(res => {
					this.shipping_fee_ing = 0;
					this.shipping_fee = res.data.shippingFee.shipping_fee;
					this.supplyerShippingFee = res.data.shippingFee.supplyerShippingFee;
					this.calcTotal();
				})
			},
			//计算总价
			calcTotal() {
				let total = 0;
				this.cartList.forEach(item => {
					total += item.sale_price * item.goods_number;
				})
				this.goods_total = total.toFixed(2);
				total = parseFloat(total) - parseFloat(this.bonusMoney) + parseFloat(this.shipping_fee);
				this.order_total = total.toFixed(2);
			},
			//获取已领取的优惠劵
			async getBonusList() {
				if (this.setting.sys_model_shop_bonus == 0){
					this.showBonus = false;
					return false;
				}
				this.showBonus = true;
				let data = {};
				data.goods_type = 1;
				data.recids = this.recids;
				this.$u.post('shop/api.bonus/getBonusListToCheckout',data).then(res => {
					this.bonusList = res.data;
				});
			},
			//优惠弹窗开关
			toggleBonusSelect(bonusMoney,used_bonus_id) {
				this.used_bonus_id = 0;
				if (this.bonusSelectClass === 'show') {
					if (bonusMoney == 0){
						this.bonusTip = this.app.langReplace('不使用优惠券');
						this.bonusMoney = 0;
					}else if (bonusMoney > 0){
						this.bonusTip = ' - '+bonusMoney;
						this.bonusMoney = bonusMoney;
						this.used_bonus_id = used_bonus_id;
					}
					this.bonusSelectClass = 'hide';
					setTimeout(() => {
						this.bonusSelectClass = 'none';
					}, 250);
					this.calcTotal();
				} else if (this.bonusSelectClass === 'none') {
					this.bonusSelectClass = 'show'
				}
			},
			//提交订单
			submit() {
				if (this.isLoading == true) {
					return false;
				}
				var data = {};
				if (this.address_id < 1) {
					uni.showToast({
						title: this.app.langReplace('请选择收货地址'),
						duration: 2000,
						icon: 'none'
					});
					return false;
				}
				data.address_id = this.address_id;
				data.is_select = 1;
				data.recids = this.recids;
				data.buy_msg = this.desc;
				data.used_bonus_id = this.used_bonus_id;
				this.isLoading = true;
				this.$u.post('shop/api.flow/addOrder', data).then(res => {
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
				max-width: 120rpx;
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
