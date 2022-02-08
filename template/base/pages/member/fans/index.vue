<template>
	<view class="page-body " :class="[app.setCStyle()]">
		<view class="main-box  base-select">
			<view class="navbar">
				<view class="nav-item" :class="{current: tabCurrentIndex === 0}" @click="tabClick(0)">{{app.langReplace('全部粉丝')}}</view>
				<view class="nav-item" :class="{current: tabCurrentIndex === 1}" @click="tabClick(1)">{{app.langReplace('直接关注')}}</view>
				<view class="nav-item" :class="{current: tabCurrentIndex === 2}" @click="tabClick(2)">{{app.langReplace('间接关注')}}</view>
				<view class="nav-item hide" :class="{current: tabCurrentIndex === 9}" @click="tabClick(9)">{{app.langReplace('树状图')}}</view>
			</view>
			<view class="p20">
				<u-search :placeholder="app.langReplace('请输入用户id、手机账号进行搜索')" bg-color="#ffffff" shape="square" input-align="left" :show-action="false"  v-model="keywordArr[tabCurrentIndex]"  @blur="loadData('refresh')" @clear="loadData('refresh')"></u-search>
			</view>
			<swiper :current="tabCurrentIndex" class="swiper-box" duration="300" @change="changeTab">
				<swiper-item class="tab-content"  v-for="(tabItem,tabIndex) in navList" :key="tabIndex">
					<view class=" hb100">
						<view class="team-list-box ">
							<view class="title flex">
								<view class="flex_bd">{{app.langReplace('选择类型')}}</view>
								<view @click="showRoleSelect=true">
									<text class="mr10">{{roleSelectName[tabIndex]}}（{{userTotal[tabIndex]}}）</text>
									<u-icon name="arrow-down"  size="28"></u-icon>
								</view>
							</view>
							<scroll-view class="user_box" scroll-y @scrolltolower="loadData('add')">
								<!-- 空白页 -->
								<empty v-if="navList[tabIndex].loaded === true && navList[tabIndex].userList.length === 0"></empty>
								<view class="user_item flex" v-for="(item, index) in navList[tabIndex].userList" :key="index">
									<view class="img">
										<u-image :src="item.headimgurl?baseUrl+item.headimgurl:'/static/public/images/headimgurl.jpg'" mode="widthFix" shape="circle"></u-image>
									</view>
									<view class="flex_bd">
										<view class="user_name text_hidden">{{item.user_id}} - {{item.nick_name?item.nick_name:' --'}}</view>
										<view class="role_name">{{item.role_id>0?roleList[item.role_id].role_name:' -- '}}</view>
									</view>
									<view class="user_btn" @click="app.goPage('taIndex?user_id='+item.user_id)">{{app.langReplace('TA的主页')}}</view>
								</view>
								<uni-load-more :status="tabItem.loadingType"></uni-load-more>
							</scroll-view>
						</view>
					</view>
				</swiper-item>
				
				<!--<swiper-item class="tab-content">
					<view class="p20 team-tree">
						<view class="all-title">
							全部<text class="count">({{teamCount}})</text>
							<view class="icon">
								<u-icon name="arrow-right" size="20"></u-icon>
							</view>
						</view>
						<view>
							<tempSub v-if="teamCount > 0" :pid="0" :pl="0" :underUserlist="underUserlist"></tempSub>
						</view>
					</view>
				</swiper-item>-->
			</swiper>
		 <u-select v-model="showRoleSelect" :default-value="roleDefault[tabCurrentIndex]" :list="selRole" @confirm="confirmRole"></u-select>
		</view>
	</view>
</template>

