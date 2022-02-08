<template>
	<view class="page-body">
		<view class="top_navbar mb10">
			<view class="navbar">
				<view class="nav-item" v-for="(item,tabIndex) in topNav" :key="tabIndex" :class="{current: topTabCurrentIndex === tabIndex}"
				 @click="topTabClick(tabIndex)">
					{{item.title}}
				</view>
			</view>
		</view>

		<swiper :current="topTabCurrentIndex" class="swiper-box" duration="300" @change="topChangeTab">
			<swiper-item class="tab-content" v-for="(topItem,topIndex) in topNav" :key="topIndex">
				<view class="navbar">
					<view v-for="(item, index) in allNavList[topItem.purchase_type]" :key="index" class="nav-item" :class="{current: topItem.state === index}"
					 @click="tabClick(index)">
						{{item.text}}
					</view>
				</view>
				<swiper :current="topItem.state" class="swiper-box" duration="300" @change="changeTab">
					<swiper-item class="tab-content" v-for="(tabItem,tabIndex) in allNavList[topItem.purchase_type]" :key="tabIndex">
						<scroll-view class="list-scroll-content" scroll-y @scrolltolower="loadData">
							<!-- 空白页 -->
							<empty v-if="tabItem.loaded === true && tabItem.orderList.length === 0"></empty>
							<!-- 订单列表 -->
							<view v-for="(item,index) in tabItem.orderList" :key="index" class="order-item" >
								<view class="i-top b-b">
									<view class="sn_time">
										<text>订单编号：{{item.order_sn}}</text>
										<text class="time">下单时间：{{item.add_time}}</text>
									</view>
									<text class="state">{{item.ostatus}}</text>
								</view>
								<view @click="app.goPage('/pagesB/channel/order/info?order_id='+item.order_id)">

									<view class="goods-box-single" v-for="(goodsItem, goodsIndex) in item.goodsList" :key="goodsIndex">
										<image class="goods-img" :src="baseUrl+goodsItem.pic" mode="aspectFill"></image>
										<view class="right">
											<text class="title clamp">{{goodsItem.goods_name}}</text>
											<view class="attr_price">
												<text class="attr-box">{{goodsItem.sku_name}} x {{goodsItem.goods_number}}</text>
												<text class="price">{{goodsItem.sale_price}}</text>
											</view>
										</view>
									</view>
									<view class="price-box">
										共
										<text class="num">{{item.goodsNum}}</text>
										件商品 实付款
										<text class="price">{{item.order_amount}}</text>
									</view>
								</view>
								<view class="action-box p20">
									<button  v-if="item.isCancel == 1" class="action-btn" @click="cancelOrder(item.order_id,index)">取消订单</button>
									<button  v-if="item.isAddStock == 1" class="action-btn green" @click="app.goPage('/pagesB/channel/product/list?purchaseType='+item.purchase_type)">一键补货</button>
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
				baseUrl:this.config.baseUrl,
				topTabCurrentIndex: 0,
				param: {},
				topNav: [{
						purchase_type:1,
						title: '云仓订单',
						state: 0,
					},
					{
						purchase_type:2,
						title: '现货订单',
						state: 0,
					},
					{
						purchase_type:3,
						title: '提货订单',
						state: 0,
					}
				],
				allNavList: {
					1: [{
							state: 0,
							text: '全部',
							loadingType: 'more',
							orderList: [],
							count:0,
							p: 0
						},
						{
							state: 2,
							text: '待付款',
							loadingType: 'more',
							orderList: [],
							count:0,
							p: 0
						},
						{
							state: 3,
							text: '待审核',
							loadingType: 'more',
							orderList: [],
							count:0,
							p: 0
						},
						{
							state: 9,
							text: '已出仓',
							loadingType: 'more',
							orderList: [],
							count:0,
							p: 0
						},
						{
							state: 20,
							text: '已拒绝',
							loadingType: 'more',
							orderList: [],
							count:0,
							p: 0
						}
					],
					2: [{
							state: 0,
							text: '全部',
							loadingType: 'more',
							orderList: [],
							count:0,
							p: 0
						},
						{
							state: 2,
							text: '待付款',
							loadingType: 'more',
							orderList: [],
							count:0,
							p: 0
						},
						{
							state: 3,
							text: '待审核',
							loadingType: 'more',
							orderList: [],
							count:0,
							p: 0
						},
						{
							state: 4,
							text: '待发货',
							loadingType: 'more',
							orderList: [],
							count:0,
							p: 0
						},
						{
							state: 5,
							text: '待签收',
							loadingType: 'more',
							orderList: [],
							count:0,
							p: 0
						},
						{
							state: 9,
							text: '已完成',
							loadingType: 'more',
							orderList: [],
							count:0,
							p: 0
						}
					],
					3: [{
							state: 0,
							text: '全部',
							loadingType: 'more',
							orderList: [],
							count:0,
							p: 0
						},
						{
							state: 4,
							text: '待发货',
							loadingType: 'more',
							orderList: [],
							count:0,
							p: 0
						},
						{
							state: 5,
							text: '待签收',
							loadingType: 'more',
							orderList: [],
							count:0,
							p: 0
						},
						{
							state: 9,
							text: '已完成',
							loadingType: 'more',
							orderList: [],
							count:0,
							p: 0
						}
					]
				},
			};
		},

		onLoad(options) {
			if (options.purchase_type >= 1){
				this.topNav.forEach((item,index)=>{
					if (options.purchase_type == item.purchase_type){
						this.topTabCurrentIndex = index;
					}
				})
			}
			this.loadData()
		},

		methods: {
			//获取订单列表
			loadData(source) {
				//这里是将订单挂载到tab列表下
				let topItem = this.topNav[this.topTabCurrentIndex];
				let navItem = this.allNavList[topItem.purchase_type][topItem.state];
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
				this.param.purchase_type = topItem.purchase_type;
				this.param.state = navItem.state;
				this.param.p = navItem.p;
				navItem.loadingType = 'loading';
				this.$u.post('channel/api.order/getSubList', this.param).then(res => {
					navItem.orderList = navItem.orderList.concat(res.data.list);
					//loaded新字段用于表示数据加载完毕，如果为空可以显示空白页
					this.$set(navItem, 'loaded', true);
					//判断是否还有下一页，有是more  没有是nomore
					console.log(navItem.p == res.data.page_count ? 'nomore' : 'more')
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
			tabClick(index) {//顶部tab点击
				this.tabCurrentIndex = index;
				this.topNav[this.topTabCurrentIndex].state = index;
			},
			changeTab(e) {//swiper 切换
				this.tabCurrentIndex = e.target.current;
				this.topNav[this.topTabCurrentIndex].state = this.tabCurrentIndex;
				this.loadData('tabChange');
			},
			//取消订单
			cancelOrder(order_id,_index){
				let topItem = this.topNav[this.topTabCurrentIndex];
				let navItem = this.allNavList[topItem.purchase_type][topItem.state];
				this.$u.post('channel/api.order/cancel', {order_id:order_id}).then(res => {
					navItem.orderList[_index].ostatus = '已取消';
					navItem.orderList[_index].isDelete = 1;
					navItem.orderList[_index].isPay = 0;
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

	.swiper-box {
		height: calc(100% - 80rpx);
	}

	.list-scroll-content {
		height: 100%;
	}

	.order-item {
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

			.sn_time {
				flex: 1;

				text {
					display: block;
					font-size: 26rpx;
				}

				.time {
					color: $font-color-light;
				}
			}

			.state {
				color: $font-color-base;
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

		/* 单条商品 */
		.goods-box-single {
			display: flex;
			padding: 20rpx 0rpx 0 0;

			.goods-img {
				display: block;
				width: 120rpx;
				height: 120rpx;
			}

			.right {
				flex: 1;
				display: flex;
				flex-direction: column;
				padding: 0 30rpx 0 24rpx;
				overflow: hidden;

				.title {
					font-size: $font-base + 2rpx;
					color: $font-color-dark;
					line-height: 1.5;
					height: 80rpx;
					overflow: hidden;
				}

				.attr_price {
					display: flex;
					padding: 10rpx 12rpx;

					.attr-box {
						flex: 1;
						font-size: $font-sm + 2rpx;
						color: $font-color-light;
					}

					.price {
						font-size: $font-base + 2rpx;
						color: $font-color-dark;

						&:before {
							content: '￥';
							font-size: $font-sm;
							margin: 0 2rpx 0 8rpx;
						}
					}
				}
			}
		}

		.price-box {
			display: flex;
			justify-content: flex-end;
			align-items: baseline;
			padding: 20rpx 30rpx;
			font-size: $font-sm + 2rpx;
			color: $font-color-light;

			.num {
				margin: 0 8rpx;
				color: $font-color-dark;
			}

			.price {
				font-size: $font-lg;
				color: $font-color-dark;

				&:before {
					content: '￥';
					font-size: $font-sm;
					margin: 0 2rpx 0 8rpx;
				}
			}
		}

	}
</style>
