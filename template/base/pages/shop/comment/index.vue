<template>
	<view class="page-body" :class="[app.setCStyle()]">
		<view class="p20 bg-white fs28" @click="app.goPage('/pages/shop/goods/info?goods_id='+goods_id)">
			<view class="flex">
				<image :src="goodsImage" style="width:140rpx;height:140rpx;border-radius: 10rpx;"></image>
				<view class="flex_bd ml20">
					<view class="flex h100">
						<view class="flex_bd">{{goods.goods_name}}</view>
					</view>
					<view class="mt10 smll fixed">
						<view><text class="fs22">￥</text>{{goods.sale_price}}</view>
					</view>
				</view>
			</view>
		</view>
		
		<view class="mt20 bg-white p20">
			<view class="font-w600">{{app.langReplace('用户评论')}}<text class="color-99">（{{commentCount}}）</text></view>
		</view>
		<view class="bg-white p20 comment_box">
			<scroll-view class="hb100" scroll-y @scrolltolower="loadData('add')">
				<!-- 空白页 -->
				<empty v-if="commentList.loaded === true && commentList.list.length === 0"></empty>
				<view class="list" v-for="(item,index) in commentList.list" :key="index">
					<view class="tit smll">
						<view class="image ">
							<image :src="baseUrl + item.headimgurl"></image>
						</view>
						<view class="flex_bd">
							{{item.user_name}}
						</view>
						<view class="time">{{item._time}}</view>
					</view>
					<view class="content">
						{{item.content}}
					</view>
					<view class="images">
						<u-grid :col="4" class="u_grid" :border="false">
							<u-grid-item v-for="(img,imgIndex) in item.imgs" :key="imgIndex" @click="app.showImg(item.imgs,imgIndex,config.baseUrl)">
								<image :src="baseUrl+img"></image>
							</u-grid-item>
						</u-grid>
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
				baseUrl: this.config.baseUrl,
				goods_id:0,
				goodsImage:'',
				goods:{},
				loadingType: 'more', //加载更多状态
				commentCount:0,
				commentList:{
					list: []
				},
				param: { //传递参数
					p: 0, //默认页
				},
			}
		},
		onLoad(options) {
			this.goods_id = options.goods_id;
			let title = this.app.langReplace('用户评论');
			uni.setNavigationBarTitle({
				title
			})
			this.getGoodsInfo();
			this.loadData();
		},
		onShow() {
		},
		onHide() {
		},
		onReady() {},
		methods: {
			//获取商品信息
			getGoodsInfo() {
				this.$u.post('shop/api.goods/getGoodsInfo', {
					'goods_id': this.goods_id
				}).then(res => {
					this.goods = res.data;
					this.goodsImage = this.config.baseUrl + this.goods.goods_thumb;
				})
			},
			//加载商品
			async loadData(source = 'add') {
				//没有更多直接返回
				if (source == 'add' && this.loadingType == 'nomore') {
					return;
				}
				if (source === 'refresh') {
					this.param.p = 0;
					this.commentList.list = [];
				}
				this.param.goods_id = this.goods_id;
				this.param.p++;
				this.$u.post('shop/api.Comment/getListByGoods', this.param).then(res => {
					this.commentList.list = this.commentList.list.concat(res.data.list);
					this.commentCount = res.data.total_count;
					//loaded新字段用于表示数据加载完毕，如果为空可以显示空白页
					this.$set(this.commentList, 'loaded', true);
					//判断是否还有下一页，有是more  没有是nomore
					this.loadingType = this.param.p == res.data.page_count ? 'nomore' : 'more';
				
				})
			},
			stopPrevent() {}
		}
	}
</script>

<style lang="scss">
	/* 评论 */
	.comment_box{
		background-color: #FFFFFF;
		padding:0rpx 20rpx;
		height: calc(100vh - 280rpx);
		.title{
			padding:20rpx 0rpx;
			font-weight: 700;
			font-size: 30rpx;
			.num{
				color: $font-color-light;
			}
			.more{
				font-size: 28rpx;
				font-weight: normal;
				color: $font-color-light;
			}
		}
		.list{
			.tit{
				font-size: 28rpx;
				color: #999999;
				.image{
					width: 80rpx;
					height: 80rpx;
					border: 1rpx solid $border-color-light;
					border-radius: 50%;
					overflow: hidden;
					margin-right: 10rpx;
					image{
						width: 100%;
						height: 100%;
						border-radius: 50%;
					}
				}
				
			}
			.content{
				padding-top: 10rpx;
			}
			.images{
				min-height: 30rpx;
				.u_grid{
					.u-grid-item{
						height: 220rpx;
						padding: 0rpx 10rpx;
					}
				}
				image{
					width: 100%;
					height: 100%;
					border-radius: 10rpx;
				}
			}
		}
	}
	
</style>
