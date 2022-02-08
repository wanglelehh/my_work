<template>
	<u-parse :content="diyitem.params.content" :imageProp="{'mode':'widthFix'}" noData="" @navigate="goUrl" :style="{background:diyitem.style.background,padding:diyitem.style.padding==0?0:app.pxToRpx(diyitem.style.padding)+'rpx '+diyitem.style.padding==0?0:app.pxToRpx(diyitem.style.padding)+'rpx',display:'block'}"></u-parse>
</template>


<script>
	export default {
		name: "tpl_richtext",
		props: {
			diyitem: {
				type: Object,
				default: function() {
					return {};
				}
			},
		},
		data() {
			return {};
		},
		watch: {},
		computed: {},
		methods: {
			goUrl: function(url){
				this.app.goPage(url);
			}
		}
	}
</script>

<style lang='scss'>
</style>