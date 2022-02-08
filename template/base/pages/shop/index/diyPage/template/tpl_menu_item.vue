<template>
		<view class="fui-icon-group noborder" :class="'col-'+diyitem.style.rownum" :style="{background:diyitem.style.background}">
			<view v-for="(childitem, childid) in menuList" :key="childid" class="fui-icon-col external" @click="app.goPage(childitem.linkurl)">
				<view class="icon">
					<image :class="diyitem.style.navstyle" :src="childitem.imgurl"></image>
				</view>
				<view class="text" :style="{color:childitem.color}">
					{{childitem.text}}
				</view>
			</view>
		</view>
</template>


<script>
	export default {
		name: "tpl_menu_item",
		props: {
			diyitem: {
				type: Object,
				default: function() {
					return {};
				}
			},
			menuList: {
				type: Array,
				default:function(){
					return [];
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

