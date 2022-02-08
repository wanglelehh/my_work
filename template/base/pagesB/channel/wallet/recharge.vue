<template>
	<view class="page-body">
		
		<view class="p20">
			<u-form :model="model" ref="uForm" :errorType="errorType">
				<view class="form_box bg-white">
					<view class="title">{{title}}</view>
					<u-form-item prop="money" label-width="40" label="￥" :label-style="{'padding-top':'10rpx'}" class="_input">
						<u-input v-model="model.money" :disabled="isDisabledMoney" type="number" :custom-style="{'font-size':'50rpx'}" placeholder-style="font-size:30rpx;" placeholder="请输入需要充值的金额" />
					</u-form-item>
					<view class="addRemark" @click="showRemark = true"><u-icon class="u-icon" name="plus" ></u-icon>添加备注</view>
					<u-form-item v-if="showRemark" prop="remark" label-width="1"  :label-style="{'padding-top':'10rpx'}" class="_input remark_input">
						<u-input v-model="model.remark" type="text"  placeholder-style="font-size:30rpx;" placeholder="点击输入#" />
					</u-form-item>
				</view>
				<payBox :payType="payType" :from="from"  :model="model" :paySetting="paySetting"></payBox>
			</u-form>
		</view>
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
				from: 'channel',
				payType:'recharge',
				order_id:0,
				paySetting: {},
				isLoading:false,
				title:'充值',
				showRemark:false,
				isDisabledMoney:false,
				model: {
					money: '',
					remark:'',
					type:''
				},
				errorType: ['toast'],
				rules:{
					money: [{
							required: true,
							message: '请输入需要充值的金额.',
							trigger: ['change', 'blur'],
						},
						{
							type: 'number',
							message: '只能输入整数.',
							trigger: ['change', 'blur'],
						}
					]
				}
			}
		},
		onLoad(options) {
			this.model.type = options.type;
			if (this.model.type == 'earnestMoney'){
				this.title = '充值保证金';
			}else if (this.model.type == 'goodsMoney'){
				this.title = '充值货款';
			}else if (this.model.type == 'uplevelGoodsMoney'){
				this.title = '充值升级货款';
				if (options.money > 0){
					this.model.money = options.money;
					this.isDisabledMoney = true;
				}
			}
			uni.setNavigationBarTitle({
			　　title:this.title
			})
			this.getRechargePayInfo();
		},
		onShow() {
		},
		onReady() {
			this.$refs.uForm.setRules(this.rules);
		},
		methods: {
			getRechargePayInfo() { //获取充值支付选项信息
				let platform = this.app.getPlatform();
				this.$u.post('channel/api.wallet/getRechargePayInfo',{platform: platform}).then(res => {
					this.paySetting = res.data;
				}).catch(res => {
					this.app.showModal('请求错误', '/pagesB/channel/center/index', true);
				})
			}
		}
	}
</script>

<style lang="scss">
	
	.form_box {
		padding: 80rpx 40rpx;
		.title {
			font-size: 35rpx;
		}
		._input {
			border-radius: 10rpx;
			display: flex;
			background-color: $border-color-light;
			margin-top: 40rpx;
			padding: 20rpx;
			font-size: 38rpx;
		}
		.remark_input{
			margin-top: 20rpx;
		}
		.addRemark{
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
