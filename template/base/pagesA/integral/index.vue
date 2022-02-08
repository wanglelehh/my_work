<template>
	<view class="page-body" :class="[app.setCStyle()]">
		<view class="u-page">
			<view class="goods_navbar">
				<view class="smll">
					<view class="flex_bd">
						<view class="smll">
							
							<u-search class="flex_bd mt20" :placeholder="app.langReplace('输入关键字搜索')" input-align="left" :show-action="false" v-model="keyword"
							 @blur="loadData('refresh')" @clear="loadData('refresh')"></u-search>
							 <view class="nav_item base-select">
								<view class=" fs28" :class="{current: tabOrder === 'integral'}" data-index="integral" @click="selectOrder">
									{{app.langReplace('积分')}}<text class="sort_icon" :class="{sort_desc:tabSort==='DESC',sort_asc:tabSort==='ASC'}"></text>
								</view>
							</view>
						</view>
					</view>
					<view>
						<view class="icon">
							<u-icon @click="app.goPage('/pagesA/integral/cart')" name="/static/public/images/shop_icon.png" size="48rpx"></u-icon>
							<u-badge :count="cartNum" :offset="[20, 20]"></u-badge>
						</view>
						<u-icon class="icon" :name="ListStyle_img" size="48rpx" @click="changListStyle"></u-icon>
					</view>
				</view>
				
			</view>

			<scroll-view class="goods-list" scroll-y @scrolltolower="loadData('add')">
				<view :class="ListStyle == 'grid'?'goods-list-grid':'goods-list-row'">
					<!-- 列表 -->
					<view v-for="(item, index) in itemList.list" :key="index" class="goods-item">
						<!-- 警告：微信小程序不支持嵌入lazyload组件，请自行如下使用image标签 -->
						<view class="image-wrapper " @click="app.goPage('info?ig_id='+item.ig_id)">
							<!-- #ifndef MP-WEIXIN -->
							<u-lazy-load threshold="250" border-radius="10" mode="aspectFill" :image="baseUrl+item.goods.goods_thumb" :index="index"></u-lazy-load>
							<!-- #endif -->
							<!-- #ifdef MP-WEIXIN -->
							<image :src="baseUrl+item.goods.goods_thumb" mode="aspectFill"></image>
							<!-- #endif -->
						</view>
						<view class="info-wrapper flex_bd">
							<view class="title goods_name" @click="app.goPage('info?ig_id='+item.ig_id)">{{item.goods.goods_name}}</view>
							<view class="title short_name" @click="app.goPage('info?ig_id='+item.ig_id)">{{item.goods.short_name}}</view>
							<view class="price-box smll base-color">
								<view class="price flex_bd ff ">{{item.show_integral}}<text class="fs24">{{app.langReplace('积分')}}</text></view>
								<u-icon name="plus-circle-fill" size="46rpx" @click="toggleSpec(item)"></u-icon>
							</view>
						</view>
					</view>
				</view>
				<!-- 空白页 -->
				<empty v-if="itemList.loaded === true && itemList.list.length === 0"></empty>
				<uni-load-more :status="loadingType"></uni-load-more>
				<view v-if="itemList.list.length > 0" style="height: 60rpx;"></view>
			</scroll-view>


			<goodsSpec @selectSpec="selectSpec" @toggleSpec="toggleSpec" :specClass="specClass" :specSelected="specSelected"
			 :selectGoods="selectGoods" :selectGoodsPrice="selectGoodsPrice" :selectGoodSmarketPrice="selectGoodSmarketPrice"
			 :selectGoodsNumber="selectGoodsNumber" :selectGoodsImg="selectGoodsImg" :specList="specList" :specChildList="specChildList"
			 :selectSkuId="selectSkuId"  :exType="exType"></goodsSpec>
		</view>
	</view>
</template>

