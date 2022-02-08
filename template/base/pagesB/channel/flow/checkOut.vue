<template>
	<view class="page-body">
		<view v-if="purchaseType == 1" class="purchase_box">
			<view class="type">云仓订单</view>
			<text class="tip mt20">当前您选择的产品订单结算后将以托管的方式存放到品牌仓库</text>
		</view>
		<view v-else class="purchase_box">
			<view class="type">{{purchaseType == 2?'现货订单':'提货订单'}}</view>
			<text class="tip mt20">当前您选择的产品将以发货的方式送达，请留意订单的发货状态</text>
		</view>
		<!-- 地址 -->
		<view v-if="purchaseType != 1">
			<view v-if="addressData.address_id>0">
				<navigator url="/pages/member/address/list?source=1&from=channel" class="address-section">
					<view  class="address-content">
						<u-icon name="map" class="ml20 mr20" color="#ccc" size="52"></u-icon>
						<view class="cen">
							<view class="top">
								<text class="name">{{addressData.consignee}}</text>
								<text class="mobile">{{addressData.mobile}}</text>
							</view>
							<text class="address">{{addressData.merger_name}} {{addressData.address}}</text>
						</view>
						<u-icon name="arrow-right" class="arrow-right" color="#ccc" size="42"></u-icon>
					</view>
				</navigator>
			</view>
			<view v-else>
				<navigator url="/pages/member/address/manage?source=1&from=channel" class="address-section">
					<view class="empty-address">
						添加收货地址<u-icon name="arrow-right-double"></u-icon>
					</view>
				</navigator>
			</view>
		</view>
		<view class="goods-section">
			<!-- 商品列表 -->
			<view class="g-item"  v-for="(item, index) in cartList" :key="item.rec_id">
				<image :src="baseUrl+item.pic"></image>
				<view class="right">
					<text class="title clamp">{{item.goods_name}}</text>
					<text class="spec">{{item.sku_name}}</text>
					<view class="price-box">
						<text class="price">￥{{item.sale_price}}</text>
						<text class="number">x {{item.goods_number}}{{item.goods_unit_name}}</text>
					</view>
				</view>
			</view>
		</view>

		<!-- 金额明细 -->
		<view class="yt-list">
			<view class="yt-list-cell b-b">
				<text class="cell-tit clamp">商品金额</text>
				<text class="cell-tip">￥{{goods_total}}</text>
			</view>
			
			<view  v-if="purchaseType != 1" class="yt-list-cell b-b">
				<text class="cell-tit clamp">运费</text>
				<text v-if="shipping_fee_ing == 0" class="cell-tip color-baseb">￥{{shipping_fee}}</text>
				<text v-else class="cell-tip color-baseb">运费计算中...</text>
			</view>
			<view  v-if="purchaseType != 1" class="yt-list-cell desc-cell">
				<text class="cell-tit clamp">备注</text>
				<input class="desc" type="text" v-model="desc" placeholder="建议备注前与供货方沟通确认" placeholder-class="placeholder" />
			</view>
		</view>
		
		<!-- 底部 -->
		<view class="footer">
			<view class="price-content">
				<text>实付款</text>
				<text class="price-tip">￥</text>
				<text class="price">{{order_total}}</text>
			</view>
			<text class="submit" :loading="isLoading" @click="submit">提交订单</text>
		</view>
		

	</view>
</template>

