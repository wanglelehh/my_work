<template>
	<view class="page-body">
		<view class="goods_navbar" >
			<view class="smll">
				<view class="flex_bd">
					<view class="smll">
					<view class="allClass"  @click="app.goPage('cateList?purchaseType='+purchaseType)"><view class="cateName">{{cateName}}</view></view>
					<view v-if="purchaseType == 4" class="text-center w100 font-w700 fs30">直购商城</view>
					<view v-else class="flex_bd purchase_box">进货上级：{{supply_name}}</view>
					</view>
				</view>
				<view>
					<view class="icon">
						<u-icon @click="app.goPage('/pagesB/channel/flow/cart?purchaseType='+purchaseType)" name="shopping-cart" size="50rpx"></u-icon>
						<u-badge :count="cartNum" :offset="[20, 20]"></u-badge>
					</view>
					<u-icon class="icon " :name="ListStyle" size="50rpx" @click="changListStyle"></u-icon>
				</view>
			</view>
			<u-search class="mt20" placeholder="输入关键字搜索" input-align="center" :show-action="false" v-model="keyword" @blur="loadData('refresh')" @clear="loadData('refresh')" ></u-search>
		</view>


		<scroll-view class="goods-list"  scroll-y @scrolltolower="loadData('add')">
			<view :class="ListStyle == 'grid'?'goods-list-grid':'goods-list-row'">
				<!-- 列表 -->
				<view v-for="(item, index) in itemList.list" :key="index" class="goods-item smll">
					<!-- 警告：微信小程序不支持嵌入lazyload组件，请自行如下使用image标签 -->
					<view class="image-wrapper " @click="app.goPage('product?purchaseType='+purchaseType+'&goods_id='+item.goods_id)">
						<!-- #ifndef MP-WEIXIN -->
						<u-lazy-load threshold="200" border-radius="10" mode="aspectFill" :image="baseUrl+item.goods_thumb" :index="index"></u-lazy-load>
						<!-- #endif -->
						<!-- #ifdef MP-WEIXIN -->
						<image :src="baseUrl+item.goods_thumb" mode="aspectFill"></image>
						<!-- #endif -->
					</view>
					<view class="info-wrapper flex_bd">
						<view class="title goods_name" @click="app.goPage('product?purchaseType='+purchaseType+'&goods_id='+item.goods_id)">{{item.goods_name}}</view>
						<view class="title short_name" @click="app.goPage('product?purchaseType='+purchaseType+'&goods_id='+item.goods_id)">{{item.short_name?item.short_name:item.goods_name}}</view>
						<view class="price-box smll">
							<text class="price flex_bd">{{item.price}}</text>
							<u-icon name="shopping-cart-fill" size="50rpx" @click="toggleSpec(item)"></u-icon>
						</view>
					</view>
				</view>
			</view>
			<!-- 空白页 -->
			<empty v-if="itemList.loaded === true && itemList.list.length === 0"></empty>
			<uni-load-more :status="loadingType"></uni-load-more>
		</scroll-view>
		
		<prductSpec :specClass="specClass" @selectSpec="selectSpec" @toggleSpec="toggleSpec" :specSelected="specSelected"
		:selectGoods="selectGoods" :selectGoodsPrice="selectGoodsPrice" :selectGoodsNumber="selectGoodsNumber" 
		:specGoodsImg="specGoodsImg"  :specList="specList" :specChildList="specChildList"
		:purchaseType="purchaseType"
		 ></prductSpec>
	</view>
</template>

