<template>
	<view class="page-body" :class="[app.setCStyle()]">
		<view class=" ostatus mt0">
			<!-- 订单状态 -->
			<view v-if="orderInfo.ostatus=='拼团中'" >
				<view class="smll">
					<u-icon name="/static/public/images/icon_wait.png" size="48"></u-icon>
					<text class="fs34 color-ff ml20">{{app.langReplace(orderInfo.ostatus)}}</text>
				</view>
				<view class="mt10 color-ff2">{{app.langReplace('正在拼团中，请耐心等待！')}}</view>
			</view>
			<view v-if="orderInfo.ostatus=='取消待退款'" >
				<view class="smll">
					<u-icon name="/static/public/images/icon_pay.png" size="48"></u-icon>
					<text class="fs34 color-ff ml20">{{app.langReplace(orderInfo.ostatus)}}</text>
				</view>
				<view class="mt10 color-ff2">{{app.langReplace('拼团失败，订单取消待退款，请耐心等待！')}}</view>
			</view>
			<view v-else-if="orderInfo.ostatus=='待付款'" >
				<view class="smll">
					<u-icon name="/static/public/images/icon_pay.png" size="48"></u-icon>
					<text class="fs34 color-ff ml20">{{app.langReplace(orderInfo.ostatus)}}</text>
				</view>
				<view class="mt10 color-ff2">{{app.langReplace('立即付款将你的宝贝带回家吧！')}}</view>
			</view>
			<view v-if="orderInfo.ostatus=='付款待审核'" >
				<view class="smll">
					<u-icon name="/static/public/images/icon_pay.png" size="48"></u-icon>
					<text class="fs34 color-ff ml20">{{app.langReplace(orderInfo.ostatus)}}</text>
				</view>
				<view class="mt10 color-ff2">{{app.langReplace('已提交线下支付凭证，我们将尽快审核！')}}</view>
			</view>
			<view v-else-if="orderInfo.ostatus=='付款审核失败'" >
				<view class="smll">
					<u-icon name="/static/public/images/icon_pay.png" size="48"></u-icon>
					<text class="fs34 color-ff ml20">{{app.langReplace(orderInfo.ostatus)}}</text>
				</view>
				<view class="mt10 color-ff2">{{app.langReplace('提交的线下支付凭证，审核失败，请重新提交！')}}</view>
			</view>
			<view v-else-if="orderInfo.ostatus=='待收货'" >
				<view class="smll">
					<u-icon name="/static/public/images/icon_receive.png" size="48"></u-icon>
					<text class="fs34 color-ff ml20">{{app.langReplace(orderInfo.ostatus)}}</text>
				</view>
				<view class="mt10 color-ff2">{{app.langReplace('您的商品还在打包中，请耐心等待！')}}</view>
			</view>
			<view v-else-if="orderInfo.ostatus=='已发货'" >
				<view class="smll">
					<u-icon name="/static/public/images/icon_receive.png" size="48"></u-icon>
					<text class="fs34 color-ff ml20">{{app.langReplace(orderInfo.ostatus)}}</text>
				</view>
				<view class="mt10 color-ff2">{{app.langReplace('您的商品已经火速运输中，请耐心等待！')}}</view>
			</view>
			<view v-else-if="orderInfo.ostatus=='已取消'">
				<view class="smll">
					<u-icon name="/static/public/images/icon_wrong.png" size="48"></u-icon>
					<text class="fs34 color-ff ml20">{{app.langReplace(orderInfo.ostatus)}}</text>
				</view>
			</view>
			<view v-else-if="orderInfo.ostatus=='已取消，已退款'" >
				<view class="smll">
					<u-icon name="/static/public/images/icon_wrong.png" size="48"></u-icon>
					<text class="fs34 color-ff ml20">{{app.langReplace(orderInfo.ostatus)}}</text>
				</view>
				<view class="mt10 color-ff2">{{app.langReplace('订单已取消，支付款已退回！')}}</view>
			</view>
			<view v-else-if="orderInfo.ostatus=='已完成'">
				<view class="smll">
					<u-icon name="/static/public/images/icon_right.png" size="48"></u-icon>
					<text class="fs34 color-ff ml20">{{app.langReplace(orderInfo.ostatus)}}</text>
				</view>
			</view>
		</view>
		<!-- 物流 -->
		<view v-if="shippingLog.context" class="p20 bg-white mb20 flex">
			<u-icon name="car" class="mr20" size="42"></u-icon>
			<view class="flex_bd">
				<view class="fs30">{{shippingLog.context}}</view>
				<view class="color-cc fs28">{{shippingLog.time}}</view>
			</view>
			<u-icon name="arrow-right" class="color-cc" size="32"></u-icon>
		</view>
		<!-- 地址 -->
		<view class="address-content bg-white">
			<u-icon name="/static/public/images/icon_live.png" class=" mr20" size="42"></u-icon>
			<view class="cen">
				<view class="top">
					<text class="name">{{orderInfo.consignee}}</text>
					<text class="mobile">{{orderInfo.mobile}}</text>
				</view>
				<text class="address">{{orderInfo.merger_name}} {{orderInfo.address}}</text>
			</view>
		</view>
		<view class="mt20 bg-white p20">
			<!-- 商品列表 -->
			<view v-for="(item, index) in orderInfo.goodsList" :key="item.rec_id" class="flex mt20">
				<image :src="baseUrl+item.pic" style="width:140rpx;height:140rpx;"></image>
				<view class="flex_bd ml20">
					<view class="flex">
						<view class="goods_name flex_bd">{{item.goods_name}}</view>
						<view class="ml10">
							<view><text class="fs22">￥</text>{{item.sale_price}}</view>
							<view class="text-right color-99">x{{item.goods_number}}</view>
						</view>
					</view>
					<view class="flex mt10">
						<view v-if="item.sku_id > 0" class="color-cc fs26">{{item.sku_name}}</view>
						<view class="text-right flex_bd">
							<text v-if="item.goods_number - item.after_sale_num == 0" class="fs26 b-all br30 border-color base-color ptm5 plr10">{{app.langReplace('查看售后')}}</text>
							<text v-else-if="orderInfo.isAfterSale == 1" class="fs26 b-all br30 border-color base-color ptm5 plr10">{{app.langReplace('申请售后')}}</text>
						</view>
					</view>
				</view>
			</view>
		</view>
		
		<view class="mt10 mb20 bg-white p20 fs28">
			<view class="smll">
				<text class="bg-base color-ff plr10 ptm5 mr10">{{fgInfo.success_num}} {{app.langReplace('人团')}}</text>
				<view v-if="orderInfo.ostatus=='待付款'">
					<text class="base-color">{{app.langReplace('待付款')}}，</text>{{app.langReplace('剩余时间')}}：<text class="color-red">{{liveCountdown}}</text>
				</view>
				<view v-else-if="fgInfo.status == 1">
					<text class="base-color">{{app.langReplace('还差')}}：{{fgInfo.last_num}}，</text>{{app.langReplace('剩余时间')}}：<text class="color-red">{{liveCountdown}}</text>
				</view>
				<view v-else-if="fgInfo.status == 2">
					<text class="base-color">{{app.langReplace('拼团满团，待完成支付')}}，</text>{{app.langReplace('剩余时间')}}：<text class="color-red">{{liveCountdown}}</text>
				</view>
				<view v-else-if="fgInfo.status == 3">
					<text class="base-color">{{app.langReplace('拼团成功')}}</text>
				</view>
				<view v-else-if="fgInfo.status == 9">
					<text class="base-color">{{app.langReplace('拼团失败')}}</text>
				</view>
			</view>
			<view  class="clearfix">
				<view  v-for="(item, index) in fgInfo.order" :key="index" class="fguser border-color">
					<view class="img">
						<image :src="(item.headimgurl?baseUrl+item.headimgurl:'/static/public/images/headimgurl.jpg')"></image>
						<text v-if="index == 0" class="tip color-ff bg-base fs24 w100 text-center">{{app.langReplace('团长')}}</text>
					</view>
					<view class="nick_name">{{item.nick_name}}</view>
				</view>
			</view>
		</view>
		

		<!-- 金额明细 -->
		<view class="yt-list">
			<view class="yt-list-cell ">
				<text class="cell-tit clamp">{{app.langReplace('商品金额')}}</text>
				<text class="cell-tip money-label ff fs32 base-color">{{orderInfo.goods_amount}}</text>
			</view>

			<view v-if="orderInfo.purchase_type != 1" class="yt-list-cell">
				<text class="cell-tit clamp">{{app.langReplace('运费')}}</text>
				<text class="cell-tip money-label ff fs32 base-color">{{orderInfo.shipping_fee}}</text>
			</view>
			<view v-if="setting.sys_model_shop_bonus == 1" class="yt-list-cell">
				<text class="cell-tit clamp">{{app.langReplace('优惠券')}}</text>
				<text class="mr10">-</text><text class="cell-tip money-label ff fs32 ">{{orderInfo.use_bonus}}</text>
			</view>
			<view class="yt-list-cell ">
				<text class="cell-tit clamp">{{app.langReplace('实付款')}}</text>
				<text class="cell-tip ff fs40 color-base money-label base-color">{{orderInfo.order_amount}}</text>
			</view>
			<view class="yt-list-cell desc-cell">
				<text class="cell-tit clamp">{{app.langReplace('备注')}}</text>
				<view class="fs28">{{orderInfo.buyer_message}}</view>
			</view>
		</view>


		<view v-if="orderInfo.shipping_id > 0" class="bg-white">
			<view class="yt-list-cell">
				<text class="cell-tit clamp">{{app.langReplace('快递公司')}}</text>
				<text class="cell-tip">{{orderInfo.shipping_name}}</text>
			</view>
			<view class="yt-list-cell">
				<text class="cell-tit clamp">{{app.langReplace('快递单号')}}</text>
				<text class="cell-tip">{{orderInfo.invoice_no}}</text>
			</view>
			<view class="yt-list-cell">
				<text class="cell-tit clamp">{{app.langReplace('发货时间')}}</text>
				<text class="cell-tip">{{orderInfo._shipping_time}}</text>
			</view>
		</view>
		<!-- 金额明细 -->
		<view class="yt-list mt20">
			<view class="yt-list-cell">
				<text class="cell-tit clamp">{{app.langReplace('订单编号')}}</text>
				<text class="cell-tip">{{orderInfo.order_sn}}</text>
			</view>
			<view class="yt-list-cell">
				<text class="cell-tit clamp">{{app.langReplace('添加时间')}}</text>
				<text class="cell-tip">{{orderInfo._add_time}}</text>
			</view>

			<view v-if="orderInfo.cancel_time > 0" class="yt-list-cell">
				<text class="cell-tit clamp">{{app.langReplace('取消时间')}}</text>
				<text class="cell-tip">{{orderInfo._cancel_time}}</text>
			</view>

			<view v-if="orderInfo.pay_status != 0">
				<view class="yt-list-cell">
					<text class="cell-tit clamp">{{app.langReplace('支付方式')}}</text>
					<text class="cell-tip">{{orderInfo.pay_name}}</text>
				</view>
				<view v-if="orderInfo.pay_code == 'offline'">
					<view class="yt-list-cell">
						<text class="cell-tit clamp">{{app.langReplace('上传时间')}}</text>
						<text class="cell-tip">{{orderInfo._pay_time}}</text>
					</view>
					<view class="yt-list-cell ">
						<text class="cell-tit clamp">{{app.langReplace('上传凭证')}}</text>
					</view>
					<view class="yt-list-cell">
						<view class="offlineimg_item" v-for="(pic, index) in orderInfo.offlineimg" :key="index">
							<image :src="baseUrl+pic" @click="app.showImg(offlineimgList,index)"></image>
						</view>
					</view>
					<view v-if="orderInfo.pay_status == '11'" class="yt-list-cell ">
						<text class="cell-tit clamp">{{app.langReplace('审核失败')}}</text>
						<text class="cell-tip color-baseb">{{orderInfo.transaction_id}}</text>
					</view>
				</view>
				<view v-else>
					<view class="yt-list-cell">
						<text class="cell-tit clamp">{{app.langReplace('支付时间')}}</text>
						<text class="cell-tip">{{orderInfo._pay_time}}</text>
					</view>
					<view v-if="orderInfo.transaction_id != ''" class="yt-list-cell">
						<text class="cell-tit clamp">{{app.langReplace('交易流水号')}}</text>
						<text class="cell-tip">{{orderInfo.transaction_id}}</text>
					</view>
				</view>
			</view>
		</view>
		<view class="h150"></view>
		<view class="action-box p20">
			<button v-if="orderInfo.ostatus=='拼团中'" class="ml10 action-btn recom" @click="app.goPage('join?join_id='+orderInfo.pid)">{{app.langReplace('邀请好友')}}</button>
			<button v-if="orderInfo.invoice_no" class="ml10 action-btn" @click="app.goPage('/pages/shop/order/shippingLog?order_id='+order_id)">{{app.langReplace('查看物流')}}</button>
			<button v-if="orderInfo.isReview == 1" class="ml10 action-btn recom" @click="">{{app.langReplace('立即评论')}}</button>
			<button v-if="orderInfo.isCancel == 1" class="ml10 action-btn" @click="orderAction('cancel')">{{app.langReplace('取消')}}</button>
			<button v-if="orderInfo.isPay == 1" class="ml10 action-btn recom" @click="app.goPage('pay?order_id='+order_id)">{{app.langReplace('立即支付')}}</button>
			<button v-if="orderInfo.isSign == 1" class="ml10 action-btn recom" @click="orderAction('sign')">{{app.langReplace('确认签收')}}</button>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				baseUrl: this.config.baseUrl,
				setting: {},
				order_id: 0,
				orderInfo: {},
				fgInfo:{},
				remark: '',
				shipping_id: 0,
				invoice_no: '',
				offlineimgList: [],
				liveCountTimes:'',
				liveCountdown:'',
				shippingLog:[]
			}
		},
		onLoad(options) {
			let title = this.app.langReplace('拼单详情');
			uni.setNavigationBarTitle({
			　　title:title
			})
			let order_id = parseInt(options.order_id);
			if (isNaN(order_id) == true || order_id < 1) {
				this.app.showModal(this.app.langReplace('ID传值错误'), -1);
				return false;
			}
			this.order_id = order_id;
			this.setting = uni.getStorageSync("setting");
		},
		onShow() {
			this.getOrderInfo();
		},
		methods: {
			getOrderInfo() {
				this.$u.post('fightgroup/api.order/info', {
					order_id: this.order_id
				}).then(res => {
					this.orderInfo = res.data.orderInfo;
					this.fgInfo = res.data.fgInfo;
					let that = this;
					this.orderInfo.offlineimg.forEach(url => {
						that.offlineimgList.push(that.config.baseUrl + url);
					})
					if (typeof(res.data.downDate) != ''){
						this.getLiveTimeCount(res.data.downDate);
					}
					if (this.orderInfo.invoice_no){
						this.getShippingLog();
					}
				}).catch(res => {})
			},
			orderAction(type) {
				let that = this;
				let tip = '';
				if (type == 'cancel') {
					tip = this.app.langReplace('确定取消订单？');
				} else if (type == 'sign') {
					tip = this.app.langReplace('确认已收到商品？操作后无法撤回.');
				}
				uni.showModal({
					title: this.app.langReplace('提示'),
					confirmText: this.app.langReplace('确定'),
					cancelText: this.app.langReplace('取消'),
					content: tip,
					showCancel: true,
					success: function(res) {
						if (res.confirm) {
							that.$u.post('shop/api.order/action', {
								order_id: that.order_id,
								type: type
							}).then(res => {
								that.getOrderInfo();
							})
						}
					}
				})
			},
			//获取物流信息
			getShippingLog(){
				this.$u.post('shop/api.shipping/getLog', {
					order_id: this.order_id,
					nottip: true
				}).then(res => {
					if (res.code == 1){
						this.shippingLog = res.data[0];
					}
				})
			},
			getLiveTimeCount(startTime){
				let that = this;
				if (typeof(this.liveCountTimes) != 'undefined'){
					clearInterval(this.liveCountTimes);
				}
				this.liveCountTimes=setInterval(()=>{
					let nowTime = new Date().getTime();
					let liveCountdown = '';
					//注：不论安卓还是ios，请将时间如 2020-02-02 20:20:20 转化为 2020/02/02 20:20:20 这种形式后再使用，否则无法转换，如下转换即可↓
					let transedPreTime= startTime.replace(/-/g,'/') //这里转化时间格式为以/分隔形式
					let preTime = new Date(transedPreTime).getTime()
					let obj = null;
					if(preTime - nowTime > 0){
						let time = (preTime - nowTime) / 1000;
						if (time > 0){
							let day = parseInt(time / (60 * 60 * 24));
							let hou = parseInt(time % (60 * 60 * 24) / 3600);
							let min = parseInt(time % (60 * 60 * 24) % 3600 / 60);
							let sec = parseInt(time % (60 * 60 * 24) % 3600 % 60);
							let msec = Math.floor(time * 1000 % 1000 / 100);
							obj = {
								day: day<10?'0'+day:day,
								hou: hou<10?'0'+hou:hou,
								min: min<10?'0'+min:min,
								sec: sec<10?'0'+sec:sec
							};
							if (day > 0){
								liveCountdown += obj.day + '天';
							}
							if (hou > 0 ){
								liveCountdown += obj.hou + ':';
							}
							liveCountdown += obj.min + ':' + obj.sec;
							liveCountdown += ':'+ msec;
						}else{
							liveCountdown = '已结束';
						}
					}else{
						liveCountdown = '已结束';
					}  
					that.liveCountdown = liveCountdown;
				},100)
			},
		}
	}
