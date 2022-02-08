<template>
	<view class="page-body" :class="[app.setCStyle()]">
		<view class="navbar base-select">
			<view v-for="(item, index) in navList" :key="index" class="nav-item" :class="{current: tabCurrentIndex === index}"
			 @click="tabClick(index)">
				{{item.text}}({{static[item.state]}})
			</view>
		</view>

		<swiper :current="tabCurrentIndex" class="swiper-box" duration="300" @change="changeTab">
			<swiper-item class="tab-content" v-for="(tabItem,tabIndex) in navList" :key="tabIndex">
				<scroll-view class="list-scroll-content" scroll-y @scrolltolower="loadData">
					<!-- 空白页 -->
					<empty v-if="tabItem.loaded === true && tabItem.list.length === 0"></empty>
					<!-- 列表 -->
					<view v-for="(item,index) in tabItem.list" :key="index" class="item card-box">
						<view class="i-top b-b">
							<text class="fs28">{{item.account_type}}</text>
						</view>
						<view class="info">
							<view class="smll">
								<view class="w50">提现到帐：<text class="fs24">￥</text>{{item.arrival_money}}</view>
								<view class="w50">提现手续费：<text class="fs24">￥</text>{{item.withdraw_fee}}</view>
							</view>
							<view class="smll mt10">
								<view class="w50">实扣余额：<text class="fs24">￥</text>{{item.real_money}}</view>
								<view class="w50">审核状态：<text class="color-base">{{item.status}}</text></view>
							</view>
							<view  v-if="item.status == '审核失败'" class="smll mt10 color-red">
								备注：{{item.admin_note}}
							</view>
							<view class=" mt10 color-99">
								申请时间：{{item.add_time}}
							</view>
							<view v-if="item.account_type == '支付宝'" class="color-99">
								<view class="mt10">帐号姓名：{{item.account_info.name}}</view>
								<view class="mt10">支付宝帐号：{{item.account_info.account}}</view>
							</view>
							<view v-if="item.account_type == '微信'" class="color-99">
								<view class="mt10">昵称：{{item.account_info.name}}</view>
								<view class="mt10">微信帐号：{{item.account_info.account}}</view>
							</view>
							<view v-if="item.account_type == '银行卡'" class=" color-99">
								<view class="mt10">银行：{{item.account_info.bank_name}}</view>
								<view class="mt10">卡号：{{item.account_info.bank_idcard_munber}}</view>
								<view class="mt10">卡主：{{item.account_info.bank_user_name}}</view>
							</view>
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
				tabCurrentIndex: 0,
				param: {
					state: 0,
					p: 0
				},
				static: {},
				navList: [{
						state: 'all',
						text: '全部',
						loadingType: 'more',
						list: [],
						p: 0
					},
					{
						state: 'waitCheck',
						text: '待审核',
						loadingType: 'more',
						list: [],
						p: 0
					},
					{
						state: 'complete',
						text: '已完成',
						loadingType: 'more',
						list: [],
						p: 0
					},
					{
						state: 'fail',
						text: '已失败',
						loadingType: 'more',
						list: [],
						p: 0
					},
				],
			};
		},

		onLoad(options) {
			/**
			 * 修复app端点击除全部订单外的按钮进入时不加载数据的问题
			 * 替换onLoad下代码即可
			 */
			if (typeof(options.state) != 'undefined') {
				this.tabCurrentIndex = parseInt(options.state);
			}
			//获取统计
			this.$u.post('member/api.withdraw/getStatic').then(res => {
				this.static = res.data;
			});
			this.loadData()
		},
		onShow() {
			this.app.isLogin(this); //强制登陆
		},
		methods: {
			//获取订单列表
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
				this.param.state = navItem.state;
				this.param.p = navItem.p;
				navItem.loadingType = 'loading';
				this.$u.post('member/api.withdraw/getLog', this.param).then(res => {
					navItem.list = navItem.list.concat(res.data.list);
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

		},
	}
</script>

<style lang="scss">
	.card-box {
		margin: 20rpx;
		border-radius: 20rpx;
		background-color: #FFFFFF;
	}

	.swiper-box {
		height: calc(100% - 40px);
	}

	.list-scroll-content {
		height: 100%;
	}

	.item {
		display: flex;
		flex-direction: column;
		padding-left: 30rpx;
		background: #fff;
		margin-top: 16rpx;

		.i-top {
			display: flex;
			align-items: center;
			height: 80rpx;
			padding-right: 30rpx;
			font-size: $font-base;
			color: $font-color-dark;
			position: relative;

			.time {
				flex: 1;
			}

			.state {
				color: $font-color-red;
			}

		}

		.info {
			color: $font-color-gray;
			padding: 20rpx 0rpx;

			.money {
				font-size: 26rpx;
				padding: 10rpx 0rpx;

				text {
					font-size: 26rpx;
				}
			}
		}

	}
</style>
