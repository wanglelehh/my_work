<template>
	<!-- 规格-模态层弹窗 -->
	<view class="popup spec" :class="specClass" @touchmove.stop.prevent="stopPrevent" >
		<!-- 遮罩层 -->
		<view class="mask" @click="toggleSpec"></view>
		<view class="layer attr-content" @click.stop="stopPrevent">
			<u-icon name="close" size="28" class="close" @click="toggleSpec"></u-icon>
			<view class="a-t">
				<image :src="selectGoodsImg" mode="aspectFill" @click="app.showImg([selectGoodsImg],0)"></image>
				<view  v-if="selectGoods.is_spec == 0" class="right">
					<view>
						<text class=" ff base-color" :class="exType == 'integral'?'integral':'price'">{{selectGoodsPrice}}</text>
						<text class=" ff base-color" v-if="exType == 'integral'" >{{app.langReplace('积分')}}</text>
						<text v-if="prom_id > 0" class="ml10 bg-base color-ff plr10 fs24">{{app.langReplace('活动价')}}</text>
						<text class="m-price ml20" v-if="selectGoods.show_market_price == 1">¥{{selectGoodSmarketPrice}}</text>
					</view>
					<view v-if="selectGoods.use_integral > 0" class="fs26">
						<text class="base-color font-w700">+{{selectGoods.use_integral}}{{app.langReplace('积分')}}</text>
						<text class="color-99">，{{app.langReplace('此商品需要消耗额外积分才能购买')}}</text>
					</view>
					<text class="stock" v-if="selectGoods.show_stock_num == 1">{{app.langReplace('库存')}}：{{selectGoodsNumber}}件</text>
					<view v-if="specSelected.length" class="selected">
						{{app.langReplace('已选')}}：
						<text class="selected-text" v-for="(sItem, sIndex) in specSelected" :key="sIndex">
							{{sItem.name}}
						</text>
					</view>
				</view>
				<view v-else class="right">
					<view v-if="selectSkuId < 1">
						<view v-if="exType == 'integral'" class="integral ff base-color">{{selectGoods.show_integral}}<text class="fs24">{{app.langReplace('积分')}}</text></view>
						<text v-else class="price ff base-color">{{byType=='fgbuy'? selectGoods.show_price :selectGoods.min_price}}</text>
						
						<text class="m-price ml20" v-if="selectGoods.show_market_price == 1">¥{{selectGoods.market_price}}</text>
					</view>
					<view v-else>
						<view>
							<text class="ff base-color" :class="exType == 'integral'?'integral':'price'">{{selectGoodsPrice}}</text>
							<text class="ff base-color" v-if="exType == 'integral'" >{{app.langReplace('积分')}}</text>
							<text v-if="prom_id > 0" class="ml10 bg-base color-ff plr10 fs24">{{app.langReplace('活动价')}}</text>
							<text class="m-price ml20" v-if="selectGoods.show_market_price == 1">¥{{selectGoodSmarketPrice}}</text>
						</view>
						<text class="stock" v-if="selectGoods.show_stock_num == 1">{{app.langReplace('库存')}}：{{selectGoodsNumber}}件</text>
						<view v-if="specSelected.length" class="selected">
							{{app.langReplace('已选')}}：
							<text class="selected-text" v-for="(sItem, sIndex) in specSelected" :key="sIndex">
								{{sItem.name}}
							</text>
						</view>
					</view>
				</view>
			</view>
			<view v-for="(item,index) in specList" :key="index" class="attr-list base-select">
				<text>{{item.name}}</text>
				<view v-if="item.img_type == 1" class="item-list">
					<view class="tit" :class="{selected:item.selChildName}" @click="extSelectSpec(index)">
						{{item.selChildName?item.selChildName:app.langReplace('请选择')}} <u-icon name="arrow-right" size="20"></u-icon>
					</view>
				</view>
				<view  class="item-list" :class="{display_none:item.img_type==1}">
					<view v-for="(childItem, childIndex) in specChildList" v-if="childItem.pid === item.id" :key="childIndex" class="tit"
					 :class="[specGetStyle(childItem,index)]"  @click="selectSpec(index,childIndex, childItem.pid)">
						{{childItem.name}}
					</view>
				</view>
			</view>
			
			<view class="numbox mt30 mb30 flex">
				<view v-if="exType == 'integral'" class="flex_bd">{{app.langReplace('兑换数量')}}：</view>
				<view v-else class="flex_bd">{{app.langReplace('购买数量')}}：</view>
				<u-number-box v-model="buyNumber" :min="1" :step="1" :disabled="false"></u-number-box>
			</view>
			<view class="btn-box">
				<view v-if="selectGoods.is_spec == 1 && selectSkuId < 1">
					<button  class="btn bg-gray" >{{app.langReplace('选择商品')}}</button>
				</view>
				<view v-else-if="selectGoodsNumber > 0">
					<view v-if="byType == 'onbuycart'" class="smll">
						<button  class="btn primarybtn" style="width: 45%;" @click="buyBtn('oncart')" :loading="isLoading">{{app.langReplace('加入购物车')}}</button>
						<button  class="btn primarybtnB" style="width: 45%;" @click="buyBtn('onbuy')" :loading="isLoading">{{app.langReplace('立即购买')}}</button>
					</view>
					<view v-else>
						<button  class="btn primarybtn" @click="buyBtn()" :loading="isLoading">{{runBtnText()}}</button>
					</view>
				</view>
				<view v-else>
					<button class="btn bg-gray" >{{app.langReplace('库存不足')}}</button>
				</view>
			</view>
		</view>
		<view v-if="isExtSelectSpec" class="extSelectSpecBox">
			<view class="mask"></view>
			<view class="selBox p10">
				<view class="selImgs bg-ff">
					<view v-for="(childItem, childIndex) in extSpecChildList" :key="childIndex" class="img" :class="[specGetStyle(childItem,topSpecIndex)]" @click="extSelectSpecChild(childIndex)">
						<image :src="childItem.goods_img" style="width: 100%;" mode="widthFix"></image>
						<image v-if="childItem.extSelected" src="/static/public/images/sku_sel.png" class="sel_iocn" mode="widthFix"></image>
					</view>
				</view>
				<view class="color-ff text-center">
					<view class=" fl w50 lh80 bg-cc " @click="extCancelSelectSpec()">{{app.langReplace('取消')}}</view>
					<view class=" fl w50 lh80 bg-00" @click="extConfirmSelectSpec()">{{app.langReplace('确认')}}</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		name: "goodsSpec",
		props: {
			byType: {
				type: String,
				default: 'oncart',
			},
			exType: {
				type: String,
				default: '',
			},
			specClass: {
				type: String,
				default: 'none',
			},
			selectGoodsPrice: {
				type: String,
				default: '0.00',
			},
			selectGoodSmarketPrice: {
				type: String,
				default: '0.00',
			},
			selectGoodsNumber: {
				type: Number,
				default: 0,
			},
			selectGoods: {
				type: Object,
				default: function() {
					return {};
				}
			},
			selectGoodsImg: {
				type: String,
				default: '',
			},
			specSelected: {
				type: Array,
				default: function() {
					return [];
				}
			},
			specList: {
				type: Array,
				default: function() {
					return [];
				}
			},
			specChildList: {
				type: Array,
				default: function() {
					return [];
				}
			},
			selectSkuId: {
				type: Number,
				default: 0,
			},
			fg_id: {
				type: Number,
				default: 0,
			},
			join_id: {
				type: Number,
				default: 0,
			},
			prom_type: {
				type: Number,
				default: 0,
			},
			prom_id: {
				type: Number,
				default: 0,
			},
		},
		data() {
			return {
				buyNumber: 1,
				selUnit: 0,
				isLoading: false,
				isExtSelectSpec:false,
				extSpecChildList:[],
				topSpecIndex:0,
			}
		},
		methods: {
			runBtnText(){
				if (this.byType == 'oncart'){
					return this.app.langReplace('加入购物车');
				}else if (this.byType == 'fgbuy'){
					if (this.join_id > 0){
						return  this.app.langReplace('参与拼单');
					}else{
						return  this.app.langReplace('发起拼单');
					}
				}else{
					if (this.exType == 'integral'){
						return this.app.langReplace('立即兑换');
					}else{
						return this.app.langReplace('立即购买');
					}
				}
			},
			specGetStyle(spec,index)
			{
				if (this.specSelected.length < index){
					return [];
				}
				let style = [];
				let specSelArr = [];
				let skuTotal = {};
				let that = this;
				if (this.byType == 'fgbuy'){
					 skuTotal = this.selectGoods.fgSkuTotal;
				}else{
					 skuTotal = this.selectGoods.skuTotal;
				}
				if (spec.selected == true){
					style.push('selected');
				}
				this.specSelected.forEach(function(item,key){
					if (key < index){
						specSelArr.push(item.id);
					}
				})
				let specSelArrLength = specSelArr.push(spec.id);
				let selSpecKey = specSelArr.join('_');
					
				if (typeof(skuTotal[selSpecKey]) != 'undefined'){
					if (skuTotal[selSpecKey].goods_number < 1){
						style.push('color_cc');
					}
				}else{
					let isok = false;
					this.selectGoods.skuValList.forEach(function(sku_val,key){
						sku_val = sku_val+':';
						var num = 0;
						specSelArr.forEach(function(i,k){
							if (sku_val.indexOf(i+':') >= 0){
								num++
							}
						})
						if (specSelArrLength == num){
							isok = true;
							return false;
						}
					})
					if (isok == false){
						style.push('display_none');
						this.specChildList[spec.index].selected = false;
						let specSelected = this.specSelected;
						specSelected.forEach(function(item,key){
							if (item.id == spec.id){
								that.specList[key].selChildName = ''
								specSelected.splice(key, 100);
							}
						})
					}
				}
				return style;
			},
			//规格弹窗开关
			toggleSpec() {
				this.selUnit = 0;
				this.$emit("toggleSpec",this.byType);
			},
			//选择规格
			selectSpec(zindex,index, pid) {
				if (zindex > this.specSelected.length){
					var title = this.specList[this.specSelected.length].name;
					uni.showToast({
						title: this.app.langReplace('请选择')+'：'+title,
						duration: 2000,
						icon: 'none'
					});
					return false;
				}
				let list = this.specChildList;
				list.forEach(item => {
					if (item.pid === pid) {
						this.$set(item, 'selected', false);
					}
				})
				list[index].selected = true;
				this.$emit("selectSpec", list);
			},
			//额外选择规格
			extSelectSpec(index){
				if (index > this.specSelected.length){
					var title = this.specList[this.specSelected.length].name;
					uni.showToast({
						title: this.app.langReplace('请选择')+'：'+title,
						duration: 2000,
						icon: 'none'
					});
					return false;
				}
				this.isExtSelectSpec = true;
				let topSpec = this.specList[index];
				if (typeof(topSpec.setImg) == 'undefined'){
					this.specChildList.forEach(item => {
						if (topSpec.id === item.pid) {
							this.selectGoods.imgSkuList.forEach(img => {
								if (img.sku_val == item.id) {
									item.goods_img = this.config.baseUrl + img.goods_img;
								}
							})
						}
					})
					topSpec.setImg = true;
				}
				let extSpecChildList = [];
				this.specChildList.forEach(item => {
					if (topSpec.id === item.pid) {
						extSpecChildList.push(item)
					}
				})
				this.topSpecIndex = index;
				this.extSpecChildList = extSpecChildList;
			},
			extSelectSpecChild(index){
				this.extSpecChildList.forEach(function(item,i) {
					if (i == index){
						item.extSelected = true;
					}else{
						item.extSelected = false;
					}
				})
				this.$forceUpdate();//刷新数据
			},
			extCancelSelectSpec(){
				this.isExtSelectSpec = false;
				let list = this.specChildList;
				this.extSpecChildList.forEach(function(item,i) {
					item.extSelected = list[item.index].selected;
				})
				this.$forceUpdate();//刷新数据
			},
			extConfirmSelectSpec(){
				this.isExtSelectSpec = false;
				let list = this.specChildList;
				let selChildName = '';
				this.extSpecChildList.forEach(item=> {
					if (item.extSelected == true){
						selChildName = item.name;
					}
					list[item.index].selected = item.extSelected;
				});
				this.specList[this.topSpecIndex].selChildName = selChildName;
				this.$emit("selectSpec", list);
			},
			selectUnit(unit) {
				this.selUnit = parseInt(unit);
			},
			buyBtn(byType = '') {
				if (this.isLoading == true) {
					return false;
				}
				if (byType == ''){
					 byType = this.byType;
				}
				this.isLoading = true;
				let url = 'shop/api.flow/addCart';
				let data = {};
				data.goods_id = this.selectGoods.goods_id;
				data.sku_id = this.selectSkuId;
				if (byType == 'fgbuy'){
					this.isLoading = false;
					let urlData = {};
					urlData.fg_id = this.fg_id;
					urlData.number = this.buyNumber;
					urlData.sku_id = data.sku_id;
					urlData.join_id = this.join_id;
					return this.app.goPage(['/pagesA/fightgroup/checkOut',urlData]);
				}
				let goUrl = '/pages/shop/flow/checkOut?recids=';
				data.number = this.buyNumber;
				data.type = byType;
				data.prom_type = this.prom_type;
				data.prom_id = this.prom_id;
				if(this.exType == 'integral'){
					url = 'integral/api.flow/addCart';
					data.prom_type = 2;
					data.prom_id = this.selectGoods.ig_id;
					goUrl = '/pagesA/integral/checkOut?recids=';
				}
				this.$u.post(url, data).then(res => {
					this.isLoading = false;
					if (byType == 'onbuy') {
						this.app.goPage( goUrl + res.data.rec_id);
					} else {
						uni.showToast({
							title: res.msg,
							duration: 2000,
							icon: 'none'
						});
					}
				}).catch(res => {
					this.isLoading = false;
				})
			},
			stopPrevent() {}
		}
	}
