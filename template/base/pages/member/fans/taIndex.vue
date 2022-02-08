<template>
	<view class="page-body " :class="[app.setCStyle()]">
		
		<view class="top_box" >
			<view class="top_title" >
				<!-- #ifndef H5 -->
				<view class="window-top mb40"></view>
				<!-- #endif -->
				<view class="relative">
					<view class="back-btn" @click="app.goPage(-1)"><u-icon name="arrow-left" color="#FFFFFF" size="40"></u-icon></view>
					<view class="fs34 text-center p20">{{app.langReplace('TA的主页')}}</view>
				</view>
			</view>
			<view class="user_info relative smll">
				<view class="headimgurl ">
					<image width="120rpx" height="120rpx" mode="aspectFill" :src="headimgurl?headimgurl:'/static/public/images/headimgurl.jpg'"></image>
				</view>
				<view  class="flex_bd ml20">
					<view class="smll">
						<text class="fs32 nickname">{{userInfo.nick_name?userInfo.nick_name:'--'}}</text>
						<view class="role_name ">{{userInfo.role_name}}</view>
					</view>
					<view class="fs22 mt10">{{app.langReplace('绑定号码')}}：{{userInfo.mobile}}</view>
						
				</view>
				
			</view>
			<view class="fs22 p30">
				<view>UID：{{userInfo.user_id}}</view>
				<view class="mt10">{{app.langReplace('注册时间')}}：{{userInfo.reg_time}}</view>
			</view>
		</view>
		<view class="main-box  base-select p20">
			<view class="navbar">
				<view class="nav-item" :class="{current: tabCurrentIndex === 0}" @click="tabClick(0)">{{app.langReplace('全部粉丝')}}</view>
				<view class="nav-item" :class="{current: tabCurrentIndex === 1}" @click="tabClick(1)">{{app.langReplace('直接关注')}}</view>
				<view class="nav-item" :class="{current: tabCurrentIndex === 2}" @click="tabClick(2)">{{app.langReplace('间接关注')}}</view>
			</view>
			<swiper :current="tabCurrentIndex" class="swiper-box" duration="300" @change="changeTab">
				<swiper-item class="tab-content"  v-for="(tabItem,tabIndex) in navList" :key="tabIndex">
					<view class=" hb100">
						<view class="team-list-box ">
							
							<scroll-view class="user_box" scroll-y @scrolltolower="loadData('add')">
								<!-- 空白页 -->
								<empty v-if="navList[tabIndex].loaded === true && navList[tabIndex].userList.length === 0"></empty>
								<view class="user_item flex" v-for="(item, index) in navList[tabIndex].userList" :key="index">
									<view class="img">
										<u-image :src="item.headimgurl?baseUrl+item.headimgurl:'/static/public/images/headimgurl.jpg'" mode="widthFix" shape="circle"></u-image>
									</view>
									<view class="flex_bd">
										<view class="user_name">{{item.nick_name?item.nick_name:' --'}}</view>
										<view class="role_name">{{item.role_id>0?roleList[item.role_id].role_name:' -- '}}</view>
									</view>
									<view class="user_btn" @click="app.goPage('taIndex?user_id='+item.user_id)">{{app.langReplace('TA的主页')}}</view>
								</view>
								<uni-load-more :status="tabItem.loadingType"></uni-load-more>
							</scroll-view>
						</view>
					</view>
				</swiper-item>
			</swiper>
		 <u-select v-model="showRoleSelect" :default-value="roleDefault[tabCurrentIndex]" :list="selRole" @confirm="confirmRole"></u-select>
		</view>
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
				baseUrl: this.config.baseUrl,
				headimgurl:'',
				userInfo:{},
				tabCurrentIndex: 0,
				keywordArr:[],
				selRole:[],
				roleList:[],
				showRoleSelect:false,
				roleDefault:[[0],[0],[0]],
				roleSelectName:[this.app.langReplace('全部'),this.app.langReplace('全部'),this.app.langReplace('全部')],
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
				}],
				user_id:0
			}
		},
		onLoad(options) {
			this.app.isLogin(this);//强制登陆
		
			if (typeof(options.tabCurrentIndex) != 'undefined') {
				this.tabCurrentIndex = options.tabCurrentIndex;
			}
			this.user_id = options.user_id;
			this.getFansInfo();
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
			getFansInfo(){
				this.$u.post('member/api.fans/getFansInfo',{'user_id':this.user_id}).then(res => {
					this.userInfo = res.data;
				})
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
				param.user_id = this.user_id;
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
					navItem.loadingType = navItem.p == res.data.page_count ? 'nomore' : 'more';
				})
			},
		
		}
	}
</script>

<style lang="scss">
	.top_box {
		height: 484rpx;
		background: linear-gradient(-39deg, #FE6C8A 0%, #FE4366 100%);
		color: #FFFFFF;
		.top_title {
			height: 90rpx;
			color: #FFFFFF;
		}
		.headimgurl {
				margin-left: 20rpx;
				width: 120rpx;
				height: 120rpx;
				border-radius: 50%;
				overflow: hidden;
				border: 3rpx solid #FFFFFF;
				background-color: #FFFFFF;
				
				image{
					border-radius: 50%;
					width: 100%;
					height: 100%;
				}
		
		}
		
		.nickname {
			min-width: 100rpx;
			max-width: 300rpx;
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: nowrap;
			-webkit-box-orient: vertical;
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
	}
	.main-box {
		height: calc(100% - 390rpx);
		margin-top: -110rpx;
	
	}
	.navbar{
		border-bottom: 1rpx solid #EFEFEF;
		border-top-left-radius: 16rpx;
		border-top-right-radius: 16rpx;
	}
	.navbar .current{
		color: #333333 !important;
	}
	.navbar .current:after{
		width: 60rpx;
		height: 4rpx;
	}
	.swiper-box {
		height: calc(100% - 60rpx);
	}

	.tab-content {
		overflow-y: auto;
	}
	.team-list-box{
		background-color: #FFFFFF;
		padding: 44rpx 32rpx;
		border-radius: 16rpx;
		border-top-left-radius: 0rpx;
		border-top-right-radius: 0rpx;
		height: calc(100% - 60rpx);
	}
	
	.team-list-box .title{
		color: #666666;
		height: 66rpx;
		font-size: 28rpx;
		border-bottom: 1rpx solid #EFEFEF;
	}
	

	.user_box {
		height: 100%;
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
