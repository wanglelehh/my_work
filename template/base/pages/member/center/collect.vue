<template>
	<view class="page-body ">
		<view class="u-page">
			<scroll-view class="goods-list" scroll-y @scrolltolower="loadData('add')">
				<view class="goods-list-row">
					<!-- 列表 -->
					<u-swipe-action :index="index" v-for="(item, index) in itemList.list" :key="item.goods_id" @click="delCollect" :options="options"
					class="mt20 mlr20 br20"
					>
						<view class="goods-item smll"  @click="app.goPage('/pages/shop/goods/info?goods_id='+item.goods_id)">
							<!-- 警告：微信小程序不支持嵌入lazyload组件，请自行如下使用image标签 -->
							<view class="image-wrapper " >
								<!-- #ifndef MP-WEIXIN -->
								<u-lazy-load threshold="-450" border-radius="10" mode="aspectFill" :image="baseUrl+item.goods_thumb" :index="index"></u-lazy-load>
								<!-- #endif -->
								<!-- #ifdef MP-WEIXIN -->
								<image :src="baseUrl+item.goods_thumb" mode="aspectFill"></image>
								<!-- #endif -->
							</view>
							<view class="info-wrapper flex_bd">
								<view class="title goods_name">{{item.goods_name}}</view>
								<view class="title short_name" >{{item.short_name}}</view>
								<view class="price-box smll">
									<text class="price flex_bd">{{item.sale_price}}</text>
								</view>
							</view>
						</view>
					</u-swipe-action>
				</view>
				<!-- 空白页 -->
				<empty v-if="itemList.loaded === true && itemList.list.length === 0"></empty>
				<view v-if="itemList.list.length > 0" style="height: 60rpx;"></view>
			</scroll-view>
		
		</view>
	</view>
</template>

<script>
	import empty from "@/components/empty";
	export default {
		components: {
			empty
		},
		data() {
			let that = this;
			return {
				baseUrl: this.config.baseUrl,
				getCartNum:0,//1执行请求购物车数量
				keyword: '',
				loadingType: 'more', //加载更多状态
				filterIndex: 0,
				itemList: {
					list: [],
				}, //商品列表
				options: [{
					text: this.app.langReplace('取消'),
					style: {
						backgroundColor: '#F65236'
					}
				}]
				
			}
		},
		onLoad(options) {
			this.app.isLogin(this);//强制登陆
			let title = this.app.langReplace('商品收藏');
			uni.setNavigationBarTitle({
				title
			})
			this.loadData();
		},
		onShow(){
		},
		onHide(){
		},
		onReady() {},
		methods: {
			delCollect(index) {
				let row = this.itemList.list[index];
				this.$u.post('shop/api.goods/collect', {
					goods_id: row.goods_id
				}).then(res => {
					this.itemList.list.splice(index, 1);
				})
			},
			
			//加载商品 
			async loadData() {
				this.$u.post('shop/api.goods/getCollectlist',).then(res => {
					if (res.data.list){
						this.itemList.list = this.itemList.list.concat(res.data.list);
					}
					//loaded新字段用于表示数据加载完毕，如果为空可以显示空白页
					this.$set(this.itemList, 'loaded', true);
				})
			},
			stopPrevent() {}
		}
	}
</script>

<style lang="scss">
	.u-page {
		//height: calc(100% - 90rpx);
		height: 100%;
		overflow: hidden;
	}
	
	.goods-list {
		position: relative;
		height: calc(100% - 178rpx);
	}

	/* 商品列表 行*/
	.goods-list-row {
		z-index: 2;
		.goods-item {
			width: 100%;
			margin-bottom: 20rpx;
			padding: 20rpx;
			background-color: #FFFFFF;
		}
		.image-wrapper {
			width: 200rpx;
			height: 200rpx;
			border-radius: 3px;
			overflow: hidden;

			image {
				width: 100%;
				height: 100%;
				opacity: 1;
			}
		}
		.info-wrapper {
			display: flex;
			flex-direction: column;
			flex: 1;
			overflow: hidden;
			position: relative;
			padding-left: 15px;
			.title {
				font-size: 32rpx;
				color: #303133;
				line-height: 50rpx;
				height: 150rpx;
				overflow: hidden;
			}
			.short_name {
				display: none;
			}
			.price-box {
				align-items: center;
				justify-content: space-between;
				padding-right: 10rpx;
				font-size: 24rpx;
				color: #909399;
			}
			.price {
				font-size: 32rpx;
				color: $font-color-red;
				line-height: 1;

				&:before {
					content: '￥';
					font-size: 26rpx;
				}
			}
			.u-icon {
				color: $font-color-red;
			}
		}
	}
</style>