<script>
	import uniLoadMore from '@/components/uni-load-more/uni-load-more.vue';
	import empty from "@/components/empty";
	import goodsSpec from '@/pages/shop/goods/spec';
	export default {
		components: {
			uniLoadMore,
			empty,
			goodsSpec
		},
		data() {
			let that = this;
			return {
				byType:'',
				exType:'integral',
				cartNum: 0,
				tabOrder: 'all',
				tabSort: '',
				baseUrl: this.config.baseUrl,
				cateName: '全部分类',
				param: { //传递参数
					p: 0, //默认页
				},
				ListStyle_img: '/static/public/images/list_icon01.png',
				ListStyle: 'grid',
				getCartNum: 0, //1执行请求购物车数量
				keyword: '',
				loadingType: 'more', //加载更多状态
				filterIndex: 0,
				cateId: 0, //已选三级分类id
				itemList: {
					list: []
				}, //商品列表
				empty: false, //商品列表为空
				cateMaskState: 0,
				specClass: 'none',
				specSelected: [],
				specList: [],
				specChildList: [],
				selectGoods: {}, //选择的商品
				selectGoodsId:0,
				selectSkuId:0,
				selectGoodsPrice: '0.00',
				selectGoodSmarketPrice: '0.00',
				selectGoodsNumber: 0,
				selectGoodsImg: '',
			}
		},
		onLoad(options) {
			let title = this.app.langReplace('积分商城');
			uni.setNavigationBarTitle({
				title
			})
			if (typeof(options.cateId) != 'undefined') {
				this.cateId = options.cateId;
				this.cateName = options.cateName;
			}
			if (typeof(options.keyword) != 'undefined') {
				this.keyword = options.keyword;
			}
			this.loadData();
			this.loadCartNum();
		},
		onShow() {
			this.getCartNum = 1;
		},
		onHide() {
			this.getCartNum = 0;
		},
		onReady() {},
		methods: {
			changListStyle() {
				var a = {
					type: 'grid',
					img: '/static/public/images/list_icon01.png',
				};
				var b = {
					type: 'row',
					img: '/static/public/images/list_icon02.png',
				};
				//切换
				var obj = this.ListStyle == a.type ? b : a;
				this.ListStyle = obj.type;
				this.ListStyle_img = obj.img;
			},
			//加载商品 
			async loadData(source = 'add') {
				//没有更多直接返回
				if (source == 'add' && this.loadingType == 'nomore') {
					return;
				}
				if (source === 'refresh') {
					this.param.p = 0;
					this.itemList.list = [];
				}
				this.param.cateId = this.cateId;
				this.param.keyword = this.keyword;
				this.param.order = this.tabOrder;
				this.param.sort = this.tabSort;
				this.param.p++;
				this.$u.post('integral/api.goods/getList', this.param).then(res => {
					this.itemList.list = this.itemList.list.concat(res.data.list);
					//loaded新字段用于表示数据加载完毕，如果为空可以显示空白页
					this.$set(this.itemList, 'loaded', true);
					//判断是否还有下一页，有是more  没有是nomore
					this.loadingType = this.param.p == res.data.page_count ? 'nomore' : 'more';

				})
			},
			selectOrder(e) { //排序筛选
				let tabOrder = e.target.dataset.index;
				if (tabOrder == 'integral') {
					if (this.tabOrder == tabOrder) {
						this.tabSort = this.tabSort != 'DESC' ? 'DESC' : 'ASC';
					} else {
						this.tabSort = 'DESC';
					}
				} else {
					this.tabSort = '';
				}
				this.tabOrder = tabOrder;
				this.loadData('refresh');
			},


			//规格弹窗开关
			toggleSpec(selGoods) {
				if (this.specClass === 'show') {
					this.getCartNum = 1;
					this.loadCartNum();
					this.specClass = 'hide';
					setTimeout(() => {
						this.specClass = 'none';
					}, 250);
				} else if (this.specClass === 'none') {
					if (this.selectGoodsId == selGoods.goods_id){
						this.specClass = 'show';
						this.showSelectSpec();
						return false;
					}
					this.selectGoodsId = selGoods.goods_id;
					this.getCartNum = 0;
					this.specSelected = [];
					this.selectGoodsImg = this.config.baseUrl + selGoods.goods.goods_thumb;
					if (selGoods.goods.is_spec == 0) { //单规格
						this.selectGoods = selGoods.goods;
						this.selectGoodsPrice = selGoods.goods.integral;
						this.selectGoodSmarketPrice = selGoods.goods.sale_price;
						this.selectGoodsNumber = selGoods.goods.goods_number;
						this.specList = [];
						this.specChildList = [];
						this.specClass = 'show';
						return false;
					}
					this.$u.post('integral/api.goods/info', {
						'ig_id': selGoods.ig_id
					}).then(res => {
						this.selectGoods = res.data.goods;
						this.specList = this.selectGoods.specList;
						this.specChildList = this.selectGoods.specChildList;
						this.specClass = 'show';
						this.specSelected = [];
						this.showSelectSpec();
					})
				}
			},
			//选择规格
			selectSpec(list) {
				//存储已选择
				/**
				 * 修复选择规格存储错误
				 * 将这几行代码替换即可
				 * 选择的规格存放在specSelected中
				 */
				this.specSelected = [];
				list.forEach(item => {
					if (item.selected === true) {
						this.specSelected.push(item);
					}
				})
				this.showSelectSpec();
			},
			showSelectSpec() {
				let that = this;
				this.selectSkuId = 0;
				let selSkuArr = [];
				this.specSelected.forEach(item => {
					selSkuArr.push(item.id);
				})
				let selSku = selSkuArr.join(":");
				Object.keys(this.selectGoods.sub_goods).forEach(sku_id => {
					let item = this.selectGoods.sub_goods[sku_id];
					if (selSku == item.sku_val) {
						if (item.integral > 0){
							this.selectSkuId = parseInt(item.sku_id);
							this.selectGoodsPrice = item.integral;
							this.selectGoodSmarketPrice = item.sale_price;
							this.selectGoodsNumber = item.goods_number;
						}
						return false;
					}
				})
				if (this.selectSkuId < 1){
					this.selectGoodsImg = this.config.baseUrl + this.selectGoods.goods_thumb;
				}
				if (selSkuArr.length < 1) {
					return false;
				}
				let selSkuImgArr = [];
				this.specList.forEach(function(item,index){
					if (item.img_type == 2){
						selSkuImgArr.push(selSkuArr[index]);
					}
				})
				let selSkuImg = selSkuImgArr.join(":");
				this.selectGoods.imgSkuList.forEach(item => {
					if (selSkuImg == item.sku_val) {
						this.selectGoodsImg = this.config.baseUrl + item.goods_thumb;
						return false;
					}
				})
			},
			//加载购物车中商品数
			async loadCartNum() {
				this.$u.post('integral/api.flow/getCartInfo').then(res => {
					this.cartNum = res.data.num;
				})
			},
			stopPrevent() {}
		}
	}
