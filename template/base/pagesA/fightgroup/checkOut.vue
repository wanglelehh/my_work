<template>
	<view  class="page-body" :class="[app.setCStyle()]">
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
			<view class="g-item">
				<image :src="goods_image"></image>
				<view class="right">
					<text class="title ">{{goods_name}}</text>
					<view class="color-99 fs24 flex mt20">
							<view class="flex_bd smll">
								<view>{{fg_sku_name}}</view>
								<view class="ml20 ff base-color">￥<text class="fs30">{{fg_sale_price}}</text></view>
							</view>
							<u-number-box  :long-press="false" :value="goods_number" :min="1" :step="1"
							 @change="numberChange"></u-number-box>
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
				<input class="desc" type="text" v-model="desc"  :placeholder="app.langReplace('请输入备注内容')" placeholder-class="placeholder" />
			</view>
		</view>

		<!-- 底部 -->
		<view class="footer">
			<view class="price-content base-color">
				<text class="fs28">{{app.langReplace('合计')}}:</text>
				<text class="price ff">{{order_total}}</text>
			</view>
			<text v-if="join_id == 0" class="submit primarybtn" :loading="isLoading" @click="submit">{{app.langReplace('发起拼单')}}</text>
			<text v-else-if="join_id > 0" class="submit primarybtn" :loading="isLoading" @click="submit">{{app.langReplace('参与拼单')}}</text>
		</view>
		<bonusSelect  @toggleBonusSelect="toggleBonusSelect"  :bonusSelectClass="bonusSelectClass" :bonusList="bonusList"></bonusSelect>
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
				fg_id:0,
				join_id:0,
				goods_name:{},
				fg_sku_name:'',
				goods_number:0,
				goods_image:'',
				sku_id:0,
				fg_sale_price:0,
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
				bonusSelectClass: 'none',
				used_bonus_id:0,
				is_usd_bonus:0,
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
			this.fg_id = parseInt(options.fg_id);
			this.join_id = parseInt(options.join_id);
			this.sku_id = parseInt(options.sku_id);
			this.goods_number = parseInt(options.number);
			this.setting = uni.getStorageSync("setting");
			this.getGoodsInfo();
			this.app.isLogin(this); //强制登陆
			this.getAddress();
		},
		onShow() {

		},
		methods: {
			//请求数据
			async getGoodsInfo() {
				this.$u.post('fightgroup/api.goods/info', {
					'fg_id': this.fg_id
				}).then(res => {
					this.goods_name = res.data.goods.goods_name;
					this.goods_image = this.baseUrl + res.data.goods.goods_thumb;
					if (res.data.goods.is_spec == 1){
						this.fg_sale_price = res.data.goods.sub_goods[this.sku_id].fg_sale_price;
						this.fg_sku_name = res.data.goods.sub_goods[this.sku_id].sku_name;
					}else{
						this.fg_sale_price = res.data.goods.fg_sale_price;
					}
					this.is_usd_bonus = res.data.is_usd_bonus;
					this.getBonusList();
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
				if (this.address_id < 1){
					return false;
				}
				let data = {};
				data.fg_id = this.fg_id;
				data.sku_id = this.sku_id;
				data.number = this.goods_number;
				data.address_id = this.address_id;
				data.goods_type = 2;
				this.shipping_fee_ing = 1;
				this.$u.post('shop/api.flow/evalShippingFee', data).then(res => {
					this.shipping_fee_ing = 0;
					this.shipping_fee = res.data.shippingFee.shipping_fee;
					this.supplyerShippingFee = res.data.shippingFee.supplyerShippingFee;
					this.calcTotal();
				})
			},
			//数量
			numberChange(data) {
				if (this.goods_number == data.value) {
					return false;
				}
				this.goods_number = data.value;
				this.evalShippingFee();
				this.getBonusList();
				this.calcTotal();
			},
			//计算总价
			calcTotal() {
				let total = this.fg_sale_price * this.goods_number;
				this.goods_total = total.toFixed(2);
				total = parseFloat(total) - parseFloat(this.bonusMoney) + parseFloat(this.shipping_fee);
				this.order_total = total.toFixed(2);
			},
			//获取已领取的优惠劵
			async getBonusList() {
				if (this.setting.sys_model_shop_bonus == 0 || this.is_usd_bonus == 0){
					this.showBonus = false;
					return false;
				}
				this.showBonus = true;
				let data = {};
				data.goods_type = 2;
				data.fg_id = this.fg_id;
				data.sku_id = this.sku_id;
				data.number = this.goods_number;
				this.$u.post('shop/api.bonus/getBonusListToCheckout',data).then(res => {
					this.bonusList = res.data;
				});
			},
			//优惠弹窗开关
			toggleBonusSelect(bonusMoney,used_bonus_id) {
				this.used_bonus_id = 0;
				if (this.bonusSelectClass === 'show') {
					if (bonusMoney == 0){
						this.bonusTip = '不使用优惠券';
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
						title: '请选择收货地址.',
						duration: 2000,
						icon: 'none'
					});
					return false;
				}
				data.address_id = this.address_id;
				data.buy_msg = this.desc;
				data.used_bonus_id = this.used_bonus_id;
				data.fg_id = this.fg_id;
				data.join_id = this.join_id;
				data.sku_id = this.sku_id;
				data.number = this.goods_number;
				this.isLoading = true;
				this.$u.post('fightgroup/api.flow/addOrder', data).then(res => {
					let order_id = res.data.order_id;
					uni.redirectTo({
						url: `pay?order_id=${order_id}`
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

		.g-item {
			display: flex;
			padding: 20rpx;

			image {
				flex-shrink: 0;
				display: block;
				width: 160rpx;
				height: 160rpx;
				border-radius: 10rpx;
			}

			.right {
				flex: 1;
				padding-left: 20rpx;
				overflow: hidden;
				display: flex;
				justify-content: space-between;
				flex-direction: column;
			}

			.title {
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
