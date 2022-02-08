<template>
	<view class="card-box">
		<view class="select_pay_box ">
			<view class="title b-tottom p20">
				<text class="tip">{{app.langReplace('请选择支付方式')}}</text>
			</view>

			<view v-for="(item,index) in paySetting.payList" :key="index" @click="pay_code=item.pay_code">
				<view class="space-between p20 pt30 pb30 b-tottom">
					<view class="smll">
						<view class="img mr20">
							<u-image :src="payImgList[item.pay_code]" mode="widthFix"></u-image>
						</view>
						<view class="fs28">{{item.pay_name}}</view>
						<view class="money  ff">
							<view v-if="item.money >= 0">
							（{{app.langReplace('剩余')}}<text class="fs24 base-color">￥</text>
								<text class="fs40 base-color">{{item.money}}</text> ）
							</view>
						</view>
					</view>

					<view v-if="pay_code == item.pay_code">
						<view class="item_icon bg-base" >
							<u-icon class="u-icon" name="checkbox-mark" color="#FFFFFF" size="26"></u-icon>
						</view>
					</view>
					<view v-else class="check-defaul"></view>
				</view>
			</view>
		</view>
		<view v-if="pay_code=='offline'" class="mt20">
			<view class="fs30 color-baseb" v-if="paySetting.offline.is_sys == 0">
				线下转帐给上级代理，以下为上级代理的收款信息
			</view>
			<view class="offline_box " v-if="paySetting.offline.weixin_usd == 1">
				<view class="title">
					<u-image class="icon" src="/static/public/images/pay/weixin.png" shape="circle"></u-image>微信转帐
				</view>
				<view class="info">
					<view>帐户姓名：{{paySetting.offline.weixin_name}}</view>
					<view>微信号：{{paySetting.offline.weixin_account}}</view>
				</view>
			</view>
			<view class="mt20 offline_box" v-if="paySetting.offline.alipay_usd == 1">
				<view class="title alipay">
					<u-image class="icon" src="/static/public/images/pay/alipay.png" shape="circle"></u-image>支付宝转帐
				</view>
				<view class="info">
					<view>帐户姓名：{{paySetting.offline.alipay_name}}</view>
					<view>支付宝帐户：{{paySetting.offline.alipay_account}}</view>
				</view>
			</view>
			<view v-if="paySetting.offline.bank_usd == 1" class="mt20  offline_box">
				<view class="title bank">
					<u-image class="icon" src="/static/public/images/pay/bank.png" shape="circle"></u-image>银行转帐
				</view>
				<view class="fs32  p20">
					<view>开户银行：{{paySetting.offline.bank_name}}</view>
					<view>开户支行：{{paySetting.offline.bank_subbranch}}</view>
					<view>持卡人姓名：{{paySetting.offline.bank_user_name}}</view>
					<view>银行卡号：{{paySetting.offline.bank_card_number}}</view>
				</view>
			</view>
			<view v-if="paySetting.offline.is_usd == 1" class=" bg-white post—box p20">
				<view class="title">请提交付款凭证：</view>
				<view class="mt20">
					<u-upload ref="upload" @on-change="uploaded" @on-remove="removeImg" :action="upAction" max-count="6"></u-upload>
				</view>
				<button class="primarybtn mt20" type="warning"  :loading="isLoading" @click="postOffLine">立即提交</button>
			</view>
			<view v-else class="p40 mt20 text-center base-color">上级未设置线下收款信息</view>
		</view>
		<button v-if="pay_code!='' && pay_code!='offline'" class="mt20 primarybtn" type="warning"  :loading="isLoading"
		 @click="pay">{{app.langReplace('立即支付')}}</button>
	</view>
</template>


