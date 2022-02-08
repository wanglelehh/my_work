<template>
	<view :style="{padding:app.pxToRpx(this.diyitem.style.paddingtop)+'rpx 0'}">
		 <view v-if="videoCover != diyitemid" class="video_cover" :style="getStyle">
				 <image class="bg" :src="diyitem.params.poster" style="height:100%;width:100%"></image>
				 <image class="play" :data-cover="diyitemid" :src="play" style="" @click="startplay"></image>
		 </view>
		 <view v-else>
			 <video controls @ended="videoEnd" autoplay="true" :muted="sound" :src="diyitem.params.videourl" :style="getStyle" >
				 <cover-image @click="changevoice" class="voice" :src="sound?mute:voice"></cover-image>
			 </video>
		 </view>
	</view>
</template>

<script>
	export default {
		name: "tpl_video",
		props: {
			diyitemid: {
				type: String,
				default: ''
			},
			diyitem: {
				type: Object,
				default: function() {
					return {};
				}
			},
			videoCover: {
				type: String,
				default: ''
			},
		},
		data() {
			return {
				sound: false,
				play:'/static/public/images/video_icon/video_play.png',
				mute:'/static/public/images/video_icon/mute.png',
				voice:'/static/public/images/video_icon/voice.png',
			};
		},
		watch: {},
		computed: {
			getStyle(){
				const info = uni.getSystemInfoSync();
				let screenWidth = info.windowWidth;
				let style = '';
				style += 'width:100%;';
				if (this.diyitem.style.ratio==0){
					style += 'height:'+(screenWidth/8*9)+'rpx';;
				}else if (this.diyitem.style.ratio==1){
					style += 'height:'+(screenWidth/2*3)+'rpx;';
				}else if(this.diyitem.style.ratio==2){
					style += 'height:'+(screenWidth*2)+'rpx;';
				}
				return style;
			}
		},
		methods: {
			videoEnd(){
				this.$emit("videoEnd");
			},
			startplay(){
				console.log(this.diyitemid);
				this.$emit("startplay",this.diyitemid);
			},
			changevoice(){
				this.sound = this.sound == false ? true : false;
			}
		}
	}
</script>

<style lang='scss'>
</style>
