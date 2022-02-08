<template>
	<view class="page-body ">
		<view class="top_box bg-base">
			<view class="head_box flex mt20">
				<view class="headimgurl ">
					<image  :src="headimgurl">
						<u-loading slot="loading"></u-loading>
						<view slot="error" style="font-size: 24rpx;">加载失败</view>
					</image>
				</view>
				<view class="flex_bd mt20 ml20">
					<text class="userName ">Hi~,{{proxyInfo.real_name}}</text>
					<view class="bindPhone">绑定号码：{{proxyInfo.mobile}}</view>
					<view v-if="proxyInfo.check_id_card == 0" class="checkIdCardTip">
						<text>您还未实名验证</text>
						<u-button size="mini" shape="circle" @click="app.goPage('/pagesB/member/center/checkIdCard')">实名验证</u-button>
					</view>
					<view v-if="proxyInfo.check_id_card == 2" class="checkIdCardTip">
						<text>实名验证正在审核中</text>
					</view>
					<view v-if="proxyInfo.check_id_card == 3" class="checkIdCardTip">
						<text class="red">实名验证失败</text>
						<u-button size="mini" shape="circle" @click="app.goPage('/pages/member/center/checkIdCard')">实名验证</u-button>
					</view>
				</view>
			</view>
			<view class="icon_email" @click="app.goPage('/pages/public/message/index')">
				<u-icon name="bell" color="#FFFFFF" size="45"></u-icon>消息
			</view>
			<view class="icon_set" @click="app.goPage('/pages/member/center/set?from=channel')">
				<u-icon name="setting" color="#FFFFFF" size="45"></u-icon>
				设置
			</view>
			
		</view>
		<view class="pbox mt10">
			<view class="menu-box user-level " @click="app.goPage('/pagesB/channel/center/appUpgrade')">{{proxyInfo.role.role_name}} <view class="title-right color-94">去升级,获取更多权益<u-icon name="arrow-right"></u-icon></view></view>
			<view class="menu-box mt20 navbar">
				<view class="nav-item" :class="{current: tabCurrentIndex === 0}" @click="tabClick(0)">库存管理</view>
				<view class="nav-item" :class="{current: tabCurrentIndex === 1}" @click="tabClick(1)">人员管理</view>
				<view class="nav-item" :class="{current: tabCurrentIndex === 2}" @click="tabClick(2)">资金管理</view>
				<view class="nav-item" :class="{current: tabCurrentIndex === 3}" @click="tabClick(3)">我的工具</view>
			</view>
			<swiper :current="tabCurrentIndex" class=" mt20 swiper-box" duration="300" @change="changeTab">
				<swiper-item class="overflow-y">
					<view class="menu-box">
						<view class="title">下级订单</view>
						<u-grid :col="3" class="u-grid mt20" :border="false">
							<u-grid-item class="relative" @click="app.goPage('/pagesB/channel/order/xjList?purchase_type=1')">
								<u-icon size="100" name="/pagesB/static/channel/images/menu/01.png"></u-icon>
								<view v-if="orderSubNum[1]>0" class="grid-num">{{orderSubNum[1]}}</view>
								<view class="grid-text">云仓订单</view>
								<view class="grid-tip">缺货需补云仓的订单</view>
							</u-grid-item>
							<u-grid-item  @click="app.goPage('/pagesB/channel/order/xjList?purchase_type=3')">
								<u-icon size="100" name="/pagesB/static/channel/images/menu/02.png"></u-icon>
								<view class="grid-text">提货订单</view>
								<view class="grid-tip">下级提货订单记录</view>
							</u-grid-item>
						</u-grid>
						
						<view class="title">自己的货流管理</view>
						<u-grid :col="3" class="u-grid mt20" :border="false">
							<u-grid-item @click="app.goPage('/pagesB/channel/myStock/index')">
								<u-icon size="100" name="/pagesB/static/channel/images/menu/04.png"></u-icon>
								<view class="grid-text">我的库存</view>
								<view class="grid-tip">查看库存及明细</view>
							</u-grid-item>
							<u-grid-item @click="app.goPage('/pagesB/channel/product/list?purchaseType=1')">
								<u-icon size="100" name="/pagesB/static/channel/images/menu/05.png"></u-icon>
								<view class="grid-text">我要进货</view>
								<view class="grid-tip">下单补充货库</view>
							</u-grid-item>
							<u-grid-item @click="app.goPage('/pagesB/channel/myStock/pickUp')">
								<u-icon size="100" name="/pagesB/static/channel/images/menu/06.png" ></u-icon>
								<view class="grid-text">我要提货</view>
								<view class="grid-tip">云仓提货配送</view>
							</u-grid-item>
							
							<u-grid-item @click="app.goPage('/pagesB/channel/order/list?purchase_type=1')">
								<u-icon size="100" name="/pagesB/static/channel/images/menu/08.png" ></u-icon>
								<view class="grid-text">我的云仓订单</view>
								<view class="grid-tip">云仓的进货订单</view>
							</u-grid-item>
							<u-grid-item @click="app.goPage('/pagesB/channel/order/list?purchase_type=3')">
								<u-icon size="100" name="/pagesB/static/channel/images/menu/09.png" ></u-icon>
								<view class="grid-text">我的提货订单</view>
								<view class="grid-tip">云仓提货的订单</view>
							</u-grid-item>
						</u-grid>
					</view>
				</swiper-item>
				<swiper-item class="overflow-y">
					<view class="menu-box">
						<u-grid :col="3" class="u-grid" :border="false">
							<u-grid-item  @click="app.goPage('/pagesB/channel/team/index')">
								<u-icon size="100" name="/pagesB/static/channel/images/menu/10.png"></u-icon>
								<view class="grid-text">代理团队</view>
								<view class="grid-tip">目前人数<text class="color-red">{{teamNum.allNum}}</text>人</view>
							</u-grid-item>
							<u-grid-item  @click="app.goPage('/pagesB/channel/team/index?topTabCurrentIndex=1')">
								<u-icon size="100" name="/pagesB/static/channel/images/menu/11.png"></u-icon>
								<view class="grid-text">拿货代理</view>
								<view class="grid-tip">目前人数<text class="color-red">{{teamNum.supplyNum}}</text>人</view>
							</u-grid-item>
							<u-grid-item  @click="app.goPage('/pagesB/channel/center/authorizedQuery')">
								<u-icon size="100" name="/pagesB/static/channel/images/menu/12.png"></u-icon>
								<view class="grid-text">授权查询</view>
								<view class="grid-tip">可查询正式授权代理</view>
							</u-grid-item>
							<u-grid-item @click="app.goPage('/pagesB/channel/team/superiorInfo')">
								<u-icon size="100" name="/pagesB/static/channel/images/menu/13.png"></u-icon>
								<view class="grid-text">我的上级</view>
								<view class="grid-tip">查看推荐和拿货上级</view>
							</u-grid-item>
							<u-grid-item @click="app.goPage('/pagesB/channel/team/invite')">
								<u-icon size="100" name="/pagesB/static/channel/images/menu/14.png"></u-icon>
								<view class="grid-text">邀请代理</view>
								<view class="grid-tip">授权邀请新代理</view>
							</u-grid-item>
							<u-grid-item  @click="app.goPage('/pagesB/channel/center/authorizedMy')">
								<u-icon size="100" name="/pagesB/static/channel/images/menu/15.png"></u-icon>
								<view class="grid-text">我的授权</view>
								<view class="grid-tip">自己的授权证书</view>
							</u-grid-item>
							<u-grid-item @click="app.goPage('/pagesB/channel/center/appUpgrade')">
								<u-icon size="100" name="/pagesB/static/channel/images/menu/16.png"></u-icon>
								<view class="grid-text">升级条件</view>
								<view class="grid-tip">查看升级代理条件</view>
							</u-grid-item>
						</u-grid>
					</view>
				</swiper-item>
				<swiper-item class="overflow-y">
					<view class="menu-box">
						<view class="title">出货收益统计</view>
						<view class="statis-box mt20">
							<view class="month-box"><text class="fs42">{{nowMonth}}</text>月</view>
							<view class="info-box">
								<u-grid :col="3" class="u-grid" :border="false">
									<u-grid-item class="p0">
										<view class="grid-text">今天收入</view>
										<view class="grid-text">{{walletCount.income.day}}</view>
									</u-grid-item>
									<u-grid-item class="p0">
										<view class="grid-text">本月收入</view>
										<view class="grid-text">{{walletCount.income.month}}</view>
									</u-grid-item>
									<u-grid-item class="p0">
										<view class="grid-text">累计收入</view>
										<view class="grid-text">{{walletCount.account.total_income}}</view>
									</u-grid-item>
								</u-grid>
							</view>
						</view>
						<view class="clearfix"></view>
						
						<u-grid :col="3" class="u-grid" :border="false">
							<u-grid-item  @click="app.goPage('/pagesB/channel/wallet/balanceMoney')">
								<u-icon size="100" name="/pagesB/static/channel/images/menu/17.png"></u-icon>
								<view class="grid-text">我的钱包</view>
								<view class="grid-tip color-red">￥{{proxyInfo.proxy_account.balance_money}}</view>
							</u-grid-item>
							<u-grid-item  @click="app.goPage('/pagesB/channel/wallet/goodsMoney')">
								<u-icon size="100" name="/pagesB/static/channel/images/menu/18.png"></u-icon>
								<view class="grid-text">货款余额</view>
								<view class="grid-tip color-red">￥{{proxyInfo.proxy_account.goods_money}}</view>
							</u-grid-item>
							<u-grid-item  @click="app.goPage('/pagesB/channel/wallet/earnestMoney')">
								<u-icon size="100" name="/pagesB/static/channel/images/menu/19.png"></u-icon>
								<view class="grid-text">保证金</view>
								<view class="grid-tip color-red">￥{{proxyInfo.proxy_account.earnest_money}}</view>
							</u-grid-item>
							<u-grid-item  @click="app.goPage('/pagesB/channel/wallet/rewardLog')">
								<u-icon size="100" name="/pagesB/static/channel/images/menu/20.png"></u-icon>
								<view class="grid-text">奖励中心</view>
								<view class="grid-tip ">奖金对账明细</view>
							</u-grid-item>
							<u-grid-item @click="app.goPage('/pagesB/channel/center/achievement')">
								<u-icon  size="100" name="/pagesB/static/channel/images/menu/21.png"></u-icon>
								<view class="grid-text">我的业绩</view>
								<view class="grid-tip">查看个人和团队业绩</view>
							</u-grid-item>
						</u-grid>
					</view>
				</swiper-item>
				<swiper-item class="overflow-y">
					<view class="menu-box">
						<u-grid :col="3" class="u-grid" :border="false">
							<u-grid-item @click="app.goPage('/pagesA/school/index')">
								<u-icon  size="100" name="/pagesB/static/channel/images/menu/22.png"></u-icon>
								<view class="grid-text">素材中心</view>
								<view class="grid-tip">课程以及发圈素材</view>
							</u-grid-item>
						
							<u-grid-item @click="app.goPage('/pages/shop/index/index')">
								<u-icon  size="100" name="/pagesB/static/channel/images/menu/24.png"></u-icon>
								<view class="grid-text" >直购商城</view>
								<view class="grid-tip">进入零售商城</view>
							</u-grid-item>
						</u-grid>
					</view>
				</swiper-item>
			</swiper>
			
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			let that = this;
			return {
				headimgurl: '',
				proxyInfo:{
					role:{},
					proxy_account:{}
				},
				tabCurrentIndex: 0,
				orderNum:[],
				orderSubNum:[],
				teamNum:{allNum:0,underNum:0,supplyNum:0},
				nowMonth:'00',
				walletCount:{
					account:{},
					income:{}
				},
			}
		},
		onLoad() {
			let nowDate = new Date();
			let nowMonth = nowDate.getMonth();
			nowMonth = nowMonth + 1;
			if (nowMonth.toString().length == 1) {
			   nowMonth = "0" + nowMonth;
			}
			this.nowMonth = nowMonth;
		},
		onShow() {
			this.getProxyInfo();
			//获取团队统计
			this.$u.post('channel/api.team/getTeamCountToCenter').then(res => {
				this.teamNum = res.data;
			});
			//获取订单数
			this.$u.post('channel/api.order/getNum').then(res => {
				this.orderNum = res.data;
			});
			//获取下级订单数
			this.$u.post('channel/api.order/getSubNum').then(res => {
				this.orderSubNum = res.data;
			});
			//收入统计
			this.$u.post('channel/api.wallet/getWalletInfo').then(res => {
				this.walletCount.account = res.data.account;
				this.walletCount.income = res.data.income;
			});
		},
		computed: {},
		onReady() {},
		methods: {
			tabClick(index) {
				this.tabCurrentIndex = index;
			},
			//swiper 切换
			changeTab(e) {
				this.tabCurrentIndex = e.target.current;
			},
			getProxyInfo(){
				//获取登录会员信息
				this.$u.api.getProxyInfo().then(res => {
					if (res.code == -1){
						return this.app.showModal(res.msg,-1);
					}
					this.proxyInfo = res.data.proxyInfo;
					if (this.proxyInfo.join_condition == 0){//未满足加入条件
						uni.redirectTo({
						    url: '/pagesB/channel/center/joinCondition'
						});
					}
					if (this.proxyInfo.headimgurl == ''){
						this.headimgurl = '/static/public/images/headimgurl.jpg';
					}else{
						this.headimgurl = this.config.baseUrl+this.proxyInfo.headimgurl;
					}
				});
			},
			
		}
	}
