<template>
	<view>
		<view v-if="diyitem.style.showtype=='0'"  :style="{background:diyitem.style.background}">
			<tpl_menu_item :diyitem="diyitem" :menuList="diyitem.data"></tpl_menu_item>
		</view>
		<view v-else>
			<swiper :autoplay="autoplay" class="menu" :duration="duration" :indicatorDots="diyitem.style.showdot==1?true:false"
			 :interval="interval" :style="{background:diyitem.style.background,height:(diyitem.data_line*120+diyitem.data_line*60)+'rpx'}">
				<swiper-item v-for="(childitem, childid) in diyitem.data_temp" :key="childid" >
					<view class="fui-icon-group noborder" >
						<tpl_menu_item :diyitem="diyitem" :menuList="childitem"></tpl_menu_item>
					</view>
				</swiper-item>
			</swiper>
		</view>
	</view>
</template>


<script>
	import tpl_menu_item from '@/pages/shop/index/diyPage/template/tpl_menu_item';
	export default {
		components: {
			tpl_menu_item
		},
		name: "tpl_menu",
		props: {
			diyitem: {
				type: Object,
				default: function() {
					return {};
				}
			},
		},
		data() {
			return {
				autoplay: false,
				interval: 5e3,
				duration: 500,
			};
		},
		watch: {},
		computed: {},
		methods: {

		}
	}
</script>

<style lang='scss'>
</style>
