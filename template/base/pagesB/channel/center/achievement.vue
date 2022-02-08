<template>
	<view class="page-body ">
		<view class="top_navbar">
			<view class="navbar">
				<view class="nav-item" v-for="(item, index) in navList" :key="index" :class="{current: topTabCurrentIndex === index}"
				 @click="topTabClick(index)">{{item.text}}</view>
			</view>
		</view>
		<u-select :default-value="defaultYearMonth" mode="mutil-column" v-model="showYearMonth" :list="yearMonthlist" @confirm="confirmYearMonth"></u-select>
		<swiper :current="topTabCurrentIndex" class="swiper-box" disable-touch="true" duration="300" @change="topChangeTab">
			<swiper-item class="tab-content " v-for="(item, index) in navList" :key="index">
				<view class="p20 bg-white">
					<view class="year_month"  @click="showYearMonth = true">
						{{item.yearMonth}}月份<u-icon class="ml10" name="arrow-down-fill" size="22"></u-icon>
						<text class="tip mt10">请按月份进行筛选</text>
					</view>
					<u-grid :col="2" :border="false" class="mt20 top_static_box">
						<u-grid-item class="relative">
							<view class="price"><text>￥</text>{{item.data.myTotal}}</view>
							<view class="text">直推代理个人业绩</view>
							<view class="b-right"></view>
						</u-grid-item>
						<u-grid-item>
							<view class="price"><text>￥</text>{{item.data.teamTotal}}</view>
							<view class="text">推荐团队业绩</view>
						</u-grid-item>
					</u-grid>
				</view>
				<view class="p20 bg-white mt20 my_static">
					<view>月报-我的业绩趋势</view>
					<view class="info">
						<view class="price"><text>￥</text>{{item.data.total}}</view>
						<view class="tip">本月我的累计业绩</view>
					</view>
					<view class="qiun-charts">
						<canvas :canvas-id="'canvasLine'+index" :id="'canvasLine'+index" class="charts" disable-scroll=true @touchstart="touchLineA"
						 @touchmove="moveLineA" @touchend="touchEndLineA"></canvas>
					</view>
					<view>直属代理团队业绩</view>
					<view class="tip"><u-icon name="question-circle" color="$font-color-light" size="28"></u-icon> 统计推荐团队含本人的汇总业绩</view>
				</view>
			</swiper-item>
		</swiper>
	</view>
</template>


