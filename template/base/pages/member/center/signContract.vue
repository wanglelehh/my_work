<template>
	<view class="page-body ">
		<view class="p20">
			<rich-text :nodes="channel_contract"></rich-text>
			<view class="mt20 mb20 flex">
				<text class="block fs32 flex_bd">请在下面灰色位置签名：</text>
				<u-button size="mini" shape="circle" type="warning" @click="clear">清除</u-button>
			</view>
			<view @touchmove.stop.prevent="">
				<canvas class="mycanvas " canvas-id="mycanvas" @touchstart="touchstart" @touchmove="touchmove" @touchend="touchend"></canvas>
			</view>
			<u-button size="default" shape="circle" type="success" class="mt40" @click="finish">提交</u-button>
		</view>
		<view class="h50"></view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				channel_contract: '' ,//协议
				ctx: '', //绘图图像
				points: [], //路径点集合 ,
				isLoading: false,
				isSign: false,
				model: {
					signature:'',
				}
			}
		},
		onLoad(options) {
			this.getContract();
			this.createCanvas();
		},
		computed: {
			
		},
		onReady() {},
		methods: {
			getContract(){
				this.$u.get('publics/api.index/getRoleContract').then(res => {
					this.channel_contract = res.data.channel_contract;
				})
			},
			//创建并显示画布
			createCanvas: function() {
				this.ctx = uni.createCanvasContext("mycanvas", this); //创建绘图对象
				//设置画笔样式
				this.ctx.lineWidth = 4;
				this.ctx.lineCap = "round"
				this.ctx.lineJoin = "round"
			},
			//触摸开始，获取到起点
			touchstart: function(e) {
				let startX = e.changedTouches[0].x;
				let startY = e.changedTouches[0].y;
				let startPoint = {
					X: startX,
					Y: startY
				};
				this.points.push(startPoint);
				//每次触摸开始，开启新的路径
				this.ctx.beginPath();
			},
			//触摸移动，获取到路径点
			touchmove: function(e) {
				let moveX = e.changedTouches[0].x;
				let moveY = e.changedTouches[0].y;
				let movePoint = {
					X: moveX,
					Y: moveY
				};
				this.points.push(movePoint); //存点
				let len = this.points.length;
				if (len >= 2) {
					this.draw(); //绘制路径
				}
			},
			// 触摸结束，将未绘制的点清空防止对后续路径产生干扰
			touchend: function() {
				this.isSign = true;
				this.points = [];
			},
			/* ***********************************************
			#   绘制笔迹
			#	1.为保证笔迹实时显示，必须在移动的同时绘制笔迹
			#	2.为保证笔迹连续，每次从路径集合中区两个点作为起点（moveTo）和终点(lineTo)
			#	3.将上一次的终点作为下一次绘制的起点（即清除第一个点）
			************************************************ */
			draw: function() {
				let point1 = this.points[0]
				let point2 = this.points[1]
				this.points.shift()
				this.ctx.moveTo(point1.X, point1.Y)
				this.ctx.lineTo(point2.X, point2.Y)
				this.ctx.stroke()
				this.ctx.draw(true)
			},
			//清空画布
			clear: function() {
				let that = this;
				this.isPost = false;
				this.isSign = false;
				uni.getSystemInfo({
					success: function(res) {
						let canvasw = res.windowWidth;
						let canvash = res.windowHeight;
						that.ctx.clearRect(0, 0, canvasw, canvash);
						that.ctx.draw(true);
					},
				})
			},
			finish() {
				if (this.isLoading == true) {
					return true;
				}
				if (this.isSign == false){
					this.$u.toast('请签名后再提交.');
					return false;
				}
				this.isLoading = true;
				let that = this;
				uni.showLoading();
				uni.canvasToTempFilePath({
					canvasId: 'mycanvas',
					success: function(res) {
						
						//#ifdef APP-PLUS 
						 that.app.appReadFileToBase64(res.tempFilePath,res=>{
							that.model.signature = res
							that.post();
						});
						return true;
						//#endif
						//#ifdef MP-WEIXIN
						that.app.wxmpReadFileToBase64(res.tempFilePath,res=>{
							that.model.signature = res
							that.post();
						});
						return true;
						//#endif
						that.model.signature = res.tempFilePath;
						that.post();
					},
					fail: function() {
						that.isLoading = false;
					}
				})
			},
			post(){
				this.$u.post('member/api.users/signRoleContract', this.model).then(res => {
					uni.hideLoading();
					this.isLoading = false;
					if (res.code == 0){
						this.$u.toast(res.msg);
						return false;
					}
					this.app.showModal(res.msg,'/pages/member/center/index');
				}).catch(res=>{
					this.isLoading = false;
				})
			},
		}
	}
</script>

<style>
	.mycanvas {
		width: 100%;
		height:300rpx;
		background-color: #ECECEC;
	}
</style>
