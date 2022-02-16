<template>
	<view class="page-body" :class="[app.setCStyle()]">
		<view class="top_title" :style="{'background-image':'url('+top_bg+')'}">
			<!-- #ifndef H5 -->
			<view class="window-top mb40"></view>
			<!-- #endif -->
			<view class="fs34 text-center p20">{{app.langReplace('个人中心')}}</view>
		</view>
		<view class="top_box" :style="{'background-image':'url('+top_bg+')'}">
			<view class="user_info relative smll">
				<view class="headimgurl " @click="app.goPage('editInfo')">
					<image width="120rpx" height="120rpx" mode="aspectFill" :src="headimgurl?headimgurl:'/static/public/images/headimgurl.jpg'"></image>
				</view>
				<view v-if="userInfo.user_id > 0" class="flex_bd ml20">
					<view class="smll">
						<text class="fs32 nickname">{{userInfo.nick_name}}</text>
						<view v-if="setting.user_center_show_role == 1" class="role_name fs20">
							{{userInfo.role.role_name}}
						</view>
					</view>
					<view class="smll mt20 fs22">
						<view class="mr30">UID：{{userInfo.user_id}}</view>
						<view>{{app.langReplace('手机号码')}}：{{userInfo.mobile}}</view>
					</view>
					<view class="smll mt20 fs22">
						<view>{{app.langReplace('我的邀请码')}}：{{userInfo.token}}</view>
					</view>
				</view>
				<view v-else class="plr10">
					<view @click="app.goPage('/pages/member/passport/login')">{{app.langReplace('登陆/注册')}}</view>
				</view>
				<view class="icon_set smll text-center">
					<view class="mr30 relative" @click="app.goPage('/pages/public/message/index')">
						<u-icon name="bell" color="#FFFFFF" size="45"></u-icon>
						<view class="set_text">{{app.langReplace('通知')}}</view>
						<view v-if="unReadMsgNum > 0" class="tip-msg-dian"></view>
					</view>
					<view @click="app.goPage('set')">
						<u-icon name="setting" color="#FFFFFF" size="45"></u-icon>
						<view class="set_text">{{app.langReplace('设置')}}</view>
					</view>

				</view>
			</view>
			<view class="card_box fs40">
				<u-grid :col="4"  :border="false">
					<u-grid-item bg-color="none" class="p0" @click="app.goPage('/pages/member/wallet/index')">
						<view class="ff">{{userInfo.account.balance_money}}</view>
						<view class="fs26 color-99">{{app.langReplace('余额')}}</view>
					</u-grid-item>
					<!-- <u-grid-item bg-color="none" class="p0" @click="app.goPage('/pages/member/wallet/points')">
						<view class="ff">{{userInfo.account.use_integral}}</view>
						<view class="fs26 color-99">{{app.langReplace('积分')}}</view>
					</u-grid-item> -->
					<u-grid-item bg-color="none" class="p0" @click="app.goPage('/pages/member/fans/index')">
						<!-- <view class="ff">{{teamCount.allNum}}</view> -->
						<view class="ff">{{teamCount.allNum>0?teamCount.allNum:0}}</view>
						<view class="fs26 color-99">{{app.langReplace('粉丝')}}</view>
					</u-grid-item>
					<!-- <u-grid-item bg-color="none" class="p0" @click="app.goPage('/pages/member/center/collect')">
						<view class="ff">{{collectNum}}</view>
						<view class="fs26 color-99">{{app.langReplace('收藏')}}</view>
					</u-grid-item> -->
					<u-grid-item bg-color="none" class="p0">
						<view class="ff">{{teamConsume>0?teamConsume:0}}</view>
						<view class="fs26 color-99">{{app.langReplace('团队业绩')}}</view>
					</u-grid-item>
					<u-grid-item bg-color="none" class="p0" v-if="userInfo.role_id>19">
						<view class="ff">{{setting.team_pool>0?setting.team_pool:0}}</view>
						<view class="fs26 color-99">{{app.langReplace('奖金池')}}</view>
					</u-grid-item>
					<u-grid-item bg-color="none" class="p0" @click="app.goPage('/pages/member/center/collect')" v-else>
						<view class="ff">{{collectNum}}</view>
						<view class="fs26 color-99">{{app.langReplace('收藏')}}</view>
					</u-grid-item>
				</u-grid>
			</view>
		</view>
		<view class="pbox pt10">
			<view class="menu_box mt20">
				<view class="p20">
					<text class="fs28 font-w600">{{app.langReplace('我的订单')}}</text>
					<view class="fs24 fr color-99 font-wn" @click="app.goPage('/pages/shop/order/list?state=0')">
						{{app.langReplace('查看全部')}}<u-icon name="arrow-right"></u-icon>
					</view>
				</view>
				<u-grid :col="setting.shop_after_sale_limit > 0 ? 5 : 4" class="fs28" :border="false">
					<u-grid-item @click="app.goPage('/pages/shop/order/list?state=1')">
						<u-badge :count="orderStats.wait_pay_num" :offset="[20, 20]"></u-badge>
						<u-icon :name="baseUrl+setting.user_center_oicon_wait_pay" :size="48"></u-icon>
						<view class="mt10 fs26 color-99 text-center ">{{app.langReplace('待付款')}}</view>
					</u-grid-item>
					<u-grid-item @click="app.goPage('/pages/shop/order/list?state=2')">
						<u-badge :count="orderStats.wait_shipping_num" :offset="[20, 20]"></u-badge>
						<u-icon :name="baseUrl+setting.user_center_oicon_wait_shipping" :size="48"></u-icon>
						<view class="mt10 fs26 color-99 text-center">{{app.langReplace('待发货')}}</view>
					</u-grid-item>
					<u-grid-item @click="app.goPage('/pages/shop/order/list?state=3')">
						<u-badge :count="orderStats.wait_sign_num" :offset="[20, 20]"></u-badge>
						<u-icon :name="baseUrl+setting.user_center_oicon_wait_sign" :size="48"></u-icon>
						<view class="mt10 fs26 color-99 text-center">{{app.langReplace('待收货')}}</view>
					</u-grid-item>
					<u-grid-item @click="app.goPage('/pages/shop/order/list?state=4')">
						<u-icon :name="baseUrl+setting.user_center_oicon_sign" :size="48"></u-icon>
						<view class="mt10 fs26 color-99 text-center">{{app.langReplace('已完成')}}</view>
					</u-grid-item>
					<u-grid-item @click="app.goPage('/pages/shop/aftersale/index')">
						<u-icon :name="baseUrl+setting.user_center_oicon_after_sale" :size="48"></u-icon>
						<view class="mt10 fs26 color-99 text-center">{{app.langReplace('售后')}}</view>
					</u-grid-item>

				</u-grid>
				<view class="p5"></view>
			</view>
			<view class="menu_box mt20 mb60">
				<view class="p20 b-tottom">
					<text class="fs30 font-w700">{{app.langReplace('我的工具')}}</text>
				</view>
				<u-grid v-if="setting.user_center_nav_tpl == 0" :col="4" class="fs28" :border="false">
					<u-grid-item v-for="(item, index) in navMenu" :key="index" class="mt10 mb10" @click="centerNav(item)">
						<u-icon :name="baseUrl+item.imgurl" :size="68"></u-icon>
						<view class="mt10 fs24">{{item.title}}</view>
					</u-grid-item>
					<!-- #ifdef MP-WEIXIN -->
					<u-grid-item  class="mt10 mb10">
						<button class="u-reset-button" open-type="contact" bindcontact="handleContact" hover-class="none">
							<u-icon :name="baseUrl+contact_imgurl" :size="68"></u-icon>
							<view class="fs24">客服</view>
						</button>
					</u-grid-item>
					<!-- #endif -->
				</u-grid>
				<view v-if="setting.user_center_nav_tpl == 1" v-for="(item, index) in navMenu" :key="index">
					<view class="list-cell b-b mt20" @click="centerNav(item)" hover-class="cell-hover" :hover-stay-time="50">
						<u-icon :name="baseUrl+item.imgurl" :size="68" class="mr20"></u-icon>
						<view class="cell-tit fs28"> {{item.title}}</view>
						<view class="cell-more">
							<u-icon name="arrow-right"></u-icon>
						</view>
					</view>
				</view>
			</view>

		</view>
		<tabbar :now_page="now_page" :getCartNum="0"></tabbar>
	</view>
