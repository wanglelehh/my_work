<template>
	<view class="page-body" :class="[app.setCStyle()]">
		<view v-if="showTopTips == true" >
			<view style="height: 90rpx;"></view>
			<view class="topTips flex">
				<view class="flex_bd">您当前还未关注微信公众号</view>
				<view >
					<view class="flex">
						<view class="gzbtn flex_bd base-color"  @click="showqrcode=true">立即关注</view>
						<u-icon name="close" size="28" class="close_btn" @click="showTopTips=false"></u-icon>
					</view>
				</view>
			</view>
			<view class="cu-modal" :class="showqrcode?'show':''"  >
				<view class="cu-dialog qrcode-dialog"> 
				<u-icon name="close" class="cu-dialog-close"  @click="showqrcode=false"></u-icon>
					<view class="qrcode-container" >
						<view class="title" >
							长按二维码识别关注
						</view>
						<image mode="widthFix" style="width: 100%;" :src="weixin_qrcode" >
					</view>
				</view>
			</view>
		</view>
		<view class="u-page">
			<view v-if="is_diy == 0" class="page-body">
				默认商城
			</view>
			<view v-else class="page-body" :style="{background:pageInfo.page.background}">
				<diyPage :pageInfo="pageInfo"></diyPage>
			</view>
			<copyright></copyright>
		</view>
		<view v-if="is_diy == 0">
			<tabbar :now_page="now_page" :getCartNum="0"></tabbar>
		</view>
		<view v-else>
			<tabbar v-if="pageInfo.page.diymenu==1" :now_page="now_page" :getCartNum="0"></tabbar>
		</view>
		<view v-if="open==true" style="position: fixed;top: 0;left: 0;z-index: 1111;width: 100%;height: 100%;background: rgba(0,0,0,0.5);">
			<view style="width: 80%;left: 10%;top: 50%;position: absolute;border-radius: 20rpx;margin-top: -400rpx;padding: 20rpx;">
				<u-icon name="close-circle" @click="closeOpen()" size="70" style="position: absolute;right: -55rpx;top: -70rpx;"></u-icon>
				<image class="play" :src="look_image" style="width:100%;height:800rpx;border-radius: 20rpx;" v-if="look_image_url" @click="app.goPage(look_image_url)"></image>
				<image class="play" :src="look_image" style="width:100%;height:800rpx;border-radius: 20rpx;" v-else ></image>
			</view>
		</view>
	</view>
</template>


<script>
	import copyright from '@/pages/public/copyright';
	import tabbar from '@/pages/shop/tabbar';
	import diyPage from '@/pages/shop/index/diyPage/index';
	export default {
		components: {
			copyright,
			tabbar,
			diyPage
		},
		data() {
			return {
				settings: uni.getStorageSync('setting'),
				look_image:'/static/public/images/video_icon/avatar.jpg',
				look_image_url:'',
                open:false,
				now_page:'',
				shareData: {},
				is_diy: -1,
				pageInfo: {
					page: {}
				},
				showTopTips:false,
				showqrcode:false,
				weixin_qrcode:'',
				user_token:uni.getStorageSync('user_token'),
			};
		},
		onLoad(options) {
			if (options.scene){//获取小程序的场景值，用于获取分享者的token
				let scene = options.scene.split('_');
				uni.setStorageSync("share_token",scene[0]);
			}
			this.now_page = this.$mp.page.route;
			this.app.isLogin(this); //强制登陆
			this.loadData();
			//微信公众号分享
			let setting = uni.getStorageSync('setting');
			this.weixin_qrcode = this.config.baseUrl+setting.weixin_qrcode;
		},
		onShow(){
		},
		onHide(){
		},
		watch: {},
		computed: {},
		onShareAppMessage() {
		    return {
		        title:this.settings.site_name,
		        imageUrl: this.baseUrl + this.settings.logo,
		        path: '/pages/shop/index/index?share_token='+this.user_token
		    }
		},
		methods: {
			closeOpen(){
				this.open=false;
			},
			//请求数据
			async loadData() {
				let openid = this.app.getWxOpenId();
				this.$u.post('shop/api.Index/getIndexInfo',{openid:openid}).then(res => {
					this.is_diy = res.data.is_diy;
					this.pageInfo = res.data.page;
					this.showTopTips = res.data.tipsubscribe == 1 ? true : false;
					// this.user_token = res.data.user_token ? res.data.user_token : '';
					let title = this.pageInfo.page.title;
					let titlebarbg = this.pageInfo.page.titlebarbg;
					let titlebarcolor = this.pageInfo.page.titlebarcolor;
					uni.setNavigationBarColor({
						 frontColor: titlebarcolor,
						 backgroundColor: titlebarbg,
					});
					uni.setNavigationBarTitle({
						title
					})
					setTimeout(() =>{
						if(res.data.shop_index_img_open ==1 && res.data.shop_index_img !=''){
							this.open=true
							this.look_image=this.config.baseUrl+res.data.shop_index_img;
							this.look_image_url=res.data.shop_index_link;
						}
					}, 1000);
					//console.log(this.pageInfo);
				})
			},
		}
	}
</script>

<style lang='scss'>
	.topTips{
		position:fixed;
		top:90rpx;
		left: 0px;
		width: 100%;
		padding: 20rpx;
		background-color: #000;
		color: #FFFFFF;
		line-height: 60rpx;
		z-index: 997;
	}
	.gzbtn{
		padding: 0rpx 20rpx;
		border-radius:20rpx ;
		background-color: #FFFFFF;
	}
	.close_btn{
		padding-left:20rpx;
	}
	.qrcode-dialog{
		background-color: transparent!important;
	}
	
	.qrcode-container {
		min-height: 300upx;
		display: flex;
		flex-direction: column;
		z-index: 3;
		background-image: linear-gradient(-180deg, #F48549 0%, #F2642E 100%);
		border: 18upx solid #E4431A;
		border-radius: 16px;
		padding:30upx;
		/* box-sizing: border-box; */
		color: #fff;
	}
	
	.qrcode-container .title {
		text-align: center;
		margin-bottom: 26upx;
		font-size: 40upx;
	}
	
	.close-cu-modal{
		color: #FFFFFF;
		position: absolute;
		
	}
</style>
