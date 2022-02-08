<template>
	<view class="fui-cell-group" :style="{'background-color': diyitem.style.background,'margin-top':diyitem.style.margintop==0?0:diyitem.style.margintop+'rpx'}">
		<view v-for="(childitem, childid) in diyitem.data" :key="childid" class="fui-cell"  @click="app.goPage(childitem.linkurl)">
				<block v-if="childitem.iconclass">
					<view class="fui-cell-icon" :style="{color:diyitem.style.iconcolor}">
						<text class="icon "  :class="childitem.iconclass"></text>
					</view>
				</block>
			<view class="fui-cell-text" :style="{color:diyitem.style.textcolor}">{{childitem.text}}</view>
			<view class="fui-cell-remark" :style="{color:diyitem.style.remarkcolor}">{{childitem.remark}}</view>
		</view>
	</view>
</template>

<script>
	export default {
		name: "tpl_listmenu",
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
	
			};
		},
		watch: {},
		computed: {
	
		},
		methods: {}
	}
</script>

<style>
</style>
