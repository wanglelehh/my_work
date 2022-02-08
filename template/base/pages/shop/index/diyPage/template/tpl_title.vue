<template>
	<view class="fui-title diypage-title" :style="{background:diyitem.style.background,color:diyitem.style.color,padding:diyitem.style.paddingtop+'rpx '+diyitem.style.paddingleft+'rpx ','font-size':diyitem.style.fontsize+'px','text-align':diyitem.style.textalign}" @click="app.goPage(diyitem.params.link)">
	    <text class="icon" :class="diyitem.params.icon"></text>
	  {{diyitem.params.title}}
	</view>
</template>


<script>
	export default {
		name: "tpl_title",
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

