<template>
	<view class="page-body" :class="[app.setCStyle()]">
		<view style="height:100%;" class="bg-base">
				<view class="p50"></view>
				<u-swiper :height="870"  :list="imgList" @change="changeImg" :title="false" :effect3d="true" :effect3d-previous-margin="80" :autoplay="false" imgMode="widthFix"></u-swiper>
				<view v-if="is_share == true" class="text-center mt80" @click="canvasImage">
					<view class="primarybtn" style="width: 100rpx; height:100rpx;padding-top:18rpx; border-radius: 50%;border: 1rpx solid #FFFFFF; margin: 0rpx auto;"><u-icon name="photo" color="#FFFFFF" size="60"></u-icon></view>
					<view class="f32 mt10 color-ff">{{app.langReplace('生成海报')}}</view>
				</view>
		</view>
		<view class="share-image-box" v-if="shareShow==true">
			<div class="share-mask" @click="shareShow=false" ></div>
			<view class="share-main">
				<view class="share-close"  @click="shareShow=false">
					<u-icon name="close" color="#FFFFFF" size="32"></u-icon>
				</view>
				<view class="share-img">
						<image :src="shareimgurl" mode="aspectFill"  ></image>
						<canvas  canvas-id="mycanvas" style="width: 580px;height: 1050px;" v-show="canvasShow"></canvas>
				</view>
				<view class="btn-box flex">
					<!-- #ifdef H5 -->
					<view  class="btn" >{{app.langReplace('长按图片保存')}}</view>
					<!-- #endif  -->
					<!-- #ifndef H5 -->
					<view  class="btn" @click="saveImage">{{app.langReplace('保存图片')}}</view>
					<!-- #endif  -->
				</view>
			</view>
		</view>
	</view>
</template>


