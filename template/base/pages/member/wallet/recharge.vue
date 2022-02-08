<template>
	<view class="page-body" :class="[app.setCStyle()]">
		<u-form :model="model" ref="uForm" :errorType="errorType">
			<view class="form_box">
				<view class="p20 pt40 pb40 bg-white radius20">
					<view class="fs26 color-99">{{title}}</view>
					<u-form-item prop="money" :border-bottom="false" label-width="40" label="￥" :label-style="{'padding-top':'10rpx'}" class="_input">
						<u-input class="ff" v-model="model.money" :disabled="isDisabledMoney" type="number" :custom-style="{'font-size':'50rpx'}"
						 placeholder-style="font-size:30rpx;" :placeholder="app.langReplace('请输入需要充值的金额')" />
					</u-form-item>
				</view>

			</view>
			<payBox :payType="payType" :from="from" :model="model" :paySetting="paySetting"></payBox>
		</u-form>
	</view>
</template>

<script>
	import payBox from '@/pages/public/pay/payBox';
	export default {
		components: {
			payBox
		},
		data() {
			return {
				from: 'member',
				payType: 'recharge',
				order_id: 0,
				paySetting: {},
				isLoading: false,
				title: this.app.langReplace('充值'),
				showRemark: false,
				isDisabledMoney: false,
				model: {
					money: '',
					remark: '',
					type: ''
				},
				errorType: ['toast'],
				rules: {
					money: [{
							required: true,
							message: this.app.langReplace('请输入需要充值的金额'),
							trigger: ['change', 'blur'],
						},
						{
							type: 'number',
							message: this.app.langReplace('只能输入整数'),
							trigger: ['change', 'blur'],
						}
					]
				}
			}
		},
		onLoad(options) {
			this.model.type = options.type;
			if (options.money > 0) {
				this.model.money = options.money;
				this.isDisabledMoney = true;
			}
			
			uni.setNavigationBarTitle({
				title: this.title
			})

		},
		onShow() {
			this.app.isLogin(this); //强制登陆
			this.getRechargePayInfo();
		},
		onReady() {
			this.$refs.uForm.setRules(this.rules);
		},
		methods: {
			getRechargePayInfo() { //获取充值支付选项信息
				let platform = this.app.getPlatform();
				
				this.$u.post('member/api.wallet/getRechargePayInfo', {
					platform: platform
				}).then(res => {
					this.paySetting = res.data;
				}).catch(res => {
					//this.app.showModal('请求错误', '/pages/channel/center/index', true);
				})
			}
		}
	}
</script>

<style lang="scss">
	.form_box {
		padding: 20rpx;

		.remark_input {
			
			.info-input{
				background-color: #FFFFFF;
				padding: 10rpx 20rpx !important;
			}
		}

		.addRemark {
			margin-top: 30rpx;
			.u-icon {
				border: 1rpx solid ;
				border-radius: 50%;
				font-size: 30rpx;
				padding: 2rpx;
				margin-right: 5rpx;
				line-height: 50rpx;
			}
		}
	}
</style>
