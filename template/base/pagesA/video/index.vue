<template>
	<view class="page-body">
		<!-- 顶部 -->
		<view class="top_nav flex">
			<view class="logo"></view>
			<view class="flex1">
				<view class="text_btn flex">
					<view v-for="(item, index) in navList" :key="index" class="flex1 relative"  :class="{current: tabCurrentIndex === index}"
			 @click="tabClick(index)">
				{{item.text}}</view>
				</view>
			</view>
			<view class="search_btn">
				<image src="/pagesA/static/static/images/video/search.png"></image>
			</view>
		</view>
		<swiper :current="tabCurrentIndex"  duration="300" @change="changeTab" class="hb100">
			<swiper-item class="tab-content" v-for="(tabItem,tabIndex) in navList" :key="tabIndex">
				<swiper :indicator-dots="false" :duration="200" :vertical="true" :current="tabItem.videoIndex" @change="handleSlider" class="hb100">
					<swiper-item  v-for="(item,index) in vlist" :key="index">
						<view class="relative hb100">
							<view style="height: 100%;width:100%;position: absolute;top:0;left:0;z-index:0;">
								<video :id="tabIndex+'myVideo' + index" x5-video-player-type="h5" :controls="false"  class="hb100 w100" :src="item.src"
								:loop="true" :show-center-play-btn="false" >
								</video>
							</view>
							<!-- 中间播放按钮 -->
							<view class="vd-cover" @click="handleClicked(index)">
								<view class="relative hb100">
									<image v-if="!isPlay" class="play" src="/static/images/video_icon/video_play.png"></image>
								</view>
							</view>
							<!-- 右侧信息 -->
							<view class="right_btn">
								<view class="headimgurl">
									<image :src="item.headimgurl"></image>
									<view v-if="item.follow == 0" class="follow">
										<image src="/pagesA/static/images/video/follow.png"></image>
									</view>
								</view>
								<view class="other_icon">
									<image :src="item.islike==0?'/pagesA/static/images/video/like_0.png':'/pagesA/static/images/video/like_1.png'"></image>
									<view class="num">{{item.likeNum}}</view>
								</view>
								<view class="other_icon">
									<image src="/pagesA/static/images/video/reply.png"></image>
									<view class="num">{{item.replyNum}}</view>
								</view>
								<view class="other_icon">
									<image src="/pagesA/static/images/video/share.png"></image>
									<view class="num">{{item.shareNum}}</view>
								</view>
							</view>
							<!-- 底部信息 -->
							<view class="footToolbar">
								<view class="avator">@{{item.avator}}</view>
								<view class="subtitle">{{item.subtitle}}</view>
							</view>
						</view>	
					</swiper-item>
				</swiper>
			</swiper-item>
		</swiper>
	</view>
</template>