</template>

<script>
	import tabbar from '@/pages/shop/tabbar';
	export default {
		components: {
			tabbar
		},
		data() {
			return {
				contact_imgurl:'/static/public/images/cneter_comment.png',
				now_page:'',
				baseUrl: this.config.baseUrl,
				setting: {},
				top_bg: '',
				userInfo: {
					user_id:0,
					role: {},
					account: {
						balance_money:'0.00',
						use_integral:'0'
					}
				},
				orderStats: {
					wait_pay_num:0,
					wait_shipping_num:0,
					wait_sign_num:0
				},
				collectNum: 0,
				unReadMsgNum: 0,
				headimgurl: '',
				navMenu: {},
				teamCount: {
					allNum:0,
					underNum:0
				},
				teamConsume:0,
			}
		},
		onLoad(options) {
			this.now_page = this.$mp.page.route;
			//this.app.isLogin(this); //强制登陆
		},
		onShow() {
			this.setting = uni.getStorageSync("setting");
			if (this.setting.user_center_top_bg != '') {
				this.top_bg = this.config.baseUrl + this.setting.user_center_top_bg;
			}
			let that = this;
			if (this.setting.user_center_top_nav_bgc != '') {
				setTimeout(function() {
					uni.setNavigationBarColor({
						backgroundColor: that.setting.user_center_top_nav_bgc
					});
				}, 500);
			}
			this.getCenterInfo();
		},
		methods: {
			getCenterInfo() {
				//获取会员中心信息
				this.$u.post('member/api.center/getCenterInfo').then(res => {
				    console.log(res);
					if (res.data.userInfo){
						this.userInfo = res.data.userInfo;
						this.orderStats = res.data.orderStats;
						this.collectNum = res.data.collectNum;
						this.teamCount = res.data.teamCount;
						this.teamConsume=res.data.teamConsume;
						this.unReadMsgNum = res.data.unReadMsgNum;
						if (this.userInfo.headimgurl != '') {
							this.headimgurl = this.config.baseUrl + this.userInfo.headimgurl;
						}
					}else{
						this.app.delAuthCode();
					}
					if(this.userInfo['role_id']>12 && this.userInfo['signature']==''){
					    uni.showModal({
					        title: this.app.langReplace('提示'),
					        content: '成为代理需签署合同,是否前往签署.',
					        showCancel: true,
					        confirmText: this.app.langReplace('前往签署'),
					        cancelText: this.app.langReplace('取消'),
					        success: function(res) {
					            if (res.confirm) {
					                uni.redirectTo({
					                    url: '/pages/member/center/signContract' });
					            }else {
					                uni.redirectTo({
					                    url: '/pages/shop/index/index' });
								}
					        }
					    });
						return false;
					}
				});
				this.$u.post('member/api.center/getCenterNavMenu').then(res => {
					this.navMenu = res.data.navMenu;
				})
			},
			centerNav(item){
				if (item.bind_type == 'tel'){
					uni.makePhoneCall({
					 	// 手机号
					    phoneNumber: item.url+'', 
						// 成功回调
						success: (res) => {
							console.log('调用成功!')	
						},
						// 失败回调
						fail: (res) => {
							console.log('调用失败!')
						}
					 });
				}else{
					this.app.goPage(item.url);
				}
			}
		}
	}
