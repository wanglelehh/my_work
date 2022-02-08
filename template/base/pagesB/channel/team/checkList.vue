<template>
	<view class="page-body">
		<view class="navbar">
			<view 
				v-for="(item, index) in navList" :key="index" 
				class="nav-item" 
				:class="{current: tabCurrentIndex === index}"
				@click="tabClick(index)"
			>
				{{item.text}}({{static[item.state]}})
			</view>
		</view>

		<swiper :current="tabCurrentIndex" class="swiper-box" duration="300" @change="changeTab">
			<swiper-item class="tab-content" v-for="(tabItem,tabIndex) in navList" :key="tabIndex">
				<scroll-view 
					class="list-scroll-content" 
					scroll-y
					@scrolltolower="loadData"
				>
					<!-- 空白页 -->
					<empty v-if="tabItem.loaded === true && tabItem.list.length === 0"></empty>
					<!-- 列表 -->
					<view 
						v-for="(item,index) in tabItem.list" :key="index"
						class="item"
					>
						<view class="i-top b-b">
							<text class="time">申请时间：{{item.add_time}}</text>
							<text class="state" >{{item.status}}</text>
						</view>
						<view class="info">
						
						</view>
					</view>
					<uni-load-more :status="tabItem.loadingType"></uni-load-more>
				</scroll-view>
			</swiper-item>
		</swiper>
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
				tabCurrentIndex: 0,
				param: {
					state:0,
					p:0
				}, 
				static:{},
				navList:[
					{
						state: 'waitCheck',
						text: '待审核',
						loadingType: 'more',
						list: [],
						p:0
					},
					{
						state: 'complete',
						text: '已通过',
						loadingType: 'more',
						list: [],
						p:0
					},
					{
						state: 'fail',
						text: '已放弃',
						loadingType: 'more',
						list: [],
						p:0
					},
				],
			};
		},
		
		onLoad(options){
			/**
			 * 修复app端点击除全部订单外的按钮进入时不加载数据的问题
			 * 替换onLoad下代码即可
			 */
			if (typeof(options.state) != 'undefined'){
				this.tabCurrentIndex = parseInt(options.state);
			}
			//获取统计
			this.$u.post('channel/api.team/getCheckStatic').then(res => {
				this.static = res.data;
			});
			this.loadData()
		},
		 
		methods: {
			//获取订单列表
			loadData(source){
				//这里是将订单挂载到tab列表下
				let index = this.tabCurrentIndex;
				let navItem = this.navList[index];
				if (navItem.loadingType == 'nomore'){
					return;
				}
				if(source === 'tabChange' && navItem.loaded === true){
					//tab切换只有第一次需要加载数据
					return;
				}
				if(navItem.loadingType === 'loading'){
					//防止重复加载
					return;
				}
				navItem.p++;
				this.param.state = navItem.state;
				this.param.p = navItem.p;
				navItem.loadingType = 'loading';
				this.$u.post('channel/api.team/getCheckLog', this.param).then(res => {
					navItem.list = navItem.list.concat(res.data.list);
					//loaded新字段用于表示数据加载完毕，如果为空可以显示空白页
					this.$set(navItem, 'loaded', true);
					//判断是否还有下一页，有是more  没有是nomore
					navItem.loadingType = navItem.p == res.data.page_count ? 'nomore' : 'more';
				})
			}, 

			//swiper 切换
			changeTab(e){
				this.tabCurrentIndex = e.target.current;
				this.loadData('tabChange');
			},
			//顶部tab点击
			tabClick(index){
				this.tabCurrentIndex = index;
			},
			
		},
	}
</script>

<style lang="scss">
	
	.swiper-box{
		height: calc(100% - 40px);
	}
	.list-scroll-content{
		height: 100%;
	}
	
	.item{
		display: flex;
		flex-direction: column;
		padding-left: 30rpx;
		background: #fff;
		margin-top: 16rpx;
		.i-top{
			display: flex;
			align-items: center;
			height: 80rpx;
			padding-right:30rpx;
			font-size: $font-base;
			color: $font-color-dark;
			position: relative;
			.time{
				flex: 1;
			}
			.state{
				color: $font-color-base;
			}
			
		}
		.info{
			color: $font-color-light;
			padding: 20rpx 0rpx;
			
		}
		
	}
	
</style>
