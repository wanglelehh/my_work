<template>
	<view :style="{padding:app.pxToRpx(diyitem.style.paddingtop)+'rpx 0rpx'}">
	<u-swiper :height="app.pxToRpx(diyitem.style.height) / 2"  :list="list" :title="diyitem.style.showtitle==1?true:false" :effect3d="diyitem.style.effect3d==1?true:false"
	:indicator-pos="diyitem.style.indicatorPos" :mode="diyitem.style.dotstyle" :interval="diyitem.style.interval * 1000" @click="click" imgMode="widthFix"></u-swiper>
	</view>
</template>

<script>
	export default {
		name: "tpl_banner",
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
				list:this.getList()
			};
		},
		watch: {},
		computed: {},
		methods: {
			getList(){
				let list = [];
				this.diyitem.data.forEach((item,index)=>{
					let row = {};
					row.image = item.imgurl;
					row.title = item.title;
					row.linkurl = item.linkurl;
					list.push(row);
				})
				return list;
			},
			click(index){
				let item = this.list[index];
				return this.app.goPage(item.linkurl);
			}
		}
	}
</script>

<style lang='scss'>
	
</style>

