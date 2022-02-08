<template>
	<view class="page-body" :class="[app.setCStyle()]">
		<view class="navbar base-select">
			<view v-for="(item, index) in navList" :key="index" class="nav-item text-center" :class="{current: tabCurrentIndex === index}"
			 @click="tabClick(index)">
				{{item.text}}
			</view>
		</view>
		<swiper :current="tabCurrentIndex" class="swiper-box" duration="300" @change="changeTab">
			<swiper-item class="tab-content" v-for="(tabItem,tabIndex) in navList" :key="tabIndex">
				<scroll-view class="hb100" scroll-y @scrolltolower="loadData">
					<!-- 空白页 -->
					<empty v-if="tabItem.loaded === true && tabItem.orderList.length === 0"></empty>
					<!-- 订单列表 -->
					<view v-for="(item,index) in tabItem.orderList" :key="index" class="order-item mb20 bg-white p20 fs28">
						<view class="i-top bg-white">
							<view class="sn_time">
								<text>{{app.langReplace('售后编号')}}：{{item.as_sn}}</text>
							</view>
							<text class="state" :class="{'base-color':item.status==1}">{{app.langReplace(item.ostatus)}}</text>
						</view>
						<view class="flex mt20 " @click="app.goPage('info?as_id='+item.as_id)">
							<image :src="baseUrl+item.goods.pic" style="width:140rpx;height:140rpx;"></image>
							<view class="flex_bd ml20">
								<view class="flex">
									<view class="goods_name flex_bd">{{item.goods.goods_name}}</view>
									<view class="ml10">
										<view><text class="fs22">￥</text>{{item.goods.sale_price}}</view>
										<view class="text-right color-99">x{{item.goods_number}}</view>
									</view>
								</view>
								<view class="flex mt10">
									<view v-if="item.goods.sku_id > 0" class="color-cc fs26">{{item.goods.sku_name}}</view>
								</view>
							</view>
						</view>
						<view class="price-box">
							{{app.langReplace('售后类型')}}：{{app.langReplace(item.type_val)}}
							<view  v-if="item.return_money > 0" >
								{{app.langReplace('退款金额')}}：<text class="price ff base-color">{{item.return_money}}</text>
							</view>
						</view>
						<view class="p20 bg-fa" @click="app.goPage('info?as_id='+item.as_id)">
							<view class="mt10">
								<text class="color-99">{{app.langReplace('售后编号')}}：</text>{{item.as_sn}}
							</view>
							<view class="mt10">
								<text class="color-99">{{app.langReplace('售后时间')}}：</text>{{item.add_time}}
							</view>
							<view v-if="item.status == 1" class="mt10 flex">
								<text class="color-99">{{app.langReplace('拒绝原因')}}：</text><text class="flex_bd">{{item.remark}}</text>
							</view>
						</view>
						<view class="mt10">
							<button v-if="item.status == 1" class="fr action-btn recom" @click="app.goPage('apply?rec_id='+item.rec_id)">{{app.langReplace('重新申请')}}</button>
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
				baseUrl: this.config.baseUrl,
				tabCurrentIndex: 0,
				param: {
					type: '',
					p: 0
				},
				navList: [{
						type: 'all',
						text: this.app.langReplace('全部'),
						loadingType: 'more',
						orderList: [],
						p: 0
					},
					{
						type: 'waitCheck',
						text: this.app.langReplace('审核中'),
						loadingType: 'more',
						orderList: [],
						num:0,
						p: 0
					},
					{
						type: 'waitShipping',
						text: this.app.langReplace('待退货'),
						loadingType: 'more',
						orderList: [],
						num:0,
						p: 0
					},
					{
						type: 'waitSign',
						text: this.app.langReplace('待验收'),
						loadingType: 'more',
						orderList: [],
						num:0,
						p: 0
					},
					{
						type: 'success',
						text: this.app.langReplace('已完成'),
						loadingType: 'more',
						orderList: [],
						p: 0
					},
				],
			}
		},
		onLoad(options) {
			this.app.isLogin(this);//强制登陆
			let title = this.app.langReplace('售后列表');
			uni.setNavigationBarTitle({
			　　title:title
			})
			this.loadData();
		},
		onShow() {
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
				this.param.type = navItem.type;
				this.param.p = navItem.p;
				navItem.loadingType = 'loading';
				this.$u.post('shop/api.after_sale/getList', this.param).then(res => {
					navItem.orderList = navItem.orderList.concat(res.data.list);
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
		}
	}
</script>

<style lang="scss">
	.swiper-box {
		height: calc(100% - 50px);
	}
	
	.order-item {
		display: flex;
		flex-direction: column;
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
