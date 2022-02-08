<template>
	<view class="page-body" :class="[app.setCStyle()]">
		
		<view class="top_navbar mb10">
			<view class="navbar base-select">
				<view class="nav-item" v-for="(item,tabIndex) in topNav" :key="tabIndex" :class="{current: topTabCurrentIndex === tabIndex}"
				 @click="topTabClick(tabIndex)">
					{{item.title}}
				</view>
			</view>
		</view>
		
		<swiper :current="topTabCurrentIndex" class="swiper-box" duration="300" @change="topChangeTab">
			<swiper-item class="tab-content" v-for="(topItem,topIndex) in topNav" :key="topIndex">
				<view class="navbar base-select">
					<view v-for="(item, index) in navList[topItem.type]" :key="index" class="nav-item text-center" :class="{current: tabCurrentIndex === index}"
					 @click="tabClick(index)">
						{{item.text}}
					</view>
				</view>
				<swiper :current="tabCurrentIndex" class="swiper-box" duration="300" @change="changeTab">
					<swiper-item class="tab-content" v-for="(tabItem,tabIndex) in navList[topItem.type]" :key="tabIndex">
						<scroll-view class="hb100" scroll-y @scrolltolower="loadData">
							<!-- 空白页 -->
							<empty v-if="tabItem.loaded === true && tabItem.orderList.length === 0"></empty>
							<!-- 订单列表 -->
							<view v-for="(item,index) in tabItem.orderList" :key="index" class="order-item p20">
								<view class="i-top">
									<view class="sn_time">
										<text>{{app.langReplace('订单编号')}}：{{item.order_sn}}</text>
									</view>
									<text class="state">{{app.langReplace(item.ostatus)}}</text>
									<u-icon class="del-btn" name="trash-fill" v-if="item.isDelete===1" @click="deleteOrder(item.order_id,index)"></u-icon>
								</view>
								<view @click="app.goPage('order?order_id='+item.order_id)">
									<view class="flex mt20" v-for="(goodsItem, goodsIndex) in item.goodsList" :key="goodsIndex">
										<image :src="baseUrl+goodsItem.pic" style="width:140rpx;height:140rpx;"></image>
										<view class="flex_bd ml20">
											<view class="flex">
												<view class="goods_name flex_bd">{{goodsItem.goods_name}}</view>
												<view class="ml10">
													<view><text class="fs22">￥</text>{{goodsItem.sale_price}}</view>
													<view class="text-right color-99">x{{goodsItem.goods_number}}</view>
												</view>
											</view>
											<view class="flex mt10">
												<view v-if="goodsItem.sku_id > 0" class="color-cc fs26">{{goodsItem.sku_name}}</view>
											</view>
										</view>
									</view>
									<view class="price-box">
										{{app.langReplace('共')}}:
										<text class="num">{{item.goodsNum}}</text>
										 {{app.langReplace('件商品')}} {{app.langReplace('实付款')}}:
										<text class="price ff base-color">{{item.order_amount}}</text>
									</view>
								</view>
								<view class="mt10">
									<button v-if="item.isReview == 1" class="fr action-btn recom" @click="">{{app.langReplace('立即评论')}}</button>
									<button v-if="item.isPay == 1" class="fr action-btn recom" @click="app.goPage('pay?order_id='+item.order_id)">{{app.langReplace('立即支付')}}</button>
									<button v-if="item.isCancel == 1" class="fr action-btn" @click="cancelOrder(item.order_id,index)">{{app.langReplace('取消')}}</button>
									<button v-if="item.invoice_no" class="fr action-btn" @click="app.goPage('/pages/shop/order/shippingLog?order_id='+item.order_id)">{{app.langReplace('查看物流')}}</button>
								</view>
							</view>

							<uni-load-more :status="tabItem.loadingType"></uni-load-more>

						</scroll-view>
					</swiper-item>
				</swiper>
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
				topTabCurrentIndex: 0,
				tabCurrentIndex: 0,
				param: {},
				topNav: [
					{
						type:1,
						title: this.app.langReplace('我发起的'),
						state: 0,
					},
					{
						type:2,
						title: this.app.langReplace('我参与的'),
						state: 0,
					}
				],
				navList: {
					1:[{
						type: 'all',
						text: this.app.langReplace('全部'),
						loadingType: 'more',
						orderList: [],
						p: 0
					},
					{
						type: 'waitPay',
						text: this.app.langReplace('待付款'),
						loadingType: 'more',
						orderList: [],
						p: 0
					},
					{
						type: 'fging',
						text: this.app.langReplace('拼团中'),
						loadingType: 'more',
						orderList: [],
						p: 0
					},
					{
						type: 'waitShipping',
						text: this.app.langReplace('待发货'),
						loadingType: 'more',
						orderList: [],
						p: 0
					},
					{
						type: 'waitSign',
						text: this.app.langReplace('待收货'),
						loadingType: 'more',
						orderList: [],
						p: 0
					},
					{
						type: 'sign',
						text: this.app.langReplace('已完成'),
						loadingType: 'more',
						orderList: [],
						p: 0
					},
				],
				2:[{
						type: 'all',
						text: this.app.langReplace('全部'),
						loadingType: 'more',
						orderList: [],
						p: 0
					},
					{
						type: 'waitPay',
						text: this.app.langReplace('待付款'),
						loadingType: 'more',
						orderList: [],
						p: 0
					},
					{
						type: 'fging',
						text: this.app.langReplace('拼团中'),
						loadingType: 'more',
						orderList: [],
						p: 0
					},
					{
						type: 'waitShipping',
						text: this.app.langReplace('待发货'),
						loadingType: 'more',
						orderList: [],
						p: 0
					},
					{
						type: 'waitSign',
						text: this.app.langReplace('待收货'),
						loadingType: 'more',
						orderList: [],
						p: 0
					},
					{
						type: 'sign',
						text: this.app.langReplace('已完成'),
						loadingType: 'more',
						orderList: [],
						p: 0
					},
				]
				}
			};
		},
		onLoad(options) {
			this.app.isLogin(this);//强制登陆
			let title = this.app.langReplace('拼单列表');
			uni.setNavigationBarTitle({
				title
			})
			if (typeof(options.state) != 'undefined'){
				this.tabCurrentIndex = parseInt(options.state);
			}
			this.getOrderStats();
			this.loadData();
		},
		methods: {
			getOrderStats(){
				this.$u.post('shop/api.order/getOrderStats').then(res => {
					let OrderStats = res.data.orderStats;
				})
			},
			//获取订单列表
			loadData(source) {
				//这里是将订单挂载到tab列表下
				let topItem = this.topNav[this.topTabCurrentIndex];
				let navItem = this.navList[topItem.type][this.tabCurrentIndex];
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
				this.param.topType = topItem.type;
				this.param.type = navItem.type;
				this.param.p = navItem.p;
				navItem.loadingType = 'loading';
				this.$u.post('fightgroup/api.order/getList', this.param).then(res => {
					navItem.orderList = navItem.orderList.concat(res.data.list);
					//loaded新字段用于表示数据加载完毕，如果为空可以显示空白页
					this.$set(navItem, 'loaded', true);
					//判断是否还有下一页，有是more  没有是nomore
					navItem.loadingType = navItem.p == res.data.page_count ? 'nomore' : 'more';
				})
			},
			topTabClick(index) {//顶部tab点击
				this.topTabCurrentIndex = index;
			},
			topChangeTab(e){
				this.topTabCurrentIndex = e.target.current;
				this.loadData()
			},
			//swiper 切换
			changeTab(e) {
				this.tabCurrentIndex = e.target.current;
				this.loadData('tabChange');
			},
			//tab点击
			tabClick(index) {
				this.tabCurrentIndex = index;
			},
			//删除订单
			deleteOrder(order_id, _index) {
				let topItem = this.topNav[this.topTabCurrentIndex];
				let navItem = this.navList[topItem.type][this.tabCurrentIndex];
				this.$u.post('shop/api.order/action', {
					order_id: order_id,
					type:'del'
				}).then(res => {
					navItem.orderList.splice(_index, 1);
				})
			},
			//取消订单
			cancelOrder(order_id, _index) {
				let that = this;
				uni.showModal({
					title: '提示',
					content: '确定取消订单？',
					showCancel: true,
					confirmText: '确定取消',
					cancelText: '放弃取消',
					success: function(res) {
						if (res.confirm) {
							let topItem = that.topNav[that.topTabCurrentIndex];
							let navItem = that.navList[topItem.type][that.tabCurrentIndex];
							that.$u.post('shop/api.order/action', {
								order_id: order_id,
								type:'cancel'
							}).then(res => {
								navItem.orderList[_index].ostatus = '已取消';
								navItem.orderList[_index].isDelete = 1;
								navItem.orderList[_index].isPay = 0;
							})
						}
					}
				})	
			}
		},
	}
