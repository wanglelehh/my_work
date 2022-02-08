<template>
	<view class="fui-picture" :style="{background:diyitem.style.background}">
	    <view  v-for="(childitem, childid) in diyitem.data" :key="childid">
	        <view @click="app.goPage(childitem.linkurl)" :style="{padding:diyitem.style.paddingtop==0?0:diyitem.style.paddingtop+'px '+(diyitem.style.paddingleft==0?0:diyitem.style.paddingleft+'px')}">
	            <image mode="widthFix" :src="childitem.imgurl" :style="{height:diyitem.style.bannerheight?diyitem.style.bannerheight+'px':'auto'}"></image>
	        </view>
	    </view>
	</view>
</template>


<script>
	export default {
		name: "tpl_picture",
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

