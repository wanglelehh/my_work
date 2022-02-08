<template>
	<view class="page-body">
		<view class="top-box">
			<u-image shape="circle" class="img" width="180rpx" height="180rpx" src="/pagesB/static/channel/images/title_icon/icon_02.png"></u-image>
			<text>授权查询中心</text>
		</view>
		<view class="p30">
			<view class="searchBox bg-white mt60">
				<u-form :model="model" ref="uForm" :errorType="errorType">
					<view class="selectText" @click="show=true">{{selectText}}<u-icon class="ml10" name="arrow-down-fill" size="20"></u-icon></view>
					<u-action-sheet :list="selList" :cancel-btn="false" @click="select" v-model="show"></u-action-sheet>
					<u-form-item prop="value" label-width="1">
					<u-input :border="false" :height="100" :clearable="false" input-align="center" class="input" placeholder="请输入查询信息" v-model="model.value" type="text"></u-input>
					</u-form-item>
					<u-button size="default" shape="circle" type="primary" class="mt30" @click="submit">立即查询</u-button>
				</u-form>
				<view class="tip">
					<view class="title">温馨提示</view>
					<text class="info">
						请输入代理商官方（手机号码、身份证）即可查询官方授权内容。请仔细查询核对”官方合作伙伴“所出具的授权证书，以便为你提供安全保障和优质的服务。对于查询无结果或查询结果内容与实际不相符，请谨慎甄别，以免上当受骗。
					</text>
				</view>
			</view>
			</view>
	</view>
</template>


<script>
	export default {
		data() {
			return {
				show:false,
				selectText: '手机号码',
				selList:[
					{
						text: '手机号码',
						id:'mobile'
					},
					{
						text: '身份证',
						id:'id_card_number'
					}
				],
				rules: {
					value: [{
							required: true,
							message: '请输入查询信息',
							trigger: ['change', 'blur'],
						}
					],
				},
				errorType: ['toast'],
				model: {
					field:'mobile',
					value:''
				},
			}
		},
		onLoad() {
		},
		onShow: function() {},
		computed: {},
		onReady() {
			this.$refs.uForm.setRules(this.rules);
		},
		methods: {
			select(index) {
				var sel = this.selList[index];
				this.model.field = sel.id;
				this.selectText = sel.text;
			},
			submit(){
				this.$refs.uForm.validate(valid => {
					if (!valid) {
						return false;
					}
					this.app.goPage('/pagesB/channel/center/authorizedShow?data='+JSON.stringify(this.model));
				})		
			}
		}
	}
</script>

<style lang="scss">
	
	.u-border-bottom:after{
		border-bottom-width:0;
	}
	.searchBox{
		padding:40rpx;
		.selectText{
			font-size: 32rpx;
		}
		.input{
			display: block;
			width: 100%;
			margin-top: 50rpx;
			background-color: #f5f5f5;
			border-radius: 50rpx;
		}
		.tip{
			border-top: 1px solid #f3f3f3;
			margin-top:80rpx;
			padding-top:60rpx;
			.title{
				font-size: 35rpx;
				color: #000000;
				margin-bottom: 20rpx;
			}
			.info{
				color: #a7a7a9;
				line-height: 50rpx;
			}
		}
	}
</style>
