<template>
	<view :style="{padding:app.pxToRpx(this.diyitem.style.paddingtop)+'rpx 0'}">
		<swiper indicator-dots="true" circular="true" :style="getStyle" :autoplay="autoplay" disable-touch="true" :current="current" >
			<swiper-item v-for="(childitem, childid) in diyitem.data" :key="childid">
				<view v-if="videoCover != diyitemid+'_'+childid" class="video_cover" :style="getStyle">
					<image class="bg" :src="childitem.poster" style="height:100%;width:100%"></image>
					<image class="play" :data-cover="childid" :src="play" style="" @click="startplay"></image>
					<image class="voiceleft" :src="lefticon" @click="cutVideos" data-type="retreat"></image>
					<image class="voiceright" :src="righticon" @click="cutVideos" data-type="advance"></image>
				</view>
				<view v-else>
					<video controls @ended="videoEnd" enable-progress-gesture="false" autoplay="true" :muted="sound" :src="childitem.videourl" :style="getStyle">
						<cover-image @click="changevoice" class="voice" :src="sound?mute:voice"></cover-image>
						<cover-image @click="cutVideos" class="voiceleft" :src="lefticon" data-type="retreat"></cover-image>
						<cover-image @click="cutVideos" class="voiceright" :src="righticon" data-type="advance"></cover-image>
					</video>
				</view>
			</swiper-item>
		</swiper>
	</view>
</template>


<script>
	export default {
		name: "tpl_videos",
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
				autoplay:true,
				current:0,
				sound: false,
				lefticon: '/static/shop/diyPage/images/icon/lefticon.png',
				righticon: '/static/shop/diyPage/images/icon/righticon.png',
				play:'/static/public/images/video_icon/video_play.png',
				mute:'/static/public/images/video_icon/mute.png',
				voice:'/static/public/images/video_icon/voice.png',
			};
		},
		watch: {},
		computed: {
			getStyle() {
				const info = uni.getSystemInfoSync();
				let screenWidth = info.windowWidth;
				let style = '';
				style += 'width:100%;';
				if (this.diyitem.style.ratio == 0) {
					style += 'height:' + (screenWidth / 8 * 9) + 'rpx';;
				} else if (this.diyitem.style.ratio == 1) {
					style += 'height:' + (screenWidth / 2 * 3) + 'rpx;';
				} else if (this.diyitem.style.ratio == 2) {
					style += 'height:' + (screenWidth * 2) + 'rpx;';
				}
				return style;
			}
		},
		methods: {
			videoEnd(){
				this.$emit("videoEnd");
			},
			startplay(e) {
				this.autoplay = false;
				this.$emit("startplay", this.diyitemid + '_' + e.currentTarget.dataset.cover);
			},
			changevoice() {
				this.sound = this.sound == false ? true : false;
			},
			cutVideos(t) {
				this.$emit("startplay", '');
				this.autoplay = true;
				let e = t.currentTarget.dataset.type,
					i = this.diyitem.data.length;
				if (e == 'retreat') {
					this.current = this.current < i - 1 ? this.current + 1 : 0;
				} else {
					this.current = this.current > 0 ? this.current - 1 : i - 1;
				}
			}
		}
	}
</script>

<style lang='scss'>

	.video_cover {
		position: relative;
	}

	.voiceleft {
		width: 40rpx;
		height: 80rpx;
		position: absolute;
		top: 40%;
		left: 0rpx;
	}

	.voiceright {
		width: 40rpx;
		height: 80rpx;
		position: absolute;
		top: 40%;
		right: 0rpx;
	}
</style>
