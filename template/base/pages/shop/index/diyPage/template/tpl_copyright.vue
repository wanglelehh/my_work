<template>
	<block>
		<view v-if="diyitem.style.showtype==0">
			<view class="fui-copyright2-group">
				<block v-if="diyitem.params.showimg==1">
					<image :src="diyitem.params.imgurl" style="width: 60rpx;height: 60rpx;"></image>
				</block>
				<view class="copyright_cover" style="font-size: 24rpx;line-height: 60rpx;">{{diyitem.params.copyright}}</view>
			</view>
		</view>
		<view v-else>
			<view class="fui-copyright-group">
				<block v-if="diyitem.params.showimg==1">
					<image :src="diyitem.params.imgurl"></image>
				</block>
				<span class="copyright_cover" style="text-align:left; font-size: 24rpx; vertical-align: middle;max-width:530rpx;display:inline-block;">
					{{diyitem.params.copyright}}
				</span>
			</view>
		</view>
	</block>
</template>

<script>
	export default {
		name: "tpl_copyright",
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

<style lang='scss'>
</style>
