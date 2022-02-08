<template>
	<view class="page-body">
		<view class="top-box">
			<u-image shape="circle" class="img" width="180rpx" height="180rpx" src="/pagesB/static/channel/images/title_icon/icon_03.png"></u-image>
			<text>授权查询结果</text>
		</view>
		<view class="p30">
			<view class="bg-white mt60 p40">
				<view v-if="satuts==-1" class="errorBox">
					{{errorInfo}}
				</view>
				<view v-if="satuts==1" class="successBox">
					<view class="title">基本信息</view>
					<view class="field">
						<text class="label">被授权人：</text>{{info.real_name}}
					</view>
					<view class="field">
						<text class="label">授权身份：</text>{{info.role_name}}
					</view>
					
					<view v-if="info.img_url">
						<image class="w100" :src="baseUrl+info.img_url" @click="app.showImg(baseUrl+info.img_url)" mode="widthFix"></image>
					</view>
				</view>
				<view class="tip">
					<view class="title">温馨提示</view>
					<text class="info">
						请仔细查询核对”官方合作伙伴“所出具的授权证书，以便为你提供安全保障和优质的服务。对于查询无结果或查询结果内容与实际不相符，请谨慎甄别，以免上当受骗。
					</text>
				</view>
			</view>
		</view>
		<bottomShare :imgFile="baseUrl+info.img_url" :url="info.share_url"></bottomShare>
	</view>
</template>


<script>
	import bottomShare from '@/components/bottomShare';
	export default {
		components: {
			bottomShare
		},
		data() {
			return {
				baseUrl:this.config.baseUrl,
				satuts:0,
				errorInfo:'',
				info:{},
			}
		},
		onLoad(options) {
			let data = {};
			if (typeof(options.uid) != 'undefined'){
				data.field = 'uid';
				data.value = options.uid;
			}else{
				 data = JSON.parse(options.data);
			}
			
			if (typeof(data.value) == 'undefined'){
				return this.app.showModal('请输入查询信息',-1);
			}
			
			//查询授权
			this.$u.post('channel/api.publics/getAuthorizedInfo',data).then(res => {
				this.satuts = res.code;
				this.errorInfo = res.msg;
				if (res.code == -1){
					return false;
				}
				this.info = res.data;
			});
		},
		onShow: function() {},
		computed: {},
		onReady() {
		},
		methods: {
			
		}
	}
</script>

<style lang="scss">
	.errorBox{
		text-align: center;
		height: 300rpx;
		display: flex;
		justify-content: center;
		align-items: center;
		color: $font-color-base;
	}
	.successBox{
		.title{
			font-size: 35rpx;
			color: #000000;
			margin-bottom: 20rpx;
		}
		.field{
			line-height: 60rpx;
			.label{
				color: $font-color-light;
			}
		}
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
</style>