<script>
	export default {
		name: "payBox",
		props: {
			paySetting: {
				type: Object,
				default: function() {
					return {};
				}
			},
			payType: {
				type: String,
				default: ''
			},
			from: {
				type: String,
				default: ''
			},
			order_id: {
				type: Number,
				default: 0
			},
			model: {
				type: Object,
				Object,
				default: function() {
					return {};
				}
			},
		},
		data() {
			return {
				baseUrl: this.config.baseUrl,
				isLoading: false,
				searchText: '',
				showSelectBox: true,
				pay_code: '',
				upAction: this.config.upImageUrl,
				fileList: [],
				payImgList: {
					balance: '/static/public/images/pay/balance.png',
					weixin: '/static/public/images/pay/weixin.png',
					appWeixinPay: '/static/public/images/pay/weixin.png',
					miniAppPay: '/static/public/images/pay/weixin.png',
					alipayMobile: '/static/public/images/pay/alipay.png',
					alipayApp: '/static/public/images/pay/alipay.png',
					offline: '/static/public/images/pay/bank.png',
					goodsMoney: '/static/public/images/pay/goodsmoney.png',
					uplevelGoodsMoney: '/static/public/images/pay/goodsmoney.png',
				},
				orderInfo: {}
			}
		},
		methods: {
			postOffLine() {
				if (this.isLoading == true) {
					return false;
				}
				if (this.fileList.length < 1) {
					this.$u.toast('请上传付款凭证.');
					return false;
				}
				this.isLoading = true;
				let order_id = this.order_id;
				let payType = this.payType;
				let from = this.from;
				let url = '';
				let data = {};

				if (payType == 'order' || payType == 'fgorder') {
					if (from == 'shop') {
						url = 'shop/api.order/postOffLinePay';
					} else if (from == 'channel') {
						url = 'channel/api.order/postOffLinePay';
					} else {
						return this.$u.toast('请求错误.');
					}
					data.order_id = order_id;
					data.fileList = this.fileList.join(',');
				} else if (payType == 'recharge') { //充值处理
					data = this.model;
					if (data.money == '' || data.money <= 0) {
						this.isLoading = false;
						return this.$u.toast('请输入充值金额.');
					}
					data.fileList = this.fileList.join(',');
					data.payCode = 'offline';
					if (from == 'member') {
						url = 'member/api.wallet/postRecharge';
					} else if (from == 'channel') {
						url = 'channel/api.wallet/postRecharge';
					} else {
						return this.$u.toast('请求错误.');
					}
				}
				this.$u.post(url, data).then(res => {
					this.isLoading = false;
					uni.redirectTo({
						url: `/pages/public/pay/paySuccess?from=${from}&payType=${payType}&order_id=${order_id}&pay_code=offline`
					});
				}).catch(res => {
					this.isLoading = false;
				})

			},
			async pay() {
				if (this.isLoading == true) {
					return false;
				}
				this.isLoading = true;

				let order_id = this.order_id;
				let from = this.from;
				let url = '';
				
				let route = '/pages/public/pay/paySuccess';
				if (this.payType=='order'){
					if (from == 'shop'){
						route = '/pages/shop/order/info';
					}else if (from == 'channel'){
						route = '/pagesB/channel/order/info';
					}
				}
				
				//充值处理，先创建充值订单，返回订单ID
				if (this.payType == 'recharge') {
					let data = this.model;
					if (data.money == '' || data.money <= 0) {
						this.isLoading = false;
						return this.$u.toast('请输入充值金额.');
					}
					data.payCode = this.pay_code;
					if (from == 'member') {
						url = 'member/api.wallet/postRecharge';
					} else if (from == 'channel') {
						url = 'channel/api.wallet/postRecharge';
					} else {
						return this.$u.toast('请求错误.');
					}
					let res = await this.$u.post(url, data).catch(res => {})
					this.isLoading = false;
					order_id = res.data.order_id;
					if (order_id < 1) return false;
				}
				let source = this.app.getPlatform();
				if (source == 'H5-WX'){
					window.location.href = this.baseUrl+'pages/public/pay/payIng?from=' + this.from + '&pay_code=' + this.pay_code + '&payType=' + this.payType +
						'&order_id=' + order_id + '&route=' + route;
				}else{
					this.app.goPage('/pages/public/pay/payIng?from=' + this.from + '&pay_code=' + this.pay_code + '&payType=' + this.payType +
						'&order_id=' + order_id + '&route=' + route,-1)
				}
					
				this.isLoading = false;
			},
			uploaded(res, index, lists) {
				var res = JSON.parse(res.data);
				this.fileList.push(res.file);
			},
			removeImg(index, lists) {
				let file = this.fileList[index];
				this.fileList.splice(index, 1);
				this.$u.post(this.config.removeImageUrl, {
					file: file
				}).then(res => {})
			}
		}
	}
</script>

<style lang="scss">
	.card-box {
		margin: 20rpx;
		border-radius: 20rpx;
		background-color: #FFFFFF;
	}

	.select_pay_box {
		.title {
			align-items: center;
			font-size: 36rpx;
			font-weight: 600;
			border-bottom: 1rpx solid $border-color-light;

			.tip {
				font-weight: normal;
				display: block;
				font-size: 26rpx;
				color: $font-color-light;
			}
		}


		.box {
			position: relative;
			height: 260rpx;
			width: calc(100% - 20rpx);
			overflow: hidden;
			text-align: center;
		}

		.img {
			width: 48rpx;
			height: 48rpx;

		}

		.item_icon {
			display: inline-block;
			width: 40rpx;
			height: 40rpx;
			border-radius: 50%;
			position: relative;
			margin: 0;
		}

		.u-icon {
			position: absolute;
			top: 6rpx;
			right: 0px;
			left: 0;
			z-index: 9;
			text-align: center;
			margin: 0 auto;
			display: flex;
			justify-content: center;
			align-items: center;
		}
	}

	.check-defaul {
		width: 40rpx;
		height: 40rpx;
		border: solid 1rpx #dddddd;
		border-radius: 50%;
	}

	.offline_box {
		line-height: 60rpx;

		.title {
			position: relative;
			padding-left: 100rpx;
			padding-top: 20rpx;
			padding-bottom: 20rpx;
			border-bottom: 1rpx solid #ccc;
			font-size: 40rpx;
			color: #FFFFFF;
			background-color: $uni-color-success;

			.icon {
				position: absolute;
				left: 20rpx;
				width: 60rpx !important;
				height: 60rpx !important;
			}

		}

		.alipay {
			background-color: $uni-color-primary;
		}

		.bank {
			background-color: $uni-color-error;
		}

		.info {
			padding: 20rpx;
			line-height: 60rpx;
			font-size: 30rpx;
		}
	}
</style>
