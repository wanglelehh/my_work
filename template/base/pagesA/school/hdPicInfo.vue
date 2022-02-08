<template>
	<view class="page-body">
		<view class=" bg-white text-center">
			<image class="img" mode="widthFix" :src="baseUrl+hdPic"></image>
		</view>
		<bottomShare :imgFile="baseUrl+hdPic" :url="baseUrl+hdPic"></bottomShare>
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
				baseUrl:this.config.baseUrl,
				hdPic:'',
			}
		},
		onLoad(options) {
			this.hdPic = options.hdPic;
		},
		onShow: function() {},
		computed: {},
		onReady() {},
		methods: {
			
		}
	}
</script>

<style lang="scss">
	.img{
		width: 100%;
	}
</style>
