<template>
	<view class="page-body">
		<view class="calendar_box b-tottom bg-white" @click="show=true">
			<view class="text">{{calendarText}}
				<u-icon class="ml10" name="arrow-down-fill" size="20"></u-icon>
			</view>
		</view>
		<u-calendar v-model="show" ref="calendar" @change="changeCalendar" :mode="mode" :start-text="startText" :end-text="endText"
		 :btn-type="btnType" :mask-close-able="false">
		</u-calendar>
		<u-grid :col="2" :border="false" class="top_static">
			<u-grid-item class="relative">
					<view class="price"><text>￥</text>{{income}}</view>
					<view class="text">收入</view>
					<view class="b-right"></view>
			</u-grid-item>
			<u-grid-item>
				<view class="price"><text>￥</text>{{outlay}}</view>
				<view class="text">支出</view>
			</u-grid-item>
		</u-grid>
		<view class="list_box bg-white mt20 ">
			<view class="title b-tottom p30" @click="showSelectBox=true">
				{{searchText}}
				<u-icon class="u-icon" name="arrow-down-fill" size="20"></u-icon>
			</view>
			<view v-if="showSelectBox" class="select_box">
				<view class="mask-box" @click="showSelectBox=false"></view>
				<view class="u-grid-box">
				<u-grid  :col="3" :border="false">
					<u-grid-item @click="selectChange('all','全部')">
						<view class="w100 ">
							<view class="img">
								<u-image src="/static/public/images/icon_04.png" mode="widthFix"></u-image>
							</view>
							<text>全部</text>
						</view>
					</u-grid-item>
					<u-grid-item  @click="selectChange('income','收入')">
						<view class="w100 ">
							<view class="img">
								<u-image src="/static/public/images/icon_05.png" mode="widthFix"></u-image>
							</view>
							<text>收入</text>
						</view>
					</u-grid-item>
					<u-grid-item @click="selectChange('outlay','支出')">
						<view class="w100 ">
							<view class="img">
								<u-image src="/static/public/images/icon_06.png" mode="widthFix"></u-image>
							</view>
							<text>支出</text>
						</view>
					</u-grid-item>
				</u-grid>
				</view>
			</view>
			<scroll-view class="list-scroll-content" scroll-y @scrolltolower="loadData">
				<!-- 空白页 -->
				<empty v-if="itemList.length === 0"></empty>
				<!-- 列表 -->
				<view v-for="(item,index) in itemList" :key="index" class="item b-tottom">
					<view v-if="item.balance_money>0" class="money income"><text>+</text>{{item.balance_money}}</view>
					<view v-else class="money">{{item.balance_money}}</view>
					<view class="info">
						{{item.change_time}}
						<text class="time">{{item.change_desc}}</text>
					</view>
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
				show: false,
				mode: 'range',
				result: "请选择日期",
				startText: '开始',
				endText: '结束',
				btnType: 'primary',
				startDate: '',
				endDate: '',
				searchText: '全部',
				param: {
					calendar: '',
					searchType: 'all',
					p: 0
				},
				income: 0.00,
				outlay: 0.00,
				showSelectBox: false,
				itemList: [],
				loadingType: 'more'
			}
		},
		onLoad() {
			this.loadData();
		},
		onShow() {

		},
		onReady() {},
		methods: {
			selectChange(type, text) {
				this.searchText = text;
				this.showSelectBox = false;
				this.param.searchType = type;
				this.param.p = 0;
				this.itemList = [];
				this.loadingType = 'more';
				this.loadData('change');
			},
			changeCalendar(e) {
				this.calendarText = e.startDate + "~" + e.endDate;
				this.param.calendar = this.calendarText;
				this.selectChange('all', '全部');
			},
			loadData(source) {
				if (source != 'change' && this.loadingType == 'nomore') {
					return;
				}
				this.param.p++;
				this.loadingType = 'loading';
				this.$u.post('channel/api.wallet/getBalanceLog', this.param).then(res => {
					this.itemList = this.itemList.concat(res.data.list);
					//loaded新字段用于表示数据加载完毕，如果为空可以显示空白页
					this.$set(this.itemList, 'loaded', true);
					//判断是否还有下一页，有是more  没有是nomore
					this.loadingType = this.param.p == res.data.page_count ? 'nomore' : 'more';
					if (this.param.searchType == 'all' && this.param.p == 1) {
						this.income = res.data.income;
						this.outlay = res.data.outlay;
					}
					if (this.calendarText == '') {
						this.calendarText = res.data.startDate + '~' + res.data.endDate;
					}
				})
			}
		}
	}
</script>

<style lang="scss">
	.calendar_box {
		display: flex;
		align-items: center;
		height: 100rpx;

		.text {
			width: 100%;
			;
			text-align: center;
			font-size: 32rpx;
			font-weight: 700;
		}
	}

	.top_static {
		text-align: center;

		.price {
			font-size: 35rpx;
			font-weight: 700;

			text {
				font-size: 28rpx;
			}
		}

		.text {
			color: $font-color-light;
		}
	}

	.list_box {
		height: calc(100vh - 260rpx);
		.title {
			align-items: center;
			font-size: 36rpx;
			font-weight: 700;
			height: 120rpx;
			border-bottom: 1rpx solid $border-color-light;

			.u-icon {
				float: right;
				margin-top: 10rpx;
			}
		}

		.select_box {
			position: relative;
			height: calc(100vh - 110rpx);
			.u-grid-box {
				position: absolute;
				top: 0rpx;
				z-index: 99;
				text-align: center;
				background-color: #FFFFFF;
				padding-top: 10rpx;
				width: 100%;
				height: 120rpx;
				.img {
					width: 50rpx;
					height: 50rpx;
					margin: 0rpx auto;
					margin-bottom: 10rpx;
				}
			}

		}
		.list-scroll-content {
			position: relative;
			height: calc(100vh - 380rpx);
			.item {
				padding: 20rpx;
				.info {
					font-size: 32rpx;
					text {
						font-size: 28rpx;
						display: block;
						color: $font-color-light;
					}
				}

				.money {
					float: right;
					font-size: 32rpx;
					font-weight: 700;
					margin-top: 20rpx;
				}

				.income {
					color: $font-color-base;
				}
			}
		}
	}
</style>