</script>

<style lang='scss'>
	/* 规格选择弹窗 */
	.attr-content {
		.a-t {
			display: flex;

			image {
				width: 160rpx;
				height: 160rpx;
				flex-shrink: 0;
				border-radius: 20rpx;

			}

			.right {
				display: flex;
				flex-direction: column;
				padding-left: 20rpx;
				font-size: 26rpx;
				color: $font-color-light;
				line-height: 55rpx;

				.price {
					font-size: 40rpx;
					margin-bottom: 10rpx;
					&:before {
						content: '￥';
						font-size: 24rpx;
					}
				}
				.integral {
					font-size: 40rpx;
					margin-bottom: 10rpx;
					
				}
				.m-price {
					color: $font-color-light;
					text-decoration: line-through;
				}

				.selected-text {
					margin-right: 10rpx;
				}
			}
		}

		.attr-list {
			display: flex;
			flex-direction: column;
			font-size: 32rpx;
			color: $font-color-dark;
			padding-top: 40rpx;
			padding-left: 20rpx;
		}

		.item-list {
			padding: 20rpx 0 0;
			display: flex;
			flex-wrap: wrap;

			.tit {
				display: flex;
				align-items: center;
				justify-content: center;
				background: #f4f4f4;
				margin-right: 20rpx;
				margin-bottom: 20rpx;
				border-radius: 16rpx;
				min-width: 110rpx;
				height: 66rpx;
				padding: 0 20rpx;
				font-size: 28rpx;
				color: $font-color-dark;
			}
			.color_cc{
				color: #cccccc;
			}
			
			.selected {
				color: #FFFFFF;
			}
		}
	}
	
	.extSelectSpecBox{
		position: fixed;
		left: 0;
		top: 0;
		right: 0;
		bottom: 0;
		z-index: 999;
		.selBox{
			position:absolute;
			z-index: 99;
			width: 100%;
			top:50%;
			margin-top:-300rpx;
			.selImgs{
				max-height: 600rpx;
				padding-bottom: 20rpx;
				overflow: auto;
				.img{
					float: left;
					width: 50%;
					border: 1rpx solid #000000;
					position: relative;
					.sel_iocn{
						width: 50rpx;
						position: absolute;
						bottom: 0rpx;
						right: 0rpx;
					}
				}
			}
		}
	}
	.display_none{
		display: none !important;
	}
</style>