<script>
	import tempSub from '@/pages/member/team/tempSub';
	import uniLoadMore from '@/components/uni-load-more/uni-load-more.vue';
	import empty from "@/components/empty";
	export default {
		components: {
			uniLoadMore,
			empty,
			tempSub
		},
		data() {
			return {
				baseUrl: this.config.baseUrl,
				tabCurrentIndex: 0,
				keywordArr:[],
				selRole:[],
				roleList:[],
				showRoleSelect:false,
				roleDefault:[[0],[0],[0]],
				roleSelectName:[this.app.langReplace('全部'),this.app.langReplace('全部'),this.app.langReplace('全部')],
				userTotal:[0,0,0],
				roleSelectId:[0,0,0],
				navList: [{
					loadingType: 'more',
					userList: [],
					p: 0
				},{
					loadingType: 'more',
					userList: [],
					p: 0
				},{
					loadingType: 'more',
					userList: [],
					p: 0
				}]
			}
		},
		onLoad(options) {
			this.app.isLogin(this);//强制登陆
			let title = this.app.langReplace('我的粉丝');
			uni.setNavigationBarTitle({
				title
			})
			if (typeof(options.tabCurrentIndex) != 'undefined') {
				this.tabCurrentIndex = options.tabCurrentIndex;
			}
			this.getRoleList();
			this.loadData();
		},
		methods: {
			//顶部tab点击

			tabClick(index) {
				this.tabCurrentIndex = index;
				this.loadData('tabChange');
			},
			//swiper 切换
			changeTab(e) {
				this.tabCurrentIndex = e.target.current;
				this.loadData('tabChange');
			},
			getRoleList(){
				this.$u.post('member/api.fans/getRoleList').then(res => {
					this.selRole = res.data.selRole;
					this.roleList = res.data.roleList;
				})
			},
			confirmRole(e){
				this.selRole.forEach((item,index)=>{
					if (item.value == e[0].value){
						this.roleDefault[this.tabCurrentIndex] = [index];
						this.roleSelectName[this.tabCurrentIndex] = item.label;
						this.roleSelectId[this.tabCurrentIndex] = item.value;
						this.loadData('refresh');
						return true;
					}
				})
			},
			loadData(source = 'add'){
				let index = this.tabCurrentIndex;
				let navItem = this.navList[index];
				if (navItem.loadingType === 'loading') {
					//防止重复加载
					return;
				}
				if (source === 'tabChange' && navItem.loaded === true) {
					//tab切换只有第一次需要加载数据
					return;
				}
				//没有更多直接返回
				if (source == 'add' && navItem.loadingType == 'nomore') {
					return;
				}
				if (source === 'refresh') {
					navItem.p = 0;
					navItem.userList = [];
					navItem.loaded = false;
				}
				
				navItem.loadingType = 'loading';
				navItem.p++;
				let param = {};
				param.keyword = this.keywordArr[index];
				param.role_id = this.roleSelectId[index];
				param.p = navItem.p;
				if (index == 1){
					param.type = 'direct';
				}else if(index == 2){
					param.type = 'indirect';
				}else{
					param.type = 'all';
				}
				this.$u.post('member/api.fans/getFansUserList',param).then(res => {
					navItem.userList = navItem.userList.concat(res.data.list);
					this.$set(navItem, 'loaded', true);
					if (param.p == 1){
						this.userTotal[index] = res.data.userTotal;
					}
					navItem.loadingType = navItem.p == res.data.page_count ? 'nomore' : 'more';
				})
			},
		
		}
	}
</script>

<style lang="scss">
	.main-box {
		height: calc(100% - 80rpx);
	}
	.navbar .current{
		color: #333333 !important;
	}
	.navbar .current:after{
		width: 60rpx;
		height: 4rpx;
	}
	.swiper-box {
		height: calc(100% - 130rpx);
		padding: 0rpx 20rpx;
	}

	.tab-content {
		overflow-y: auto;
	}
	.team-list-box{
		background-color: #FFFFFF;
		padding: 44rpx 32rpx;
		border-radius: 16rpx;
		height: calc(100% - 60rpx);
	}
	
	.team-list-box .title{
		color: #666666;
		height: 66rpx;
		font-size: 28rpx;
		border-bottom: 1rpx solid #EFEFEF;
	}
	

	.user_box {
		height: calc(100% - 50rpx);
		.user_item {
			padding: 40rpx 0rpx 20rpx 0rpx;
			font-size: 28rpx;
			border-bottom: 1rpx solid #EFEFEF;
			.img {
				float: left;
				margin-right: 36rpx;
				width: 86rpx;
				height: 86rpx;
				border-radius: 50%;
			}
			.user_name{
				width: 300rpx;
				color: #333333;
			}
			.role_name{
				margin-top: 10rpx;
				background: linear-gradient(57deg, #C8DAF3 0%, #ABBCDF 100%);
				display: table;
				padding: 5rpx 20rpx;
				border-radius: 20rpx;
				color: #FFFFFF;
				font-size: 22rpx;
			}
			
			.user_btn{
				margin-top: 20rpx;
				height: 50rpx;
				line-height: 30rpx;
				color: #FF3C58;
				border: 1rpx solid #FF3C58;
				border-radius: 30rpx;
				padding: 10rpx  20rpx;
			}
		}
	}

	.team-tree {

		.all-title {
			position: relative;
			width: 100%;
			padding: 0 20rpx;
			background-color: #ffffff;
			margin-bottom: 20rpx;
			box-shadow: 0px 10rpx 20rpx 0px rgba(0, 0, 0, 0.04);
			border-radius: 0px 0px 10rpx 10rpx;
			line-height: 100rpx;
			color: $font-color-dark;
			font-size: 28rpx;

			.count {
				color: #98979a;
				font-size: 28rpx;
				margin-left: 5rpx;
			}

			.icon {
				float: right;
				color: rgb(144, 147, 153);
				font-size: inherit;
			}
		}
	}
</style>
