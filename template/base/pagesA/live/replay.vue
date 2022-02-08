<template>
	<view class="page-body" :class="[app.setCStyle()]">
		<video id="myVideo"  :src="videoSrc" class="myVideo" @ended="videoEnd"></video>
		<view class="room_info">
	        <view class="live_player">
	            <view class="inner">
	                <image class="inner_avatar" :src="roomInfo.cover_img"role="img"></image>
	                <view bindtap="clickProfileModal" class="inner_name">{{roomInfo.name}}</view>
	            </view>
	        </view>
	    </view>
		 <view @click="clickStore" class="person-operation__item person-operation__store">
		        <view class="person-operation__btn person-operation__store-btn has-events"></view>
		 </view>
		 <view v-if="show_store==1" class="store-list" >
		         <view class="store-list-bg">
		             <view class="store-list__inner">
		                 <view class="store-list__header">
		                     <view class="store-list__title">直播商品</view>
		                 </view>
		             </view>
		             <view class="store-list__body">
		                 <scroll-view class="store-list__body__inner"style="max-height: 300px;" scroll-y>
		                     <view  v-for="(good, index) in roomInfo.goods" :key="index" class="store-list__item" @click="app.goPage(good.url)">
		                             <view class="store-list__item__header">
		                                 <image class="store-list__item__avatar" mode="aspectFill" :src="good.cover_img" role="img"></image>
		                                 <view class="store-list__item__index">{{index+1}}</view>
		                             </view>
		                             <view class="store-list__item__body">
		                                 <view class="store-list__item__title">{{good.name}}</view>
		                                 <view class="store-list__item__price__container">
		                                  <view v-if="good.price_type==1">
		                                   <text class="store-list__item__price">¥ {{good.price/100}}</text>
		                                  </view>
		                                   <view v-if="good.price_type==2">
		                                     <text class="store-list__item__price">¥ {{good.price/100}} ~ {{good.price2/100}}</text>
		                                  </view>
		                                   <view v-if="good.price_type==3">
		                                       <text class="store-list__item__price">¥ {{good.price2/100}}</text>
		                                     <text class="store-list__item__price store-list__item__price-before">¥ {{good.price/100}}</text>
		                                   </view>
		 
		                                 </view>
		                             </view>
		                     </view>
		                 </scroll-view>
		             </view>
		         </view>
		     </view>
	</view>
</template>

<script>
	let settings = uni.getStorageSync('setting');
	export default {
		data() {
			return {
				roomid:0,
				roomInfo:{},
				logo:this.config.baseUrl + settings.logo,
				inner_name:settings.site_name,
				videoIndex:0,
				videoChangeIng: 0,
				videoSrc:'',
				show_store:0
			}
		},
		onLoad(options) {
			this.roomid = options.roomid;
			this.getInfo();
		},
		onShow() {
		},
		onHide() {
		},
		onReady() {},
		methods: {
			getInfo(){
				this.$u.post('weixin/api.live_room/getRoomReplay', {
					'roomid': this.roomid
				}).then(res => {
					this.roomInfo = res.data;
					this.videoPlay();
				})
			},
			videoEnd: function (res) {   // 视频播放结束后执行的方法
			    if (this.videoChangeIng == 1){
			      return false;
			    }
			    if (this.videoIndex == this.roomInfo.live_replay.length - 1) {
			        this.videoIndex = 1;
					this.videoContext.pause()  //暂停
			    }else{
					this.videoChangeIng = 1;
					this.videoPlay();
			    }
			},
			videoPlay() {
			    var videolLength = this.roomInfo.live_replay.length;
			    this.videoIndex = this.videoIndex + 1;
			    this.videoSrc = this.roomInfo.live_replay[this.videoIndex]['media_url'];
			    this.videoChangeIng = 0;
			    uni.createVideoContext('myVideo').play();//播放视频
			},
			clickStore(){
				this.show_store = this.show_store == 0 ? 1 : 0
			},
			stopPrevent() {}
		}
	}
</script>

<style>
	.page-body{
		background-color: #000000;
		overflow: hidden;
	}
	.myVideo{
		width:100%;
		height:100vh;
		position:fixed;
		top:-20rpx;
		background-color:gray;
	}
	.room_info {
	position: relative;
	display: -webkit-flex;
	display: flex;
	-webkit-align-items: center;
	align-items: center;
	margin-top:10px;
	margin-left: 10px;
	}
	.live_player {
	position: relative;
	display: inline-block;
	background-color: rgba(0, 0, 0, 0.25);
	border-radius: 21px;
	padding: 0 7px;
	padding-right: 24px;
	
	}
	.inner {
	display: -webkit-flex;
	display: flex;
	-webkit-justify-content: flex-start;
	justify-content: flex-start;
	-webkit-align-items: center;
	align-items: center;
	min-height: 42px;
	
	}
	
	.inner_avatar {
	display: block;
	width: 26px;
	height: 26px;
	-webkit-flex-shrink: 0;
	flex-shrink: 0;
	box-sizing: border-box;
	border: 1px solid rgba(255,255,255,0.70);
	border-radius: 50%;
	margin-right: 8px;
	
	}
	.inner_name {
	font-size: 14px;
	color: #FFFFFF;
	max-width: 300rpx;
	overflow: hidden;
	font-weight: 450;
	white-space: nowrap;
	text-overflow: ellipsis;
	}

