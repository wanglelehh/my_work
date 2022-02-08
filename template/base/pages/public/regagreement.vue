<template>
	<view class="page-body p20 bg-white" :class="[app.setCStyle()]">
		<view class="detail-desc">
			<u-parse :content="content" :imageProp="{'mode':'widthFix'}" noData="" @navigate="goUrl" ></u-parse>
		</view>
		<button size="default" shape="circle" class="primarybtn mt40" type="warning"  @click="app.goPage(-1)">前往注册</button>
	</view>
</template>


<script>
	export default {
		data() {
			return {
				content:''
			}
		},
		onLoad(options) {
			this.getInfo();
		},
		onShow() {
		},
		onReady() {},
		methods: {
			goUrl(url){
				this.app.goPage(url);
			},
			getInfo(){
				this.$u.post('publics/api.article/regagreement').then(res => {
					this.content = res.data.content;
				})
			}
		}
	}
</script>

<style lang="scss">

.title{
	margin-top: 20rpx;
	font-size: 32rpx;
	font-weight: bold;
	text-align: center;
}

/*  详情 */
	.detail-desc{
		background: #fff;
		margin-top: 16rpx;
		padding-bottom: 50rpx;
		img{
			width: 100%;
		}
	}
</style>
