<template>
	<view class="page-body" :class="[app.setCStyle()]">
		<view class="navbar base-select">
			<view v-for="(item, index) in navList" :key="index" class="nav-item" :class="{current: tabCurrentIndex === index}"
			 @click="tabClick(index)">
				{{item.text}}
			</view>
		</view>
		<swiper :current="tabCurrentIndex" class="swiper-box" duration="300" @change="changeTab">
			<swiper-item class="tab-content" v-for="(tabItem,tabIndex) in navList" :key="tabIndex">
				<scroll-view class="hb100" scroll-y @scrolltolower="loadData">
					<!-- 空白页 -->
					<empty v-if="tabItem.loaded === true && tabItem.list.length === 0"></empty>
					<view v-for="(item,index) in tabItem.list" :key="index" class="p20 bg-white mb20">
						<view class="flex mt20">
							<image :src="baseUrl+item.pic" style="width:140rpx;height:140rpx;border-radius:10rpx;"></image>
							<view class="flex_bd ml20">
								<view class="flex">
									<view class="goods_name flex_bd">{{item.goods_name}}</view>
									<view class="ml10">
										<view><text class="fs22">￥</text>{{item.sale_price}}</view>
									</view>
								</view>
								<view class="mt10 smll fixed">
									<view class="flex_bd color-cc fs26">{{item.sku_name}}</view>
									<button v-if="item.is_evaluate == 1" class="fr action-btn recom" @click="app.goPage('/pages/shop/comment/post?rec_id='+item.rec_id)">{{app.langReplace('立即评论')}}</button>
									<button v-else-if="item.is_evaluate == 2" class="fr action-btn recom" @click="app.goPage('/pages/shop/comment/info?rec_id='+item.rec_id)">{{app.langReplace('查看评论')}}</button>
								</view>
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
				baseUrl: this.config.baseUrl,
				tabCurrentIndex: 0,
				param: {
					type: '',
					p: 0
				},
				navList: [{
						type: 'wait',
						text: this.app.langReplace('待评论'),
						loadingType: 'more',
						list: [],
						p: 0
					},
					{
						type: 'finish',
						text: this.app.langReplace('已评论'),
						loadingType: 'more',
						list: [],
						p: 0
					}
				],
			};
		},
		onLoad(options) {
			this.app.isLogin(this);//强制登陆
			let title = this.app.langReplace('我的评论');
			uni.setNavigationBarTitle({
				title
			})
			this.loadData();
		},
		methods: {
			//获取列表
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
				this.$u.post('shop/api.Comment/getList', this.param).then(res => {
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
	.swiper-box {
		height: calc(100% - 50px);
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
</style>
