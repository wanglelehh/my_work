<template>
	<view class="bottomShare">
		<view class="shareTip" v-if="shareTip == true">
			<view class="mask" @click="showShareTip"></view>
			<image class="img" src="/static/public/images/share/tip.png"></image>
		</view>
		<view class="btn_box">
			<u-grid :col="isWeixn == true && isShowWxShare == true?3:2" class="u-grid" :border="false">
				<u-grid-item @click="copy">
					<view class="btn">
						<image class="img" src="/static/public/images/share/icon_01.png"></image>
					</view>
					<view>复制链接</view>
				</u-grid-item>
				<u-grid-item v-if="isWeixn == true && isShowWxShare == true" @click="showShareTip">
					<view class="btn">
						<image class="img" src="/static/public/images/share/icon_02.png"></image>
					</view>
					<view>微信分享</view>
				</u-grid-item>
				<u-grid-item @click="save">
					<view class="btn">
						<image class="img" src="/static/public/images/share/icon_03.png"></image>
					</view>
					<view>保存图片</view>
				</u-grid-item>
			</u-grid>
		</view>
	</view>
</template>

<script>
	let isWeixn = false;
	//#ifdef H5
	var ua = navigator.userAgent.toLowerCase();
	if(ua.match(/MicroMessenger/i)=="micromessenger") {
		isWeixn =  true;
	} else {
		isWeixn = false;
	}
	//#endif
	export default {
		name: "bottomShare",
		props: {
			isShowWxShare: {
				type: Boolean,
				default: false
			},
			url: {
				type: String,
				default: '',
			},
			imgFile: {
				type: String,
				default: '',
			}
		},
		data() {
			return {
				shareTip:false,
				isWeixn: isWeixn
			}
		},
		methods: {
			showShareTip(){
				this.shareTip = !this.shareTip;
			},
			copy(){
				let that = this;
				uni.setClipboardData({ data:that.url, success:function(data){
					that.$u.toast('复制成功.');
				}, fail:function(err){
					that.$u.toast('复制失败，请重试.');
				}, complete:function(res){} })
			},
			save(){
				let that = this;
				if (this.imgFile == ''){
					return false;
				}
				/* #ifdef H5 */
				that.$u.toast('长按图片，保存图片.');
				return false;
				/* #endif */
				
				uni.downloadFile({
					url:that.imgFile,      //文件链接
					success:({statusCode,tempFilePath})=>{
						//statusCode状态为200表示请求成功，tempFIlePath临时路径
						if(statusCode==200){
							uni.saveImageToPhotosAlbum({
								filePath: tempFilePath,//    图片文件路径，可以是临时文件路径也可以是永久文件路径，不支持网络图片路径
								success: () => {
									that.$u.toast('保存成功.');
								},
								fail: () => {
									uni.showToast({
										title: '保存失败',
										duration: 2000 
									});
								}
							});
						}
					},
					fail:()=>{
						uni.showToast({
							title: '保存失败',
							duration: 2000 
						});
					}
				})
				
			}
		}
	}
</script>

<style lang="scss">
	.bottomShare{
		height: 220rpx;
		.shareTip {
			position: fixed;
			top: 0rpx;
			right: 0;
			width: 100%;
			text-align: right;
			height: 100%;
			z-index: 999;
			background: rgba(0, 0, 0, .5);
			.mask {
				position: absolute;
				z-index: 999;
				width: 100%;
				height: 100%;
			}
		}
		.btn_box {
			position: fixed;
			bottom: 0px;
			width: 100%;
			.u-grid-item {
				background: none !important;
			}
			.btn {
				border-radius: 100%;
				width: 120rpx;
				height: 120rpx;
				background: #fff;
				text-align: center;
				margin-bottom: 20rpx;
				border:1rpx solid #cccccc;
				.img {
					padding-top: 28rpx;
					width: 56rpx;
					height: 56rpx;
				}
			}
		}
	}
</style>
