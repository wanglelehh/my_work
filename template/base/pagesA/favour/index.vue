<template>
	<view class="page-body" :class="[app.setCStyle()]">
		<view>
			<image src="@/pagesA/static/images/scall.png" class="w100" mode="widthFix"></image>
		</view>
		<view class="main">
			<scroll-view class="scroll_tab" scroll-x="true"  :scroll-left="scrollLeft">
				<view class="scroll_item" :class="selKey==index?'active':''" v-for="(item,index) in tabList" :key="index" @click="selTab(index)">
					<view class="fs34">{{item.name}}</view>
					<view class="fs22 mt10">{{app.langReplace(item._status)}}</view>
				</view>
			</scroll-view>
			<view class="list_box">
				<view class="list">
					<view class="flex p20">
						<view class="flex_bd fs34 font-w700">{{app.langReplace('好货限量抢')}}</view>
						<view v-if="tabList.length > 0" class="fs26 times">
							<view v-if="tabList[selKey].status == 0">{{app.langReplace('距开始')}}<text>{{times.hou}}</text>:<text>{{times.min}}</text>:<text>{{times.sec}}</text></view>
							<view v-else-if="tabList[selKey].status == 1">{{app.langReplace('距结束')}}<text>{{times.hou}}</text>:<text>{{times.min}}</text>:<text>{{times.sec}}</text></view>
							<view v-else >{{app.langReplace('已结束')}}</view>
						</view>
					</view>
					<scroll-view class="scroll_goods" scroll-y>
						<empty v-if="loaded === true && selList.length === 0"></empty>
						<view v-for="(item, index) in selList" :key="index" class="goods_item" @click="goPage('/pages/shop/goods/info?goods_id='+item.goods_id)">
							<view class="image" >
								<image :src="baseUrl+item.cover" class="w100" mode="widthFix"></image>
							</view>
							<view class="info flex_bd">
								<view class="goods_name">{{item.goods_name}}</view>
								<view v-if="tabList[selKey].status == 1" class="smll">
									<view class="line"><view class="bg-base hb100" :style="{'width':item.percent+'%'}"></view></view><view class="ml20 fs26 color-99">{{app.langReplace('已售')}}:{{item.sales}}{{app.langReplace('件')}}</view>
								</view>
								<view class="smll">
									<view class="flex_bd fs22 base-color">
										￥<text class="fs34">{{item.exp_price[0]}}</text><text>.{{item.exp_price[1]}}</text>
										<text class="color-cc ml20 line-through">￥{{item.shop_price}}</text>
									</view>
									<view v-if="tabList[selKey].status == 0" class="bg-cc br20 plr20 ptm5 fs28 color-66">{{app.langReplace('未开始')}}</view>
									<view v-else-if="tabList[selKey].status == 1" class="primarybtn br20 plr20 ptm5 fs28">{{app.langReplace('马上抢')}}</view>
									<view v-else class="bg-cc br20 plr20 ptm5 fs28 color-66">{{app.langReplace('已结束')}}</view>
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
	import empty from "@/components/empty";
	export default {
		components: {
			empty
		},
		data() {
			return {
				baseUrl: this.config.baseUrl,
				tabCurrentIndex: 0,
				tabList:[],
				itemList:[],
				scrollLeft:0,
				selKey:0,
				selList:[],
				loaded:false,
				liveCountTimes:'',
				times:{}
			};
		},
		onLoad(options) {
			let title = this.app.langReplace('限时优惠');
			uni.setNavigationBarTitle({
			　　title:title
			})
			this.loadData();
		},
		onShow(){
		},
		onHide(){
		},
		computed: {},
		methods: {
			//获取拼团列表
			loadData() {
				let that = this;
				let scrollLeft = 0
				this.$u.post('favour/api.goods/getList').then(res => {
					this.tabList = res.data.tabList;
					this.itemList = res.data.list;
					this.loaded = true;
					this.tabList.forEach(function(v,k){
						if (v.status == 1){
							scrollLeft = 62 * k;
							that.selTab(k);
						}
					});
					setTimeout(function(){
						that.scrollLeft = scrollLeft;
					},100);
				})
				
			},
			selTab(key) {
				this.selKey = key;
				this.selList = [];
				let k = this.tabList[key].name;
				if (typeof(this.itemList[k]) != 'undefined'){
					this.selList = this.itemList[k];
				}
				this.getLiveTimeCount(this.tabList[key].diff_time);
			},
			getLiveTimeCount(time){
				this.times = {
					day: '00',
					hou: '00',
					min: '00',
					sec: '00'
				};
				if (time == 0) return false; 
				if (typeof(this.liveCountTimes) != 'undefined'){
					clearInterval(this.liveCountTimes);
				}
				this.liveCountTimes=setInterval(()=>{
					let nowTime = new Date().getTime();
					//注：不论安卓还是ios，请将时间如 2020-02-02 20:20:20 转化为 2020/02/02 20:20:20 这种形式后再使用，否则无法转换，如下转换即可↓
					let transedPreTime= time.replace(/-/g,'/') //这里转化时间格式为以/分隔形式
					let preTime = new Date(transedPreTime).getTime()
					if(preTime - nowTime > 0){
						let time = (preTime - nowTime) / 1000;
						if (time > 0){
							let day = parseInt(time / (60 * 60 * 24));
							let hou = parseInt(time % (60 * 60 * 24) / 3600);
							let min = parseInt(time % (60 * 60 * 24) % 3600 / 60);
							let sec = parseInt(time % (60 * 60 * 24) % 3600 % 60);
							let msec = Math.floor(time * 1000 % 1000 / 100);
							this.times = {
								day: day<10?'0'+day:day,
								hou: hou<10?'0'+hou:hou,
								min: min<10?'0'+min:min,
								sec: sec<10?'0'+sec:sec
							};
						}else{
							this.loadData();
						}
					}else{
						this.loadData();
					}
				},500)
			},
			goPage(url){
				this.app.goPage(url);
			}
		}
	}
