<template>
	<view class="page-body p20">
		<view class="bg-white mt20 p20 superior-box">
			<u-image class="fl headimgurl" :src="supply_headimgurl" shape="circle">
				<u-loading slot="loading"></u-loading>
				<view slot="error" style="font-size: 24rpx;">加载失败</view>
			</u-image>
			<view class="fl ml20">
				<text class="block">{{proxyInfo.supply_name}}</text>
				<text class="block mt10">电话：{{proxyInfo.supply_mobile}}</text>
			</view>
			
			<view class="persql">上级信息</view>
		</view>
		<view class="bg-white mt20 p20">
				<view >
					你的层级：{{proxyInfo.proxyLevel.proxy_name}}
				</view>
				<view class="join-box mt20">你需要完成以下任务</view>
				<view class="join-box mt50" v-if="proxyInfo.proxyLevel.join_uplevel_goods_money_limit == 1">
					充值升级货款：￥{{proxyInfo.proxyLevel.uplevel_goods_money}}
					<u-button v-if="proxyInfo.join_uplevel_goods_money_ok == 0" class="fr" size="mini" type="primary" shape="circle" @click="app.goPage('/pagesB/channel/wallet/recharge?type=uplevelGoodsMoney&money='+proxyInfo.proxyLevel.uplevel_goods_money)">前往充值</u-button>
					<u-button v-else-if="proxyInfo.join_uplevel_goods_money_ok==2" class="fr" size="mini" type="warning" shape="circle">待审核</u-button>
					<u-button v-else-if="proxyInfo.join_uplevel_goods_money_ok==3" class="fr" size="mini" type="warning" shape="circle" @click="app.goPage('/pagesB/channel/wallet/recharge?type=uplevelGoodsMoney&money='+proxyInfo.proxyLevel.uplevel_goods_money)">审核失败，重新提交</u-button>
					<u-button v-else class="fr" size="mini" type="success" shape="circle">已完成</u-button>
				</view>
				<view class="join-box mt50" v-if="proxyInfo.proxyLevel.join_earnest_money_limit == 1">
					充值保证金：￥{{proxyInfo.proxyLevel.join_earnest_money}}
					<u-button v-if="proxyInfo.join_earnest_money_ok == 0" class="fr" size="mini" type="primary" shape="circle" @click="app.goPage('/pagesB/channel/wallet/recharge?type=earnestMoney&money='+proxyInfo.proxyLevel.join_earnest_money)">前往充值</u-button>
					<u-button v-else-if="proxyInfo.join_earnest_money_ok==2" class="fr" size="mini" type="warning" shape="circle">待审核</u-button>
					<u-button v-else-if="proxyInfo.join_earnest_money_ok==3" class="fr" size="mini" type="warning" shape="circle" @click="app.goPage('/pagesB/channel/wallet/recharge?type=earnestMoney&money='+proxyInfo.proxyLevel.join_earnest_money)">审核失败，重新提交</u-button>
					<u-button v-else class="fr" size="mini" type="success" shape="circle">已完成</u-button>
				</view>
				<view class="join-box mt50" v-if="proxyInfo.proxyLevel.join_goods_money_limit == 1">
					充值货款：￥{{proxyInfo.proxyLevel.join_goods_money}}
					<u-button v-if="proxyInfo.join_goods_money_ok == 0" class="fr" size="mini" type="primary" shape="circle" @click="app.goPage('/pagesB/channel/wallet/recharge?type=goodsMoney&money='+proxyInfo.proxyLevel.join_goods_money)">前往充值</u-button>
					<u-button v-else-if="proxyInfo.join_goods_money_ok==2" class="fr" size="mini" type="warning" shape="circle">待审核</u-button>
					<u-button v-else-if="proxyInfo.join_goods_money_ok==3" class="fr" size="mini" type="warning" shape="circle" @click="app.goPage('/pagesB/channel/wallet/recharge?type=earnestMoney&money='+proxyInfo.proxyLevel.join_goods_money)">审核失败，重新提交</u-button>
					<u-button v-else class="fr" size="mini" type="success" shape="circle">已完成</u-button>
				</view>
				<view class="join-box mt50" v-if="proxyInfo.proxyLevel.order_first_spot_limit == 1">
					完成首笔现货订单
					<text v-if="proxyInfo.proxyLevel.order_first_spot_money > 0">
						：￥{{proxyInfo.proxyLevel.order_first_spot_money}}
					</text>
					<u-button v-if="proxyInfo.order_first_spot_ok == 0" class="fr" size="mini" type="primary" shape="circle" @click="app.goPage('/pagesB/channel/product/list?purchaseType=2')">前往下单</u-button>
					<u-button v-else-if="proxyInfo.order_first_spot_ok == 2" class="fr" size="mini" type="warning" shape="circle"  @click="app.goPage('/pagesB/channel/order/list?purchaseType=1')">下单待完成</u-button>
					<u-button v-else class="fr" size="mini" type="success" shape="circle">已完成</u-button>
				</view>
				<view class="join-box mt50" v-if="proxyInfo.proxyLevel.order_first_cloud_limit == 1">
					完成首笔云仓订单
					<text v-if="proxyInfo.proxyLevel.order_first_cloud_money > 0">
						：￥{{proxyInfo.proxyLevel.order_first_cloud_money}}
					</text>
					<u-button v-if="proxyInfo.order_first_cloud_ok == 0" class="fr" size="mini" type="primary" shape="circle" @click="app.goPage('/pagesB/channel/product/list?purchaseType=1')">前往下单</u-button>
					<u-button v-else-if="proxyInfo.order_first_cloud_ok == 2" class="fr" size="mini" type="warning" shape="circle" @click="app.goPage('/pagesB/channel/order/list?purchaseType=1')">下单待完成</u-button>
					<u-button v-else class="fr" size="mini" type="success" shape="circle">已完成</u-button>
				</view>
				<view style="padding: 30rpx;">
					<u-button size="default" shape="circle" type="primary" class="mt40" @click="toLogout">退出登录</u-button>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			let that = this;
			return {
				supply_headimgurl: '',
				proxyInfo:{
					byUser:{},
					proxyLevel:{}
				}
			}
		},
		onLoad() {
		},
		onShow: function() {
			//获取登录会员信息
			this.$u.api.getProxyInfo().then(res => {
				if (res.data.proxyInfo.join_condition == 1){//已满足加入条件
					uni.switchTab({
					    url: '/pagesB/channel/center/index'
					});
				}
				this.proxyInfo = res.data.proxyInfo;
				this.supply_headimgurl = this.config.baseUrl+this.proxyInfo.supply_headimgurl;
			});
		},
		computed: {},
		onReady() {},
		methods: {
			//退出登录
			toLogout(){
				uni.showModal({
				    content: '确定要退出登录么',
				    success: (e)=>{
				    	if(e.confirm){
							this.app.delAuthCode();//清除本地登陆缓存
				    		uni.navigateTo({
				    			url: '/pagesB/passport/login'
				    		});
				    	}
				    }
				});
			}
		}
	}
</script>

<style lang="scss">
	.superior-box{
		position: relative;
		display: block;
		overflow: hidden;
		.headimgurl {
			width: 100rpx !important;
			height: 100rpx !important;
			border: 5rpx solid #FFFFFF;
		}
		.persql{
			font-size: 23rpx;
			background: #5392f3;
			color: #FFFFFF;
		    position: absolute;
		    z-index: 9;
			padding: 0rpx 50rpx;
		    top: 28rpx;
		    right: -63rpx;
		    -webkit-transform: rotate(50deg);
		    transform: rotate(50deg);
		}
	}
	.join-box{
		border-bottom: 1px solid #f1f1f1;
		line-height: 60rpx;
	}
</style>
