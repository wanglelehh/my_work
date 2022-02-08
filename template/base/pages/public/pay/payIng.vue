<template>
	<view class="page-body" :class="[app.setCStyle(from)]">
		<view class="success-icon">
			<u-icon name="rmb" color="#FFFFFF" size="88"></u-icon>
		</view>
		<text class="tip">正在支付...</text>
		<view v-if="from == 'channel'" class="btn-group">
			<view v-if="payType == 'order'" @click="app.goPage('/pagesB/channel/order/info?order_id='+order_id,-1)" class="mix-btn primarybtn" >返回订单</view>
			<view @click="app.goPage('/pagesB/channel/center/index',-1)" class="mix-btn primarybtnB" >返回代理中心</view>
		</view>
		<view v-else>
			<view v-if="from == 'shop'" class="btn-group">
				<view v-if="payType == 'order'" @click="app.goPage('/pages/shop/order/info?order_id='+order_id,-1)" class="mix-btn  primarybtn" >返回订单</view>
				<view v-if="payType == 'fgorder'" @click="app.goPage('/pagesA/fightgroup/order?order_id='+order_id,-1)" class="mix-btn  primarybtn" >返回拼团订单</view>
			</view>
			<view @click="app.goPage('/pages/member/center/index',-1)" class="mix-btn primarybtnB" >返回会员中心</view>
			<view @click="app.goPage('/pages/shop/index/index',-1)" class="mix-btn primarybtnB" >返回首页</view>
		</view>
		<payPassWord :payPassWordClass="payPassWordClass" :payTip="payTip" :payMoney="payMoney"  @openPayPassWord="openPayPassWord"  @finishPayPassWord="finishPayPassWord"></payPassWord>
	</view>
</template>

<script>
	import payPassWord from '@/pages/member/wallet/payPassWord';
	export default {
		components: {
			payPassWord
		},
		data() {
			return {
				options:{},
				from:'',
				payType:'',
				pay_code:'',
				order_id:0,
				payTip:'',
				payMoney:'0',
				payPassWordClass: 'hide',
				pay_password:''
			}
		},
		onLoad(options) {
			// #ifdef H5
			if (options.gopay != 1 && options.pay_code == 'weixin'){
				var pages = getCurrentPages(); //当前页
				var beforePage = pages[pages.length - 2]; //上个页面
				if (typeof(beforePage) != 'undefined'){
					uni.setStorageSync("beforeUrl", '/'+beforePage.route+'?'+this.app.toParams(beforePage.options));
				} 
				window.location.href = window.location.href+'&gopay=1';//微信支付，限制支付目录，进行跳转处理
				return true;
			}
			// #endif
			this.from = options.from;
			this.payType = options.payType;
			this.order_id = options.order_id;
			this.pay_code = options.pay_code;
			this.options = options;
			this.openPayPassWord();
		},
		computed: {},
		onReady() {},
		methods: {
			openPayPassWord() {
				let setting = uni.getStorageSync('setting');
				if (setting.sys_model_pay_password != 1){
					return this.goPay();
				}
				if (this.pay_code == 'balance' || this.pay_code == 'goodsMoney' || this.pay_code == 'uplevelGoodsMoney'){
					if (this.payPassWordClass === 'show') {
						this.payPassWordClass = 'hide';
					} else if (this.payPassWordClass === 'hide') {
						this.payPassWordClass = 'show';
					}
				}else{
					 return this.goPay();
				}
				
			},
			finishPayPassWord(e){
				this.pay_password = e;
				this.goPay();
			},
			async goPay(){
				switch (this.pay_code) {
					case 'alipayMobile':
						await this.$mPayment.aliPay(this);
						break;
					  case 'alipayApp':
						await this.$mPayment.alipayApp(this);
						break;
					case 'appWeixinPay':
						await this.$mPayment.appWeixinPay(this);
						break;
					case 'weixin':
					case 'miniAppPay':
						await this.$mPayment.weixinPay(this);
						break;
					case 'balance':
						await this.$mPayment.balancePay(this);
						break;
					case 'goodsMoney':
						await this.$mPayment.goodsMoneyPay(this);
					break;
					case 'uplevelGoodsMoney':
						await this.$mPayment.uplevelGoodsMoneyPay(this);
					break;
					default:
						this.app.showModal('暂不支持.',-1);
						break;
				}
			}
		}
	}
</script>

<style lang="scss">
.page-body{
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
	}
	.success-icon{
		background-color: #fdb448;
		padding:40rpx 40rpx;
		border-radius: 50%;
		margin-bottom: 20rpx;
	}
	.tit{
		font-size: 38rpx;
		color: #303133;
	}
	.btn-group{
		padding-top: 80rpx;
	}
	.mix-btn {
		margin-top: 30rpx;
		display: flex;
		align-items: center;
		justify-content: center;
		width: 600rpx;
		height: 80rpx;
		font-size: $font-lg;
		color: #fff;
		border-radius: 10rpx;
		
	}

</style>
