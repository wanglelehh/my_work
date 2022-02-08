<template>
	<u-tabbar v-model="current" :list="list" :before-switch="beforeSwitch" :_autoFun="autoFun" :mid-button="false" :mid-button-size="60" :iconSize="50" ></u-tabbar>
</template>

<script>
	export default {
		name: "tabbar",
		props: {
			now_page:{
				type: String,
				default:''
			},
			getCartNum:{
				type: Number,
				default:0
			}
		},
		data() {
			return {
				list: [],
				current:0
			}
		},
		computed:{
			autoFun(){
				let setting = uni.getStorageSync('setting');
				this.list = setting.tabarList;
				let that = this;
				this.list.forEach(function(item,key){
					if (item.url == '/'+that.now_page){
						that.current = key;
						return false;
					}
				})
			}
		},
		methods: {
			beforeSwitch(index){
				this.app.goPage(this.list[index].url);
				return false;
			},
			//加载购物车中商品数
			async loadCartNum() {
				this.$u.post('shop/api.flow/getCartInfo').then(res => {
					let cartNum = res.data.num;
					this.$set(this.list[2], 'count', cartNum);
				})
			},
		}
	}
</script>
<style>
</style>