</script>

<style lang="scss">
	.u-page {
		height: 100%;
		overflow: hidden;
	}

	.goods_navbar {
		width: 100%;
		background-color: #FFFFFF;
		padding: 15rpx;
		line-height: 50rpx;
		z-index: 1;
	}

	.goods_navbar .allClass {
		position: relative;
		margin-left: 20rpx;
		margin-right: 30rpx;
		padding-right: 20rpx;
		padding-top: 20rpx;
	}

	.goods_navbar .allClass .cateName {
		max-width: 120rpx;
		white-space: nowrap;
		overflow: hidden;
	}

	.goods_navbar .allClass:after {
		content: "";
		position: absolute;
		top: 55%;
		right: -10rpx;
		border: 10rpx solid #000000;
		border-bottom-color: transparent;
		/* 设置透明背景色 */
		border-left-color: transparent;
		border-right-color: transparent;
	}

	.goods_navbar .allClass.active:after {
		border-top-color: transparent;
		/* 设置透明背景色 */
		border-bottom-color: #000;
		border-left-color: transparent;
		border-right-color: transparent;
		top: 40%;
	}

	.goods_navbar .icon {
		margin-top: 30rpx;
		margin-left: 15rpx;
		position: relative;
		width: 50rpx;
		float: left;
	}

	.goods_navbar .icon .u-badge {
		position: absolute;
		top: -10rpx !important;
		right: -10rpx !important;
	}
	
	.goods_navbar .nav_item{
		position: relative;
		margin-left: 30rpx;
		padding-right: 30rpx;
		padding-top: 20rpx;
		.current {
			&:after {
				display: none;
			}
			.sort_desc {
				&:after {
					border-bottom-color: transparent !important;
					/* 设置透明背景色 */
					border-left-color: transparent !important;
					border-right-color: transparent !important;
				}
			}
			.sort_asc {
				&:before {
					border-top-color: transparent !important;
					/* 设置透明背景色 */
					border-left-color: transparent !important;
					border-right-color: transparent !important;
				}
			}
		}
		
		.sort_icon {
			position: relative;
			height: 100%;
			margin-left: 10rpx;
			&:before {
				content: "";
				position: absolute;
				top: -10%;
				border: 8rpx solid;
				border-color:#000;
				border-top-color: transparent;
				/* 设置透明背景色 */
				border-left-color: transparent;
				border-right-color: transparent;
			}
		
			&:after {
				content: "";
				position: absolute;
				bottom: -10%;
				border: 8rpx solid ;
				border-color:#000;
				border-bottom-color: transparent;
				/* 设置透明背景色 */
				border-left-color: transparent;
				border-right-color: transparent;
			}
		}
	}


	.goods-list {
		position: relative;
		height: calc(100% - 128rpx);
	}

	/* 商品列表 宫格*/
	.goods-list-grid {
		z-index: 2;
		display: flex;
		flex-wrap: wrap;
		padding: 20rpx;

		.goods-item {
			display: block;
			width: calc(50% - 11rpx);
			background: #FFFFFF;
			margin-bottom: 20rpx;
			border-radius: 10rpx;

			&:nth-child(2n+1) {
				margin-right: 20rpx;
			}
		}

		.image-wrapper {
			width: 100%;
			height: 330rpx;
			border-radius: 3px;
			overflow: hidden;

			image {
				width: 100%;
				height: 100%;
				opacity: 1;
			}
		}

		.info-wrapper {
			padding: 16rpx;

			.title {
				font-size: 28rpx;
				color: $font-color-dark;
				line-height: 40rpx;
				height: 80rpx;
				overflow: hidden;
				text-overflow: ellipsis;
				display: -webkit-box;
				-webkit-line-clamp: 2;
				-webkit-box-orient: vertical;
			}

			.goods_name {
				display: none;
			}

			.price-box {
				display: flex;
				align-items: center;
				justify-content: space-between;
				margin-top: 10rpx;
			}

			.price {
				font-size: 40rpx;
				line-height: 1;
				
			}

			
		}
	}

	/* 商品列表 行*/
	.goods-list-row {
		z-index: 2;

		.goods-item {
			margin: 20rpx;
			padding: 20rpx;
			border-radius: 10rpx;
			background-color: #FFFFFF;
			display: flex;
		}

		.image-wrapper {
			width: 220rpx;
			height: 220rpx;
			border-radius: 10rpx;
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
			justify-content: space-between;
			flex: 1;
			overflow: hidden;
			position: relative;
			padding-left: 20rpx;

			.title {
				font-size: 28rpx;
				color: $font-color-dark;
				line-height: 40rpx;
				height: 80rpx;
				overflow: hidden;
				text-overflow: ellipsis;
				display: -webkit-box;
				-webkit-line-clamp: 2;
				-webkit-box-orient: vertical;
			}

			.short_name {
				display: none;
			}

			.price-box {
				align-items: center;
				justify-content: space-between;
				padding-right: 10rpx;
	
			}

			.price {
				font-size: 40rpx;
				line-height: 1;
			}

			
		}
	}
</style>
