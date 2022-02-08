<template>
	<view v-if="diyitem.data.length > 0" class="p20">
		<view v-if="diyitem.params.istitle == 1" class="font-w700 fs32 b-tottom pb20 mb20">{{diyitem.params.title}}</view>
		<view v-for="(item,index) in diyitem.data" :key="index" class="mb20 bg-white item" @click="app.goPage('/pagesA/fightgroup/info?fg_id='+item.fg_id)">
			<view class="w100 relative">
				<image class="w100" :src="baseUrl+item.cover" mode="widthFix"></image>
				<view class="join_tip">{{app.langReplace('参团数')}}：{{item.success_order_num}}</view>
				<view class="fg_tip">{{app.langReplace('距结束')}}：{{liveCountdown[index]}}</view>
			</view>
			<view class="mt20 fs28 font-w700">
				<text class="color-f6">【{{item.success_num}}{{app.langReplace('人团')}}】</text>
				{{item.goods_name}}
			</view>
			<view class="fs24 mt10">
				<text class="font-w600">￥</text><text class="fs38 font-w600">{{item.exp_price[0]}}</text>.<text class="font-w600">{{item.exp_price[1]}}</text>
				<text class="ml20">{{app.langReplace('单买价')}}： ￥{{item.shop_price}}</text>
				<view class="p10 plr30 fr primarybtn br20">{{app.langReplace('去参团')}}</view>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		name: "tpl_fightgroup",
		props: {
			diyitem: {
				type: Object,
				default:function(){
					return {};
				}
			},
			diyitemid: {
				type: String,
				default: ''
			}
		},
		data() {
			return {
				baseUrl:this.config.baseUrl,
				liveCountTimes:[],
				liveCountdown:[],
				setTime:this.setTimes()
			};
		},
		watch: {},
		computed: {},
		methods: {
			setTimes(){
				let times = [];
				this.diyitem.data.forEach(function(item,key) {
					times.push(item.downDate);
				})
				this.getLiveTimeCount(times);
			},
			getLiveTimeCount(startTime){
				let that = this;
				clearInterval(this.liveCountTimes);
				this.liveCountTimes=setInterval(()=>{
					let nowTime = new Date().getTime();
					let liveCountdownArr = [];
					startTime.forEach(function(time,key) {
						let liveCountdown = '';
						//注：不论安卓还是ios，请将时间如 2020-02-02 20:20:20 转化为 2020/02/02 20:20:20 这种形式后再使用，否则无法转换，如下转换即可↓
						let transedPreTime= time.replace(/-/g,'/') //这里转化时间格式为以/分隔形式
						let preTime = new Date(transedPreTime).getTime()
						let obj = null;
						if(preTime - nowTime > 0){
							let time = (preTime - nowTime) / 1000;
							if (time > 0){
								let day = parseInt(time / (60 * 60 * 24));
								let hou = parseInt(time % (60 * 60 * 24) / 3600);
								let min = parseInt(time % (60 * 60 * 24) % 3600 / 60);
								let sec = parseInt(time % (60 * 60 * 24) % 3600 % 60);
								let msec = Math.floor(time * 1000 % 1000 / 100);
								obj = {
									day: day<10?'0'+day:day,
									hou: hou<10?'0'+hou:hou,
									min: min<10?'0'+min:min,
									sec: sec<10?'0'+sec:sec
								};
								if (day > 0){
									liveCountdown += obj.day + '天';
								}
								if (hou > 0 ){
									liveCountdown += obj.hou + ':';
								}
								liveCountdown += obj.min + ':' + obj.sec;
								liveCountdown += ':'+ msec;
							}else{
								liveCountdown = '已结束';
							}
						} else {
							liveCountdown = '已结束';
						}  
						liveCountdownArr[key] = liveCountdown;
					})
					that.liveCountdown = liveCountdownArr;
				},100)
			},
		}
	}
</script>

<style lang='scss'>
.join_tip{
	position: absolute;
	bottom: 0rpx;
	left: 0rpx;
	background-color: rgba( #000000, 0.5);
	padding:5rpx 15rpx;
	font-size: 24rpx;
	color: #FFFFFF;
}
.fg_tip{
	position: absolute;
	bottom: 0rpx;
	right: 0rpx;
	background-color: rgba( #FF0000, 0.8);
	padding:5rpx 15rpx;
	font-size: 24rpx;
	color: #FFFFFF;
}
</style>

