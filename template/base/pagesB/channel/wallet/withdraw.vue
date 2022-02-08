<template>
	<view class="page-body">
		<view class="top_title">
			<view v-if="paymentList.length > 0" class="payment_info">
				提现至：
				<u-image width="60rpx" height="60rpx" shape="circle" :src="payImgList[paymentInfo.payment_code]"></u-image>
				{{paymentInfo.payment_name}} <text>({{paymentInfo.payment_account}})</text>
			</view>
			<view v-if="showPaymentList == false" class="sel_account" @click="showPaymentList=true">切换提现帐户</view>
		</view>
		
		<view class="p20">
			<u-form :model="model" ref="uForm" :errorType="errorType">
				<view class="form_box bg-white">
					<view class="title">提现金额</view>
					<u-form-item prop="money" label-width="40" label="￥" :label-style="{'padding-top':'10rpx'}" class="money_input">
						<u-input v-model="model.money" @blur="changeMoney" @confirm="changeMoney" :custom-style="{'font-size':'50rpx'}" placeholder-style="font-size:30rpx;" type="number" placeholder="请输入需要提现的金额" />
					</u-form-item>
					<view class="balance_money">
						<view v-if="settings.channel_withdraw_max_money > 0" class="mb20">单次最高提现：￥{{settings.channel_withdraw_max_money}}</view>
						帐户余额：<text>{{account.balance_money}}</text>
						<view class="all_btn" @click="changeMoney(true);">全部提现</view>
					</view>
					<view class="withdraw_fee">
						提现手续费：<text>{{withdrawFee}}</text>
					</view>
					<view class="withdraw_tip">
						{{withdrawTip}}
					</view>
				</view>
				<view class="p30">
					<u-button size="default" shape="circle" type="primary" :loading="isLoading" class="mt40" @click="post" >立即提现</u-button>
				</view>
			</u-form>
		</view>
		<view v-if="showPaymentList" class="mask-box" @click="showPaymentList=false"></view>
		<view v-if="showPaymentList" class="payment_list_box">
			<radio-group  :size="20" @change="changePayment">
				<label class="radio_box" v-for="(item,index) in paymentList" :key="index">
					<radio class="radio" :value="index.toString()" :checked="index == paymentIndex" />
					<view class="info">
						<u-image width="60rpx" height="60rpx" shape="circle" :src="payImgList[item.payment_code]"></u-image>
						{{item.payment_name}}<text>({{item.payment_account}})</text>
					</view>
				</label>
			</radio-group>
			<view v-if="paymentList.length <= 0" class="payment_error">
				<text>没有设置收款信息</text>
				<u-button size="default" shape="circle" type="primary" class="mt40"  @click="app.goPage('/pages/member/center/editReceivePay?source=channel_withdraw')">点击前往设置</u-button>
			</view>
			<view v-else>
				<view class="payment_manage" @click="app.goPage('/pages/member/center/editReceivePay?source=channel_withdraw')"><u-icon class="u-icon" name="plus" ></u-icon>设置收款信息</view>
				<view class="p30">
					<u-button size="default" shape="circle" type="primary" class="mt40" @click="selectPayment">确认选择</u-button>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				settings:uni.getStorageSync('setting'),
				isLoading:false,
				model: {
					money: '',
					maxMoney:0,
					payment:''
				},
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
				}
				
			}
		},
		onLoad() {
		},
		onShow() {
			this.getPaymentList();
			this.$u.post('channel/api.wallet/getWalletInfo').then(res => {
				this.account = res.data.account;
			});
		},
		onReady() {},
		methods: {
			getPaymentList(){
				//获取收款信息
				this.$u.post('channel/api.wallet/getPaymentList').then(res => {
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
				if (maxMoney === true){
					this.model.maxMoney = 1;
				}else if (this.model.money <= 0){
					return false;
				}
				//获取提现手续费
				this.$u.post('channel/api.withdraw/getFee',this.model).then(res => {
					this.withdrawFee = res.data.withdraw_fee;
					this.withdrawTip = res.data.tip;
					if (maxMoney === true){
						this.model.money = res.data.show_money;
					}
				});
			},
			post(){
				this.isLoading = true;
				this.$u.post('channel/api.withdraw/post',this.model).then(res => {
					this.isLoading = false;
					this.app.showModal(res.msg,-1);
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
		padding: 0rpx 20rpx;
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
			color: $font-color-base;
		}
	}

	.form_box {
		padding: 80rpx 40rpx;
		.title {
			font-size: 35rpx;
		}
		.money_input {
			border-radius: 10rpx;
			display: flex;
			background-color: $border-color-light;
			margin-top: 40rpx;
			padding: 20rpx;
			font-size: 38rpx;
		}
		.balance_money{
			color: $font-color-light;
			margin-top: 20rpx;
			text{
				color: $font-color-base;
			}
			.all_btn{
				color: $font-color-base;
				float: right;
			}
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
	.mask-box{
		top:200rpx;
	}
	.payment_list_box{
		padding: 0rpx 20rpx;
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
			padding: 40rpx 30rpx;
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
				border: 1rpx solid $font-color-base;
				border-radius: 50%;
				font-size: 20rpx;
				padding: 2rpx;
				margin-right: 5rpx;
				line-height: 50rpx;
			}
		}
	}
</style>
