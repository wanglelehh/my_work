<template>
	<view class="page-body" :class="[app.setCStyle()]">
		<view class="top_title space-between"  @click="showPaymentList=true">
			<view v-if="paymentList.length > 0" class="payment_info">
				<view class="mr20">到账账户</view>
				<u-image width="48rpx" height="48rpx" shape="circle" :src="payImgList[paymentInfo.payment_code]"></u-image>
				{{paymentInfo.payment_name}} <text>({{paymentInfo.payment_account}})</text>
			</view>
			<view v-else class="payment_info">
				<view class="mr20" @click="app.goPage('/pages/member/center/editReceivePay?source=withdraw')">未设置收款信息，请点击设置</view>
			</view>
			<u-icon :name="showPaymentList?'arrow-up':'arrow-down'" size="20"></u-icon>
		</view>
		
		<view class="pn20 radius20" v-if="paymentInfo.payment_code == 'wxbag'">
			<view class="flex_box bg-white" v-if="paymentInfo.is_auth == 1">
				<view class="mr10"><image width="120rpx" height="120rpx" mode="aspectFill" :src="paymentInfo.wx_headimgurl?paymentInfo.wx_headimgurl:'/static/public/images/headimgurl.jpg'"></image></view>
				<view>
					<view class="mt10">{{paymentInfo.wx_nickname}}</view>
					<view class="mt10">{{paymentInfo.wx_openid}}</view>
				</view>
			</view>
			<view class="flex_box bg-white flex_center" v-else>
				<view class="auth_btn" @click="getWxInfo()">点击此处授权微信信息</view>
			</view>
		</view>
		
		<view class="p20 radius20">
			<u-form :model="model" ref="uForm" :errorType="errorType">
				<view class="form_box bg-white">
					<view class="fs26 color-99">提现金额</view>
					<u-form-item prop="money" label-width="40" label="￥" :label-style="{'padding-top':'10rpx'}" class="money_input">
						<u-input class="ff" v-model="model.money" @blur="changeMoney" @confirm="changeMoney" :custom-style="{'font-size':'50rpx'}" placeholder-style="font-size:30rpx;" type="number" placeholder="请输入需要提现的金额" />
					    <text class="fs24 base-color">手续费{{withdrawFee}}元</text>
					</u-form-item>
					<view class="balance_money ">
						<view v-if="settings.withdraw_max_money > 0">单次最高提现：￥{{settings.withdraw_max_money}}</view>
						<view class="smll">
							可提现金额：<text class="ff color-33 font-w600 mr20">￥{{account.balance_money}}</text>
							<view class="all_btn base-color" @click="changeMoney(true);">全部提现</view>
						</view>
						
					</view>
					<view class="withdraw_tip">
						{{withdrawTip}}
					</view>
				</view>
				<view  class="p10 mt80">
					<u-button size="default"  type="primary" :loading="isLoading" class="primarybtn" @click="openPayPassWord" >立即提现</u-button>
				</view>
			</u-form>
		</view>
		<view v-if="showPaymentList" class="mask-box" @click="showPaymentList=false"></view>
		<view v-if="showPaymentList" class="payment_list_box">
			<radio-group  :size="20" @change="changePayment">
				<label class="radio_box"  v-for="(item,index) in paymentList" :key="index">
					<radio class="radio" color="#fe0079" :value="index.toString()" :checked="index == paymentIndex" />
					<view class="info">
						<u-image width="48rpx" height="48rpx" shape="circle" :src="payImgList[item.payment_code]"></u-image>
						{{item.payment_name}}<text>({{item.payment_account}})</text>
					</view>
				</label>
			</radio-group>
			<view v-if="paymentList.length <= 0" class="payment_error">
				<text>没有设置收款信息</text>
				<u-button size="default" shape="circle" type="primary" class="mt40 primarybtn"  @click="app.goPage('/pages/member/center/editReceivePay?source=withdraw')">点击前往设置</u-button>
			</view>
			<view v-else>
				<view class="payment_manage smll base-color" @click="app.goPage('/pages/member/center/editReceivePay?source=withdraw')"><u-icon class="u-icon" name="plus" ></u-icon>设置收款信息</view>
				<view class="pt50 pb30">
					<u-button size="default"  type="primary" class="primarybtn" @click="selectPayment">确认选择</u-button>
				</view>
			</view>
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
				settings:uni.getStorageSync('setting'),
				isLoading:false,
				model: {
					money: '',
					maxMoney:0,
					payment:'',
					pay_password:''
				},
				payTip:'提现扣除',
				payMoney:'0',
				payPassWordClass: 'hide',
				account:{},
				withdrawFee:0,//提现手续费
				withdrawTip:'',//提现提示
				errorType: ['toast'],
				showPaymentList:false,
				paymentIndex:0,
				_paymentIndex:0,//选择待确认支付方式
				paymentList:[],
				paymentInfo:{},
				payImgList:{
					wxpay:'/static/public/images/pay/weixin.png',
					alipay:'/static/public/images/pay/alipay.png',
					bank:'/static/public/images/pay/bank.png',
					wxbag:'/static/public/images/pay/weixin.png',
				}
				
			}
		},
		onLoad(options) {
			this._paymentIndex = 0;
			this.app.isLogin(this);//强制登陆
			if(options.code){
				setTimeout(() => {
					let openid = this.app.getWxOpenId();
					if(openid){
						this.$u.post('weixin/api.index/bindWxUser',{openid:openid}).then(res => {
						});
					}
				},1000);
			}
			
			this.getPaymentList();
			this.$u.post('member/api.wallet/getWalletInfo').then(res => {
				this.account = res.data.account;
			});
		},
		onShow() {
			
		},
		onReady() {},
		methods: {
			getPaymentList(){
				let arr = {};
				let platform = this.app.getPlatform();
				if(platform == 'H5-WX'){
					arr.platform = platform;
				}
				
				//获取收款信息
				this.$u.post('member/api.wallet/getPaymentList',arr).then(res => {
					if (res.data.paymentList.length > 0){
						this.paymentList = res.data.paymentList;
						this.paymentInfo = this.paymentList[0];
						this.model.payment = this.paymentInfo.payment_code;
					}
				});
			},
			changePayment(evt){//选择支付
				this._paymentIndex = evt.target.value;
			},
			selectPayment(){//确定使用提现方式
				this.paymentIndex = this._paymentIndex;
				this.paymentInfo = this.paymentList[this.paymentIndex];
				
				this.model.payment = this.paymentInfo.payment_code;
				this.showPaymentList = false;
			},
			changeMoney(maxMoney){//提现金额变化
				this.withdrawFee = 0;
				this.withdrawTip = '';
				this.model.maxMoney = 0;
				this.payMoney  = 0;
				if (maxMoney === true){
					this.model.maxMoney = 1;
				}else if (this.model.money <= 0){
					return false;
				}
				//获取提现手续费
				this.$u.post('member/api.withdraw/getFee',this.model).then(res => {
					this.withdrawFee = res.data.withdraw_fee;
					this.withdrawTip = res.data.tip;
					this.payMoney = res.data.real_money;
					if (maxMoney === true){
						this.model.money = res.data.show_money;
					}
				});
			},
			getWxInfo(){
				let openid = this.app.getWxOpenId();
				let that = this;
				if(openid){
					this.$u.post('weixin/api.index/bindWxUser',{openid:openid}).then(res => {
						that.getPaymentList();
					});
				}else{					
					let query = {};
					query.code = '';
					this.app.getWxOauthOpenId(this,query);
				}
			},
			openPayPassWord() {
				let setting = uni.getStorageSync('setting');
				if (setting.sys_model_pay_password != 1){
					return this.post();
				}
				if (this.payPassWordClass === 'show') {
					this.payPassWordClass = 'hide';
				} else if (this.payPassWordClass === 'hide') {
					if (this.payMoney <= 0){
						this.$u.toast('请输入需要提现的金额.');
						return false;
					}
					this.payPassWordClass = 'show';
				}
			},
			finishPayPassWord(e){
				this.model.pay_password = e;
				this.post();
			},
			post(){
				if(this.paymentInfo.payment_code == 'wxbag' && this.paymentInfo.is_auth == 0){
					this.$u.toast('请先授权微信信息.');
					return false;
				}
				this.isLoading = true;
				this.$u.post('member/api.withdraw/post',this.model).then(res => {
					if (res.code == 0){
						this.$u.toast(res.msg);
						return false;
					}
					this.isLoading = false;
					this.app.showModal(res.msg,'/pages/member/wallet/index');
				}).catch(res=>{
					this.isLoading = false;
				});
			}
		}
	}
