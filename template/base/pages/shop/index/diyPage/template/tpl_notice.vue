<template>
	<view class="index-hot">
	    <view class="weui-flex" :style="{background:diyitem.style.background}">
	        <view class="flex-head-item item-hotdot">
	            <image class="hotdot"   mode="aspectFit" :src="diyitem.params.iconurl"></image>
	        </view>
	        <view class="weui-flex__item">
	            <swiper autoplay="true" circular="true" class="index-adcs-sqiper index-notification-swiper" :interval="diyitem.params.speed*1000" vertical="true">
	                   <swiper-item v-for="(item, index) in diyitem.data" :key="index">
	                        <view class="notification-navigoter" hoverClass="none" :style="{color:diyitem.style.color}" @click="app.goPage(item.linkurl)">
	                                {{item.title}}
	                        </view>
	                  </swiper-item>
	            </swiper>
	        </view>
	    </view>
	</view>

</template>

<script>
	export default {
		name: "tpl_notice",
		props: {
			diyitem: {
				type: Object,
				default:function(){
					return {};
				}
			},
		},
		data() {
			return {
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
