<template>
	<view class="page-body">
		<view class="navbar " >
			<u-search  placeholder="输入关键字搜索" input-align="center" :show-action="false" v-model="keyword" @blur="loadData('refresh')" @clear="loadData('refresh')" ></u-search>
		</view>
		<scroll-view class="list-scroll-content" scroll-y @scrolltolower="loadData">
			<view class="big_img p10">
			<!-- 空白页 -->
			<empty v-if="itemList.list.length === 0"></empty>
			<!-- 列表 -->
				<u-grid :col="2" class="u-grid" :border="false">
					<u-grid-item  v-for="(item,index) in itemList.list" :key="index" class="p0">
						<view class="img">
							<u-image width="100%"  mode="widthFix" :src="baseUrl+item.image"  @click="app.goPage('hdPicInfo?hdPic='+item.image);"></u-image>
						</view>
						<view class="grid-text mt20">{{item.title}}</view>
					</u-grid-item>
				</u-grid>
			<uni-load-more :status="loadingType"></uni-load-more>
			</view>
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
				this.$u.post('school/api.hd_pic/getList', this.param).then(res => {
					this.itemList.list = this.itemList.list.concat(res.data.list);
					//loaded新字段用于表示数据加载完毕，如果为空可以显示空白页
					this.$set(this.itemList, 'loaded', true);
					//判断是否还有下一页，有是more  没有是nomore
					this.loadingType = this.param.p == res.data.page_count ? 'nomore' : 'more';
					
				})
			},
		}
	}
</script>

<style lang="scss">
	.list-scroll-content {
		position: relative;
		height: calc(100vh - 80rpx);
	}
	.big_img{
		.u-grid-item{
			border: 10rpx solid #FFFFFF;
			.img{
				width: 100%;
				height: 580rpx;
				overflow: hidden;
			}
		}
	}
</style>
