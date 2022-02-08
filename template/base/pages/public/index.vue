<template>
	
</template>

<script>
	export default {
		data() {
			return {
				
			}
		},
		onLoad(options) {
			if (options.scene){//获取小程序的场景值，用于获取分享者的token
				let scene = options.scene.split('_');
				uni.setStorageSync("share_token",scene[0]);
			}
			this.app.goPage(this.config.defaultIndex,-1);
		},
		computed: {},
		onReady() {},
		methods: {
			
		}
	}
</script>

<style lang="scss">
	
	

</style>
