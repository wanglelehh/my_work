<template>
	<view class="page-body  " :class="[app.setCStyle()]">
		<view class="u-page">
			<view class="goods_navbar">
				<view class="smll">
					<view class="flex_bd">
						<view class="smll">
							<u-search class="flex_bd mt20" :placeholder="app.langReplace('输入关键字搜索')" input-align="center" :show-action="false" v-model="keyword"
							 @blur="loadData('refresh')" @clear="loadData('refresh')"></u-search>
						</view>
					</view>
					<view>
						<view class="icon">
							<u-icon @click="app.goPage('/pages/shop/flow/cart')" name="/static/public/images/shop_icon.png" size="48rpx"></u-icon>
							<u-badge :count="cartNum" :offset="[20, 20]"></u-badge>
						</view>
						<u-icon class="icon" :name="ListStyle_img" size="48rpx" @click="changListStyle"></u-icon>
					</view>
				</view>
				<view class="bonus_list p20">
					<view class="item flex mt20">
						<view class="left">
							<view class="color-00 fs32 font-w700">{{bonus.type_name}}</view>
							<view class="fs26 mt5">{{app.langReplace('满')}}￥{{bonus.min_amount}}{{app.langReplace('可用')}}</view>
							<view class="fs26 mt5">{{bonus._use_start_date}}-{{bonus._use_end_date}}</view>
						</view>
						<view class="center"></view>
						<view class="right flex_bd smll ">
							<view class="w100 text-center">
								<view class="fs60 font-w700 "><text class="fs36">￥</text>{{bonus.type_money}}</view>
							</view>
						</view>
					</view>
				</view>
			</view>

			<scroll-view class="goods-list" scroll-y @scrolltolower="loadData('add')">
				<view :class="ListStyle == 'grid'?'goods-list-grid':'goods-list-row'">
					<!-- 列表 -->
					<view v-for="(item, index) in itemList.list" :key="index" class="goods-item">
						<!-- 警告：微信小程序不支持嵌入lazyload组件，请自行如下使用image标签 -->
						<view class="image-wrapper " @click="app.goPage('/pages/shop/goods/info?goods_id='+item.goods_id)">
							<!-- #ifndef MP-WEIXIN -->
							<u-lazy-load threshold="-450" border-radius="10" mode="aspectFill" :image="baseUrl+item.goods_thumb" :index="index"></u-lazy-load>
							<!-- #endif -->
							<!-- #ifdef MP-WEIXIN -->
							<image :src="baseUrl+item.goods_thumb" mode="aspectFill"></image>
							<!-- #endif -->
						</view>
						<view class="info-wrapper flex_bd">
							<view class="title goods_name" @click="app.goPage('/pages/shop/goods/info?goods_id='+item.goods_id)">{{item.goods_name}}</view>
							<view class="title short_name" @click="app.goPage('/pages/shop/goods/info?goods_id='+item.goods_id)">{{item.short_name}}</view>
							<view class="price-box smll base-color">
								<text class="price flex_bd ff ">{{item.sale_price}}</text>
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
			 :selectGoodsNumber="selectGoodsNumber" :specGoodsImg="specGoodsImg" :specList="specList" :specChildList="specChildList"></goodsSpec>
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
				cartNum: 0,
				tabOrder: 'all',
				tabSort: '',
				baseUrl: this.config.baseUrl,
				param: { //传递参数
					p: 0, //默认页
				},
				ListStyle_img: '/static/public/images/list_icon01.png',
				ListStyle: 'grid',
				getCartNum: 0, //1执行请求购物车数量
				keyword: '',
				loadingType: 'more', //加载更多状态
				filterIndex: 0,
				type_id: 0, //已选优惠券
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
				selectGoodsPrice: '0.00',
				selectGoodSmarketPrice: '0.00',
				selectGoodsNumber: 0,
				specGoodsImg: '',
				bonus:[]
			}
		},
		onLoad(options) {
			if (typeof(options.type_id) != 'undefined') {
				this.type_id = options.type_id;
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
				this.param.type_id = this.type_id;
				this.param.keyword = this.keyword;
				this.param.p++;
				this.$u.post('shop/api.bonus/getGoodsList', this.param).then(res => {
					this.itemList.list = this.itemList.list.concat(res.data.list);
					this.bonus = res.data.bonus;
					//loaded新字段用于表示数据加载完毕，如果为空可以显示空白页
					this.$set(this.itemList, 'loaded', true);
					//判断是否还有下一页，有是more  没有是nomore
					this.loadingType = this.param.p == res.data.page_count ? 'nomore' : 'more';

				})
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
					this.getCartNum = 0;
					this.specSelected = [];
					this.specGoodsImg = this.config.baseUrl + selGoods.goods_thumb;
					this.selectGoodsPrice = selGoods.sale_price;
					if (selGoods.is_spec == 0) { //单规格
						this.selectGoods = selGoods;
						this.selectGoodsPrice = selGoods.sale_price;
						this.selectGoodSmarketPrice = selGoods.market_price;
						this.selectGoodsNumber = selGoods.goods_number;
						this.specList = [];
						this.specChildList = [];
						this.specClass = 'show';
						return false;
					}
					this.$u.post('shop/api.goods/getGoodsInfo', {
						'goods_id': selGoods.goods_id
					}).then(res => {
						this.selectGoods = res.data;
						this.specList = res.data.specList;
						this.specChildList = res.data.specChildList;
						this.specClass = 'show';
						this.specSelected = [];
						//规格 默认选中第一条
						this.specList.forEach(item => {
							for (let cItem of this.specChildList) {
								if (cItem.pid === item.id) {
									this.$set(cItem, 'selected', true);
									this.specSelected.push(cItem);
									break; //forEach不能使用break
								}
							}
						})
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
				let selSkuArr = [];
				this.specSelected.forEach(item => {
					selSkuArr.push(item.id);
				})
				let selSku = selSkuArr.join(":");
				this.specGoodsImg = '';
				this.selectGoods.imgSkuList.forEach(item => {
					if (selSku == item.sku_val) {
						this.specGoodsImg = this.config.baseUrl + item.goods_thumb;
						return false;
					}
				})
				if (this.specGoodsImg == '') {
					this.selectGoods.imgSkuList.forEach(item => {
						let _sku = item.sku_val.split(':');
						if (selSkuArr[0] == _sku[0]) {
							this.specGoodsImg = this.config.baseUrl + item.goods_thumb;
							return false;
						}
					})
				}

				Object.keys(this.selectGoods.sub_goods).forEach(sku_id => {
					let item = this.selectGoods.sub_goods[sku_id];
					if (selSku == item.sku_val) {
						this.selectGoodsPrice = item.sale_price;
						this.selectGoodSmarketPrice = item.market_price;
						this.selectGoodsNumber = item.goods_number;
						return false;
					}
				})
			},
			//加载购物车中商品数
			async loadCartNum() {
				this.$u.post('shop/api.flow/getCartInfo').then(res => {
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
		z-index: 1;
	}

	.goods_navbar .allClass {
		position: relative;
		margin-left: 20rpx;
		margin-right: 30rpx;
		float: left;
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
		margin-left: 30rpx;
		position: relative;
		width: 50rpx;
		float: left;
	}

	.goods_navbar .icon .u-badge {
		position: absolute;
		top: -10rpx !important;
		right: -10rpx !important;
	}

	.navbar {
		box-shadow: none !important;
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
				top: 25%;
				border: 8rpx solid;
				border-color:#999999;
				border-top-color: transparent;
				/* 设置透明背景色 */
				border-left-color: transparent;
				border-right-color: transparent;
			}

			&:after {
				content: "";
				position: absolute;
				bottom: 25%;
				border: 8rpx solid ;
				border-color:#999999;
				border-bottom-color: transparent;
				/* 设置透明背景色 */
				border-left-color: transparent;
				border-right-color: transparent;
			}
		}
	}

	.goods-list {
		position: relative;
		height: calc(100% - 280rpx);
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
				&:before {
					content: '￥';
					font-size: 24rpx;
				}
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

				&:before {
					content: '￥';
					font-size: 24rpx;
				}
			}

			
		}
	}
</style>
