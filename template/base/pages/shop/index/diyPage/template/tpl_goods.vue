<template>
	<view>
		<view v-if="diyitem.params.goodsscroll==1" class="fui-goods-swiper-group" style="position: relative;">
			<view @click="cutGoods" class="cut retreat"  :data-id="diyitemid" :data-num="diyitem.data_temp.length" data-type="retreat">
				<u-icon name="arrow-left" color="#FFFFFF" size="32"></u-icon>
			</view>
			<swiper circular="true" class="swiper fui-goods-group " :class="swiperClass" :current="current" :duration="duration"
			 :interval="interval" :style="{background:diyitem.style.background}" @change="changeSwiper">
				<swiper-item nextMargin="10px" v-for="(childitem, childid) in diyitem.data_temp" :key="childid">
					<tpl_goods_item :diyitem="diyitem" :goodsList="childitem" isswiper="true"></tpl_goods_item>
				</swiper-item>
			</swiper>
			<view @click="cutGoods" class="cut advance"  :data-id="diyitemid" :data-num="diyitem.data_temp.length" data-type="advance">
				<u-icon name="arrow-right" color="#FFFFFF" size="32"></u-icon>
			</view>
		</view>
		<view v-else >
			<tpl_goods_item :diyitem="diyitem" :goodsList="diyitem.data" isswiper="false"></tpl_goods_item>
		</view>
	</view>
</template>


<script>
	import tpl_goods_item from '@/pages/shop/index/diyPage/template/tpl_goods_item';
	export default {
		components: {
			tpl_goods_item
		},
		name: "tpl_goods",
		props: {
			diyitem: {
				type: Object,
				default: function() {
					return {};
				}
			},
			diyitemid: {
				type: String,
				default: ''
			}
		},
		data() {
			return {
				autoplay: !0,
				interval: 5e3,
				duration: 500,
				circular: !0,
				current:0
			};
		},
		watch: {},
		computed: {
			swiperClass() {
				let _class = this.diyitem.style.liststyle;
				if (this.diyitem.params.showprice == '1' && (this.diyitem.params.showproductprice == '1' || this.diyitem.params.showsales ==
						'1')) {
					_class += ' showproduct';
				}
				if (this.diyitem.params.showtitle == 1) {
					_class += ' showtitle';
				}
				if (this.diyitem.params.showprice == 1) {
					_class += ' showprice';
				}
				return _class;
			}
		},
		methods: {
			changeSwiper(e){
				this.current = e.target.current;
			},
			cutGoods(t) {
				let e = t.currentTarget.dataset.type,
					i = t.currentTarget.dataset.num;
					if (e == 'retreat'){
						this.current =  this.current < i - 1  ? this.current + 1 : 0;
					}else{
						this.current = this.current > 0 ? this.current - 1 : i - 1;
					}
				}
			}
		}
</script>

<style lang='scss'>
</style>