</script>

<style scoped lang="scss">
	.top_box {
		position: relative;
		padding-top: 20rpx;
		color: #FFFFFF;
		padding-bottom: 40rpx;
		.head_box{
			position: relative;
			margin-left: 20rpx;
			.headimgurl {
				width: 150rpx;
				height: 150rpx;
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
				top: 110rpx;
				left: 70rpx;
			}
			.userName {
				font-size: 36rpx;
				padding-right: 40rpx;
				background: url(/pagesB/static/channel/images/title_icon/icon_01.png) no-repeat right center;
				background-size: 30rpx;
			}
			.bindPhone {
				font-size: 22rpx;
				background-size: 30rpx;
			}
			.checkIdCardTip {
				color: #FFFFFF;
				font-size: 22rpx;
			}
			.checkIdCardTip .u-btn {
				height: 40rpx;
				line-height: 40rpx;
				margin-left: 10rpx;
			}
		}
		.icon_email {
			position: absolute;
			top: 30rpx;
			right: 100rpx;
			width: 50rpx;
			font-size: 24rpx;
		}
		.icon_set {
			position: absolute;
			top: 30rpx;
			right: 20rpx;
			width: 50rpx;
			font-size: 24rpx;
		}
	}
	
	.pbox {
		margin-top: -45rpx;
		position: relative;
		height: calc(100vh - 600rpx);
	}
	.statis-box .month-box{
		float: left;
		width: 20%;
		text-align: center;
		line-height: 100rpx;
		background-color: #FFFFFF;
		border-right:1rpx solid #EEEEEE;
	}
	
	.navbar{
		height: 90rpx;
		padding: 15rpx;
		.current{
			background-color: $base-color;
			color: #FFFFFF;
			border-radius: 10rpx;
			font-weight:normal;
			&:after{
				display: none !important;
			}
		}
	} 
	
	.statis-box .info-box{
		float: left;
		width: 80%;
		padding-bottom: 20rpx;
	}
	.menu-box {
		border-radius: 10rpx;
		background-color: #FFFFFF;
	}

	.menu-box .title {
		padding: 0rpx 20rpx;
		padding-top: 30rpx;
		font-weight: 700;
	}

	.menu-box .title .title-right {
		float: right;
		font-size: 26rpx;
		font-weight: normal;
	}
	.menu-box .grid-text{
		font-size: 28rpx;
		margin-top: 10rpx;
	}
	.menu-box .grid-tip{
		font-size: 18rpx;
		color: $font-color-disabled;
	}
	.menu-box .grid-num{
		background-color: #fc4a5b;
		border-radius: 50%;
		width: 40rpx;
		height: 40rpx;
		color: #FFFFFF;
		position: absolute;
		right: 20%;
		top: 20rpx;
		text-align: center;
		line-height: 40rpx;
		font-size: 23rpx;
	}
	.user-level {
		line-height: 90rpx;
		padding-left: 80rpx;
		padding-right: 10rpx;
		background-image: url(/pagesB/static/channel/images/title_icon/icon_08.png);
		background-position: left center;
		background-repeat: no-repeat;
		background-size: 40rpx;
		background-position-x: 20rpx;
	}

	.user-level .title-right {
		float: right;
		font-size: 26rpx;
	}
	.swiper-box {
		height: 100%;
		overflow-y:auto;;
	}
</style>
