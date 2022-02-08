<template>
	<view class="page-body ">
		<view class="top_navbar b-tottom">
			<view class="navbar">
				<view class="nav-item" v-for="(item, index) in navList" :key="index" :class="{current: topTabCurrentIndex === index}"
				 @click="topTabClick(index)">{{item.text}}</view>
			</view>
			<view class="search_box smll mt20">
				<view class="allClass"  @click="app.goPage('/pagesB/channel/product/cateList?purchaseType='+purchaseType)"><view class="cateName">{{cateName}}</view></view>
				<u-search class="flex_bd ml30" placeholder="输入关键字搜索" input-align="center" :show-action="false" v-model="keyword" @blur="loadData('refresh')" @clear="loadData('refresh')"></u-search>
			</view>
		</view>
	
		<scroll-view scroll-y class="goods-list">
				<!-- 空白页 -->
				<empty v-if="navList[topTabCurrentIndex].loaded === true && navList[topTabCurrentIndex].itemList.length === 0"></empty>
				<view v-for="(item, index) in navList[topTabCurrentIndex].itemList" :key="index">
					<view class="goods-item b-tottom" @click="showSku(index)">
							<view class="image">
								<image :src="baseUrl+item.goods_thumb" mode="aspectFill"></image>
							</view>
							<view class="info right-sj">
								<view class="goods-name">{{item.goods_name}}</view>
								<view class="text-main mt20 flex">
									<view class="flex_bd">
										<text class="mr10">库存:{{item.goods_number}}</text>
										<text>出货:{{item.out_number}}</text>
									</view>
									<u-button v-if="item.skuList.length == 0" class="btn" size="mini" shape="square" @click="app.goPage('/pagesB/channel/myStock/detail?hash='+item.hash_code)">明细</u-button>
								</view>
							</view>
					</view>
					<view  v-if="item.skuList.length > 0 && item.showSku == true" class="p20 bg-white">
						<view class="goods-sku left-yuan"  v-for="(skuItem, skuIndex) in item.skuList" :key="skuIndex">
							<u-button class="btn" size="mini" shape="square" @click="app.goPage('/pagesB/channel/myStock/detail?hash='+skuItem.hash_code)">明细</u-button>
							<view class="sku-name">{{skuItem.sku_name}}</view>
							<view class="sku-stock">
								<text class="mr20">库存:{{skuItem.goods_number}}</text>
								<text >出货:{{skuItem.out_number}}</text>
							</view>
						</view>
					</view>
				</view>
		</scroll-view>
		<view class="fixed-footer smll">
			<view class="btn" @click="app.goPage('/pagesB/channel/myStock/pickUp')">我要提货</view>
			<view class="btn" @click="app.goPage('/pagesB/channel/product/selectAdd')">我要进货</view>
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
			return {
				baseUrl:this.config.baseUrl,
				topTabCurrentIndex: 0,
				cateName: '全部分类',
				cateId: 0, //已选三级分类id
				cateList: [], //分类列表
				cateMaskState: 0,
				keyword: '',
				navList: [{
						state: 'cloud',
						text: '云仓库',
						itemList: [],
					},
					{
						state: 'entity',
						text: '实体库',
						itemList: [],
					}
				],
				param: {}, 
			}
		},
		onLoad(options) {
			this.loadData();
		},
		methods: {
			//顶部tab点击
			topTabClick(index) {
				this.topTabCurrentIndex = index;
				this.loadData();
			},
			loadData(){	//获取列表
				uni.pageScrollTo({
					duration: 300,
					scrollTop: 0
				})
				let index = this.topTabCurrentIndex;
				let navItem = this.navList[index];
				this.param.state = navItem.state;
				this.param.cateId = this.cateId;
				this.param.keyword = this.keyword;
				this.$u.post('channel/api.stock/getList', this.param).then(res => {
					navItem.itemList = res.data.list;
					//loaded新字段用于表示数据加载完毕，如果为空可以显示空白页
					this.$set(navItem, 'loaded', true);
				})
			}, 
			loadCateList() {//加载分类
				this.$u.post('shop/api.goods/getCateList').then(res => {
					this.cateList = res.data.list;
				})
			},
			toggleCateMask(type) { //显示分类面板
				if (type === 'show' && this.cateList.length < 1) {
					this.loadCateList();
				}
				let timer = type === 'show' ? 10 : 300;
				let state = type === 'show' ? 1 : 0;
				this.cateMaskState = 2;
				setTimeout(() => {
					this.cateMaskState = state;
				}, timer)
			},
			//分类点击
			changeCate(item) {
				if (typeof(item) == 'undefined'){
					this.cateId = 0;
					this.cateName = '全部分类';
				}else{
					this.cateId = item.id;
					this.cateName = item.name;
				}
				this.toggleCateMask();
				this.loadData();
				uni.showLoading({
					title: '正在加载'
				})
			},
			showSku(index){//显示sku列
				let item = this.navList[this.topTabCurrentIndex].itemList[index];
				if (item.skuList.length == 0){
					return false;
				}
				this.$set(item,'showSku',item.showSku==true?false:true);
			}
		}
	}
</script>

<style lang="scss">
	@import "~@/pagesB/static/channel/css/myStock.scss";
	.fixed-footer {
		position: fixed;
		bottom: 0;
		left: 0;
		padding: 14rpx 30px;
		background-color: #fff;
		width: 100%;
		box-sizing: border-box;
		.btn {
			-webkit-box-flex: 1;
			-webkit-flex: 1;
			flex: 1;
			line-height: 35px;
			font-size: 14px;
			font-weight: 600;
			color: #fff;
			text-align: center;
			&:first-of-type {
				background-color: #252525;
				border-radius: 35px 0 0 35px;
			}
			&:last-of-type {
				background-color: #5392f3;
				border-radius: 0 35px 35px 0;
			}
		}
	}
</style>
