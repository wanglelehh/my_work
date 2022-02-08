<template>
	<view class="page-body">
			<view class="bg-white  p40">
				<view v-if="satuts==-1" class="errorBox">
					{{errorInfo}}
				</view>
				<view v-if="satuts==1" class="successBox">
					<view class="title">基本信息</view>
					<view class="field">
						<text class="label">被授权人：</text>{{info.real_name?info.real_name:'未填写'}}
					</view>
					<view class="field">
						<text class="label">授权身份：</text>{{info.role_name}}
					</view>
					<view v-if="info.img_url" >
						<image class="w100" :src="baseUrl+info.img_url" @click="app.showImg(baseUrl+info.img_url)" mode="widthFix"></image>
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
				satuts:0,
				errorInfo:'',
				info:{},
				baseUrl:this.config.baseUrl
			}
		},
		onLoad(options) {
			//查询授权
			this.$u.post('channel/api.publics/getAuthorizedInfo',{field:'my'}).then(res => {
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
	
</style>
