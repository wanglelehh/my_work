<template>
	<view class="page-body">
		<view class="menu_box ">
			<view class="grid_price"><text>￥</text>{{moneyInfo.earnest_money}}</view>
			<view class="grid_text">剩余保证金</view>
		</view>
		<view class="line_box bg-white p20">
			<u-line-progress class="u-line-progress" :percent="moneyInfo.earnest_money_total_pre" :active-color="activeColor" :inactive-color="inactiveColor">
			</u-line-progress>
			<view class="text">
				<text>扣款比例</text>
				<text class="fr">充值比例</text>
			</view>
			<u-button size="default" shape="circle" type="primary" class="mt40" @click="app.goPage('/pagesB/channel/wallet/recharge?type=earnestMoney')" >去充值</u-button>
		</view>
		<view class="navbar mt20">
			<view 
				v-for="(item, index) in navList" :key="index" 
				class="nav-item" 
				:class="{current: tabCurrentIndex === index}"
				@click="tabClick(index)"
			>
				{{item.text}}
			</view>
		</view>
		<swiper :current="tabCurrentIndex" class="swiper-box bg-white" duration="300" @change="changeTab">
			<swiper-item class="tab-content" v-for="(tabItem,tabIndex) in navList" :key="tabIndex">
				<scroll-view 
					class="list-scroll-content" 
					scroll-y
					@scrolltolower="loadData"
				>
					<!-- 空白页 -->
					<empty v-if="tabItem.loaded === true && tabItem.list.length === 0"></empty>
					<!-- 列表 -->
					<view 
						v-for="(item,index) in tabItem.list" :key="index"
						class="item b-tottom"
					>
					<view v-if="item.earnest_money>0" class="money income"><text>+</text>{{item.earnest_money}}</view>
					<view v-else class="money">{{item.earnest_money}}</view>
						<view class="info">
							{{item.change_desc}}
							<text class="time">{{item.change_time}}</text>
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
				percent: 80,
				mode: 'line',
				activeColor: '#fa436a',
				inactiveColor:'#6696e0',
				tabCurrentIndex: 0,
				moneyInfo:{},
				param: {
					state:0,
					p:0
				}, 
				navList:[{
						state: 'all',
						text: '全部明细',
						loadingType: 'more',
						list: [],
						p:0
					},
					{
						state: 'inc',
						text: '充值明细',
						loadingType: 'more',
						list: [],
						p:0
					},
					{
						state: 'dec',
						text: '扣款明细',
						loadingType: 'more',
						list: [],
						p:0
					},
					{
						state: 'log',
						text: '充值记录',
						loadingType: 'more',
						list: [],
						p:0
					}
				],
			}
		},
		onLoad() {
			this.loadData()
		},
		onShow() {
			this.getMoneyStatic();
		},
		onReady() {},
		methods: {
			//获取订单列表
			loadData(source){
				//这里是将订单挂载到tab列表下
				let index = this.tabCurrentIndex;
				let navItem = this.navList[index];
				if (navItem.loadingType == 'nomore'){
					return;
				}
				if(source === 'tabChange' && navItem.loaded === true){
					//tab切换只有第一次需要加载数据
					return;
				}
				if(navItem.loadingType === 'loading'){
					//防止重复加载
					return;
				}
				navItem.p++;
				this.param.state = navItem.state;
				this.param.p = navItem.p;
				navItem.loadingType = 'loading';
				this.$u.post('channel/api.wallet/getEarnestMoneyLog', this.param).then(res => {
					navItem.list = navItem.list.concat(res.data.list);
					//loaded新字段用于表示数据加载完毕，如果为空可以显示空白页
					this.$set(navItem, 'loaded', true);
					//判断是否还有下一页，有是more  没有是nomore
					navItem.loadingType = navItem.p == res.data.page_count ? 'nomore' : 'more';
				})
			},
			getMoneyStatic(){//钱包保证金相关
				this.$u.post('channel/api.wallet/getEarnestMoneyStatic').then(res => {
					this.moneyInfo = res.data;
				})
			},
			//swiper 切换
			changeTab(e){
				this.tabCurrentIndex = e.target.current;
				this.loadData('tabChange');
			},
			//顶部tab点击
			tabClick(index){
				this.tabCurrentIndex = index;
			},
		}
	}
</script>
	
<style lang="scss">
	@import "~@/pagesB/static/channel/css/wallet.scss";
	.line_box{
		.text{
			margin-top: 10rpx;
			color: $font-color-light;
		}
	}
	.swiper-box{
		height: calc(100% - 520rpx);
		.list-scroll-content{
			position: relative;
			height: 100%;
			.item{
				padding: 20rpx;
				
				.info{
					font-size: 32rpx;
					text{
						font-size: 28rpx;
						display: block;
						color: $font-color-light;
					}
				}
				.money{
					float: right;
					font-size: 32rpx;
					font-weight: 700;
					margin-top: 20rpx;
				}
				.income{
					color: $font-color-base;
				}
			}
		}
	}
</style>