</script>

<style lang="scss">
	page {
		background: $page-color-base;
		padding-bottom: 100rpx;
	}

	.ostatus {
		padding: 50rpx 30rpx;
		color: #ffffff;
		background: linear-gradient(131deg, #FF4261 0%, #FE6584 98%);
		background-size: 100%;
	}
	.fguser{
		width: 160rpx;
		height: 150rpx;
		margin: 0rpx 8rpx;
		margin-top: 20rpx;
		float: left;
		justify-content: space-around;
		align-items: center;
		.img{
			width: 120rpx;
			height: 120rpx;
			margin: 0px auto;
			overflow: hidden;
			border-radius: 50%;
			position: relative;
			border: 1rpx dotted;
			display:flex;
		}
		.nick_name{
			text-align: center;
			width: 100%;
			overflow: hidden;
			white-space:nowrap;
		}
		image{
			width: 100%;
		}
		.tip{
			position: absolute;
			bottom: 0rpx;
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
			font-size: 30rpx;
			color: $font-color-dark;
		}

		.name {
			margin-right: 24rpx;
			font-weight: 600;
		}

		.address {
			margin-top: 10rpx;
			font-size: 26rpx;
			color: $font-color-light;
		}


	}

	.money-label {
		&::before {
			content: '￥';
			font-size: 24rpx;
		}
	}
	.goods_name {
		display: block;
		font-size: 28rpx;
		color: $font-color-dark;
		line-height: 45rpx;
		height: 90rpx;
		overflow: hidden;
		text-overflow: ellipsis;
		display: -webkit-box;
		-webkit-line-clamp: 2;
		-webkit-box-orient: vertical;
	}
	
	.yt-list {
		background: #fff;
	}

	.yt-listyt-list {
		margin: 20rpx;
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
			font-size: 28rpx;
			color: $font-color-dark;

			&.disabled {
				color: $font-color-light;
			}

			&.active {
				color: $base-color;
			}

			&.red {
				color: $font-color-red;
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

	.action-box {
		width: 100%;
		background-color: #ffffff;
		position: fixed;
		left: 0;
		right: 0;
		bottom: 0;
	}
</style>