</script>

<style lang="scss">
	.main{
		position: fixed;
		top:160rpx;
		width: 100%;
		height: calc(100vh - 160rpx);
	}
	.scroll_tab{
		background-color: #f65236;
		white-space: nowrap;
		padding-top: 20rpx;
		color: #FFFFFF;
	}
	.scroll_item{
		text-align: center;
		display: inline-block;
		width: 125rpx;
		opacity: .6;
		padding-bottom: 20rpx;
	}
	.scroll_tab .active{
		opacity: 1;
		position: relative;
	}
	.scroll_tab .active::after {
		position: absolute;
		bottom:0rpx;
		left: 25%;
	    content: " ";
	    width:50%;
	    border-bottom: 8rpx solid #fff;
	    transform-origin: left top;
	}
	.times text{
		font-size: 23rpx;
		margin:0rpx 6rpx;
		padding:6rpx 4rpx;
		background-color: #000000;
		color: #FFFFFF;
	}
	.list_box{
		padding: 20rpx;
		height: calc(100% - 130rpx);
	}
	.list{
		height: 100%;
		background-color: #FFFFFF;
		border-radius: 20rpx;
	}
	.scroll_goods{
		height: calc(100% - 100rpx);
	}
	.goods_item {
	    margin-bottom: 20rpx;
		padding: 20rpx;
	    border-radius: 10rpx;
	    display:flex;
	}
	.goods_item .image{
		width: 200rpx;
		height: 200rpx;
		border-radius: 10rpx;
		overflow: hidden;
		image {
			width: 100%;
			height: 100%;
			opacity: 1;
		}
	}
	.goods_item .info{
		display: flex;
		flex-direction: column;
		justify-content: space-between;
		flex: 1;
		overflow: hidden;
		position: relative;
		padding-left: 20rpx;
		.goods_name {
			font-size: 28rpx;
			color: $font-color-dark;
			line-height: 40rpx;
			height: 80rpx;
			overflow: hidden;
			text-overflow: ellipsis;
			display: -webkit-box;
		}
		.line{
			width: 160rpx;
			height: 10rpx;
			background-color: #fcebe8;
			border-radius: 10rpx;
			overflow: hidden;
		}
	}
</style>
