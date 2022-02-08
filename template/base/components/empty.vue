<template>
	<view class="empty-content">
		<image class="empty-content-image" :src="setSrc" mode="aspectFit"></image>
		<text class="tip">{{app.langReplace('哎呀~该页面暂无记录哦')}}</text>
	</view>
</template>

<script>
	export default {
		props: {
			src: {
				type: String,
				default: 'empty'
			},
		},

		data() {
			return {
				typeSrc: {
					empty: '/static/public/images/img_05.png'
				},
			}
		},

		computed: {
			setSrc() {
				return this.typeSrc[this.src];
			},
		}
	}
</script>

<style lang="scss">
	.empty-content {
		display: flex;
		align-items: center;
		justify-content: center;
		flex-direction: column;
		
		position: absolute;
		left: 0;
		top: 0;
		right: 0;
		bottom: 0;
		background: #FFFFFF;
		padding-bottom: 200rpx;

		&-image {
			width: 400rpx;
			height: 400rpx;
		}
		.tip{
			color: $font-color-light;
		}
	}
</style>
