<template>
	<view class="page-body ">
		<view class="p20 bg-white">
			<view class="search_box smll">
				<view class="allClass"  @click="app.goPage('/pagesB/channel/product/cateList?purchaseType='+purchaseType)"><view class="cateName">{{cateName}}</view></view>
				<u-search class="flex_bd ml30" placeholder="输入关键字搜索" input-align="center" :show-action="false" v-model="keyword" @blur="loadData('refresh')" @clear="loadData('refresh')"></u-search>
			</view>
		</view>
		
		<scroll-view scroll-y class="goods-list flex">
				<!-- 空白页 -->
				<empty v-if="goodsList.loaded === true && goodsList.itemList.length === 0"></empty>
				<view v-for="(item, index) in goodsList.itemList" :key="index">
					<view class="goods-item b-tottom" @click="showSku(index)">
							<view class="image">
								<image :src="config.baseUrl+item.goods_thumb" mode="aspectFill"></image>
							</view>
							<view class="info " :class="item.skuList.length>0?'right-sj':''">
								<view class="goods-name">{{item.goods_name}}</view>
								<view class="text-main mt20">
									<text class="mr10">库存:{{item.goods_number}}</text>
									<text>出货:{{item.out_number}}</text>
								</view>
							</view>
					</view>
					<view v-if="item.showSku !== false"  class="p20 bg-white">
						<view v-if="item.skuList.length > 0">
							<view class="goods-sku left-yuan pickup-box"  v-for="(skuItem, skuIndex) in item.skuList" :key="skuIndex">
								<view class="p10 flex">
								<text class="sku-name">{{skuItem.sku_name}}</text>
								<view class="sku-stock">
									<text class="mr20">可提{{skuItem.goods_number}}件</text>
									<u-number-box class="number-box" :long-press="false"  :value="skuItem.pickup_number" :index="index+':'+skuIndex"  :min="0" :max="skuItem.goods_number" :step="1" @change="numberChange"></u-number-box>
								</view>
								</view>
							</view>
						</view>
						<view v-else>
							<view class="goods-sku pickup-box flex">
								<text class="sku-name"></text>
								<view class="sku-stock">
									<text class="mr20">可提{{item.goods_number}}件</text>
									<u-number-box class="number-box" :long-press="false"  :value="item.pickup_number" :index="index+':0'" :min="0" :max="item.goods_number" :step="1" @change="numberChange"></u-number-box>
								</view>
							</view>
						</view>
					</view>
				</view>
		</scroll-view>
		
		<view class="fixed_footer smll">
			<view class="number_box" @click="showPopup">
				<u-image width="44rpx" height="44rpx" src="/pagesB/static/channel/images/title_icon/icon_7.png"></u-image>
				<text class="number ">{{numberTotal}}</text>
			</view>
			<uni-view class="ns-text fs26">已选{{numberTotal}}件商品</uni-view>
			<u-button class="btn" type="primary" size="medium" shape="circle" @click="checkOut" >立即结算</u-button>
		</view>
		<u-popup v-model="isShowPopup" mode="bottom" z-index="99" >
			<view class="popbox">
				<view class="smll p20">
					<view class="title">
						提货清单
				    </view>
					<u-icon @click="isShowPopup=false" name="close-circle-fill" color="#b2b2b2" size="48"></u-icon>
				</view>
				<scroll-view scroll-y class="content">
					<view v-for="(item, index) in selGoodsList" :key="index" v-if="item.pickup_number>0">
						<view class="goods-item b-tottom">
								<view class="image">
									<image :src="config.baseUrl+item.goods_thumb" mode="aspectFill"></image>
								</view>
								<view class="info right-sj">
									<view class="goods-name">{{item.goods_name}}</view>
									<view class="text-main mt20">
										<text class="fr">总计:{{item.pickup_number}}件</text>
									</view>
								</view>
						</view>
						<view class="p20 bg-white">
							<view v-if="item.skuList.length > 0">
								<view class="goods-sku left-yuan pickup-box b-tottom" v-for="(skuItem, skuIndex) in item.skuList" :key="skuIndex" v-if="skuItem.pickup_number>0">
									<view class="p10 flex">
										<text class="sku-name">{{skuItem.sku_name}}</text>
										<view class="sku-stock">
											数量：{{skuItem.pickup_number}}件
										</view>
									</view>
								</view>
							</view>
						</view>
					</view>
				</scroll-view>
			</view>
		</u-popup>
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
				purchaseType:3,
				goodsList:{
					itemList:[]
				},
				selGoodsList:{},
				cateName: '全部分类',
				cateId: 0, //已选三级分类id
				cateList: [], //分类列表
				cateMaskState: 0,
				keyword: '',
				param: {},
				numberTotal:0,
				isShowPopup:false,
			}
		},
		onLoad(options) {
			this.loadData();
		},
		methods: {
			loadData() { //获取列表
				this.param.state = 'cloud';
				this.param.cateId = this.cateId;
				this.param.keyword = this.keyword;
				let that = this;
				this.$u.post('channel/api.stock/getList', this.param).then(res => {
					this.goodsList.itemList = res.data.list;
					this.goodsList.itemList.forEach(item=>{
						if (item.skuList.length > 0){
							item.skuList.forEach(function(skuItem,skuIndex){
								if (typeof(that.selGoodsList[item.goods_id]) == 'undefined'){
									skuItem.pickup_number = 0;
								}else{
									skuItem.pickup_number = that.selGoodsList[item.goods_id].skuList[skuIndex].pickup_number;
								}
							})
						}else{
							if (typeof(that.selGoodsList[item.goods_id]) == 'undefined'){
								item.pickup_number = 0;
							}else{
								item.pickup_number = that.selGoodsList[item.goods_id].pickup_number;
							}
						}
					})
					//loaded新字段用于表示数据加载完毕，如果为空可以显示空白页
					this.$set(this.goodsList, 'loaded', true);
				})
			},
			showSku(index) { //显示sku列
				let item = this.goodsList.itemList[index];
				if (item.skuList.length == 0) {
					return false;
				}
				this.$set(item, 'showSku', item.showSku === true || typeof(item.showSku) == 'undefined' ? false : true);
			},
			numberChange(data){
				let index = data.index.split(":") ;
				let goodsIndex = index[0];
				let skuIndex = index[1];
				let goods = this.goodsList.itemList[goodsIndex];
				if (goods.skuList.length > 0){
					if (goods.skuList[skuIndex].pickup_number > 0){
						goods.pickup_number -= goods.skuList[skuIndex].pickup_number;
						this.numberTotal -= goods.skuList[skuIndex].pickup_number;
					}
					goods.skuList[skuIndex].pickup_number = data.value;
					goods.pickup_number += data.value;
					this.numberTotal += data.value;
				}else{
					if (goods.pickup_number > 0){
						this.numberTotal -= goods.pickup_number;
					}
					goods.pickup_number = data.value;
					this.numberTotal += data.value;
				}
				this.selGoodsList[goods.goods_id] = goods;
			},
			showPopup(){
				this.isShowPopup = true;
			},
			checkOut(){//前往结算
				let data = {};
				data.goods = [];
				let goodsList = this.selGoodsList;
				
				Object.keys(goodsList).forEach(key=>{
					if (goodsList[key].skuList.length > 0){
						goodsList[key].skuList.forEach(skuItem=>{
							if (skuItem.pickup_number > 0){
								let goods = {};
								goods.goods_id = goodsList[key].goods_id;
								goods.sku_id = skuItem.sku_id;
								goods.goods_number = skuItem.pickup_number;
								data.goods.push(goods);
							}
						})
					}else{
						if (goodsList[key].pickup_number > 0){
							let goods = {};
							goods.goods_id = goodsList[key].goods_id;
							goods.sku_id = 0;
							goods.goods_number = goodsList[key].pickup_number;
							data.goods.push(goods);
						}
					}
				})
				if (data.goods.length < 1){
					return this.$u.toast('请选择需要提货的商品.');
				}
				this.$u.post('channel/api.flow/addCartByPickUp',data).then(res => {
					this.app.goPage('/pagesB/channel/flow/checkOut?purchaseType=3');
				})
			}
		}
	}
