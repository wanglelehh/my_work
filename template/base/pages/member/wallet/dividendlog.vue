<template>
	<view class="page-body" :class="[app.setCStyle()]">
		<view class="p20 bg-white smll">
			<view class="calendar_box flex_bd" @click="show=true">
				<view class="select_month"  @click="showYearMonth = true">
					{{yearMonth}}<u-icon name="arrow-down" class="ml10" color="#FFFFFF" size="26"></u-icon>
				</view>
			</view>
			<view class="fr ">
				<view class="selectText fs26" @click="selectshow=true">{{selectText}}<u-icon class="ml10" name="arrow-down-fill" size="20"></u-icon></view>
				<u-action-sheet :list="rewardTypeList" :cancel-btn="false" @click="selectRewardType" v-model="selectshow"></u-action-sheet>
			</view>
		</view>
		<u-select :default-value="defaultYearMonth" mode="mutil-column" v-model="showYearMonth" :list="yearMonthlist" @confirm="confirmYearMonth"></u-select>
		<view class="navbar base-select mt10">
			<view v-for="(item, index) in navList" :key="index" class="nav-item" :class="{current: tabCurrentIndex === index}"
			 @click="tabClick(index)">
				{{item.text}}
			</view>
		</view>
		<swiper :current="tabCurrentIndex" class="swiper-box " duration="300" @change="changeTab">
			<swiper-item class="tab-content relative br20 mt20" v-for="(tabItem,tabIndex) in navList" :key="tabIndex">
				<view class="absolute w100 font-w600 p20  bg-white b-top" style="bottom:0;;z-index: 9999;">
						{{app.langReplace('总计')}}：<text class="fs26">￥</text>{{tabItem.income}}
				</view>
				<scroll-view class="list-scroll-content " scroll-y >
					<!-- 空白页 -->
					<empty v-if="tabItem.loaded === true && tabItem.list.length === 0"></empty>
					<!-- 列表 -->
					<view v-for="(item,index) in tabItem.list" :key="index" class="item  bg-white mlr20 mt20 br20">
						<view>{{item.award_name}} 
						<text class=" color-base fr">{{app.langReplace('收益')}}：{{item.dividend_amount}}</text>
						</view>
						<view class="mt10 color-94">
							<text >{{item._time}}</text>
							<text class=" color-base fr">{{app.langReplace(item.status)}}</text>
						</view>
						<view class="mt10 color-94">
							<text >{{app.langReplace('分佣身份')}}：{{item.role_name}}</text>
							<text class="fr" v-if="item.buy_uid > 0">{{app.langReplace('来源会员')}}：{{item.buy_uid}}</text>
						</view>
						<view class="info mt10">
							<text v-if="item.level_award_name">{{app.langReplace('奖励名称')}}：{{item.level_award_name}},</text>
							<text v-if="item.level > 0">{{app.langReplace('分佣层级')}}：{{item.level}}级</text>
						</view>
					</view>
					<view class="p40"></view>
				</scroll-view>
			</swiper-item>
		</swiper>
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
				selectText:this.app.langReplace('全部奖励'),
				rewardTypeList:[
					{
						text: this.app.langReplace('全部奖励'),
						id:'all'
					}
				],
				selectshow: false,
				yearMonth: '',
				showYearMonth: false,
				defaultYearMonth:[],
				yearMonthlist:[],
				tabCurrentIndex: 0,
				param: {
					calendar: '',
					rewardType:'all',
					type: '',
				},
				navList: [{
						type: 'all_balance_money',
						text: this.app.langReplace('全部'),
						loadingType: 'more',
						list: [],
						income: 0.00
					},
					{
						type: 'wait_balance_money',
						text: this.app.langReplace('待返'),
						loadingType: 'more',
						list: [],
						income: 0.00
					},
					{
						type: 'arrival_balance_money',
						text: this.app.langReplace('已返'),
						loadingType: 'more',
						list: [],
						income: 0.00
					},
					{
						type: 'cancel_balance_money',
						text: this.app.langReplace('失效'),
						loadingType: 'more',
						list: [],
						income: 0.00
					}
				],
			}
		},
		onLoad() {
			this.loadData();
			let title = this.app.langReplace('我的帐本');
			uni.setNavigationBarTitle({
			　　title:title
			})
			this.getyearMonth();
		},
		onShow() {
		},
		onReady() {},
		methods: {
			getyearMonth(){//获取月份
				let nowDate = new Date();
				let nowYear = nowDate.getFullYear();
				let nowMonth = nowDate.getMonth();
				nowMonth = nowMonth + 1;
				if (nowMonth.toString().length == 1) {
				   nowMonth = "0" + nowMonth;
				}
				this.yearMonth = nowYear+'年'+nowMonth+'月';
				let yearList = [];
				for(let yi=0;yi<=10;yi++){
					let year = {};
					year.label = (nowYear-yi).toString();
					year.value = yi;
					yearList.push(year);
				}
				this.yearMonthlist.push(yearList);
				let monthList = [];
				let selectMonth = 0;
				for(let mi=1;mi<=12;mi++){
					let month = {};
					month.label = mi.toString();
					if (month.label.length == 1) {
					   month.label = "0" + month.label;
					}
					month.value = mi-1;
					monthList.push(month);
					if (month.label == nowMonth){
						selectMonth = month.value;
					}
				}
				this.yearMonthlist.push(monthList);
				this.defaultYearMonth = [0,selectMonth];
			},
			confirmYearMonth(e){//选择日期
				this.yearMonth = e[0].label+'年'+e[1].label+'月';
				this.defaultYearMonth = [e[0].value,e[1].value];
				this.param.calendar = e[0].label+'-'+e[1].label;
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
				
				this.param.type = navItem.type;
				navItem.loadingType = 'loading';
				this.$u.post('member/api.wallet/getDividendLog', this.param).then(res => {
					navItem.list = navItem.list.concat(res.data.list);
					navItem.income = res.data.income;
					//loaded新字段用于表示数据加载完毕，如果为空可以显示空白页
					this.$set(navItem, 'loaded', true);
					//判断是否还有下一页，有是more  没有是nomore
					navItem.loadingType = navItem.p == res.data.page_count ? 'nomore' : 'more';
					if (this.rewardTypeList.length == 1 && res.data.rewardList) {
						res.data.rewardList.forEach(item=>{
							this.rewardTypeList.push(item);
						})
					}
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
		}
	}
</script>

<style lang="scss">
	@import "~@/pagesB/static/channel/css/wallet.scss";
	.select_month{
		width: 200rpx;
		height: 54rpx;
		line-height: 54rpx;
		text-align: center;
		font-size: 26rpx;
		background: linear-gradient(90deg, #FE385E 0%, #FE6988 100%);
		border-radius: 27rpx;
		color:#FFFFFF;
	}
	.line_box {
		.text {
			margin-top: 10rpx;
			color: $font-color-light;
		}
	}

	.swiper-box {
		height: calc(100% - 210rpx);
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
