<template>
	<view class="page-body" :class="[app.setCStyle()]">
		 <scroll-view scroll-y="true" lower-threshold="100" @scrolltolower="loadData('add')">
			<view class="p20">
				<view class="live_item bg-white flex mb20" v-for="(item, k) in itemList.list" :key="k" @click="goPage(item)">
					<view class="live_left">
						<image :src="item.anchor_img" mode="aspectFill"></image>
						<view class="live_status">
							<text :class="item.live_status<=102?'recordColorB':'recordColorA'" v-if="item.replay_status==0">{{item.live_status_txt}}</text>
							<text class="replay" v-else>回播中</text>
						</view>
						<view class="playbtn">
							<image src="@/pagesA/static/images/live/play_gray.svg" mode="aspectFill"></image>
						</view>
					</view>
					<view class="live_info flex_bd">
						<view class="room_name">{{item.name}}</view>
						<view class="liver_info flex">
							<view class="liver_img">
								<image :src="item.cover_img" mode="aspectFill"></image>
							</view>
							<view class="liver_name flex_bd">{{item.anchor_name}}</view>
						</view>
						<view class="goods_list">
							<view v-for="(gitem, goodsIndex) in item.goods" :key="goodsIndex" v-if="goodsIndex<=2" class="goods_item">
								 <image :src="gitem.cover_img" mode="aspectFill"></image>
							</view>
							<view v-if="item.goods_num > 3" class="goods_item">
							    <view class="nums">{{item.goods_num}}件</view>
							 </view>
						</view>
					</view>
				</view>
				<empty v-if="itemList.loaded === true && itemList.list.length === 0"></empty>
				<uni-load-more :status="loadingType"></uni-load-more>
			</view>
		</scroll-view>
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
				param: { //传递参数
					page: 0, //默认页
				},
				itemList: {
					list: []
				},
				loadingType: 'more', //加载更多状态
			}
		},
		onLoad(options) {
			this.loadData();
		},
		onShow() {
		},
		onHide() {
		},
		onReady() {},
		methods: {
			//加载商品
			async loadData(source = 'add') {
				//没有更多直接返回
				if (source == 'add' && this.loadingType == 'nomore') {
					return;
				}
				if (source === 'refresh') {
					this.param.page = 0;
					this.itemList.list = [];
				}
			
				this.param.page++;
				this.$u.post('weixin/api.live_room/getList', this.param).then(res => {
					this.itemList.list = this.itemList.list.concat(res.data);
					//loaded新字段用于表示数据加载完毕，如果为空可以显示空白页
					this.$set(this.itemList, 'loaded', true);
					//判断是否还有下一页，有是more  没有是nomore
					this.loadingType = this.param.p == res.data.page_count ? 'nomore' : 'more';
			
				})
			},
			goPage(item){
				let config = this.config;
				if (item.replay_status==0){
					// #ifdef H5
					let settings = uni.getStorageSync('setting');
					this.app.showModal('请打开小程序【 '+settings.xcx_name+' 】查看直播.');
					return false;
					// #endif
					wx.navigateTo({
					    url: `plugin-private://${config.wxmpProvider}/pages/live-player-plugin?room_id=${item.roomid}&custom_params=`
					})
				}else if(item.replay_status==1){
					this.app.goPage('replay?roomid='+item.roomid);
				}
			},
			stopPrevent() {}
		}
	}
</script>

<style lang="scss">
	.live_item{
		height: 220rpx;
		box-shadow: 0 4rpx 20rpx 0 rgba(204,137,137,0.10);
		border-radius: 8rpx;
	}
	.live_left{
		width: 325rpx;
		height: 100%;
		position: relative;
		image{
			width: 100%;
			height: 100%;
		}
		.live_status{
			position: absolute;
			left: 2rpx;
			top: 2rpx;
			height: 32rpx;
			font-size: 20rpx;
			line-height: 32rpx;
			color: #FFFFFF;
			border-radius: 4rpx;
			overflow: hidden;
			text{
				display: inline-block;
				padding: 20rpx auto;
				min-width: 80rpx;
				text-align: center;
			}
			.recordColorA {
			    background: #7695B9;
			}
			.recordColorB {
			    background-color: #EC2828;
			    width: 90rpx;
			}
			.replay {
			    background: #7695B9;
			    font-size: 20rpx;
			    color: #FFFFFF;
			    text-align: center;
			    width: 80rpx;
			    height: 32rpx;
			    line-height: 32rpx;
			}
		}
		.playbtn{
			width: 56rpx;
			height: 56rpx;
			position: absolute;
			left: 50%;
			top: 50%;
			transform: translate3d(-50%,-50%,0);
		}
	}
	.live_info{
		padding: 10rpx;
		background: #fff;
		.room_name {
		    font-size: 30rpx;
		    color: #333333;
		    letter-spacing: 0;
		    overflow: hidden;
		    text-overflow: ellipsis;
		    white-space: nowrap;
		    line-height: 42rpx;
		}
		.liver_info{
			margin-top: 10rpx;
			.liver_img{
				width: 50rpx;
				height: 50rpx;
				border-radius: 50%;
				background-size: cover;
				margin-right: 14rpx;
				image{
					width: 100%;
					height: 100%;
				}
			}
			.liver_name {
			    font-size: 28rpx;
			    color: #999999;
			    letter-spacing: 0;
			    overflow: hidden;
			    text-overflow: ellipsis;
			    white-space: nowrap;
			}
		}
		.goods_list {
		    margin-top: 30rpx;
		    display: flex;
		    flex-wrap: wrap;
		    overflow: hidden;
		    height: 72rpx;
			.goods_item{
				width: 72rpx;
				height: 72rpx;
				border-radius: 8rpx;
				background-size: cover;
				margin-right: 18rpx;
				text-align: center;
				image{
					width: 100%;
					height: 100%;
				}
				.nums{
					height: 100%;
					color: #FFFFFF;
					background-color: rgba($color: #000000, $alpha: 0.3);
					font-size: 22rpx;
					display: flex;
					align-items: center;
					justify-content: center;
				}
				
			}
		}
	}
	
</style>