</script>

<style lang="scss">
	page,
	.page-body {
		background: $page-color-base;
		height: 100%;
	}
	.navbar{
		.nav-item{
			position: relative;
			color: #999999;
			&.current{
				&:after{
					border-bottom: 4rpx solid ;
				}
			}	
			.tip-num{
				position: absolute;
				top:5rpx;
				right: 0rpx;
				font-size: 26rpx;
				width: 35rpx;
				text-align: center;
				border-radius: 50%;
				background-color: $font-color-red;
				color: #FFFFFF;
				
			}
		}
	}
	.swiper-box {
		height: calc(100% - 50px);
	}

	.order-item {
		display: flex;
		flex-direction: column;
		background: #fff;
		margin: 20rpx;
		border-radius: 10rpx;
		.i-top {
			display: flex;
			align-items: center;
			height: 50rpx;
			font-size: $font-base;
			color: $font-color-dark;
			position: relative;

			.sn_time {
				flex: 1;
				text {
					display: block;
					font-size: 24rpx;
					color: #666666;
				}

				.time {
					color: $font-color-light;
				}
			}

			.state {
				font-size: 24rpx;
			}

			.del-btn {
				padding: 10rpx 0 10rpx 36rpx;
				font-size: $font-lg;
				color: $font-color-light;
				position: relative;

				&:after {
					content: '';
					width: 0;
					height: 30rpx;
					border-left: 1px solid $border-color-dark;
					position: absolute;
					left: 20rpx;
					top: 50%;
					transform: translateY(-50%);
				}
			}
		}

		.goods_name {
			display: block;
			font-size: 28rpx;
			color: $font-color-dark;
			line-height: 45rpx;
			height: 90rpx;
			overflow: hidden;
			text-overflow: ellipsis;
			display: -webkit-box;
			-webkit-line-clamp: 2;
			-webkit-box-orient: vertical;
		}

		.price-box {
			display: flex;
			justify-content: flex-end;
			align-items: baseline;
			font-size: $font-sm + 2rpx;
			color: $font-color-light;

			.num {
				margin: 0 8rpx;
				color: $font-color-dark;
			}

			.price {
				font-size: $font-lg;
				color: $font-color-gray;

				&:before {
					content: '￥';
					font-size: $font-sm;
					margin: 0 2rpx 0 8rpx;
				}
			}
		}

	}
</style>
