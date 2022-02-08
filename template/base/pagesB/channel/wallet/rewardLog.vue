<template>
	<view class="page-body">
		<view class="p20 bg-white smll">
			<view class="calendar_box flex_bd" @click="show=true">
				<view class="text fs26">时间：{{calendarText}}
					<u-icon class="ml10" name="arrow-down-fill" size="20"></u-icon>
				</view>
			</view>
			<view class="fr">
				<view class="selectText fs26" @click="selectshow=true">{{selectText}}<u-icon class="ml10" name="arrow-down-fill" size="20"></u-icon></view>
				<u-action-sheet :list="rewardTypeList" :cancel-btn="false" @click="selectRewardType" v-model="selectshow"></u-action-sheet>
			</view>
		</view>
		<u-calendar v-model="show" ref="calendar" @change="changeCalendar" :mode="mode" :start-text="startText" :end-text="endText"
		 :btn-type="btnType" :mask-close-able="false">
		</u-calendar>
		<view class="navbar mt10">
			<view v-for="(item, index) in navList" :key="index" class="nav-item" :class="{current: tabCurrentIndex === index}"
			 @click="tabClick(index)">
				{{item.text}}
			</view>
		</view>
		<swiper :current="tabCurrentIndex" class="swiper-box bg-white" duration="300" @change="changeTab">
			<swiper-item class="tab-content" v-for="(tabItem,tabIndex) in navList" :key="tabIndex">
				<scroll-view class="list-scroll-content" scroll-y @scrolltolower="loadData">
					<!-- 空白页 -->
					<empty v-if="tabItem.loaded === true && tabItem.list.length === 0"></empty>
					<!-- 列表 -->
					<view v-for="(item,index) in tabItem.list" :key="index" class="item b-tottom">
						<view>{{item.reward_name}} 
						<text class=" color-base fr">收益：{{item.reward_money}}</text>
						</view>
						<view class="mt10 color-94">
							<text >{{item.add_time}}</text>
							<text class=" color-base fr">{{item.status}}</text>
						</view>
						<view class="mt10 color-94">
							<text>我的层级：{{item.to_role_name}}</text>
							<text class="fr" v-if="item.from_uid > 0">来源下级：{{item.from_uid}}</text>
						</view>
						<view class="info mt10">{{item.reward_info}}</view>
						
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
				selectText:'全部奖励',
				rewardTypeList:[
					{
						text: '全部奖励',
						id:'all'
					}
				],
				selectshow: false,
				calendarText: '',
				show: false,
				mode: 'range',
				result: "请选择日期",
				startText: '开始',
				endText: '结束',
				btnType: 'primary',
				startDate: '',
				endDate: '',
				tabCurrentIndex: 0,
				param: {
					calendar: '',
					rewardType:'all',
					state: 0,
					p: 0
				},
				navList: [{
						state: 'all',
						text: '全部',
						loadingType: 'more',
						list: [],
						p: 0
					},
					{
						state: '1',
						text: '待返',
						loadingType: 'more',
						list: [],
						p: 0
					},
					{
						state: '2',
						text: '已返',
						loadingType: 'more',
						list: [],
						p: 0
					},
					{
						state: '9',
						text: '取消',
						loadingType: 'more',
						list: [],
						p: 0
					}
				],
			}
		},
		onLoad() {
			this.loadData()
		},
		onShow() {
		},
		onReady() {},
		methods: {
			changeCalendar(e) {
				this.calendarText = e.startDate + "~" + e.endDate;
				this.param.calendar = this.calendarText;
				this.searchText = this.calendarText;
				this.loadData('change');
			},
			selectRewardType(index){
				var sel = this.rewardTypeList[index];
				this.selectText = sel.text;
				this.param.rewardType = sel.id;
				this.loadData('change');
			},
			//获取订单列表
			loadData(source) {
				//这里是将订单挂载到tab列表下
				let index = this.tabCurrentIndex;
				let navItem = this.navList[index];
				if (source === 'change'){
					navItem.list = [];
					navItem.p = 0;
					navItem.loadingType = 'more';
				}
				if (navItem.loadingType == 'nomore' || navItem.loadingType === 'loading') {
					return;
				}
				if (source === 'tabChange' && navItem.loaded === true) {
					//tab切换只有第一次需要加载数据
					return;
				}
				
				navItem.p++;
				this.param.state = navItem.state;
				this.param.p = navItem.p;
				navItem.loadingType = 'loading';
				this.$u.post('channel/api.wallet/getRewardLog', this.param).then(res => {
					navItem.list = navItem.list.concat(res.data.list);
					//loaded新字段用于表示数据加载完毕，如果为空可以显示空白页
					this.$set(navItem, 'loaded', true);
					//判断是否还有下一页，有是more  没有是nomore
					navItem.loadingType = navItem.p == res.data.page_count ? 'nomore' : 'more';
					if (this.calendarText == '') {
						this.calendarText = res.data.startDate + '~' + res.data.endDate;
						res.data.rewardList.forEach(item=>{
							this.rewardTypeList.push(item);
						})
					}
				})
			},
			
			//swiper 切换
			changeTab(e) {
				this.tabCurrentIndex = e.target.current;
				this.loadData();
			},
			//顶部tab点击
			tabClick(index) {
				this.tabCurrentIndex = index;
			},
		}
	}
</script>

<style lang="scss">
	@import "~@/pagesB/static/channel/css/wallet.scss";

	.line_box {
		.text {
			margin-top: 10rpx;
			color: $font-color-light;
		}
	}

	.swiper-box {
		height: calc(100% - 190rpx);
		.list-scroll-content {
			position: relative;
			height: 100%;
			.item {
				padding: 20rpx;
				font-size: 30rpx;
					
				.info{
					display: block;
					font-size: 28rpx;
					color: $font-color-light;
				}
				.money {
					font-size: 30rpx;
					font-weight: 700;
					color: $font-color-base;
				}
			}
		}
	}
</style>
