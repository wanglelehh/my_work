<template>
	<view class="page-body">
		<view class="navbar plr10" >
			<u-search  class="flex_bd" placeholder="输入关键字搜索" input-align="left" :show-action="false" v-model="keyword" @blur="loadData('refresh')" @clear="loadData('refresh')" ></u-search>
		</view>
		<scroll-view class="list-scroll-content" scroll-y @scrolltolower="loadData">
			<!-- 空白页 -->
			<empty v-if="itemList.list.length === 0"></empty>
			<!-- 列表 -->
			<view v-for="(item,index) in itemList.list" :key="index" class="item_box  bg-white  mb10">
				<view class="smll w100" >
					<view class="logo mr10">
						<u-image width="100%" height="100%"  :src="baseUrl+settings.logo"></u-image>
					</view>
					<view class="flex_bd">
						<view class=" w100">
								<view>{{settings.site_name}}</view>
								<view class="color-94 fs22">{{item.add_time}}</view>
						</view>
					</view>
				</view>
				<view class="title">{{item.title}}</view>
				<view class="img_list mt10">
					<u-grid :col="3" :border="false">
						<u-grid-item class="p0"  v-for="(img,ii) in item.images" :key="ii">
							<view class="img" @click="app.showImg(item.images,ii,baseUrl)">
								<u-image width="100%"  mode="widthFix" :src="baseUrl+img"></u-image>
							</view>
						</u-grid-item>
					</u-grid>
				</view>
				<view>
					<view class="bottom">
						<view class="btn smll ">
							<view class="flex_bd">
								<text class="copytxt" @click="app.copy(item.title)">复制文本</text>
							</view>
							<view class="smll fr">
								<image class="icon " src="/static/public/images/share/icon_03.png"></image>
								<text class="flex_bd"  @click="save(item.images)">保存图片</text>
							</view>
						</view>
					</view>
				</view>
			</view>
			<uni-load-more :status="loadingType"></uni-load-more>
		</scroll-view>
	</view>
</template>

<script>
	import uniLoadMore from '@/components/uni-load-more/uni-load-more.vue';
	import empty from "@/components/empty";
	export default {
		components: {
			uniLoadMore,
			empty
		},
		data() {
			return {
				baseUrl:this.config.baseUrl,
				settings: uni.getStorageSync('setting'),
				keyword: '',
				loadingType: 'more',
				param: {
					p: 0
				},
				itemList:{
					list:[]
				}
			}
		},
		onLoad() {
			this.loadData();
		},
		onShow() {
		},
		onReady() {},
		methods: {
			loadData(source = 'add') {
				if (source == 'add' && this.loadingType == 'nomore') {
					return;
				}
				
				if (source === 'refresh') {
					this.param.p = 0;
					this.itemList.list = [];
				}
				this.param.keyword = this.keyword;
				this.param.p++;
				this.$u.post('school/api.graphic_material/getList', this.param).then(res => {
					this.itemList.list = this.itemList.list.concat(res.data.list);
					//loaded新字段用于表示数据加载完毕，如果为空可以显示空白页
					this.$set(this.itemList, 'loaded', true);
					//判断是否还有下一页，有是more  没有是nomore
					this.loadingType = this.param.p == res.data.page_count ? 'nomore' : 'more';
					
				})
			},
			
			save(images){
				let that = this;
				/* #ifdef H5 */
				that.$u.toast('点击图片预览，长按图片保存.');
				return false;
				/* #endif */
				let downStatus = true;
				images.forEach((imgFile,index)=>{
					uni.downloadFile({
						url:that.baseUrl+imgFile,      //文件链接
						success:({statusCode,tempFilePath})=>{
							//statusCode状态为200表示请求成功，tempFIlePath临时路径
							if(statusCode==200){
								uni.saveImageToPhotosAlbum({
									filePath: tempFilePath,//    图片文件路径，可以是临时文件路径也可以是永久文件路径，不支持网络图片路径
									success: () => {
										
									},
									fail: () => {
										downStatus = false;
									}
								});
							}
						},
						fail:()=>{
							downStatus = false;
						}
					})
				});
				if (downStatus == false){
					uni.showToast({
						title: '保存失败',
						duration: 2000 
					});
					return false;
				}
				
				uni.showToast({
					title: '保存成功',
					duration: 2000
				});
			}
		}
	}
</script>

<style lang="scss">
	.navbar{
		width: 100%;
	}
	.list-scroll-content {
		position: relative;
		height: calc(100vh - 80rpx);
		.he150{
			height: 150rpx;
			display: -webkit-box;
			    display: -webkit-flex;
			    display: flex;
			    -webkit-box-orient: vertical;
			    -webkit-box-direction: normal;
			    -webkit-flex-direction: column;
			    flex-direction: column;
		}
	}
	.item_box{
		padding: 10rpx 30rpx;
		.logo{
			width: 100rpx;
			height: 100rpx;
			overflow: hidden;
			border-radius:50%;
		}
		.copytxt{
			color: $font-color-base;
		}
		.title{
		}
		.img_list{
			.u-grid-item{
				border: 5rpx solid #FFFFFF;
				.img{
					width: 100%;
					height: 180rpx;
					overflow: hidden;
				}
			}
		}
		.bottom{
			margin-top: 10rpx;
			width: 100%;
			height: 40rpx;
			.btn{
				color: $font-color-light;
				.icon{
					width: 40rpx;
					height: 40rpx;
				}
				
			}
		}
	}
	
	
</style>
