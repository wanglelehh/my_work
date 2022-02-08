<template>
	<view class="page-body ">
		<view class="top_navbar mb10">
			<view class="navbar">
				<view class="nav-item " :class="{current: topTabCurrentIndex == 0}" @click="topTabClick(0)">推荐团队</view>
				<view class="nav-item" :class="{current: topTabCurrentIndex == 1}" @click="topTabClick(1)">拿货团队</view>
			</view>
		</view>
		<view class="main-box bg-white">
			<view class="navbar" v-if="topTabCurrentIndex == 0">
				<view class="nav-item" :class="{current: tabCurrentIndex === 0}" @click="tabClick(0)">直推下级</view>
				<view class="nav-item" :class="{current: tabCurrentIndex === 1}" @click="tabClick(1)">我的团队</view>
				<view class="nav-item" :class="{current: tabCurrentIndex === 2}" @click="tabClick(2)">树状图</view>
			</view>
			<swiper :current="topTabCurrentIndex" class="swiper-box" duration="300" @change="topChangeTab">
				<swiper-item class="tab-content">
					<swiper :current="tabCurrentIndex" class="swiper-box" duration="300" @change="changeTab">
						<swiper-item class="tab-content">
							<view class="p20">
								<view class="collapse_item" :index="index" @click="collapseChange(index)" v-for="(item, index) in directList"
								 :key="index">
									<view class="title">
										{{item.role_name}}
										<text class="count">({{item.count}})</text>
										<view class="fr">
											<view v-if="item.show == 1">
												<u-icon name="arrow-up" size="28"></u-icon>
											</view>
											<view v-else>
												<u-icon name="arrow-down" size="28"></u-icon>
											</view>
										</view>
									</view>
									<view class="user_box" v-if="item.show == 1">
										<view class="user_item" v-for="(uItem, uIndex) in item.userList" :key="uIndex">
											<view class="img">
												<u-image :src="uItem.headimgurl?baseUrl+uItem.headimgurl:'/static/public/images/headimgurl.jpg'" mode="widthFix" shape="circle"></u-image>
											</view>
											<text v-if="uItem.real_name">{{uItem.real_name}}</text>
											<text v-else class="color-cc">-未填写-</text>
											<text class="fr">{{uItem.mobile}}</text>
										</view>
									</view>
								</view>
							</view>
						</swiper-item>
						<swiper-item class="tab-content">
							<view class="p20">
								<view class="collapse_item" :index="index" @click="collapseChange(index)" v-for="(item, index) in indirectList"
								 :key="index">
									<view class="title">
										{{item.role_name}}
										<text class="count">({{item.count}})</text>
										<view class="fr">
											<view v-if="item.show == 1">
												<u-icon name="arrow-up" size="28"></u-icon>
											</view>
											<view v-else>
												<u-icon name="arrow-down" size="28"></u-icon>
											</view>
										</view>
									</view>
									<view class="user_box" v-if="item.show == 1">
										<view class="user_item" v-for="(uItem, uIndex) in item.userList" :key="uIndex">
											<view class="img">
												<u-image :src="uItem.headimgurl?baseUrl+uItem.headimgurl:'/static/public/images/headimgurl.jpg'" mode="widthFix" shape="circle"></u-image>
											</view>
											<text v-if="uItem.real_name">{{uItem.real_name}}</text>
											<text v-else class="color-cc">-未填写-</text>
											<text class="fr">{{uItem.mobile}}</text>
										</view>
									</view>
								</view>
							</view>
						</swiper-item>
						<swiper-item class="tab-content">
							<view class="p20 team-tree">
								<view class="all-title">
									全部<text class="count">({{teamCount}})</text>
									
								</view>
								<view style="border: 1px solid rgb(230, 230, 230);border-top:none;">
									<tempSub v-if="teamCount > 0" :pid="0" :pl="0" :underUserlist="underUserlist"></tempSub>
								</view>
							</view>
						</swiper-item>
					</swiper>
				</swiper-item>
				<swiper-item class="tab-content">
					<view class="p20">
						<view class="collapse_item" :index="index" @click="collapseChange(index)" v-for="(item, index) in purchaseList"
						 :key="index">
							<view class="title">
								{{item.role_name}}
								<text class="count">({{item.count}})</text>
								<view class="fr">
									<view v-if="item.show == 1">
										<u-icon name="arrow-up" size="28"></u-icon>
									</view>
									<view v-else>
										<u-icon name="arrow-down" size="28"></u-icon>
									</view>
								</view>
							</view>
							<view class="user_box" v-if="item.show == 1">
								<view class="user_item" v-for="(uItem, uIndex) in item.userList" :key="uIndex">
									<view class="img">
										<u-image :src="uItem.headimgurl?baseUrl+uItem.headimgurl:'/static/public/images/headimgurl.jpg'" mode="widthFix" shape="circle"></u-image>
									</view>
									<text v-if="uItem.real_name">{{uItem.real_name}}</text>
									<text v-else class="color-cc">-未填写-</text>
									<text class="fr">{{uItem.mobile}}</text>
								</view>
							</view>
						</view>
					</view>
				</swiper-item>
			</swiper>
		</view>
	</view>
</template>