</script>

<style lang="scss">
	.top_title {
		position: relative;
		background-color: #FFFFFF;
		padding: 0rpx 30rpx;
		display: flex;
		align-items: center;
		height: 100rpx;
		width: 100%;
		.payment_info{
			display: flex;
			align-items: center;
			.u-image {
				display: inline-block;
				margin-right: 10rpx;
			}
			text {
				color: $font-color-light;
			}
		}
		.sel_account {
			position: absolute;
			right: 20rpx;
			color: $font-color-red;
		}
	}
	.pn20{
		padding: 20rpx;
		padding-bottom: 0;
	}
	.flex_center{
		justify-content: center;
		align-items: center;
	}
	.auth_btn{
		color:#fff;
		background-color: #f00;
		padding:10rpx;
		border-radius: 10rpx;
	}
	.form_box {
		padding: 40rpx 20rpx;
		border-radius: 20rpx;
	
		.money_input {
			padding: 20rpx;
		}
		.balance_money{
			color: $font-color-light;
			margin-top: 20rpx;
		}
		.withdraw_fee{
			color: $font-color-light;
			margin-top: 20rpx;
			text{
				color: $font-color-red;
			}
		}
		.withdraw_tip{
			color: $font-color-red;
			margin-top: 20rpx;
		}
	}
	.flex_box{
		padding: 40rpx 20rpx;
		border-radius: 20rpx;
		display: flex;
	}
	.mask-box{
		top:200rpx;
	}
	.payment_list_box{
		padding: 0rpx 30rpx;
		position: absolute;
		top: 100rpx;
		background-color: #FFFFFF;
		border-top: 1rpx solid $border-color-light;
		width: 100%;
		z-index: 99;
		.payment_error{
			text-align: center;
			padding: 50rpx 0rpx;
		}
		.radio_box{
			display: block;
			padding: 30rpx 0;
			border-bottom: 1rpx solid $border-color-light;
			.info{
				align-items: center;
				display: flex;
				.u-image {
					display: inline-block;
					margin-right: 10rpx;
				}
			}
			.radio{
				float: right;
			}
		}
		.payment_manage{
			margin-top: 30rpx;
			color: $font-color-base;
			.u-icon{
				border: 1rpx solid ;
				border-radius: 50%;
				font-size: 20rpx;
				padding: 2rpx;
				margin-right: 10rpx;
				line-height: 50rpx;
			}
		}
	}
</style>
