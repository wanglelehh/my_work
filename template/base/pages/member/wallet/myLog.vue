<template>
	<view class="page-body" :class="[app.setCStyle()]">
		<view class="bg-white mlr20 mt20 p20 br20">
			<view class="relative p20">
				<view class="fs26 color-99" v-if="pageType == 'points'">总积分</view>
					<view class="fs26 color-99" v-else>总余额</view>
				<view class="fs50 font-w500 color-33 mt20"><text class="fs30"  v-if="pageType == 'balance'">￥</text>{{balance}}</view>
				<view class="select_month"  @click="showYearMonth = true">
					{{yearMonth}}<u-icon name="arrow-down" class="ml10" color="#FFFFFF" size="26"></u-icon>
				</view>
			</view>
			<u-select :default-value="defaultYearMonth" mode="mutil-column" v-model="showYearMonth" :list="yearMonthlist" @confirm="confirmYearMonth"></u-select>
			<view class="smll mt30 color-ff text-center mb20">
				<view class="gridbox_l">
					<view class="fs40 ff mt50 font-w500"><text v-if="pageType == 'balance'" class="fs24">￥</text>{{income}}</view>
					<view class="fs28">{{app.langReplace('收入')}}</view>
				</view>
				<view class="gridbox_r">
					<view class="fs40 ff mt50 font-w500"><text v-if="pageType == 'balance'" class="fs24">￥</text>{{outlay}}</view>
					<view class="fs28 ">{{app.langReplace('支出')}}</view>
				</view>
			</view>
		</view>
		

		<view class="bg-white mlr20 mt20 p20 br20 list_box">
			<view class="navbar base-select mb20">
				<view v-for="(item, index) in navList" :key="index" class="nav-item text-center" :class="{current: tabCurrentIndex === index}"
				 @click="tabClick(index)" >
					{{item.text}}
				</view>
			</view>
			<scroll-view class="list-scroll-content " scroll-y @scrolltolower="loadData('add')">
				<!-- 空白页 -->
				<empty v-if="itemList.list.length === 0 && itemList.loaded == true"></empty>
				<!-- 列表 -->
				<view v-for="(item,index) in itemList.list" :key="index" class="item b-tottom smll" @click="app.goPage('logInfo?log_id='+item.log_id+'&type='+pageType)">
					<view class="info flex_bd">
						<text class="fs28">{{item.change_desc}}</text>
						<text class="fs26 color-99 mt10">{{item.change_time}}</text>
					</view>
					<view v-if="pageType == 'points'">
						<view v-if="item.use_integral>0" class="money income fs30"><text>+</text>{{item.use_integral}}</view>
						<view v-else class="money">{{item.use_integral}}</view>
						<view class="fs20 color-99"><text class="fs20">余额:</text>{{item.now_val}}</view>
					</view>
					<view v-else >
						<view v-if="item.balance_money>0" class="money income fs30"><text>+</text>{{item.balance_money}}</view>
						<view v-else class="money fs30">{{item.balance_money}}</view>
						<view class="fs26 color-99"><text class="fs20">余额:￥</text>{{item.now_val}}</view>
					</view>
					<u-icon class="ml20 mt20" name="arrow-right" color="#999999" size="28"></u-icon>
				</view>
				<uni-load-more :status="loadingType"></uni-load-more>
			</scroll-view>
		</view>
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
				calendarText: '',
				showYearMonth:false,
				yearMonth:'',
				defaultYearMonth:[],
				yearMonthlist:[],
				pageType:'',
				tabCurrentIndex: 0,
				navList: [{
						type: 'all',
						text: this.app.langReplace('全部'),
					},
					{
						type: 'income',
						text: this.app.langReplace('收入'),
					},
					{
						type: 'outlay',
						text: this.app.langReplace('支出'),
					},
				],
				param: {
					pageType:'',
					calendar: '',
					searchType: 'all',
					p: 0
				},
				balance: 0.00,
				income: 0.00,
				outlay: 0.00,
				itemList: {
					list: []
				},
				loadingType: 'more'
			}
		},
		onLoad(options) {
			this.pageType = options.pageType;
			let title =  this.app.langReplace('余额明细');
			if (this.pageType == 'points'){
				title =  this.app.langReplace('积分明细');
			}
			uni.setNavigationBarTitle({
			　　title:title
			})
			this.loadData();
			this.getyearMonth();
		},
		onShow() {
			this.app.isLogin(this); //强制登陆
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
				this.loadData('refresh');
			},
			//顶部tab点击
			tabClick(index) {
				this.tabCurrentIndex = index;
				this.param.searchType = this.navList[index].type;
				this.loadData('refresh');
			},
			loadData(source = 'add') {
				if (source == 'add' && this.loadingType == 'nomore') {
					return;
				}
				
				if (source === 'refresh') {
					this.param.p = 0;
					this.itemList.list = [];
					this.itemList.loaded = false;
					this.loadingType == 'more';
				}
				this.param.p++;
				this.param.pageType = this.pageType;
				this.loadingType = 'loading';
				this.$u.post('member/api.wallet/getAccountLog', this.param).then(res => {
					this.itemList.list = this.itemList.list.concat(res.data.list);
					//loaded新字段用于表示数据加载完毕，如果为空可以显示空白页
					this.$set(this.itemList, 'loaded', true);
					//判断是否还有下一页，有是more  没有是nomore
					this.loadingType = this.param.p == res.data.page_count ? 'nomore' : 'more';
					
					if (this.param.searchType == 'all' && this.param.p == 1) {
						this.balance = res.data.balance;
						this.income = res.data.income;
						this.outlay = res.data.outlay;
					}
				})
			}
		}
	}
</script>

<style lang="scss">
	.select_month{
		width: 200rpx;
		height: 54rpx;
		line-height: 54rpx;
		text-align: center;
		font-size: 26rpx;
		background: linear-gradient(90deg, #FE385E 0%, #FE6988 100%);
		border-radius: 27rpx;
		color:#FFFFFF;
		position: absolute;
		right: 0rpx;
		top: 30rpx;
	}
	.gridbox_l{
		width: 50%;
		height: 200rpx;
		margin-right:15rpx;
		background: linear-gradient(0deg, #FE5C86 0%, #FF809B 100%);
		box-shadow: 0px 4rpx 22rpx 2rpx rgba(254, 97, 137, 0.44);
		border-radius: 24rpx;
	}
	.gridbox_r{
		width: 50%;
		height: 200rpx;
		margin-left:15rpx;
		background: linear-gradient(0deg, #FBAF4D 0%, #FEC263 100%);
		box-shadow: 0px 5rpx 16rpx 2rpx rgba(242, 154, 60, 0.5);
		border-radius: 24rpx;
	}
	.navbar .nav-item.current:after{
		width: 35%;
	}
	.list_box {
		height: calc(100vh - 600rpx);
		.title {
			align-items: center;
			font-size: 28rpx;
			border-bottom: 1rpx solid $border-color-light;

			.u-icon {
				float: right;
				margin-top: 10rpx;
			}
		}

		.list-scroll-content {
			position: relative;
			height: calc(100vh - 780rpx);
			.item {
				padding: 20rpx;
				.info {
					font-size: 28rpx;
					text {
						display: block;
					}
				}

				.money {
					width: 100%;
					text-align: right;
					font-size: 28rpx;
					margin-top: 20rpx;
					color: #666666;
				}

				.income {
					color: #FE385E;
				}
			}
		}
	}
</style>
