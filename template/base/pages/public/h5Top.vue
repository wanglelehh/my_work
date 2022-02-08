<template>
	<!-- #ifdef H5 -->
	<view class="diy-page-head">
		<view class=" diy-page-head-main">
				<view class="back-btn" @click="h5GoBack()">
					<u-icon name="arrow-left" class="uni-btn-icon" color="#000000" size="40"></u-icon>
				</view>
				<view class="title" style="font-size: 16px; opacity: 1;">{{_title}}</view>
		</view>
	</view>
	<!-- #endif -->
</template>

<script>
	export default {
		name: "h5Top",
		props: {
			_title:{
				type: String,
				default:''
			},
		},
		data() {
			return {}
		},
		computed:{
		},
		methods: {
			h5GoBack(){
				this.app.goPage(-1);
			}
		}
	}
</script>

<style lang="scss">
	.diy-page-head{
		height: 88rpx;
		color: rgb(0, 0, 0);
	}
	.diy-page-head-main{
	   position:fixed;
	   height: 88rpx;
	   width: 100%;
	   padding: 16rpx 16rpx;
	   background-color: rgb(255, 255, 255);
	   z-index: 99999;
	}
	.diy-page-head-main .back-btn{
		position: absolute;
		z-index:99992;
		left: 10rpx;
		width:60rpx;
		font-weight: 500;
	}
	.diy-page-head-main .title{
		position: absolute;
		z-index:99991;
		top: 10rpx;
		left: 0;
		right: 0;
		font-weight: 700;
		font-size: 30rpx;
		line-height: 30px;
		text-align: center;
		overflow: hidden;
		white-space: nowrap;
		text-overflow: ellipsis;
	}
</style>