</script>

<style lang="scss">
	@import "~@/pagesB/static/channel/css/myStock.scss";
	.goods-list{
		height: calc(100% - 210rpx);
	}
	.sku_stock{
		float: right;
	}
	.fixed_footer {
		position: fixed;
		z-index: 100;
		bottom: 0;
		left: 0;
		padding: 10rpx 30rpx;
		background-color: #fff;
		width: 100%;
		box-sizing: border-box;
		.number_box{
		    position: relative;
			.number {
			    position: absolute;
			    right: -25%;
			    top: -25%;
			    width: 32rpx;
			    height: 32rpx;
			    background-color: #dc655b;
			    text-align: center;
			    line-height: 32rpx;
			    color: #fff;
			    font-size: 24rpx;
			    border-radius: 50%;
			}
		}
		.ns-text{
			border-left: 1rpx solid #eee;
			height: 60rpx;
			line-height: 60rpx;
			padding-left: 24rpx;
			margin-left: 40rpx;
			margin-right: 100rpx;
		}
		.btn{
			margin-right: 0rpx;
		}
	}
	.popbox{
		padding-bottom: 90rpx;
		.title{
			-webkit-box-flex: 1;
			-webkit-flex: 1;
			flex: 1;
			color: #323232;
			font-weight: 700;
			font-size: 34rpx;
		}
		.content{
			min-height: 150px;
			max-height: 500px;
			overflow: auto;
			padding-bottom: 20rpx;
			background: #f5f5f5;
		}
	}
</style>
