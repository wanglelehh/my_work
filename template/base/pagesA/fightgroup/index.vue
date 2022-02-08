<template>
	<view class="page-body" :class="[app.setCStyle()]">
		<view class="navbar base-select">
			<view v-for="(item, index) in navList" :key="index" class="nav-item" :class="{current: tabCurrentIndex === index}"
			 @click="tabClick(index)">
				{{item.text}}
			</view>
		</view>
		<swiper :current="tabCurrentIndex" class="swiper-box" duration="300" @change="changeTab">
			<swiper-item class="tab-content" v-for="(tabItem,tabIndex) in navList" :key="tabIndex">
				<scroll-view class="hb100" scroll-y @scrolltolower="loadData">
					<!-- 空白页 -->
					<empty v-if="tabItem.loaded === true && tabItem.list.length === 0"></empty>
					<!-- 列表 -->
					<view v-for="(item,index) in tabItem.list" :key="index" class="p20 mb20 bg-white item" @click="goPage(item.fg_id)">
						<view class="w100 relative">
							<image class="w100" :src="baseUrl+item.cover" mode="widthFix"></image>
							<view class="join_tip">{{app.langReplace('参团数')}}：{{item.success_order_num}}</view>
							<view v-if="tabItem.type == 'pging'" class="fg_tip">{{app.langReplace('距结束')}}：{{showDownTime(tabItem.type,index)}}</view>
							<view v-else class="fg_tip">{{app.langReplace('距开始')}}：{{showDownTime(tabItem.type,index)}}</view>
						</view>
						<view class="mt20 fs28 font-w700">
							<text class="color-f6">【{{item.success_num}} {{app.langReplace('人团')}}】</text>
							{{item.goods_name}}
						</view>
						<view class="fs24 mt10">
							<text class="font-w600">￥</text><text class="fs38 font-w600">{{item.exp_price[0]}}</text>.<text class="font-w600">{{item.exp_price[1]}}</text>
							<text class="ml20">{{app.langReplace('单买价')}}：￥{{item.shop_price}}</text>
							<view v-if="tabItem.type == 'pging'" class="p10 plr30 fr primarybtn br20">{{app.langReplace('去参团')}}</view>
							<view v-else class="p10 plr30 fr bg-gray color-ff br20">{{app.langReplace('待开团')}}</view>
						</view>
					</view>
					<uni-load-more :status="tabItem.loadingType"></uni-load-more>
				</scroll-view>
			</swiper-item>
		</swiper>
	</view>
</template>


<script>
	import uniLoadMore from '@/components/uni-load-more/uni-load-more.vue';
	import empty from "@/components/empty";
	export default {
		components: {
			uniLoadMore,
			empty
		},
		data() {
			return {
				baseUrl: this.config.baseUrl,
				tabCurrentIndex: 0,
				param: {
					type: '',
					p: 0
				},
				navList: [{
						type: 'pging',
						text: this.app.langReplace('拼团中'),
						loadingType: 'more',
						list: [],
						p: 0
					},
					{
						type: 'pgwait',
						text: this.app.langReplace('待开团'),
						loadingType: 'more',
						list: [],
						num:0,
						p: 0
					},
				],
				liveCountTimes:[],
				liveCountdown:[]
			};
		},
		onLoad(options) {
			let title = this.app.langReplace('拼团列表');
			uni.setNavigationBarTitle({
				title
			})
			this.loadData();
		},
		onShow(){
		},
		onHide(){
		},
		computed: {},
		methods: {
			goPage(fg_id){
				this.app.goPage('info?fg_id='+fg_id);
			},
			//获取拼团列表
			loadData(source) {
				//这里是将订单挂载到tab列表下
				let index = this.tabCurrentIndex;
				let navItem = this.navList[index];
				if (navItem.loadingType == 'nomore') {
					return;
				}
				if (source === 'tabChange' && navItem.loaded === true) {
					//tab切换只有第一次需要加载数据
					return;
				}
				if (navItem.loadingType === 'loading') {
					//防止重复加载
					return;
				}
				navItem.p++;
				this.param.type = navItem.type;
				this.param.p = navItem.p;
				navItem.loadingType = 'loading';
				this.$u.post('fightgroup/api.goods/getList', this.param).then(res => {
					navItem.list = navItem.list.concat(res.data.list);
					let times = [];
					navItem.list.forEach(function(item,key) {
						times.push(item.downDate);
					})
					this.getLiveTimeCount(this.param.type,times);
					//loaded新字段用于表示数据加载完毕，如果为空可以显示空白页
					this.$set(navItem, 'loaded', true);
					//判断是否还有下一页，有是more  没有是nomore
					navItem.loadingType = navItem.p == res.data.page_count ? 'nomore' : 'more';
				})
			},
			//swiper 切换
			changeTab(e) {
				this.tabCurrentIndex = e.target.current;
				this.loadData('tabChange');
			},
			//顶部tab点击
			tabClick(index) {
				this.tabCurrentIndex = index;
			},
			showDownTime(type,index){
				if (typeof(this.liveCountdown[type]) != 'undefined'){
					return this.liveCountdown[type][index];
				}
			},
			getLiveTimeCount(type,startTime){
				let that = this;
				if (typeof(this.liveCountTimes[type]) != 'undefined'){
					clearInterval(this.liveCountTimes[type]);
				}
				this.liveCountTimes[type]=setInterval(()=>{
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
								liveCountdown = type == 'pging'?'已结束':'已开团';
							}
						} else {
							liveCountdown = type == 'pging'?'已结束':'已开团';
						}  
						liveCountdownArr[key] = liveCountdown;
					})
					this.$set(that.liveCountdown, type, liveCountdownArr);
				},100)
			},
		}
	}
</script>

<style lang='scss'>
	.swiper-box {
		height: calc(100% - 50px);
	}
	.item{
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
	}
	
</style>
