<template>
	<view class="u-wrap">
		
		<view class="u-menu-wrap">
			<scroll-view scroll-y scroll-with-animation class="u-tab-view menu-scroll-view" :scroll-top="scrollTop">
				<view v-for="(item,index) in cateList[0]" :key="index" class="u-tab-item" :class="[current==index ? 'u-tab-item-active' : '']"
				 :data-current="index" @tap.stop="swichMenu(index)">
					<text class="u-line-1">{{item.name}}</text>
				</view>
			</scroll-view>
			<block v-for="(item,index) in cateList[0]" :key="index">
				<scroll-view scroll-y class="right-box" v-if="current==index">
					<view class="page-view">
						<view class="class-item">
							<view class="item-title">
								<image  :src="baseUrl+item.cover" mode="widthFix"></image>
							</view>
							<view class="item-container">
								<block v-for="(item1, index1) in cateList[item.id]" :key="index1">
									<block v-if="cateList[item1.id]">
										<view class="sub_title">
											<view class="line"></view>
											<view class="name"><text>{{item1.name}}</text></view>
										</view>
										<view class="thumb-box" v-for="(item2, index2) in cateList[item1.id]" :key="index2" @click="select(item2.id,item2.name)">
											<image class="item-menu-image" :src="baseUrl+item2.pic" mode=""></image>
											<view class="item-menu-name">{{item2.name}}</view>
										</view>
									</block>
									<view v-else class="thumb-box"  @click="select(item1.id,item1.name)">
										<image class="item-menu-image" :src="baseUrl+item1.pic" mode=""></image>
										<view class="item-menu-name">{{item1.name}}</view>
									</view>
								</block>
							</view>
						</view>
					</view>
				</scroll-view>
			</block>
		</view>
	</view>
</template>

<script>
	export default {
		
		data() {
			return {
				baseUrl:this.config.baseUrl,
				purchaseType:0,
				cateList: {}, //分类列表
				scrollTop: 0, //tab标题的滚动条位置
				current: 0, // 预设当前项的值
				menuHeight: 0, // 左边菜单的高度
				menuItemHeight: 0, // 左边菜单item的高度
			}
		},
		onLoad(options) {
			this.purchaseType = options.purchaseType;
			this.loadCateList();			
		},
		computed: {
			
		},
		methods: {
			//加载分类
			async loadCateList() {
				this.$u.post('shop/api.goods/getCateList').then(res => {
					this.cateList = res.data.list;
					let selectCateId = uni.getStorageSync("selectCateId");
					if (selectCateId == ''){
						let keys = Object.keys(this.cateList);
						this.current = keys[1];
						uni.setStorageSync("selectCateId",this.current);
					}else{
						this.current = selectCateId;
					}
					
				})
			},
			getImg() {
				return Math.floor(Math.random() * 35);
			},
			// 点击左边的栏目切换
			async swichMenu(index) {
				if(index == this.current) return ;
				uni.setStorageSync("selectCateId",index);
				this.current = index;
				// 如果为0，意味着尚未初始化
				if(this.menuHeight == 0 || this.menuItemHeight == 0) {
					await this.getElRect('menu-scroll-view', 'menuHeight');
					await this.getElRect('u-tab-item', 'menuItemHeight');
				}
				// 将菜单菜单活动item垂直居中
				this.scrollTop = index * this.menuItemHeight + this.menuItemHeight / 2 - this.menuHeight / 2;
			},
			// 获取一个目标元素的高度
			getElRect(elClass, dataVal) {
				new Promise((resolve, reject) => {
					const query = uni.createSelectorQuery().in(this);
					query.select('.' + elClass).fields({size: true},res => {
						// 如果节点尚未生成，res值为null，循环调用执行
						if(!res) {
							setTimeout(() => {
								this.getElRect(elClass);
							}, 10);
							return ;
						}
						this[dataVal] = res.height;
					}).exec();
				})
			},
			select(id,cateName){
				var pages = getCurrentPages(); //当前页
				var beforePage = pages[pages.length - 2]; //上个页面
				if (typeof(beforePage) == 'undefined') {
					let purchaseType = this.purchaseType;
					uni.redirectTo({
					    url: 'list?purchaseType='+purchaseType+'&cateId='+id+'&cateName='+cateName
					});
				}else{
					beforePage.$vm.cateId = id;
					beforePage.$vm.keyword = '';
					beforePage.$vm.tabOrder = 'all';
					beforePage.$vm.tabSort = '';
					beforePage.$vm.cateName = cateName;
					beforePage.$vm.loadData('refresh');
					uni.navigateBack();
				}
			}
		}
	}
</script>

<style lang="scss" scoped>
	.u-wrap {
		height: calc(100vh);
		/* #ifdef H5 */
		height: calc(100vh - var(--window-top));
		/* #endif */
		display: flex;
		flex-direction: column;
	}


	.u-menu-wrap {
		flex: 1;
		display: flex;
		overflow: hidden;
	}

	

	.u-tab-view {
		width: 200rpx;
		height: 100%;
	}

	.u-tab-item {
		height: 110rpx;
		background: #f6f6f6;
		box-sizing: border-box;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 26rpx;
		color: #444;
		font-weight: 400;
		line-height: 1;
	}
	
	.u-tab-item-active {
		position: relative;
		color: $font-color-base;
		font-size: 28rpx;
		font-weight: 600;
		background: #fff;
	}
	
	.u-tab-item-active::before {
		content: "";
		position: absolute;
		border-left: 4px solid $font-color-base;
		height: 32rpx;
		left: 0;
		top: 39rpx;
	}

	.u-tab-view {
		height: 100%;
	}
	
	.right-box {
		background-color: rgb(250, 250, 250);
	}
	
	.page-view {
		padding: 16rpx;
	}
	
	.class-item {
		margin-bottom: 30rpx;
		background-color: #fff;
		padding: 16rpx;
		border-radius: 8rpx;
	}
	
	.item-title {
		font-size: 26rpx;
		color: $u-main-color;
		font-weight: bold;
		overflow: hidden;
		border-radius: 10rpx;
		image{
			width: 100%;
		}
	}
	.sub_title{
		position: relative;
		padding: 30rpx 0rpx;
		width: 100%;
		.line{
			width: 100%;
			border-top: 1rpx solid $border-color-base;
		}
		.name{
			position: absolute;
			top:10rpx;
			width: 100%;
			height: 100%;
			text-align: center;
			text{
				display: inline;
				background-color: #FFFFFF;
				padding: 0rpx 30rpx;
			}
			
		}
	}
	.item-title-img {
		width: 100%;
	}
	.item-menu-name {
		font-weight: normal;
		font-size: 24rpx;
		color: $u-main-color;
	}
	
	.item-container {
		display: flex;
		flex-wrap: wrap;
	}
	
	.thumb-box {
		width: 33.333333%;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-direction: column;
		margin-top: 20rpx;
	}
	
	.item-menu-image {
		width: 120rpx;
		height: 120rpx;
	}
</style>
