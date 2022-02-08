<template>
	<view class="page-body">
		<view v-if="orderInfo.purchase_type == 1" class="purchase_box">
			<view class="type">云仓订单</view>
			<text class="tip mt20">当前您选择产品订单结算后将以托管的方式存放到品牌仓库</text>
		</view>
		<view v-else class="purchase_box">
			<view class="type">{{orderInfo.purchase_type==2?'现货订单':'提货订单'}}</view>
			<text class="tip mt20">当前您选择的产品将以发货的方式送达，请留意订单的发货状态</text>
		</view>
		<view class="yt-list">
			<view class="yt-list-cell b-b">
				<text class="cell-tit clamp">订单状态</text>
				<text class="cell-tip red">{{orderInfo.ostatus}}</text>
			</view>
		</view>
		<view class="goods-section">
			<!-- 商品列表 -->
			<view class="g-item" v-for="(item, index) in orderInfo.goodsList" :key="item.rec_id">
				<image :src="baseUrl+item.pic"></image>
				<view class="right">
					<text class="title ">{{item.goods_name}}</text>
					<view class="price-box">
						<text class="spec">{{item.sku_name}}</text>
						<text class="number">￥{{item.sale_price}} x {{item.goods_number}}{{item.goods_unit_name}}</text>
					</view>
				</view>
			</view>
		</view>

		<!-- 金额明细 -->
		<view class="yt-list">
			<view class="yt-list-cell b-b">
				<text class="cell-tit clamp">商品金额</text>
				<text class="cell-tip">￥{{orderInfo.goods_amount}}</text>
			</view>

			<view v-if="orderInfo.purchase_type != 1" class="yt-list-cell b-b">
				<text class="cell-tit clamp">运费</text>
				<text class="cell-tip">￥{{orderInfo.shipping_fee}}</text>
			</view>
			<view class="yt-list-cell b-b">
				<text class="cell-tit clamp">实付款</text>
				<text class="cell-tip">￥{{orderInfo.order_amount}}</text>
			</view>
			<view class="yt-list-cell desc-cell">
				<text class="cell-tit clamp">备注</text>
				<rich-text :nodes="orderInfo.buyer_message"></rich-text>
			</view>
		</view>
		<!-- 地址 -->
		<view v-if="orderInfo.purchase_type > 1">
			<view class="address-content mt20 bg-white">
				<u-icon name="map" class="ml20 mr20" color="#ccc" size="52"></u-icon>
				<view class="cen">
					<view class="top">
						<text class="name">{{orderInfo.consignee}}</text>
						<text class="mobile">{{orderInfo.mobile}}</text>
					</view>
					<text class="address">{{orderInfo.merger_name}} {{orderInfo.address}}</text>
				</view>
			</view>
		</view>
		
		<view  v-if="orderInfo.shipping_id > 0" class="bg-white">
			<view class="yt-list-cell b-b">
				<text class="cell-tit clamp">快递公司</text>
				<text class="cell-tip">{{orderInfo.shipping_name}}</text>
			</view>
			<view class="yt-list-cell b-b">
				<text class="cell-tit clamp">快递单号</text>
				<text class="cell-tip">{{orderInfo.invoice_no}}</text>
			</view>
			<view class="yt-list-cell b-b">
				<text class="cell-tit clamp">发货时间</text>
				<text class="cell-tip">{{orderInfo._shipping_time}}</text>
			</view>
		</view>
		<!-- 金额明细 -->
		<view class="yt-list">
			<view class="yt-list-cell b-b">
				<text class="cell-tit clamp">下单代理</text>
				<view class="cell-tip"><text>{{orderInfo.user_id}} - </text>{{orderInfo.user_name}}</view>
			</view>
			<view class="yt-list-cell b-b">
				<text class="cell-tit clamp">供货上级</text>
				<view class="cell-tip"><text v-if="orderInfo.supply_uid > 0">{{orderInfo.supply_uid}} - </text>{{orderInfo.supply_name}}</view>
			</view>
			<view class="yt-list-cell b-b">
				<text class="cell-tit clamp">订单编号</text>
				<text class="cell-tip">{{orderInfo.order_sn}}</text>
			</view>
			<view class="yt-list-cell b-b">
				<text class="cell-tit clamp">创建时间</text>
				<text class="cell-tip">{{orderInfo._add_time}}</text>
			</view>

			<view v-if="orderInfo.cancel_time > 0" class="yt-list-cell b-b">
				<text class="cell-tit clamp">取消时间</text>
				<text class="cell-tip">{{orderInfo._cancel_time}}</text>
			</view>

			<view v-if="orderInfo.pay_status != 0">
				<view class="yt-list-cell b-b">
					<text class="cell-tit clamp">支付方式</text>
					<text class="cell-tip">{{orderInfo.pay_name}}</text>
				</view>
				<view v-if="orderInfo.pay_code == 'offline'">
					<view class="yt-list-cell b-b">
						<text class="cell-tit clamp">上传时间</text>
						<text class="cell-tip">{{orderInfo._pay_time}}</text>
					</view>
					<view class="yt-list-cell ">
						<text class="cell-tit clamp">上传凭证</text>
					</view>
					<view class="yt-list-cell b-b">
						<view class="offlineimg_item" v-for="(pic, index) in orderInfo.offlineimg" :key="index">
							<image :src="baseUrl+pic" @click="app.showImg(offlineimgList,index)"></image>
						</view>
					</view>
					<view v-if="orderInfo.pay_status == '11'"  class="yt-list-cell ">
						<text class="cell-tit clamp">审核失败</text>
						<text class="cell-tip color-baseb">{{orderInfo.transaction_id}}</text>
					</view>
				</view>
				<view v-else>
					<view class="yt-list-cell b-b">
						<text class="cell-tit clamp">支付时间</text>
						<text class="cell-tip">{{orderInfo._pay_time}}</text>
					</view>
					<view class="yt-list-cell b-b">
						<text class="cell-tit clamp">交易流水号</text>
						<text class="cell-tip">{{orderInfo.transaction_id}}</text>
					</view>
				</view>
			</view>
		</view>
		<view class="action-box  mt20">
			<button v-if="orderInfo.invoice_no" class="ml10 action-btn" @click="app.goPage('shippingLog?order_id='+order_id)">查看物流</button>
			<button v-if="orderInfo.isCancel == 1" class="action-btn" @click="cancelOrder()">取消订单</button>
			<button v-if="orderInfo.isPay == 1" class="action-btn recom" @click="app.goPage('/pagesB/channel/order/pay?order_id='+order_id)">立即支付</button>
			<button v-if="orderInfo.isCheckPay == 1" class="action-btn recom" @click="showCheckPay=true">付款审核</button>
			<button v-if="orderInfo.isAddStock == 1" class="action-btn green" @click="app.goPage('/pagesB/channel/product/list?purchaseType='+orderInfo.purchase_type)">一键补货</button>
			<button v-if="orderInfo.isShipping == 1" class="action-btn recom" @click="showShippingBox">发货</button>
			<button v-if="orderInfo.isSign == 1" class="action-btn recom" @click="cmfSing">确认签收</button>
		</view>
		<u-popup style="z-index: 8;" v-model="showCheckPay" mode="bottom" :closeable="true">
			<view class="popup_box">
				<view class="title">付款审核</view>
				<view class="input">
					<u-input v-model="remark" type="textarea" :border="false" placeholder="请输入备注信息" />
				</view>
				<view class="action-box b-t">
					<button class="action-btn" @click="cfmCodPay(2)">拒绝付款</button>
					<button class="action-btn recom" @click="cfmCodPay(1)">确认付款</button>
				</view>
			</view>
		</u-popup>
		<u-popup style="z-index: 8;" v-model="showShipping" mode="bottom" :closeable="true">
			<view class="popup_box">
				<view class="title">发货</view>
				<view class="input" @click="shippingListShow=true">
					{{shipping_name}}
					<u-icon class="fr" name="arrow-right"></u-icon>
				</view>
				<view class="input">
					<u-input v-model="invoice_no" type="text" :border="false" placeholder="请输入快递单号" />
				</view>
				<view class="action-box b-t">
					<button class="action-btn recom" @click="postShipping">确认发货</button>
				</view>
			</view>
		</u-popup>
		<u-select v-model="shippingListShow" :list="shippinglist" @confirm="confirmShipping"></u-select>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				baseUrl:this.config.baseUrl,
				order_id: 0,
				orderInfo: {},
				showCheckPay: false,
				remark: '',
				showShipping: false,
				shippingListShow: false,
				shipping_name: '请选择快递',
				shipping_id: 0,
				shippinglist: [],
				invoice_no:'',
				offlineimgList:[],
			}
		},
		onLoad(option) {
			let order_id = parseInt(option.order_id);
			if (isNaN(order_id) == true || order_id < 1) {
				this.app.showModal('订单ID传值错误.', '/pagesB/channel/center/index', true);
				return false;
			}
			this.order_id = order_id;
		},
		onShow() {
			this.getOrderInfo();
		},
		methods: {
			getOrderInfo() {
				this.$u.post('channel/api.order/info', {
					order_id: this.order_id
				}).then(res => {
					this.orderInfo = res.data;
					let that = this;
					this.orderInfo.offlineimg.forEach(url=>{
						that.offlineimgList.push(that.config.baseUrl+url);
					})
				}).catch(res => {})
			},
			//取消订单
			cancelOrder() {
				let that = this;
				uni.showModal({
					title: '提示',
					content: '确定取消订单？',
					showCancel: true,
					confirmText: '确定取消',
					cancelText: '放弃取消',
					success: function(res) {
						if (res.confirm) {
							that.$u.post('channel/api.order/cancel', {
								order_id: that.order_id
							}).then(res => {
								that.getOrderInfo();
							})
						}
					}
				});
			},
			cfmCodPay(operate) {
				if (operate == 2 && this.remark.length < 8) {
					return this.$u.toast('拒绝付款，请填写备注原因，长度不能少于8个字符.');
				}
				let that = this;
				uni.showModal({
					title: '提示',
					content: operate == 1 ? '确认已收到款项？操作后无法撤回.' : '确定拒绝付款？操作后无法撤回.',
					showCancel: true,
					success: function(res) {
						that.showCheckPay = false;
						if (res.confirm) {
							that.$u.post('channel/api.order/cfmCodPay', {
								order_id: that.order_id,
								operate: operate
							}).then(res => {
								that.getOrderInfo();
							})
						}
					}
				});
			},
			showShippingBox() { //点击选择快递
				if (this.shippinglist.length < 1) {
					this.$u.post('channel/api.order/getShippingList').then(res => {
						this.shippinglist = res.data;
					})
				}
				this.showShipping = true;
			},
			confirmShipping(e) { //确定选择快递
				this.shipping_name = e[0].label;
				this.shipping_id = e[0].value;
			},
			postShipping(){//提交发货
				if (this.shipping_id < 1){
					return this.$u.toast('请选择快递公司.');
				}
				if (this.invoice_no.length < 5){
					return this.$u.toast('请输入快递单号.');
				}
				this.$u.post('channel/api.order/postShipping',{order_id:this.order_id,shipping_id:this.shipping_id,invoice_no:this.invoice_no}).then(res => {
					this.getOrderInfo();
				})
			},
			cmfSing(){
				let that = this;
				uni.showModal({
					title: '提示',
					content: '确认已收到商品？操作后无法撤回.',
					showCancel: true,
					success: function(res) {
						that.showCheckPay = false;
						if (res.confirm) {
							that.$u.post('channel/api.order/sign', {
								order_id: that.order_id,
							}).then(res => {
								that.getOrderInfo();
							})
						}
					}
				});
			}
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

	.address-content {
		align-items: center;
		padding: 20rpx;
		display: flex;

		.cen {
			display: flex;
			flex-direction: column;
			flex: 1;
			font-size: 28rpx;
			color: $font-color-dark;
		}

		.name {
			margin-right: 24rpx;
		}

		.address {
			margin-top: 16rpx;
			margin-right: 20rpx;
			color: $font-color-light;
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
			color: $font-color-dark;
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
				margin-bottom: 30rpx;
			}

			.price-box {
				font-size: 32rpx;
				color: $font-color-dark;
				display: flex;

				.spec {
					flex: 1;
					font-size: 26rpx;
					color: $font-color-light;
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

	.offlineimg_item {
		image {
			flex-shrink: 0;
			width: 140rpx;
			height: 140rpx;
			border-radius: 4rpx;
			margin-right: 10rpx;
		}
	}

	.popup_box {
		padding: 0rpx 20rpx;

		.title {
			display: flex;
			height: 100rpx;
			align-items: center;
			font-weight: 700;
			font-size: 32rpx;
		}

		.input {
			padding: 20rpx;
		}
	}
</style>
