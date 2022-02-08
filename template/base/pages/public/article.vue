<template>
	<view class="page-body p20 bg-white" :class="[app.setCStyle()]">
		<view class="title">{{item.title}}</view>
		<view class="mt20 color-94 fs26 text-center">
			<text class="time">{{item.add_time}}</text>
		</view>
		<view  v-if="app.isMPTextModel() == false && item.is_video == 1" class="mt20" >
			<view v-if="videoPaly == false" class="video_cover" style="min-height:500rpx;">
				<image class="bg" :src="baseUrl+item.video_cover" style="height:100%;width:100%"></image>
				<image class="play" src="/static/public/images/video_icon/video_play.png" style="" @click="startplay"></image>
			</view>
			<video v-else  :enable-progress-gesture="false" autoplay="true" @ended="videoPaly = false" @click="videoPaly = false"  :muted="sound" :src="baseUrl+item.video_url" style="width: 100%;" >
				<cover-image @click="changevoice" class="voice" :src="sound?mute:voice"></cover-image>
			</video>
		</view>
		<view class="detail-desc">
			<u-parse :content="item.content" :imageProp="{'mode':'widthFix'}" noData="" @navigate="goUrl" ></u-parse>
		</view>
		<copyright></copyright>
		<tabbar :_current="0" :getCartNum="0"></tabbar>
	</view>
</template>


<script>
	import copyright from '@/pages/public/copyright';
	import tabbar from '@/pages/shop/tabbar';
	export default {
		components: {
			copyright,
			tabbar
		},
		data() {
			return {
				baseUrl: this.config.baseUrl,
				item:{},
				sound: false,
				mute:'/static/public/images/video_icon/mute.png',
				voice:'/static/public/images/video_icon/voice.png',
				videoPaly:false
			}
		},
		onLoad(options) {
			if (options.id < 1){
				return this.app.showModal('传参错误.',-1);
			}
			this.getInfo(options.id);
		},
		onShow() {
		},
		onReady() {},
		methods: {
			goUrl(url){
				this.app.goPage(url);
			},
			getInfo(id){
				this.$u.post('publics/api.article/getInfo',{id:id}).then(res => {
					this.item = res.data;
					//微信公众号分享
					let setting = uni.getStorageSync('setting');
					let shareimg = this.config.baseUrl+setting.logo;
					if (this.item.img_url != ''){
						shareimg = this.item.img_url;
					}
					uni.setNavigationBarTitle({
						title: this.item.title
					})
				})
			},
			changevoice(){
				this.sound = this.sound == false ? true : false;
			},
			startplay(){
				this.videoPaly = true;
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
	.video_cover {
	    width: 100%;
		height: 100%;
	    position: relative;
	}
	
	.video_cover .play {
	    position: absolute;
	    top: 50%;
	    left: 50%;
	    width: 130rpx !important;
	    height: 120rpx !important;
	    margin-top: -60rpx;
	    margin-left: -65rpx;
	}
	.video_cover .bg {
	    position: absolute;
	    left: 0;
	    top: 0;
	}
</style>
