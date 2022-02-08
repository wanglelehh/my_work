<template>
	<view v-if="showPage" class="page-body" :class="[app.setCStyle()]">
		<view class="u-page">
			<view class="page-body" :style="{background:pageInfo.page.background}">
				<diyPage :pageInfo="pageInfo"></diyPage>
			</view>
		</view>
		<copyright></copyright>
		<tabbar v-if="pageInfo.page.diymenu==1" :_current="0" :getCartNum="0"></tabbar>
	</view>
</template>


<script>
	import copyright from '@/pages/public/copyright';
	import tabbar from '@/pages/shop/tabbar';
	import diyPage from '@/pages/shop/index/diyPage/index';
	export default {
		components: {
			tabbar,
			diyPage,
			copyright
		},
		data() {
			return {
				showPage:false,
				page_id:0,
				getCartNum:0,//1执行请求购物车数量
				pageInfo: {
					page: {}
				}
			};
		},
		onLoad(options) {
			let pageid = parseInt(options.pageid);
			if (isNaN(pageid) == true || pageid < 1) {
				this.app.showModal('传值错误.', -1);
				return false;
			}
			this.pageid = pageid;
			this.loadData();
		},
		onShow(){
			//this.getCartNum = 1;
		},
		onHide(){
			this.getCartNum = 0;
		},
		watch: {},
		computed: {},
		methods: {
			//请求数据
			async loadData() {
				this.$u.post('shop/api.Index/getDiyPage',{pageid:this.pageid}).then(res => {
					this.is_diy = res.data.is_diy;
					this.pageInfo = res.data.page;
					 uni.setNavigationBarTitle({
					 	title: this.pageInfo.page.title
					 })
					this.showPage = true;
				})
			},
		}
	}
</script>

<style lang='scss'>
</style>
