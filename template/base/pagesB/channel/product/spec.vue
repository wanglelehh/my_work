<template>
	<!-- 规格-模态层弹窗 -->
	<view class="popup spec" :class="specClass" @touchmove.stop.prevent="stopPrevent" @click="toggleSpec">
		<!-- 遮罩层 -->
		<view class="mask"></view>
		<view class="layer attr-content" @click.stop="stopPrevent">
			<u-icon name="close" size="28" class="close" @click="toggleSpec"></u-icon>
			<view class="a-t">
				<image :src="specGoodsImg" mode="aspectFill"></image>
				<view class="right">
					<text class="price">¥{{selectGoodsPrice}}</text>
					<text class="stock">供货商库存：{{selectGoodsNumber}}件</text>
					<view v-if="specSelected.length" class="selected">
						已选：
						<text class="selected-text" v-for="(sItem, sIndex) in specSelected" :key="sIndex">
							{{sItem.name}}
						</text>
					</view>
				</view>
			</view>
			<view v-for="(item,index) in specList" :key="index" class="attr-list">
				<text>{{item.name}}</text>
				<view class="item-list">
					<text v-for="(childItem, childIndex) in specChildList" v-if="childItem.pid === item.id" :key="childIndex" class="tit"
					 :class="[specGetStyle(childItem,index)]" @click="selectSpec(index,childIndex, childItem.pid)">
						{{childItem.name}}
					</text>
				</view>
			</view>
			<view class="attr-list unit-list" v-if="selectGoods.unit">
				<text>单位</text>
				<view class="item-list">
					<text class="tit"  :class="{selected: selectGoods.unit == selUnit || selUnit == 0}" @click="selectUnit(selectGoods.unit)">
						{{selectGoods.unitLits[selectGoods.unit].name}}
					</text>
					<text v-for="(item, index) in selectGoods.convert_unit"  :key="index" class="tit" 
					:class="{selected: index == selUnit}"
					@click="selectUnit(index)">
						{{selectGoods.unitLits[index].name}}/{{item}}{{selectGoods.unitLits[selectGoods.unit].name}}
					</text>
				</view>
			</view>
			<view class="mt20">
				购买数量：<u-number-box v-model="buyNumber" :min="1" :step="1" :disabled="false"></u-number-box>
			</view>
			<button class="btn primarybtn" @click="buyBtn" :loading="isLoading">{{byType=='byCart'?'加入购物车':'立即购买'}}
			<text v-if="selectGoodsNumber < 1" style="margin-left: 10rpx; color: #ccc;"> - 库存不足</text>
			</button>
		</view>
	</view>
</template>

<script>
	export default {
		name: "prductSpec",
		props: {
			purchaseType: {
				type: String,
				default: '0',
			},
			byType: {
				type: String,
				default: 'byCart',
			},
			specClass: {
				type: String,
				default: 'none',
			},
			selectGoodsPrice: {
				type: String,
				default: '0.00',
			},
			selectGoodsNumber: {
				type: Number,
				default: '0',
			},
			selectGoods:{
				type: Object,
				default:function(){
					return {};
				}
			},
			specGoodsImg:{
				type: String,
				default: '',
			},
			specSelected:{
				type: Array,
				default:function(){
					return [];
				}
			},
			specList:{
				type: Array,
				default:function(){
					return [];
				}
			},
			specChildList:{
				type: Array,
				default:function(){
					return [];
				}
			}
		},
		data() {
			return {
				buyNumber:1,
				selUnit:0,
				isLoading:false
			}
		},
		methods: {
			specGetStyle(spec,index,type)
			{
				let style = [];
				let specSelArr = [];
				let skuTotal = {};
				let that = this;
				skuTotal = this.selectGoods.skuTotal;
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
				this.$emit("toggleSpec");
			},
			//选择规格
			selectSpec(zindex,index, pid){
				if (zindex > this.specSelected.length){
					var title = this.specList[this.specSelected.length].name;
					uni.showToast({
						title: '请先选择：'+title,
						duration: 2000,
						icon: 'none'
					});
					return false;
				}
				let list = this.specChildList;
				list.forEach(item=>{
					if(item.pid === pid){
						this.$set(item, 'selected', false);
					}
				})
				this.$set(list[index], 'selected', true);
				this.$emit("selectSpec",list);
			},
			selectUnit(unit){
				this.selUnit = parseInt(unit);
			},
			buyBtn(){
				if (this.isLoading == true){
					return false;
				}
				this.isLoading = true;
				let data = {};
				data.purchaseType = this.purchaseType;
				data.goods_id = this.selectGoods.goods_id;
				data.sku_id = 0;
				if (this.selectGoods.is_spec == 1){
					let selSkuArr = [];
					this.specSelected.forEach(item=>{ 
						selSkuArr.push(item.id); 
					})
					selSkuArr = selSkuArr.join(":");
					this.selectGoods.sub_goods.forEach(item=>{
						if (selSkuArr == item.sku_val){
							data.sku_id = item.sku_id;
						}
					})
				}
				data.buyNumber = this.buyNumber;
				data.buyUnit = this.selUnit == 0 ? this.selectGoods.unit : this.selUnit;
				let that = this;
				this.$u.post('channel/api.flow/addCart', data).then(res => {
					setTimeout(function (){
						that.isLoading = false;
					},1000);
					if (this.byType == 'byBuy'){
						this.app.goPage('/pagesB/channel/flow/checkOut?purchaseType='+this.purchaseType+'&rec_id='+res.data.rec_id);
					}else{
						uni.showToast({
						    title: res.msg,
						    duration: 2000,
							icon:'none'
						});
					}
				}).catch(res=>{
					this.isLoading = false;
				})
			},
			stopPrevent(){}
		}
	}
</script>

<style lang='scss'>
	/* 规格选择弹窗 */
	.attr-content{
		padding: 10rpx 30rpx;
		.close{
			position: absolute;
			top:20rpx;
			right:20rpx;
		}
		.a-t{
			display: flex;
			image{
				width: 170rpx;
				height: 170rpx;
				flex-shrink: 0;
				margin-top: -40rpx;
				border-radius: 8rpx;;
			}
			.right{
				display: flex;
				flex-direction: column;
				padding-left: 24rpx;
				font-size: $font-sm + 2rpx;
				color: $font-color-dark;
				line-height: 42rpx;
				.price{
					font-size: $font-lg;
					color: $uni-color-primary;
					margin-bottom: 10rpx;
				}
				.selected-text{
					margin-right: 10rpx;
				}
			}
		}
		.attr-list{
			display: flex;
			flex-direction: column;
			font-size: $font-base + 2rpx;
			color: $font-color-base;
			padding-top: 30rpx;
			padding-left: 10rpx;
		}
		.item-list{
			padding: 20rpx 0 0;
			display: flex;
			flex-wrap: wrap;
			text{
				display: flex;
				align-items: center;
				justify-content: center;
				background: #eee;
				margin-right: 20rpx;
				margin-bottom: 20rpx;
				border-radius: 100rpx;
				min-width: 60rpx;
				height: 60rpx;
				padding: 0 20rpx;
				font-size: $font-base;
				color: $font-color-dark;
			}
			.color_cc{
				color: #cccccc;
			}
			.display_none{
				display: none;
			}
			.selected{
				background: #eaf0fb;
				color: $uni-color-primary;
			}
		}
	}
</style>