<script>
	import uniLoadMore from '@/components/uni-load-more/uni-load-more.vue';
	import empty from "@/components/empty";
	import prductSpec from '@/pagesB/channel/product/spec';
	export default {
		components: {
			uniLoadMore,
			empty,
			prductSpec
		},
		data() {
			let that = this;
			return {
				baseUrl:this.config.baseUrl,
				purchaseType:0,//进货类型
				cateName:'全部分类',
				supply_name:'', //取货上级名
				param: { //传递参数
					p: 0, //默认页
				}, 
				ListStyle: 'grid',
				cartNum:0,
				keyword: '',
				loadingType: 'more', //加载更多状态
				filterIndex: 0,
				cateId: 0, //已选三级分类id
				itemList: {
					list:[]
				},//商品列表
				unitLits:[],//单位列表
				empty:false,//商品列表为空
				cateMaskState: 0,
				specClass:'none',
				specSelected:[],
				specList: [],
				specChildList: [],
				selectGoods:{},//选择的商品
				selectGoodsPrice:'0.00',
				selectGoodsNumber:0,
				specGoodsImg:'',
			}
		},
		onLoad(options) {
			if (typeof(options.purchaseType) == 'undefined' || options.purchaseType < 1 || options.purchaseType > 2){
				uni.showModal({
				    title: '提示',
				    content: '进货类型错误.',
					showCancel:false,
				    success: function (res) {
				        if (res.confirm) {
				          uni.navigateTo({
				              url: '/pagesB/channel/product/selectAdd'
				          });
				        } 
				    }
				});
				return false;
			}
			if (typeof(options.cateId) != 'undefined'){
				this.cateId = options.cateId;
				this.cateName = options.cateName;
			}
			this.purchaseType = options.purchaseType;
			this.loadData();
			this.loadCartNum();
			//获取登录会员信息
			this.$u.api.getProxyInfo().then(res => {
				this.supply_name = res.data.proxyInfo.supply_name;
			});	
		},
		
		computed: {

		},
		onReady() {},
		methods: {
			changListStyle(){
				if (this.ListStyle == 'grid'){
					this.ListStyle = 'list-dot';
				}else{
					this.ListStyle = 'grid';
				}
			},
			//加载购物车中商品条数
			async loadCartNum() {
				this.$u.post('channel/api.flow/getCartNum',{purchaseType:this.purchaseType}).then(res => {
					this.cartNum = res.data.cartNum;
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
					this.itemList.list = [];
				}
				this.param.purchaseType = this.purchaseType;
				this.param.cateId = this.cateId;
				this.param.keyword = this.keyword;
				this.param.p++;
				this.$u.post('channel/api.goods/getList', this.param).then(res => {
					this.itemList.list = this.itemList.list.concat(res.data.list);
					if (this.param.p == 1){
						this.unitLits = res.data.unitLits;
					}
					//loaded新字段用于表示数据加载完毕，如果为空可以显示空白页
					this.$set(this.itemList, 'loaded', true);
					//判断是否还有下一页，有是more  没有是nomore
					this.loadingType = this.param.p == res.data.page_count ? 'nomore' : 'more';
					
				})
			},
			
			//规格弹窗开关
			toggleSpec(selGoods){
				if(this.specClass === 'show'){
					this.loadCartNum();
					this.specClass = 'hide';
					setTimeout(() => {
						this.specClass = 'none';
					}, 250);
				}else if(this.specClass === 'none'){
					this.specSelected = [];
					this.specGoodsImg = this.config.baseUrl+selGoods.goods_thumb;
					this.selectGoodsPrice = selGoods.price;
					if (selGoods.is_spec == 0){//单规格
						selGoods.unitLits = this.unitLits;
						this.selectGoods = selGoods;
						this.selectGoodsNumber = selGoods.goods_number;
						this.specList = [];
						this.specChildList = [];
						this.specClass = 'show';
						return false;
					}
					this.$u.post('channel/api.goods/getGoodsInfo', {'goods_id':selGoods.goods_id,'purchaseType':this.purchaseType}).then(res => {
						this.selectGoods = res.data;
						this.specList = res.data.specList;
						this.specChildList = res.data.specChildList;
						this.specClass = 'show';
						this.specSelected = [];
						//规格 默认选中第一条
						this.specList.forEach(item=>{
							for(let cItem of this.specChildList){
								if(cItem.pid === item.id){
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
			selectSpec(list){
				//存储已选择
				/**
				 * 修复选择规格存储错误
				 * 将这几行代码替换即可
				 * 选择的规格存放在specSelected中
				 */
				this.specSelected = []; 
				list.forEach(item=>{ 
					if(item.selected === true){ 
						this.specSelected.push(item); 
					} 
				})
				this.showSelectSpec();
			},
			showSelectSpec(){
				let selSkuArr = [];
				this.specSelected.forEach(item=>{ 
					selSkuArr.push(item.id); 
				})
				let selSku = selSkuArr.join(":");
				this.specGoodsImg = '';
				this.selectGoods.imgSkuList.forEach(item=>{
					if (selSku == item.sku_val){
						this.specGoodsImg = this.config.baseUrl+item.goods_thumb;
						return false;
					}
				})
				if (this.specGoodsImg == ''){
					this.selectGoods.imgSkuList.forEach(item=>{
						let _sku = item.sku_val.split(':');
						if (selSkuArr[0] == _sku[0]){
							this.specGoodsImg = this.config.baseUrl+item.goods_thumb;
							return false;
						}
					})
				}
				this.selectGoods.sub_goods.forEach(item=>{
					if (selSku == item.sku_val){
						this.selectGoodsPrice = item.price;
						this.selectGoodsNumber = item.goods_number;
						return false;
					}
				})
			},
			
			stopPrevent(){}
		}
	}
</script>

<style lang="scss">
	.list-scroll-content{
		height: 100%;
	}
	.goods_navbar {
		width: 100%;
		background-color: #FFFFFF;
		padding: 15rpx;
		padding-bottom: 30rpx;
		line-height: 50rpx;
		z-index: 1;
	}

	.goods_navbar .allClass {
		position: relative;
		margin-left: 20rpx;
		float: left;
		padding-right: 20rpx;
		font-weight: 700;
		
	}
	.goods_navbar .allClass .cateName{
		max-width: 120rpx;
		white-space:nowrap;
		overflow: hidden;
	}
	.goods_navbar .allClass:after {
		content: "";
		position: absolute;
		top: 40%;
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
		top: 20%;
	}

	.goods_navbar .purchase_box {
		background-color: #000000;
		float: left;
		margin-left: 50rpx;
		color: #FFFFFF;
		padding-left: 40rpx;
		width: 90%;
		background-size: 20rpx;
		background-repeat: no-repeat;
		background-position: 10rpx center;
		border-radius: 10rpx;
		font-size: 22rpx;
	}

	.goods_navbar .icon {
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
	.goods-list {
		position: relative;
		height: calc(100% - 180rpx);
	}
	/* 商品列表 宫格*/
	.goods-list-grid {
		z-index: 2;
		display: flex;
		flex-wrap: wrap;
		padding: 15rpx;
		.goods-item {
			display: block;
			width: calc(50% - 15rpx);
			padding: 10rpx;
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
		.info-wrapper{
			.title {
				font-size: 32rpx;
				color: #303133;
				line-height: 50rpx;
				height: 100rpx;
				overflow: hidden;
			}
			.goods_name {
				display: none;
			}
			.price-box {
				display: flex;
				align-items: center;
				justify-content: space-between;
				padding-right: 10rpx;
				font-size: 24rpx;
				color: #909399;
			}
			.price {
				font-size: 32rpx;
				color: $font-color-base;
				line-height: 1;
				font-weight: 700;
				&:before {
					content: '￥';
					font-size: 26rpx;
				}
			}
			.u-icon{
				color: #4399fc;
			}
		}
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
				color: $font-color-base;
				line-height: 1;
	
				&:before {
					content: '￥';
					font-size: 26rpx;
				}
			}
			.u-icon {
				color: $font-color-base;
			}
		}
	}
</style>
