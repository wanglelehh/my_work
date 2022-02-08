<template>
	<view class="page-body ">
		<view class="p20 bg-white ">
			<image class="img w100" mode="widthFix" :src="posterImg"></image>
		</view>
		<bottomShare :imgFile="posterImg" :url="url" :isShowWxShare="isShowWxShare"></bottomShare>
	</view>
</template>


<script>
	import bottomShare from '@/components/bottomShare';
	export default {
		components: {
			bottomShare
		},
		data() {
			return {
				isShowWxShare:false,
				posterImg:'',
				url:'',
			}
		},
		onLoad(options) {
			let data = {};
			data.role_id = options.role_id;
			data.is_wxmp = 0;
			//#ifdef MP-WEIXIN
			data.is_wxmp = 1;
			//#endif
			//获取可邀请的代理层级
			this.$u.post('channel/api.proxy_users/getInvitePoster',data).then(res => {
				this.posterImg = this.config.baseUrl+res.data.file;
				this.url = res.data.url;
			});
		},
		onShow: function() {},
		computed: {},
		onReady() {},
		methods: {
			
		}
	}
</script>

<style lang="scss">
</style>