.person-operation__store {
	width: 84rpx;
	height: 84rpx;
	background-image: linear-gradient(180deg, #8385F3 0%, #6467F0 100%);
	position: absolute;
	right: 10rpx;
	bottom: 28rpx;
}

.person-operation__item {
	width: 84rpx;
	height: 84rpx;
	border-radius: 50%;
	display: -webkit-flex;
	display: flex;
	-webkit-justify-content: center;
	justify-content: center;
	-webkit-align-items: center;
	align-items: center;
	text-align: center;
	margin-bottom: 100rpx;
}
.has-events {
	pointer-events: auto;
}
.person-operation__store-btn {
	position: relative;
	top: -4rpx;
	width: 56rpx;
	height: 56rpx;
	background: url("data:image/svg+xml, %3Csvg height='28' width='28' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M19.25 8.166H17.5v4.667h1.75V8.166zm-8.75 0H8.75v4.667h1.75V8.166zm8.75 0h2.916a1.17 1.17 0 0 1 1.167 1.176v14a2.326 2.326 0 0 1-2.326 2.325H6.993a2.326 2.326 0 0 1-2.326-2.324v-14c0-.65.518-1.176 1.167-1.176H8.75v-.584a5.25 5.25 0 0 1 10.5 0v.583zm-8.75 0h7v-.583a3.5 3.5 0 0 0-7 0v.583z' fill='%23FFF' fill-rule='evenodd'/%3E%3C/svg%3E") no-repeat center;

}


.store-list {
	border-radius: 16px;
	overflow: hidden;
	position:absolute;
	bottom: 0px;
	width:calc(100% - 115rpx);
	margin-left: 5px;
}

.store-list-bg{
	background-color: rgba(0, 0, 0, 0.25);
	-webkit-backdrop-filter: blur(20px);
	backdrop-filter: blur(20px);
	border-radius: inherit;
	padding: 0 16px;
}
.store-list__header{
	position: relative;
	display: -webkit-flex;
	display: flex;
	-webkit-justify-content: flex-start;
	justify-content: flex-start;
	-webkit-align-items: center;
	align-items: center;
	padding-top: 20rpx;
	font-weight: 500;
}
.store-list__title {
	font-size: 15px;
	color: #ffffff;
	text-shadow: 0.5px 0.5px 2px rgba(0, 0, 0, 0.1);
}
.store-list__body{
	margin-top: 4px;
	overflow: hidden;
}
.store-list__body__inner{
	position: relative;
	width: calc(100% + 20px);
	box-sizing: border-box;
	-webkit-mask-image: -webkit-gradient(linear, left bottom, left 96%, from(rgba(0, 0, 0, 0)), to(rgba(0, 0, 0, 04)));
}
.store-list__item{
	margin-right: 20rpx;
	margin-bottom: 12rpx;
	background: #ffffff;
	border-radius: 16rpx;
	padding: 20rpx;
	height: 120rpx;
	display: flex;
}
.store-list__item__body {
	padding: 4rpx 10rpx 0 0;
	height: 90rpx;
	box-sizing: border-box;
	-webkit-flex: 1;
	flex: 1;
	display: -webkit-flex;
	display: flex;
	-webkit-flex-direction: column;
	flex-direction: column;
	-webkit-justify-content: space-between;
	justify-content: space-between;
	float: left;
}
.store-list__item__header{
	position: relative;
	margin-right: 18rpx;
	font-size: 0;
	float: left;
}
.store-list__item__title {
	font-size:30rpx;
	color: rgba(0, 0, 0, 0.7);
	overflow: hidden;
	text-overflow: ellipsis;
	display: -webkit-box;
	-webkit-line-clamp: 2;
	-webkit-box-orient: vertical;
	word-break: break-all;
	line-height: 19.6px;
	margin-bottom: 10rpx;
}

.store-list__item__price {
	opacity: 0.9;
	font-size: 14px;
	color: #fa9d3b;
}
.store-list__item__price-before {
	color: #B2B2B2;
	text-decoration: line-through;
	margin-left: 10px;
}

.store-list__item__avatar{
	width: 100rpx;
	height: 100rpx;
	border-radius: 2px;
	box-size: border-box;
}
.store-rank-index {
	position: absolute;
	top: 0;
	left: 0;
	display: inline-block;
	height: 16px;
	line-height: 15px;
	border-radius: 2px 0 8px 0;
	color: #fff;
	text-align: center;
	line-height: 16px;
	background-color: rgba(100, 103, 240, 1);
	font-size: 10px;
	padding: 0 5px;
}
.store-list__item:first-child .store-rank-index {
	background-color: rgba(250, 81, 81, 1);
}

.store-list__item__index {
	position: absolute;
	top: 0;
	left: 0;
	background: rgba(0, 0, 0, 0.5);
	border-radius: 2px 0px 8px 0px;
	text-align: center;
	padding: 0 2px;
	min-width: 14px;
	line-height: 12px;
	font-size: 10px;
	color: #ffffff;
	text-align: center;
}
</style>