</script>

<style lang="scss">
	button::after{
		line-height: none !important;
		background-color: none !important;
		// color: #000;
		border-radius: 0;
		border: none;
	}
	.u-badge{
		z-index: 999;
	}
	.top_title {
		position: relative;
		top: 0;
		width: 100%;
		z-index: 99;
		background-size: 100%;
		background-repeat: no-repeat;
		/* #ifdef H5 */
		height: 90rpx;
		background-position: 0rpx -130rpx;
		/* #endif */
		/* #ifndef H5*/
		height: calc(90rpx + var(--status-bar-height) + var(--status-bar-height));
		/* #endif */
		color: #FFFFFF;
	}

	.top_box {
		height: 360rpx;
		background-repeat: no-repeat;
		background-size: 100%;
		/* #ifdef H5 */
		background-position: 0rpx -220rpx;
		/* #endif */
		/* #ifndef H5*/
		background-position: 0rpx calc(-90rpx - var(--status-bar-height) - var(--status-bar-height));
		/* #endif */
		color: #FFFFFF;

		.user_info {
			height: 200rpx;
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

		.headimgEdit {
			position: absolute;
			top: 100rpx;
			left: 70rpx;
		}

		.icon_set {
			position: absolute;
			top: 10rpx;
			right: 20rpx;

			.set_text {
				font-size: 22rpx;
				color: #FFFFFF;
			}
		}
	}

	.card_box {
		background-color: #ffffff;
		border-radius: 20rpx;
		margin: 20rpx;
		width: auto;
		color: $font-color-dark;
		padding: 30rpx 0;
	}

	.nickname {
		min-width: 100rpx;
		max-width: 300rpx;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
		-webkit-box-orient: vertical;
	}

	.role_name {
		min-width: 90rpx;
		line-height: 40rpx;
		color: #ffffff;
		padding: 2rpx 10rpx;
		display: inline-block;
		text-align: center;
		background: linear-gradient(100deg, #F8CD89 1%, #F6A957 100%);
		border-radius: 20rpx;
	}

	.menu_box {
		border-radius: 20rpx;
		background-color: #FFFFFF;
	}
	.tip-msg-dian{
		position: absolute;
		right: -5rpx;
		top:0rpx;
		width: 15rpx;
		height: 15rpx;
		background-color: #FFFFFF;
		border-radius: 50%;
	}
</style>