<script>
	import tempSub from '@/pagesB/channel/team/tempSub';
	export default {
		components: {
			tempSub
		},
		data() {
			return {
				baseUrl: this.config.baseUrl,
				topTabCurrentIndex: 0,
				tabCurrentIndex: 0,
				directList: {}, //直推
				indirectList: {}, //间推
				purchaseList: {}, //拿货团队
				teamCount: 0,
				underUserlist: {}
			}
		},
		onLoad(options) {
			if (typeof(options.topTabCurrentIndex) != 'undefined') {
				this.topTabCurrentIndex = options.topTabCurrentIndex;
			}
			if (typeof(options.tabCurrentIndex) != 'undefined') {
				this.tabCurrentIndex = options.tabCurrentIndex;
			}
			this.getTeamCount(); //默认获取直推
		},
		methods: {
			//顶部tab点击
			topTabClick(index) {
				this.topTabCurrentIndex = index;
			},
			topChangeTab(e) {
				this.topTabCurrentIndex = e.target.current;
				this.getTeamCount();
			},
			tabClick(index) {
				this.tabCurrentIndex = index;
			},
			//swiper 切换
			changeTab(e) {
				this.tabCurrentIndex = e.target.current;
				this.getTeamCount();
			},
			collapseChange(index) { //查看列表
				this.getUserList(index);
			},
			getTeamCount() {
				if (this.topTabCurrentIndex == 0) { //推荐团队
					if (this.tabCurrentIndex == 0) { //直推
						if (this.directList.length > 0) {
							return false;
						}
						this.$u.post('channel/api.team/getTeamCount', {
							type: 'direct'
						}).then(res => {
							this.directList = res.data;
						})
					} else if (this.tabCurrentIndex == 1) { //间推
						if (this.indirectList.length > 0) {
							return false;
						}
						this.$u.post('channel/api.team/getTeamCount', {
							type: 'indirect'
						}).then(res => {
							this.indirectList = res.data;
						})
					} else { //树状图
						if (this.underUserlist.length > 0) {
							return false;
						}
						this.$u.post('channel/api.team/getUnderUserlist').then(res => {
							
							this.teamCount = res.data.count > 0 ? res.data.count : 0;
							this.underUserlist = res.data.list;
						})
					}
				} else { //拿货团队
					if (this.purchaseList.length > 0) {
						return false;
					}
					this.$u.post('channel/api.team/getPurchaseTeamCount').then(res => {
						this.purchaseList = res.data;
					})
				}
			},
			getUserList(index) {
				if (this.topTabCurrentIndex == 0) { //推荐团队
					if (this.tabCurrentIndex == 0) { //直推
						let directList = this.directList[index];
						if (directList.count < 1) {
							return false;
						}
						if (directList.show == 1) {
							directList.show = 0;
							this.$forceUpdate()
							return false;
						} else {
							directList.show = 1;
						}
						if (typeof(directList.userList) != 'undefined') {
							this.$forceUpdate()
							return false;
						}
						this.$u.post('channel/api.team/getTeamUserList', {
							type: 'direct',
							role_id: directList.role_id
						}).then(res => {
							directList.userList = res.data;
							this.$set(directList, 'loaded', true);
						})
					} else if (this.tabCurrentIndex == 1) { //间推
						let indirectList = this.indirectList[index];
						if (indirectList.count < 1) {
							return false;
						}
						if (indirectList.show == 1) {
							indirectList.show = 0;
							this.$forceUpdate()
							return false;
						} else {
							indirectList.show = 1;
						}
						if (typeof(indirectList.userList) != 'undefined') {
							this.$forceUpdate()
							return false;
						}
						this.$u.post('channel/api.team/getTeamUserList', {
							type: 'indirect',
							role_id: indirectList.role_id
						}).then(res => {
							indirectList.userList = res.data;
							this.$set(indirectList, 'loaded', true);

						})
					}
				} else { //拿货团队
					let purchaseList = this.purchaseList[index];
					if (purchaseList.count < 1) {
						return false;
					}
					if (purchaseList.show == 1) {
						purchaseList.show = 0;
						this.$forceUpdate()
						return false;
					} else {
						purchaseList.show = 1;
					}
					if (typeof(purchaseList.userList) != 'undefined') {
						this.$forceUpdate()
						return false;
					}
					this.$u.post('channel/api.team/getPurchaseUserList', {
						role_id: purchaseList.role_id
					}).then(res => {
						purchaseList.userList = res.data;
						this.$set(purchaseList, 'loaded', true);
					})
				}
			}
		}
	}
</script>

<style lang="scss">
	.main-box {
		height: calc(100% - 80rpx);
	}

	.swiper-box {
		height: calc(100% - 40rpx);
	}

	.tab-content {
		overflow-y: auto;
	}

	.collapse_item {
		display: block;
		position: relative;
		width: 100%;
		padding: 10rpx 20rpx;
		border: 1px solid rgb(230, 230, 230);
		margin-top: 10rpx;

		.title {
			line-height: 80rpx;
			color: #000000;
			font-size: 32rpx;

			.count {
				color: #98979a;
				font-size: 28rpx;
				margin-left: 5rpx;
			}
		}
	}

	.user_box {
		background-color: #f6f5f7;
		border-radius: 10rpx;
		padding: 20rpx;

		.user_item {
			height: 80rpx;
			margin-top: 20rpx;
			line-height: 60rpx;
			padding-bottom: 10rpx;
			border-bottom: 1px solid #e8e6e8;

			.img {
				float: left;
				margin-right: 10rpx;
				width: 60rpx;
				height: 60rpx;
				;
			}
		}
	}

	.team-tree {
		.all-title {
			position: relative;
			width: 100%;
			padding: 10rpx 20rpx;
			border: 1px solid #e6e6e6;
			line-height: 60rpx;
			color: #000000;
			font-size: 32rpx;

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
