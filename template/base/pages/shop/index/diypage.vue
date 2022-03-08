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
				settings: uni.getStorageSync('setting'),
				user_token:uni.getStorageSync('user_token'),
				showPage:false,
				page_id:0,
				pageid:0,
				getCartNum:0,//1执行请求购物车数量
				pageInfo: {
					page: {}
				}
			};
		},
		onLoad(options) {
			if (options.scene){//获取小程序的场景值，用于获取分享者的token
				let scene = options.scene.split('_');
				uni.setStorageSync("share_token",scene[0]);
				this.pageid = scene[1];
			}else{
				this.pageid = options.pageid;
			}
			if (this.pageid < 1) {
				this.app.showModal('传值错误.', -1);
				return false;
			}
			this.loadData();
		},
		onShow(){
			let setting = uni.getStorageSync('setting');
			if(setting.shop_force_login==1){
				this.app.isLogin(this); //强制登陆
			}
			//this.getCartNum = 1;
		},
		onHide(){
			this.getCartNum = 0;
		},
		watch: {},
		computed: {},
		onShareAppMessage() {
		    return {
		        title:this.pageInfo.page.title,
		        imageUrl: this.baseUrl + this.settings.logo,
		        path: '/pages/shop/index/diypage?pageid='+this.pageid+'&share_token='+this.user_token
		    }
		},
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