<script>
	let timer = null
	export default {
		data() {
			return {
				tabCurrentIndex: 0,
				navList: [{
						type: 'base',
						text: '推荐',
						videoIndex: 0,
						vList: [],
						p: 0
					},
					{
						type: 'follow',
						text: '关注',
						videoIndex: 0,
						vList: [],
						p: 0
					}
				],
				vlist: [],
				isPlay: false,    //当前视频是否播放中
				clickNum: 0,    //记录点击次数
			}
		},
		components: {
			//videoCart, videoComment
		},
		onLoad(option) {
			this.getData();
		},
		onReady() {
		},
		methods: {
			getData(){
				this.vlist = [{
					src:'https://limitao.myuan.cn/gongyong/shangchuan/yinpin/33/2020/05/Ef1BgzZVA2bF79H67lA67b15L7J10A.mp4',
					headimgurl:'http://7niu.zhiwi.cn/Q18Twfqi3W58nI5JZLkv4Ili8X4JwP.jpg',
					keyword:['测试1','测试2'],
					subtitle:'一年之计在于春，一日之计在于晨',
					avator:'老关',
					follow:0,
					cart:[],
					islike:1,
					likeNum:11,
					replyNum:3,
					shareNum:1
				},
				{
					src:'http://7niu.zhiwi.cn/SCe1Lc3KLQ1bQ33UAKMLmUKmta8t3k.mp4',
					headimgurl:'http://7niu.zhiwi.cn/Q18Twfqi3W58nI5JZLkv4Ili8X4JwP.jpg',
					keyword:['测试1','测试2'],
					subtitle:'我来啦我来啦,下班后的嚣张小姐姐来 啦排银行小姐姐下班抖一抖,快乐天 天有',
					avator:'老关',
					follow:1,
					cart:[],
					islike:0,
					likeNum:20,
					replyNum:9,
					shareNum:91
				}]
			},
			//swiper 切换
			changeTab(e) {
				this.pause(this.navList[this.tabCurrentIndex].videoIndex);
				this.tabCurrentIndex = e.target.current;
				let videoIndex = this.navList[this.tabCurrentIndex].videoIndex;
				this.play(videoIndex);
				
			},
			//顶部tab点击
			tabClick(index) {
				this.tabCurrentIndex = index;
			},
			// 视频滑动切换
			handleSlider(e) {
				this.pause(this.navList[this.tabCurrentIndex].videoIndex);
				this.play(e.target.current);
			},
			// 播放
			play(index) {
				console.log('播放');
				this.navList[this.tabCurrentIndex].videoIndex = index;
				this.isPlay = true;
				uni.createVideoContext(this.tabCurrentIndex+'myVideo' + index, this).play();
			},
			// 暂停
			pause(index) {
				console.log('暂停');
				this.isPlay = false;
				uni.createVideoContext(this.tabCurrentIndex+'myVideo' + index, this).pause();
			},
			// 点击视频事件
			handleClicked(index) {
				if(timer){
					clearTimeout(timer)
				}
				this.clickNum++
				timer = setTimeout(() => {
					if(this.clickNum >= 2){
						console.log('双击视频')
					}else{
						console.log('单击视频')
						if(this.isPlay){
							this.pause(index)
						}else{
							this.play(index)
						}
					}
					this.clickNum = 0
				}, 300)
			},
			
			// 喜欢
			handleIsLike(index){
				let vlist = this.vlist
				vlist[index].islike =! vlist[index].islike
				this.vlist = vlist
			},
			// 显示评论
			handleVideoComment() {
				//this.$refs.videoComment.show()
			},
			
			// 显示购物车
			handleVideoCart(index) {
				//this.$refs.videoCart.show(index)
			},
		}
	}
</script>

<style lang="scss">
.page-body{
	background-color: #000000;
}
.relative{
	position: relative;
}
.flex{
	display: flex;
	flex-direction: row;
}
.flex1{
	flex: 1;
}
.top_nav{
	position:fixed;
	top:0;
	width: 100%;
	z-index:9999;
	color: #FFFFFF;
	.logo{
		width: 150rpx;
	}
	.text_btn{
		text-align: center;
		font-size: 38rpx;
		padding-top: 20rpx;
		.current{
			&:after{
				content: '';
				position: absolute;
				display: inline-block;
				left: 50%;
				bottom: -10rpx;
				width: 15%;
				transform: translateX(-50%);
				border-bottom: 4rpx solid ;
				border-color:#FFFFFF;
			}
		}
	}
	.search_btn{
		width: 150rpx;
		padding-right: 30rpx;
		padding-top: 30rpx;
		position: relative;
		image{
			position: absolute;
			right: 30rpx;
			width: 40rpx;
			height: 40rpx;
		}
	}
}
.vd-cover{
	position:absolute;
	top:0;
	width: 100%;
	height: 100%;
	.play{
		position: absolute;
		top: calc(50% - 50rpx);
		left: calc(50% - 50rpx);
	}
}

.right_btn{
	position:absolute;
	right: 10rpx;
	bottom: 100rpx;
	.headimgurl {
		width: 100rpx;
		height: 100rpx;
		border-radius: 50%;
		border: 3rpx solid #FFFFFF;
		background-color: #FFFFFF;
		position: relative;
		margin-bottom: 50rpx;
		image{
			border-radius: 50%;
			width: 100%;
			height: 100%;
		}
		.follow{
			position:absolute;
			bottom:0rpx;
			left: calc(50% - 20rpx);
			width: 40rpx;
			height: 40rpx;
		}
	}
	.other_icon{
		margin: 0rpx auto;
		margin-bottom: 50rpx;
		width: 70rpx;
		image{
			width: 100%;
			height: 70rpx;
		}
		.num{
			color: #FFFFFF;
			text-align: center;
		}
	}
}
.footToolbar{
	position:absolute;
	left: 20rpx;
	bottom: 80rpx;
	color: #FFFFFF;
	width: 80%;
	.avator{
		font-weight: 700;
	}
	.subtitle{
		margin-top: 10rpx;
		font-size: 28rpx;
	}
}
</style>
