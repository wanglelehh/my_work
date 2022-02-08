<template>
	<view class="page-body p10 bg-white">
		<view v-if="item.is_video == 1">
			<view v-if="videoCover == false" class="video_cover">
				<image class="bg" :src="baseUrl+item.video_cover" style="height:100%;width:100%"></image>
				<image class="play" :src="play" @click="videoCover = true"></image>
			</view>
			<view v-else>
				<video controls @ended="videoCover=false"  class="video_box" autoplay="true" :muted="sound" :src="baseUrl+item.video_url" >
					<cover-image @click="changevoice" class="voice" :src="sound?mute:voice"></cover-image>
				</video>
			</view>
		</view>
		<view class="title">{{item.title}}</view>
		<view class="mt20 color-94 fs26">
			<text class="time">{{item.add_time}}</text>
			<text class="fr">阅读：{{item.view_num}}</text>
		</view>
		<view class="mt20 color-94">{{item.description}}</view>
		<view class="detail-desc">
			<view class="d-header">
				<text>课程详情</text>
			</view>
			<u-parse :content="item.content" :imageProp="{'mode':'widthFix'}" noData="" @navigate="goUrl" ></u-parse>
		</view>
	</view>
</template>


<script>
	export default {
		data() {
			return {
				baseUrl:this.config.baseUrl,
				videoCover:false,
				sound: false,
				play:'/static/public/images/video_icon/video_play.png',
				mute:'/static/public/images/video_icon/mute.png',
				voice:'/static/public/images/video_icon/voice.png',
				item:{}
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
				this.$u.post('school/api.train_topic/getInfo',{id:id}).then(res => {
					this.item = res.data;
				})
			},
			changevoice(){
				this.sound = this.sound == false ? true : false;
			}
		}
	}
</script>

<style lang="scss">
.video_box{
	width: 100%;
}
.video_cover {
	width: 100%;
	position: relative;
	height:420rpx;
}
.video_cover .play {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 130rpx;
    height: 120rpx;
    margin-top: -60rpx;
    margin-left: -65rpx;
}
.voice {
    width: 60rpx;
    height: 60rpx;
    position: absolute;
    top: 50rpx;
    right: 30rpx;
}

.title{
	margin-top: 20rpx;
	font-size: 32rpx;
	font-weight: bold;
}

/*  详情 */
	.detail-desc{
		background: #fff;
		margin-top: 16rpx;
		.d-header{
			display: flex;
			justify-content: center;
			align-items: center;
			height: 80rpx;
			font-size: $font-base + 2rpx;
			color: $font-color-dark;
			position: relative;
			text{
				padding: 0 20rpx;
				background: #fff;
				position: relative;
				z-index: 1;
			}
			&:after{
				position: absolute;
				left: 50%;
				top: 50%;
				transform: translateX(-50%);
				width: 300rpx;
				height: 0;
				content: '';
				border-bottom: 1px solid #ccc; 
			}
		}
		img{
			width: 100%;
		}
	}
</style>
