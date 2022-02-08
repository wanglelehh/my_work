<template>
	<view class="page-body" :class="[app.setCStyle()]">
		<view class="u-search-box">
			 <u-search class="flex_bd " :placeholder="setting.shop_index_search_text" input-align="left" :show-action="false" v-model="keyword"
			  @blur="searchGoods()"></u-search>
		</view>
		<view class="tabs ">
			<scroll-view id="tab-bar" class="scroll-h base-select" :scroll-x="true" :show-scrollbar="false" >
				<view v-for="(tab,index) in cateList[0]" :key="tab.id" class="uni-tab-item" :class="tabIndex==tab.id ? 'current' : ''" >
					<text class="uni-tab-item-title" :class="tabIndex==tab.id ? 'uni-tab-item-title-active' : ''"  :data-cid="tab.id" @click="ontabtap">{{tab.name}}</text>
				</view>
			</scroll-view>
			<view class="line-h"></view>
		</view>
		<view class="category-list">
			<!-- 左侧分类导航 -->
			<scroll-view  scroll-y="true" class="left base-select" >
				<view class="row" v-for="(category,index) in cateList[tabIndex]" :key="index" >
					<view v-if="cateList[category.id]" class="text">
						<view class="tes nor">{{category.name}}</view>
						<view class="texts" v-for="(childcate,cid) in cateList[category.id]" :key="cid" @tap="selectCid(childcate.id)" :class="childcate.id==select_cid?'current':''">
							<view class="block"></view>
							{{childcate.name}}
						</view>
					</view>
					<view v-else class="text">
						<view class="texts" :class="category.id==select_cid?'current':''" @tap="selectCid(category.id)"><view class="block"></view>{{category.name}}</view>
					</view>
				</view>
			</scroll-view>
			<!-- 右侧子导航 -->
			<scroll-view  scroll-y="true" class="right">
				<view class="category" v-show="true">
					<view class="list">
						<view class="box" v-for="(item,index) in goodsList" :key="index" @click="app.goPage(`/pages/shop/goods/info?goods_id=${item.goods_id}`)">
							<image :src="baseUrl+item.goods_thumb"  model="aspectFill"></image>
							<view class="text fs28">{{item.goods_name}}</view>
							<view class="price mt10">{{item.sale_price}}</view>
						</view>
						<view v-if="goodsList.length < 1" class="w100 text-center color-cc mt30">
							{{app.langReplace('暂无相关商品')}}
						</view>
					</view>
				</view>
			</scroll-view>
		</view>
		<tabbar :now_page="now_page" :getCartNum="0"></tabbar>
	</view>
</template>

<script>
	import tabbar from '@/pages/shop/tabbar';
	export default {
		components: {
			tabbar
		},
		data() {
			return {
				baseUrl:this.config.baseUrl,
				now_page:'',
				setting:{},
				cateList: {}, //分类列表
				tabIndex:0,
				select_cid:0,
				goodsList:{},
				keyword:'',
			}
		},
		onLoad(options) {
			this.loadCateList();			
			//微信公众号分享
			this.setting = uni.getStorageSync('setting');
			let title = this.app.langReplace('商品分类');
			uni.setNavigationBarTitle({
				title
			})
			this.now_page = this.$mp.page.route;
		},
		computed: {
			
		},
		methods: {
			//加载分类
			async loadCateList() {
				this.$u.post('shop/api.goods/getCateList').then(res => {
					this.cateList = res.data.list;
					this.tabIndex = this.cateList[0][0]['id'];
					this.getDefaultCid();
				})
			},
			ontabtap(e) {
				this.tabIndex = e.target.dataset.cid;
				this.getDefaultCid();
			},
			getDefaultCid(){
				let selCid = 0;
				if (typeof(this.cateList[this.tabIndex]) == 'undefined'){
					this.selectCid(selCid);
					return true;
				}
				selCid = this.cateList[this.tabIndex][0]['id'];
				this.selectCid(selCid);
			},
			selectCid(cid){
				this.select_cid = cid;
				this.getGoodsList();
			},
			getGoodsList(){
				this.goodsList = {};
				if (this.select_cid < 1){
					return false;
				}
				this.$u.post('shop/api.goods/getList', {'cateId':this.select_cid,'page_size':999}).then(res => {
					this.goodsList = res.data.list;
				})
			},
			searchGoods(){
				let keyword = this.keyword;
				if (keyword == ''){
					return false;
				}
				return this.app.goPage('index?keyword='+this.keyword);
			}
		}
	}
