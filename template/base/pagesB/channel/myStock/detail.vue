<template>
	<view class="page-body">
		<view class="goods_item b-tottom">
				<view class="image">
					<image :src="goods.goods_thumb" mode="aspectFill"></image>
				</view>
				<view class="info right-sj">
					<view class="goods_name">{{goods.goods_name}}</view>
					<view class="text_main mt20">
						<text >{{goods.sku_name}}</text>
						<view class="fr">
						<text class="mr10">库存:{{goods.goods_number}}</text>
						<text>出货:{{goods.out_number}}</text>
						</view>
					</view>
				</view>
		</view>
		<view class="navbar">
			<view class="nav-item" v-for="(item, index) in navList" :key="index" :class="{current: tabCurrentIndex === index}"
			 @click="tabClick(index)">{{item.text}}</view>
		</view>
		<swiper :current="tabCurrentIndex" class="swiper-box" duration="300" @change="changeTab">
			<swiper-item class="tab-content" v-for="(tabItem,tabIndex) in navList" :key="tabIndex">
				<scroll-view 
					class="list-scroll-content" 
					scroll-y
					@scrolltolower="loadData"
				>
					<!-- 空白页 -->
					<empty v-if="tabItem.loaded === true && tabItem.itemList.length === 0"></empty>
					<!-- 订单列表 -->
					<view 
						v-for="(item,index) in tabItem.itemList" :key="index"
						class="item p20 mt20 bg-white"
					>
						<view class="time">时间：{{item.add_time}}<text class="goods_number">数量：{{item.goods_number}}</text></view>
						<view class="log">备注：{{item.log}}</view>
					</view>
					 
					<uni-load-more :status="tabItem.loadingType"></uni-load-more>
					
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
				tabCurrentIndex: 0,
				goods:{},
				navList: [{
						state: 'enter',
						text: '入库',
						itemList: [],
						loadingType: 'more',
						p:0
					},
					{
						state: 'out',
						text: '出库',
						itemList: [],
						loadingType: 'more',
						p:0
					}
				],
				param: {
					p:0
				}, 
			}
		},
		onLoad(options) {
			this.param.hash = options.hash;
			this.getGoodsInfo(options.hash);
			this.loadData();
		},
		methods: {
			getGoodsInfo(hash){
				this.$u.post('channel/api.stock/getGoodsInfo', {hash:hash}).then(res => {
					this.goods = res.data.goods;
					this.goods.goods_thumb = this.config.baseUrl+this.goods.goods_thumb;
				})
			},
			//swiper 切换
			changeTab(e){
				this.tabCurrentIndex = e.target.current;
				this.loadData('tabChange');
			},
			//顶部tab点击
			tabClick(index){
				this.tabCurrentIndex = index;
			},
			loadData(source){	//获取列表
				let index = this.tabCurrentIndex;
				let navItem = this.navList[index];
				if (navItem.loadingType == 'nomore'){
					return;
				}
				if(source === 'tabChange' && navItem.loaded === true){
					//tab切换只有第一次需要加载数据
					return;
				}
				if(navItem.loadingType === 'loading'){
					//防止重复加载
					return;
				}
				navItem.p++;
				this.param.state = navItem.state;
				this.param.p = navItem.p;
				this.$u.post('channel/api.stock/getDetail', this.param).then(res => {
					navItem.itemList = navItem.itemList.concat(res.data.list);
					//loaded新字段用于表示数据加载完毕，如果为空可以显示空白页
					this.$set(navItem, 'loaded', true);
					//判断是否还有下一页，有是more  没有是nomore
					navItem.loadingType = navItem.p == res.data.page_count ? 'nomore' : 'more';
				})
			}, 
		}
	}
</script>

<style lang="scss">
	.swiper-box{
		height: calc(100% - 255rpx);
	}
	.goods_item {
		display: flex;
		padding: 20rpx;
		background-color: #FFFFFF;
		margin-top: 20rpx;
		.image {
			display: block;
			width: 150rpx;
			height: 150rpx;
			border-radius: 3px;
			overflow: hidden;
			image {
				width: 100%;
				height: 100%;
				opacity: 1;
			}
		}
		.info{
			padding-left: 20rpx;
			flex: 1;
			padding-right: 30rpx;
			.goods_name{
				height: 80rpx;
				overflow: hidden;
			}
			.text_main{
				color: $font-color-base;
				position: relative;
				.btn{
					position: absolute;
					right: 0rpx;
					color: $font-color-base;
				}
			}
		}	
	}
	.list-scroll-content{
		height: 100%;
	}
	.item{
		.goods_number{
			float: right;
			color: $font-color-base;
		}
		.log{
			margin-top: 20rpx;
			color: $font-color-light;
		}
	}
	
</style>
