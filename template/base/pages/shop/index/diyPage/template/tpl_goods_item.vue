<template>
	<view class="fui-goods-group" :class="diyitem.style.liststyle" :style="{background:diyitem.style.background}">
		<view v-if="diyitem.style.liststyle=='block'" class="flex">
			<view class="flex_bd">
				<view class="fui-goods-item" v-for="(childitem, childid) in goodsList" :key="childid"  v-if="(childid+1) % 2 != 0">
					<tpl_goods_item_info @toggleSpec="toggleSpec" :diyitem="diyitem" :childitem="childitem"></tpl_goods_item_info>
				</view>
			</view>
			<view class="flex_bd">
				<view class="fui-goods-item" v-for="(childitem, childid) in goodsList" :key="childid"  v-if="(childid+1) % 2 == 0">
					<tpl_goods_item_info @toggleSpec="toggleSpec" :diyitem="diyitem" :childitem="childitem"></tpl_goods_item_info>
				</view>
			</view>
		</view>
		<view v-else>
			<view class="fui-goods-item" v-for="(childitem, childid) in goodsList" :key="childid">
				<tpl_goods_item_info @toggleSpec="toggleSpec" :diyitem="diyitem" :childitem="childitem"></tpl_goods_item_info>
			</view>
		</view>
		
		<goodsSpec @selectSpec="selectSpec" @toggleSpec="toggleSpec" :specClass="specClass" :specSelected="specSelected"
		 :selectGoods="selectGoods" :selectGoodsPrice="selectGoodsPrice" :selectGoodSmarketPrice="selectGoodSmarketPrice"
		 :selectGoodsNumber="selectGoodsNumber" :selectGoodsImg="selectGoodsImg" :specList="specList" :specChildList="specChildList"
		 :selectSkuId="selectSkuId" :exType="exType" :byType="byType"></goodsSpec>
	</view>
</template>

<script>
	import goodsSpec from '@/pages/shop/goods/spec';
	import tpl_goods_item_info from '@/pages/shop/index/diyPage/template/tpl_goods_item_info';
	export default {
		components: {
			goodsSpec,
			tpl_goods_item_info
		},
		name: "tpl_goods_item",
		props: {
			diyitem: {
				type: Object,
				default: function() {
					return {};
				}
			},
			goodsList: {
				type: Array,
				default: function() {
					return [];
				}
			},
			isswiper: {
				type: String,
				default: 'false',
			},
		},
		data() {
			return {
				exType:'',
				byType:'',
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
			};
		},
		watch: {},
		computed: {},
		methods: {
			
			//规格弹窗开关
			toggleSpec(selGoods) {
				if (this.isswiper == 'true'){
					if (selGoods.is_integral == 1){
						this.app.goPage('/pagesA/integral/info?ig_id='+selGoods.gid);
					}else{
						this.app.goPage('/pages/shop/goods/info?goods_id='+selGoods.gid);
					}
					return false;
				}
				if (selGoods.gid < 1){
					return false;
				}
				if (this.specClass === 'show') {
					this.getCartNum = 1;
					this.loadCartNum();
					this.specClass = 'hide';
					setTimeout(() => {
						this.specClass = 'none';
					}, 250);
				} else if (this.specClass === 'none') {
					if (this.selectGoodsId == selGoods.gid){
						this.specClass = 'show';
						this.showSelectSpec();
						return false;
					}
					this.selectGoodsId = selGoods.gid;
					this.getCartNum = 0;
					this.specSelected = [];
					this.selectSkuId = 0;
					if (selGoods.is_integral == 1){
						this.exType = 'integral';
						this.byType = '';
						this.$u.post('integral/api.goods/info', {'ig_id': selGoods.gid}).then(res => {
							this.selectGoodsImg = this.config.baseUrl + res.data.goods_thumb;
							this.selectGoods = res.data.goods;
							this.specList = this.selectGoods.specList;
							this.specChildList = this.selectGoods.specChildList;
							this.specClass = 'show';
							this.specSelected = [];
							this.showSelectSpec();
						})
					}else{
						this.exType = '';
						this.byType = 'onbuycart';
						this.$u.post('shop/api.goods/getGoodsInfo', {'goods_id': selGoods.gid}).then(res => {
							this.selectGoodsImg = this.config.baseUrl + res.data.goods_thumb;
							this.selectGoods = res.data;
							this.selectGoodsPrice = res.data.sale_price;
							this.selectGoodsNumber = res.data.goods_number;
							this.selectGoodSmarketPrice = res.data.market_price;
							this.specClass = 'show';
							this.specSelected = [];
							this.specList = [];
							this.specChildList = [];
							if (res.data.is_spec == 1){
								this.specList = res.data.specList;
								this.specChildList = res.data.specChildList;
								this.showSelectSpec();
							}
						})
					}
					
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
				Object.keys(this.selectGoods.sub_goods).forEach(sku_id => {
					let item = this.selectGoods.sub_goods[sku_id];
					if (selSku == item.sku_val) {
						if (item.sale_price > 0){
							this.selectSkuId = parseInt(sku_id);
							if (this.exType == 'integral'){
								this.selectGoodsPrice = item.integral;
								
							}else{
								this.selectGoodsPrice = item.sale_price;
								this.selectGoodSmarketPrice = item.market_price;
							}
							this.selectGoodsNumber = item.goods_number;
						}
						return false;
					}
				})
				if (this.selectSkuId < 1){
					this.selectGoodsImg = this.config.baseUrl + this.selectGoods.goods_thumb;
					return false;
				}
				this.selectGoods.imgSkuList.forEach(item => {
					if (selSku == item.sku_val) {
						this.selectGoodsImg = this.config.baseUrl + item.goods_thumb;
						return false;
					}
				})
				if (this.selectGoodsImg == '') {
					this.selectGoods.imgSkuList.forEach(item => {
						let _sku = item.sku_val.split(':');
						if (selSkuArr[0] == _sku[0]) {
							this.selectGoodsImg = this.config.baseUrl + item.goods_thumb;
							return false;
						}
					})
				}
			},
			//加载购物车中商品数
			async loadCartNum() {
				
			},
			stopPrevent() {}
		}
	}
</script>

<style lang='scss'>
</style>