<script>
	export default {
		data() {
			return {
				imgList:[],
				is_share:false,
				is_wxmp:0, 
				img_url:'',
				baseUrl:this.config.baseUrl,
				current:0,
				shareShow:false,
				canvasShow:true,
				shareimgurl:'',
				shareimgurlList:{},
				shareInfo:{}
			}
		},
		onLoad(options) {
			this.app.isLogin(this);//强制登陆
			let title = this.app.langReplace('邀请好友');
			uni.setNavigationBarTitle({
				title
			})
			this.getShareInfo();
			//微信公众号分享
			let setting = uni.getStorageSync('setting');
			let share_bg = setting.share_bg.split(',');
			share_bg.forEach((item,index)=>{
				let row = {};
				row.image = this.baseUrl+item;
				this.imgList.push(row);
			});
		},
		onShow: function() {},
		computed: {},
		onReady() {
		},
		methods: {
			changeImg(current){
				this.current = current;
			},
			getShareInfo(){
				let is_wxmp = 0;
				//#ifdef MP-WEIXIN
				is_wxmp = 1;
				//#endif
				this.$u.post('member/api.users/shareInfo', {
					'is_wxmp':is_wxmp
				}).then(res => {
					if (res.code == 0){
						return false;
					}
					this.is_share = true;
					this.shareInfo = res.data;
				})
			},
			async canvasImage(){
			    this.shareShow = true;
				if (typeof(this.shareimgurlList[this.current]) != 'undefined'){
					this.shareimgurl = this.shareimgurlList[this.current];
					return false;
				}
				this.shareimgurl = '';
				uni.showLoading({
					title:'加载中',
					mask:true
				})
				this.canvasShow = true;
				let ImgInfo = {};
				let	myCanvas = uni.createCanvasContext('mycanvas', this);
				this.app.circleImgTwo(myCanvas,'',0,0,580,1050,30);
				
				myCanvas.fillStyle = "#FFFFFF";
				myCanvas.fill();
				let bg_img = this.imgList[this.current].image;
				ImgInfo = await this.app.getImgInfo(bg_img);
				myCanvas.drawImage(ImgInfo.path,0,0,ImgInfo.width,ImgInfo.height,0,0,580,870);
				
				
				
				let share_headimgurl = this.shareInfo.share_headimgurl ? this.baseUrl+this.shareInfo.share_headimgurl : '/static/public/images/headimgurl.jpg';
				ImgInfo = await this.app.getImgInfo(share_headimgurl);
				this.app.circleImg(myCanvas,ImgInfo.path,30,890,30);
				
				myCanvas.fillStyle = "#333333";
				let nick_name = this.shareInfo.share_nick_name;
				let nick_name_x = 105;
				let nick_name_y = 928;
				myCanvas.font="30px Georgia";
				myCanvas.fillText(nick_name,nick_name_x,nick_name_y);
				
				let share_qrcode = this.baseUrl+this.shareInfo.share_qrcode
				ImgInfo = await this.app.getImgInfo(share_qrcode);
				myCanvas.drawImage(ImgInfo.path,0,0, ImgInfo.width,ImgInfo.height,430,900,130,130);
				
				if (this.shareInfo.share_hb_text.length > 0){
					myCanvas.font="20px Georgia";
					let share_hb_text_x = 30;
					let share_hb_text_y = 990;
					this.shareInfo.share_hb_text.forEach((text,index)=>{
						myCanvas.fillText(text,share_hb_text_x ,share_hb_text_y + (index * 30));
					})
				}
				
				//开始绘画，必须调用这一步，才会把之前的一些操作实施
				myCanvas.draw(true,()=>{
					uni.canvasToTempFilePath({
						canvasId: 'mycanvas',
						success: (res) => {
							// 在H5平台下，tempFilePath 为 base64
							this.shareimgurl = res.tempFilePath;
							this.shareimgurlList[this.current] = res.tempFilePath;
							this.canvasShow = false;
							uni.hideLoading();
							
						  },
						fail: () => {
							uni.showToast({
								title: '名片加载失败',
								duration: 2000 
							});
						}
					});
				});
				
			},
			saveImage(){
				uni.showActionSheet({
					itemList: ['保存图片'], 
					success: (res) => {
						if(res.tapIndex == 0){
							uni.saveImageToPhotosAlbum({
								filePath: this.shareimgurl,//    图片文件路径，可以是临时文件路径也可以是永久文件路径，不支持网络图片路径
								success: () => {
									uni.showToast({
										title: '保存成功',
										duration: 2000
									});
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
					fail: function (res) {
						console.log(res.errMsg);
					}
				});
			},
			getImg(){
				//#ifdef MP-WEIXIN
					this.is_wxmp = 1;
				//#endif
				this.$u.post('member/api.users/getShareImg',{isnew:1,is_wxmp:this.is_wxmp}).then(res => {
					this.img_url = res.data.img;
				}).catch(res=>{
					this.app.showModal(res.msg, -1);
				})
			}
		}
	}
</script>

<style lang="scss">
	.page-body{
		height: 100%;
	}
	.share-image-box{
		position: absolute;
		top: 0;
		width: 100%;
		height: 100%;
		z-index: 999;
		.share-mask{
			position: fixed;
			top:0px;
			left: 0px;
			width: 100%;
			height: 100%;
			background-color: #000;
			opacity: 0.85;
		}
		.share-main{
			position: absolute;
			top:0px;
			left: 0px;
			width: 100%;
			padding-top: 100rpx;
		}
		
		.share-close{
			position: absolute;
			top:40rpx;
			right: 10%;
			width: 50rpx;
			height: 50rpx;
			border: 4rpx solid #FFFFFF;
			border-radius: 50%;
			text-align: center;
		}
		.share-img{
			width: 580rpx;
			height: 1050rpx;
			margin: 0 auto;
			overflow: hidden;
			image{
				width: 580rpx;
				height: 1050rpx;
			}
		}
		.btn-box{
			padding-top: 30rpx;
			margin: 0rpx auto;
			width: 590rpx;
		}
		.btn-box .btn{
			min-width: 50%;
			padding: 0rpx 10rpx;
			border: 2rpx solid #FFFFFF;
			margin: 0rpx auto;
			border-radius: 20rpx;
			line-height: 70rpx;
			text-align: center;
			color: #FFFFFF;
		}
	}
	
</style>
