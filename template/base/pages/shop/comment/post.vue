<template>
	<view v-if="showPage" class="page-body" :class="[app.setCStyle()]">
		<view class="p20 bg-white mb20">
			<view class="font-w600">商品信息</view>
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
		<view class="mt20 p20 bg-white fs28">
			<view>评价</view>
			<textarea placeholder-style="color:#cccccc;font-size:28rpx;" class="w100 h200 mt10" v-model="content" placeholder="请输入您的评价~"/>
		</view>
		<view class="bg-white p20 mt20">
			<view class="fs30">上传图片</view>
			<view class="mt20">
				<u-upload ref="upload" @on-change="uploaded" @on-remove="removeImg" :action="upAction" max-count="6"></u-upload>
			</view>
		</view>
		<view class="p20 mt20">
			<button class="primarybtn mt20" type="warning"  :loading="isLoading" @click="post">立即提交</button>
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
				upAction: this.config.upImageUrl,
				fileList: [],
				content:''
			}
		},
		onLoad(options) {
			this.rec_id = options.rec_id;
			let title = this.app.langReplace('提交评论');
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
				this.$u.post('shop/api.comment/getInfo', {
					rec_id: this.rec_id,
				}).then(res => {
					this.comment = res.data;
					this.goodsImage = this.config.baseUrl + this.comment.pic;
					this.showPage = true;
				}).catch(res=>{
					this.app.showModal(res.msg, -1);
				})
			},
			uploaded(res, index, lists) {
				var res = JSON.parse(res.data);
				this.fileList.push(res.file);
			},
			removeImg(index, lists) {
				let file = this.fileList[index];
				this.fileList.splice(index, 1);
				this.$u.post(this.config.removeImageUrl, {
					file: file
				}).then(res => {})
			},
			post(){
				if (this.isLoading == true) {
					return false;
				}
				this.isLoading = true;
				let data = {};
				data.rec_id = this.rec_id;
				data.content = this.content;
				data.fileList = this.fileList.join(',');
				this.$u.post('shop/api.comment/post', data).then(res => {
					this.isLoading = false;
					this.app.showModal(res.msg,'my',-1);
				}).catch(res => {
					this.isLoading = false;
				})
			},
			stopPrevent() {}
		}
	}
</script>

<style lang="scss">
	
</style>