<script>
	// 引入组件
	import uCharts from '@/components/u-charts/u-charts.js';
	var _self;
	var canvaLineA = null;
	export default {
		data() {
			return {
				topTabCurrentIndex: 0,
				navList: [{
						state: 'recharge',
						text: '充值业绩',
						yearMonth: '',
						series: [{
							name: '升级货款',
							data: [],
							color: '#000000'
						}, {
							name: '货款',
							data: []
						}],
						data: {},
						get:true,
						url:'channel/api.stat/getMyRechargeStat'
					},
					{
						state: 'order',
						text: '下单业绩',
						yearMonth: '',
						series: [{
							name: '云仓订单',
							data: [],
							color: '#000000'
						}],
						data: {},
						get:true,
						url:'channel/api.stat/getMyOrderStat'
					}
				],
				showYearMonth:false,
				defaultYearMonth:[],
				yearMonthlist:[],
				cWidth: '',
				cHeight: '',
				pixelRatio: 1,
				chartData: {
					categories: [],
					series: []
				}
			}
		},
		onLoad(options) {
			this.getyearMonth();
			this.cWidth = uni.upx2px(750);
			this.cHeight = uni.upx2px(500);
			this.loadData();
		},
		methods: {
			//顶部tab点击
			topTabClick(index) {
				this.topTabCurrentIndex = index;
			},
			topChangeTab(e) {
				this.topTabCurrentIndex = e.target.current;
				this.loadData();
			},
			getyearMonth(){//获取月份
				let nowDate = new Date();
				let nowYear = nowDate.getFullYear();
				let nowMonth = nowDate.getMonth();
				nowMonth = nowMonth + 1;
				if (nowMonth.toString().length == 1) {
				   nowMonth = "0" + nowMonth;
				}
				this.navList[0].yearMonth = this.navList[1].yearMonth = nowYear+'-'+nowMonth;
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
				this.navList[this.topTabCurrentIndex].yearMonth = e[0].label+'-'+e[1].label;
				this.defaultYearMonth = [e[0].value,e[1].value];
				this.loadData('refresh');
			},
			showLineA(canvasId, chartData) {
				_self = this;
				canvaLineA = new uCharts({
					$this: _self,
					canvasId: canvasId,
					type: 'line',
					fontSize: 11,
					legend: {
						show: true
					},
					dataLabel: false,
					dataPointShape: true,
					background: '#FFFFFF',
					pixelRatio: _self.pixelRatio,
					categories: chartData.categories,
					series: chartData.series,
					animation: true,
					enableScroll: true,
					xAxis: {
						type: 'grid',
						gridColor: '#CCCCCC',
						gridType: 'dash',
						dashLength: 8,
						itemCount: 15, //x轴单屏显示数据的数量，默认为5个
						scrollShow: true, //新增是否显示滚动条，默认false
						scrollAlign: 'right', //滚动条初始位置
					},
					yAxis: {
						gridType: 'dash',
						gridColor: '#CCCCCC',
						dashLength: 8,
						splitNumber: 5,
						min: 10,
						max: 180,
						format: (val) => {
							return val.toFixed(0) + ''
						}
					},
					width: _self.cWidth * _self.pixelRatio,
					height: _self.cHeight * _self.pixelRatio,
					extra: {
						line: {
							type: 'straight'
						}
					}
				});

			},
			touchLineA(e) {
				canvaLineA.scrollStart(e);
			},
			moveLineA(e) {
				canvaLineA.scroll(e);
			},
			touchEndLineA(e) {
				canvaLineA.scrollEnd(e);
				//下面是toolTip事件，如果滚动后不需要显示，可不填写
				canvaLineA.showToolTip(e, {
					format: function(item, category) {
						return category + ' ' + item.name + ':￥' + item.data+''
					}
				});
			},
			loadData(source = 'add'){//获取统计数据
				let index = this.topTabCurrentIndex;
				let navItem = this.navList[index];
				if (source == 'add' && navItem.get == false){
					return false;
				}
				if (navItem.loadingType === 'loading') {
					//防止重复加载
					return;
				}
				navItem.loadingType = 'loading';
				let param = {};
				param.yearMonth = navItem.yearMonth;
				navItem.get = false;
				this.$u.post(navItem.url,param).then(res => {
					navItem.data.total = res.data.total;
					navItem.data.myTotal = res.data.myTotal;
					navItem.data.teamTotal = res.data.teamTotal;
					navItem.loadingType = '';
					this.$set(navItem, 'loaded', true);
					this.$forceUpdate();//刷新数据
					this.chartData.categories = res.data.days;
					this.chartData.series = navItem.series;
					this.chartData.series[0].data = res.data.series;
					this.showLineA("canvasLine"+index, this.chartData);
				});
			}
		}
	}
</script>

<style lang="scss">
	.year_month {
		.tip {
			display: block;
			font-size: 24rpx;
			color: $font-color-light;
		}
	}

	.top_static_box {
		.price {
			float: left;
			padding-top: 10rpx;
			font-size: 36rpx;
			font-weight: 500;

			text {
				font-size: 28rpx;
			}
		}

		.text {
			float: left;
			color: #979598;
			font-size: 28rpx;
		}
	}

	.my_static {
		.info {
			text-align: center;
			.price {
				padding-top: 20rpx;
				font-size: 36rpx;
				font-weight: 500;
				text {
					font-size: 28rpx;
				}
			}
			.tip {
				color: #979598;
				font-size: 28rpx;
			}
		}
		.tip {
			color: #979598;
			font-size: 26rpx;
		}
	}

	.qiun-charts {
		width: 750upx;
		height: 500upx;
		background-color: #FFFFFF;
	}

	.charts {
		width: 750upx;
		height: 500upx;
		background-color: #FFFFFF;
	}
</style>
