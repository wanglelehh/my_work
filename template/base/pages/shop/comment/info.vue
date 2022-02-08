<template>
	<view v-if="showPage" class="page-body" :class="[app.setCStyle()]">
		<view class="p20 bg-white mb20">
			<view class="font-w600">{{app.langReplace('商品信息')}}</view>
			<view class="flex mt20">
				<image :src="goodsImage" style="width:140rpx;height:140rpx;border-radius:10rpx;"></image>
				<view class="flex_bd ml20">
					<view class="flex">
						<view class="goods_name flex_bd">{{comment.goods_name}}</view>
						<view class="ml10">
							<view><text class="fs22">￥</text>{{comment.sale_price}}</view>
						</view>
					</view>
					<view class="mt10 smll fixed">
						<view v-if="comment.sku_name" class="flex_bd color-cc fs26">{{comment.sku_name}}</view>
					</view>
				</view>
			</view>
		</view>
		<view class="p20 bg-white mb20">
			<view><text class="font-w600">{{app.langReplace('我的评价')}}</text><text class="fr color-99">{{comment._time}}</text></view>
			<view class="mt20 fs28">
				<rich-text :nodes="comment.comment.content"></rich-text>
			</view>
		</view>
		<view class="mt20 p20 fs28 bg-white">
			<view class="fs30 font-w600">{{app.langReplace('上传图片')}}</view>
			<view class="mlr10 fl mt20" v-for="(pic, index) in imgList" :key="index">
				<image :src="pic" style="width:210rpx;height:210rpx;" @click="app.showImg(imgList,index)"></image>
			</view>
			<view class="clearfix"></view>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				isLoading:false,
				baseUrl: this.config.baseUrl,
				showPage:false,
				rec_id:0,
				comment:{},
				goodsImage:'',
				imgList:[]
			}
		},
		onLoad(options) {
			this.rec_id = options.rec_id;
			let title = this.app.langReplace('查看评论');
			uni.setNavigationBarTitle({
				title
			})
			this.getInfo();
		},
		onShow() {
		},
		onHide() {
		},
		onReady() {},
		methods: {
			getInfo(){
				let that = this;
				this.$u.post('shop/api.comment/getInfo', {
					rec_id: this.rec_id,
				}).then(res => {
					this.comment = res.data;
					this.goodsImage = this.config.baseUrl + this.comment.pic;
					this.showPage = true;
					this.imgList = [];
					this.comment.imgs.forEach(item => {
						that.imgList.push(that.config.baseUrl + item.image);
					})
				}).catch(res=>{
					this.app.showModal(res.msg, -1);
				})
			},
			stopPrevent() {}
		}
	}
</script>

<style>
</style>
