<template>
	<view>
	<view class="image" :class="diyitem.params.showicon=='1'?diyitem.style.iconstyle:''" :data-text="diyitem.style.goodsicon"
	 @click="goPage(diyitem.params.goodstype,childitem.gid)">
	 <image  mode="widthFix" :src="childitem.imgurl?childitem.imgurl:childitem.thumb" :style="{width:'100%', height:'auto'}"></image>
		<view v-if="diyitem.params.showicon=='2'">
			<view class="goodsicon" :style="{position:'relative',width:iconwidth+'px',height:iconheight+'px'}">
				<view v-if="diyitem.params.iconposition=='left top'">
					<image bindload="goodsicon" class="left top" mode="widthFix" :src="diyitem.params.goodsiconsrc" :style="{width:diyitem.style.iconzoom+'%', left:diyitem.style.iconpaddingleft+'rpx',top:diyitem.style.iconpaddingtop+'rpx'}"></image>
				</view>
				<view v-if="diyitem.params.iconposition=='right top'">
					<image bindload="goodsicon" class="right top" mode="widthFix" :src="diyitem.params.goodsiconsrc" :style="{width:diyitem.style.iconzoom+'%', right:diyitem.style.iconpaddingleft+'rpx',top:diyitem.style.iconpaddingtop+'rpx'}"></image>
				</view>
				<view v-if="diyitem.params.iconposition=='left bottom'">
					<image bindload="goodsicon" class="left bottom" mode="widthFix" :src="diyitem.params.goodsiconsrc" :style="{width:diyitem.style.iconzoom+'%',left:diyitem.style.iconpaddingleft+'rpx',bottom:diyitem.style.iconpaddingtop+'rpx'}"></image>
				</view>
				<view v-if="diyitem.params.iconposition=='right bottom'">
					<image bindload="goodsicon" class="right bottom" mode="widthFix" :src="diyitem.params.goodsiconsrc" :style="{width:diyitem.style.iconzoom+'%',right:diyitem.style.iconpaddingleft+'rpx',bottom:diyitem.style.iconpaddingtop+'rpx'}"></image>
				</view>
			</view>
		</view>
		<view v-if="diyitem.params.saleout!=-1&&childitem.total==0&&childitem.cansee<=0||diyitem.params.saleout!=-1&&childitem.total==0&&childitem.cansee>0&&childitem.seecommission<=0">
			<image class="salez" :src="diyitem.params.saleout==0?'/static/shop/diyPage/images/saleout-2.png':diyitem.params.saleout"></image>
		</view>
		<view v-if="childitem.cansee>0&&childitem.seecommission>0">
			<view class="goods-Commission">{{childitem.seetitle}}￥{{childitem.seecommission}}</view>
		</view>
	</view>
	<view class="detail">
		<view v-if="diyitem.params.showtitle=='1'">
			<view class="name" :style="{color:diyitem.style.titlecolor}" @click="">
				<view v-if="childitem.bargain>0">
					<image class="bargain_label" src="/static/shop/diyPage/images/label.png"></image>
				</view>
				<view v-if="childitem.ctype==9">
					<text class="cycle-tip">周期购</text>
				</view>
				<text>{{childitem.title}}</text>
			</view>
		</view>
		<view class="other_info">
			 <view v-if="diyitem.params.showbuyrole=='1' && diyitem.style.liststyle==''" class="buyrole">
				<text :style="{color: diyitem.style.buyrolecolor,'background-color': diyitem.style.buyrolebgcolor}"  v-for="(roleName, roleIndex) in childitem.limit_user_role" :key="roleIndex">{{roleName}}</text>
			 </view>
			<view v-if="diyitem.params.showprice=='1'&&(diyitem.params.showproductprice=='1'||diyitem.params.showsales=='1')">
				<view class="productprice">
					<view v-if="childitem.productprice>0&&diyitem.params.showproductprice=='1'">
						<text :style="{color:diyitem.style.productpricecolor,'margin-right':'16rpx'}">{{diyitem.params.productpricetext}}：<text
							 :class="diyitem.params.productpriceline=='1'?'line':''">￥{{childitem.productprice}}</text>
						</text>
					</view>
					<view v-if="diyitem.params.showsales=='1'">
						<text :style="{color:diyitem.style.salescolor}">{{diyitem.params.salestext}}：{{childitem.sales}}</text>
					</view>
				</view>
			</view>
		</view>
		<view v-if="diyitem.params.showprice=='1'">
			<view class="price">
				<view v-if="diyitem.params.goodstype==1" class="text" :style="{color:diyitem.style.pricecolor}">{{childitem.credit}}<text class="fs22">积分</text></view>
				<view v-else class="text" :style="{color:diyitem.style.pricecolor}">￥{{childitem.price}}</view>
				<view v-if="childitem.ctype==9&&childitem.bargain==0">
					<view class="buy buybtnbtn buybtn-1" @click="">详情</view>
				</view>
				<view v-if="childitem.ctype==5&&childitem.bargain==0">
					<view class="buy buybtnbtn buybtn-1" @click="">详情</view>
				</view>
				<view v-if="childitem.bargain>0">
					<view class="buy buybtnbtn buybtn-1" @click="">砍价</view>
				</view>
				<view v-if="diyitem.params.goodstype==1" >
					<text class="buy buybtn-3"  :style="{background:diyitem.style.buybtncolor}" @click="toggleSpec(childitem)">兑换</text>
				</view>
				<block v-else >
					<view v-if="diyitem.style.buystyle=='buybtn-1'&&childitem.ctype!=9&&(childitem.bargain==0||childitem.bargain==null)&&childitem.ctype!=5">
						<text class="buy buybtnbtn" :class="diyitem.style.buystyle" :style="{background:'none',color:diyitem.style.buybtncolor,'border-color':diyitem.style.buybtncolor}" @click="toggleSpec(childitem)">购买</text>
					</view>
					<view v-if="diyitem.style.buystyle=='buybtn-2'&&childitem.ctype!=9&&(childitem.bargain==0||childitem.bargain==null)&&childitem.ctype!=5">
						<text class="buy buybtnbtn" :class="diyitem.style.buystyle" :style="{background:diyitem.style.buybtncolor,'border-color':diyitem.style.buybtncolor}" @click="toggleSpec(childitem)">购买</text>
					</view>
				</block>
				<view v-if="diyitem.style.buystyle=='buybtn-3'&&childitem.ctype!=9&&childitem.ctype!=5&&childitem.bargain==0">
					<text class="buy icon icon-cartfill buybtnbtn" :class="diyitem.style.buystyle" :style="{background:diyitem.style.buybtncolor,'border-color':diyitem.style.buybtncolor}" @click="toggleSpec(childitem)"></text>
				</view>
				<view v-if="diyitem.style.buystyle=='buybtn-4'&&childitem.ctype!=9&&childitem.ctype!=5&&childitem.bargain==0">
					<text class="buy icon icon-cart buybtnbtn " :class="diyitem.style.buystyle" :style="{background:'none',color:diyitem.style.buybtncolor,'border-color':diyitem.style.buybtncolor}" @click="toggleSpec(childitem)"></text>
				</view>
				<view v-if="diyitem.style.buystyle=='buybtn-5'&&childitem.ctype!=9&&childitem.ctype!=5&&childitem.bargain==0">
					<text class="buy icon icon-add buybtnbtn " :class="diyitem.style.buystyle" :style="{background:'none',color:diyitem.style.buybtncolor,'border-color':diyitem.style.buybtncolor}" @click="toggleSpec(childitem)"></text>
				</view>
				<view v-if="diyitem.style.buystyle=='buybtn-6'&&childitem.ctype!=9&&childitem.ctype!=5&&childitem.bargain==0">
					<text class="buy icon icon-add buybtnbtn" :class="diyitem.style.buystyle" :style="{background:diyitem.style.buybtncolor,'border-color':diyitem.style.buybtncolor}"  @click="toggleSpec(childitem)"></text>
				</view>
			</view>
		</view>
	</view>
	</view>
</template>

<script>
	export default {
		name: "tpl_goods_item_info",
		props: {
			diyitem: {
				type: Object,
				default: function() {
					return {};
				}
			},
			childitem: {
				type: Object,
				default: function() {
					return {};
				}
			},
		},
		methods: {
			goPage(goodstype,gid){
				if (gid < 1){
					return false;
				}
				this.app.goPage(goodstype==1?'/pagesA/integral/info?ig_id='+gid:'/pages/shop/goods/info?goods_id='+gid);
			},
			//规格弹窗开关
			toggleSpec(selGoods) {
				this.$emit("toggleSpec",selGoods);
			},
		}
	}
</script>

