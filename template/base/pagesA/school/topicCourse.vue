<template>
	<view class="page-body">
		<view class="navbar " >
			<u-search  placeholder="输入关键字搜索" input-align="center" :show-action="false" v-model="keyword" @blur="loadData('refresh')" @clear="loadData('refresh')" ></u-search>
		</view>
		<scroll-view class="list-scroll-content" scroll-y @scrolltolower="loadData">
			<!-- 空白页 -->
			<empty v-if="itemList.list.length === 0"></empty>
			<!-- 列表 -->
			<view v-for="(item,index) in itemList.list" :key="index" class="toppicCourse p20 smll bg-white" @click="app.goPage('topicCourseInfo?id='+item.id)">
				<u-image width="256rpx" height="150rpx" :src="baseUrl+item.title_img"></u-image>
				<view class="flex_bd ml20">
					<view class="he150 ">
						<view class="flex_bd">
							<view class="fs30 color-33 title">{{item.title}}</view>
							<view class="fs26 color-94 description">{{item.description}}</view>
						</view>
						<view class="fs22 color-94">阅读：{{item.view_num}}</view>
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
				this.$u.post('school/api.train_topic/getList', this.param).then(res => {
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
	.toppicCourse{
		margin-bottom: 5rpx;
		.title{
			height: 34rpx;
			overflow: hidden;
		}
		.description{
			margin-top: 10rpx;
			height: 74rpx;
			overflow: hidden;
		}
	}
</style>
