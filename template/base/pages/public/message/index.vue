<template>
	<view class="page-body" :class="[app.setCStyle()]">
		<view class="top_navbar">
			<view class="navbar base-select">
				<view class="nav-item" v-for="(item, index) in navList" :key="index" :class="{current: topTabCurrentIndex === index}"
				 @click="topTabClick(index)">{{item.text}}</view>
			</view>
		</view>
		<swiper :current="topTabCurrentIndex" class="swiper-box" disable-touch="true" duration="300" @change="topChangeTab">
			<swiper-item class="tab-content " v-for="(tabItem, tabIndex) in navList" :key="tabIndex">
				<scroll-view class="list-scroll-content" scroll-y @scrolltolower="loadData">
					<!-- 空白页 -->
					<empty v-if="tabItem.loaded === true && tabItem.list.length === 0"></empty>
					<!-- 列表 -->
					<view v-for="(item,index) in tabItem.list" :key="index" class="bg-white mt20 p20 br20 mlr20" @click="goPage(item)">
						<view class="fs32"><text  v-if="item.is_see === 0" class="tip-dian"></text>{{item.title}}</view>
						<view class="mt10 fs28 color-94">{{item.send_time}}</view>
						<view class="mt10 fs28 color-94">{{item.content}}</view>
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
			let that = this;
			return {
				topTabCurrentIndex: 0,
				param: {
					state: '',
					p: 0
				},
				navList: [{
						state: 'my',
						text: this.app.langReplace('我的消息'),
						list: [],
						loadingType: 'more',
						p: 0
					},
					{
						state: 'sys',
						text: this.app.langReplace('系统公告'),
						list: [],
						loadingType: 'more',
						p: 0
					}
				],
			}
		},
		onLoad() {
			this.app.isLogin(this);//强制登陆
			let title = this.app.langReplace('消息');
			uni.setNavigationBarTitle({
				title
			})
			this.loadData();
		},
		computed: {},
		onReady() {},
		methods: {
			//顶部tab点击
			topTabClick(index) {
				this.topTabCurrentIndex = index;
			},
			topChangeTab(e) {
				this.topTabCurrentIndex = e.target.current;
				this.loadData('tabChange');
			},
			loadData(source) {
				//这里是将订单挂载到tab列表下
				let index = this.topTabCurrentIndex;
				let navItem = this.navList[index];
				if (navItem.loadingType == 'nomore') {
					return;
				}
				if (source === 'tabChange' && navItem.loaded === true) {
					//tab切换只有第一次需要加载数据
					return;
				}
				if (navItem.loadingType === 'loading') {
					//防止重复加载
					return;
				}
				navItem.p++;
				this.param.state = navItem.state;
				this.param.p = navItem.p;
				navItem.loadingType = 'loading';
				this.$u.post('publics/api.message/getList', this.param).then(res => {
					navItem.list = navItem.list.concat(res.data.list);
					//loaded新字段用于表示数据加载完毕，如果为空可以显示空白页
					this.$set(navItem, 'loaded', true);
					//判断是否还有下一页，有是more  没有是nomore
					navItem.loadingType = navItem.p == res.data.page_count ? 'nomore' : 'more';
				})
			},
			goPage(item){
				if (item.is_see == 0){
					let data = {};
					if (item.type == 'all'){
						data.type = 'all';
						data.rec_id = item.message_id;
					}else{
						data.rec_id = item.rec_id;
					}
					this.$u.post('publics/api.message/setSee', data).then(res => {
						item.is_see = 1;
					});
				}
				if (item.type == 'all'){//系统公告 
					if (item.article_id == 0){
						return true;
					}
					this.app.goPage('/pages/public/article?id='+item.article_id);
					return true;
				}
				if (item.type == 3){//代理商品缺货
					this.app.goPage('/pagesB/channel/order/info?order_id='+item.ext_id);
				}else if (item.type == 0){
				   if (item.ext_id == 0){
					 return true;
				   }
				   this.app.goPage('/pages/public/article?id='+item.ext_id);
				}
			}
		}
	}
</script>

<style lang="scss">
	
.swiper-box{
	height: calc(100% - 80rpx);
}
.list-scroll-content {
		height: 100%;
}

</style>