<script>
	export default {
		data() {
			return {
				shipping_fee_ing: 1,
				baseUrl:this.config.baseUrl,
				isLoading:false,
				desc: '', //备注
				address_id:0,
				addressData: {},
				purchaseType:0,
				rec_id:'',
				cartList:[],
				goods_total:0,
				shipping_fee:0,
				order_total:0,
			}
		},
		onLoad(options){
			if (options.purchaseType < 0 || options.purchaseType > 3){
				uni.showModal({
				    title: '提示',
				    content: '进货类型错误.',
					showCancel:false,
				    success: function (res) {
				        if (res.confirm) {
							uni.switchTab({url: '/pagesB/channel/center/index'});
				        } 
				    }
				});
				return false;
			}
			this.purchaseType = options.purchaseType;
			this.rec_id = options.rec_id;
			if (this.purchaseType > 1){
				this.getAddress();
			}
			this.loadData();
			
		},
		methods: {
			//请求数据
			async loadData(){
				this.$u.post('channel/api.flow/getCartList',{purchaseType:this.purchaseType,is_select:1,rec_id:this.rec_id}).then(res => {
					if (res.data.cartList.length < 1){
						uni.showModal({
						    title: '提示',
						    content: '没找到相关商品信息.',
							showCancel:false,
						    success: function (res) {
						        if (res.confirm) {
									uni.navigateBack();
						        } 
						    }
						});
						return false;
					}
					this.cartList = res.data.cartList;
					this.calcTotal();  //计算总价
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
				data.purchaseType = this.purchaseType;
				data.address_id = this.address_id;
				data.rec_id = this.rec_id;
				this.shipping_fee_ing = 1;
				this.$u.post('channel/api.flow/evalShippingFee', data).then(res => {
					this.shipping_fee_ing = 0;
					this.shipping_fee = res.data.shippingFee.shipping_fee;
					this.calcTotal();
				})
			},
			//计算总价
			calcTotal(){
				let total = 0;
				this.cartList.forEach(item=>{
					total += item.sale_price * (item.goods_number * item.convert_unit_val);
				})
				
				this.goods_total = total.toFixed(2);
				if (this.purchaseType == 3){//提货单只计算运费
					this.order_total = this.shipping_fee;
				}else{
					total = parseFloat(total) + parseFloat(this.shipping_fee);
					this.order_total = total.toFixed(2);
				}
			},
			//提交订单
			submit(){
				if (this.isLoading == true){
					return false;
				}
				var data = {};
				data.purchaseType = this.purchaseType;
				if (data.purchaseType != 1){
					if (this.address_id < 1){
						uni.showToast({
						    title: '请选择收货地址.',
						    duration: 2000,
							icon:'none'
						});
						return false;
					}
					data.address_id = this.address_id;
				}
				data.is_select = 1;
				data.rec_id = this.rec_id;
				data.buyer_message = this.desc;
				this.isLoading = true;
				this.$u.post('channel/api.flow/addOrder',data).then(res => {
					let order_id = res.data.order_id;
					if (this.purchaseType == 3){
						uni.redirectTo({
							url: `/pagesB/channel/order/info?order_id=${order_id}`
						});
					}else{
						uni.redirectTo({
							url: `/pagesB/channel/order/pay?order_id=${order_id}`
						});
					}
				}).catch(res=>{
					this.isLoading = false;
				})
				
			},
			stopPrevent(){}
		}
	}
</script>

<style lang="scss">
	page {
		background: $page-color-base;
		padding-bottom: 100rpx;
	}
	
	.purchase_box {
		background-color: #FFFFFF;
		padding: 20rpx;
		margin-bottom: 20rpx;
	
		.type {
			background-color: #000000;
			width: 180rpx;
			color: #FFFFFF;
			padding-left: 50rpx;
			background-image: url(/pagesB/static/channel/images/icon_purchase.png);
			background-size: 25rpx;
			background-repeat: no-repeat;
			background-position: 10rpx center;
			border-radius: 10rpx;
			font-size: 26rpx;
			line-height: 50rpx;
		}
	
		.tip {
			display: block;
			color: #999999;
			font-size: 20rpx;
		}
	}
	.address-section {
		padding: 30rpx 0;
		background: #fff;
		position: relative;
		.empty-address{
			text-align: center;
			line-height: 80rpx;
			font-size: 32rpx;
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
			font-size: 34rpx;
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
			top:40%;
		}
	}

	.goods-section {
		margin-top: 16rpx;
		padding-top: 10rpx;
		background: #fff;
		padding-bottom: 1px;

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
				line-height: 36rpx;
			}

			.spec {
				font-size: 26rpx;
				color: $font-color-light;
			}

			.price-box {
				float: right;
				align-items: center;
				font-size: 30rpx;
				color: $font-color-dark;
				padding-top: 10rpx;

				.price {
					margin-bottom: 4rpx;
				}
				.number{
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
	.yt-list {
		margin-top: 16rpx;
		background: #fff;
	}

	.yt-list-cell {
		display: flex;
		align-items: center;
		padding: 10rpx 30rpx 10rpx 40rpx;
		line-height: 70rpx;
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
			font-size: 26rpx;
			color: $font-color-light;
			margin-right: 10rpx;
		}

		.cell-tip {
			font-size: 26rpx;
			color: $font-color-dark;

			&.disabled {
				color: $font-color-light;
			}

			&.active {
				color: $base-color
			}
			&.red{
				color: $base-color
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
	
	.footer{
		position: fixed;
		left: 0;
		bottom: 0;
		z-index: 995;
		display: flex;
		align-items: center;
		width: 100%;
		height: 90rpx;
		justify-content: space-between;
		font-size: 30rpx;
		background-color: #fff;
		z-index: 998;
		color: $font-color-base;
		box-shadow: 0 -1px 5px rgba(0,0,0,.1);
		.price-content{
			padding-left: 30rpx;
		}
		.price-tip{
			color: $base-color;
			margin-left: 8rpx;
		}
		.price{
			font-size: 36rpx;
			color: $base-color;
		}
		.submit{
			display:flex;
			align-items:center;
			justify-content: center;
			width: 280rpx;
			height: 100%;
			color: #fff;
			font-size: 32rpx;
			background-color: $base-color
		}
	}
	

</style>