</script>

<style lang="scss" scoped>
	.page-body {
		height: calc(100vh);
		/* #ifdef H5 */
		height: calc(100vh - var(--window-top));
		/* #endif */
		display: flex;
		flex-direction: column;
		background-color: #FFFFFF;
	}

	.u-search-box {
		padding: 18rpx 30rpx;
	}

	.tabs {
		flex-direction: column;
		overflow: hidden;
		background-color: #ffffff;
		height: 100rpx;
		border-bottom:1rpx solid #efefef;
	}
	.tabs .current{
		position: relative;
		font-size: 36rpx;
		font-weight: bold;
	}
	.tabs .current:after{
		position: absolute;
		content: '';
		bottom: 0;
		width: 60rpx;
		left: 30%;
		//border-bottom:6rpx solid #000000;
		height: 6rpx;
		border-radius: 4rpx;
	}
	
	.scroll-h {
		width: 750rpx;
		line-height: 100rpx;
		height: 110rpx;
		flex-direction: row;
		/* #ifndef APP-PLUS */
		white-space: nowrap;
		/* #endif */
	}
	
	.line-h {
		height: 1rpx;
		background-color: #cccccc;
	}
	.uni-tab-item {
		/* #ifndef APP-PLUS */
		display: inline-block;
		/* #endif */
		height: 85%;
		flex-wrap: nowrap;
		width: calc(100% / 4.7);
		text-align: center;
	}
	
	.uni-tab-item-title {
		color: #999999;
		font-size: 30rpx;
		height: 80rpx;
		line-height: 80rpx;
		flex-wrap: nowrap;
		/* #ifndef APP-PLUS */
		white-space: nowrap;
		/* #endif */
	}
	.uni-tab-item-title-active{
		color: #333333;
	}
	.price {
		font-size: 32rpx;
		line-height: 1;
		font-weight: bold;
		&:before {
			content: '￥';
			font-size: 24rpx;
		}
	}
	

	.category-list{
		flex: 1;height: 100%; position: relative;
		width: 100%;
		background-color: #fff;
		display: flex;
		height: 100%;
		overflow: scroll;
		.left,.right{
			position: absolute;
			top: 0px;
			height: 100%;
		}
		.left{
			width: 24%;
			left: 0rpx;
			background-color: #f2f2f2;
			height: 100%;
			.row{
				width: 100%;
				align-items: center;
				.text{
					text-align: center;
					width: 100%;
					position: relative;
					font-size: 28upx;
					color: #999999;
					.tes{
						height: 100rpx;
						line-height: 100rpx;
						&.nor{
							&::after{
								position: absolute;
								content: '';
								width: 12px;
								height: 1px;
								background: #C2C2C2;
								top: 24px;
								left: 8px;
							}
							&::before{
								position: absolute;
								content: '';
								width: 12px;
								height: 1px;
								background: #C2C2C2;
								top: 24px;
								right: 8px;
							}
						}
					}
					.texts{
						height: 100rpx;
						line-height: 100rpx;
						color: #999999;
						&.current{
							background-color: #fff;
							font-weight: bold;
						}
					}
				}
			
			}
		}
		.right{
			width: 76%;
			left: 24%;
		    overflow-y: scroll;
			background-color: #FFFFFF;
			height: 100%;
			.category{
				width: 100%;
				// height: 100%;
				padding: 20rpx 5%;
				background: #ffffff;
				.banner{
					width: 100%;
					height: 24.262vw;
					// border-radius: 10upx;
					overflow: hidden;
					// box-shadow: 0upx 5upx 20upx rgba(0,0,0,0.3);
					image{
						width: 100%;
						height: 24.262vw;
					}
				}
				.list{
					margin-top: 40rpx;
					width: 100%;
					display: flex;
					flex-wrap: wrap;
					.box{
						width: calc(100% / 2);
						margin-bottom: 30rpx;
						display: flex;
						justify-content: center;
						align-items: center;
						flex-wrap: wrap;
						image {
							width: 220rpx;
							height: 220rpx;
							opacity: 1;
						}
						.text{
							margin-top: 5rpx;
							width: 90%;
							display: block;
							    text-align: center;
							justify-content: center;
							font-size: 26rpx;
							white-space: nowrap;
							overflow: hidden;
							text-overflow: ellipsis;
						}
					}
				}
			}
		}
	}
</style>
