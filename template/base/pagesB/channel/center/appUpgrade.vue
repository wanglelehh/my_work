<template>
	<view class="page-body p20">
		<view class="bg-white mt20 p20 superior_box">
			<u-image class="fl headimgurl" :src="supply_headimgurl?supply_headimgurl:'/static/public/images/headimgurl.jpg'" shape="circle">
			</u-image>
			<view class="fl ml20">
				<view class="block">{{proxyInfo.supply_name}} <text class="level">{{proxyInfo.parent_role}}</text></view>
				<text class="block mt10">电话：{{proxyInfo.supply_mobile}}</text>
			</view>
			
			<view class="persql">上级信息</view>
		</view>
		<view class="bg-white mt20 p20 up_box">
			<view class="title">您的级别：{{proxyInfo.role.name}}</view>
			<view class="levle_box mt60" @click="showRoleSelect=true">
				<u-image class="img" width="80rpx" height="80rpx" src="/pagesB/static/channel/images/title_icon/icon_01.png"></u-image>
				<view v-if="isMaxLevel==0">
					<view class="mt60 select_level">{{role_name}} <u-icon class="ml10" name="arrow-down-fill"  size="26"></u-icon></view>
					<view class="tip">请选择升级级别</view>
				</view>
				<view v-else class="select_level">
					你已是最高级别
				</view>
			</view>
			
			<u-select v-model="showRoleSelect" :default-value="roleDefault" :list="roleList" @confirm="confirmRole"></u-select>
			<view v-if="isMaxLevel==0" class="mt60 condition_box">
				<view class="tit">升级条件 
					<text v-if="up_condition == 1" class="tip ml20">- 满足以下任一条件</text>
					<text v-else-if="up_condition == 2"class="tip ml20">- 满足以下全部条件</text>
					<text v-else-if="up_condition == 9"class="tip ml20">- 联系平台</text>
				</view>
				
				<view class="tip" v-for="(item, index) in condition_list" :key="index" >
					{{parseInt(index) + 1}}、{{item.text}} 
					<u-button v-if="item.type == 'earnest_money'" class="fr" size="mini" type="primary" shape="circle" @click="app.goPage('/pagesB/channel/wallet/recharge?type=earnestMoney')">前往充值</u-button>
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
					role:{}
				},
				isMaxLevel:0,//是否已是最高级别
				roleList:[],
				showRoleSelect:false,
				roleDefault:[0],
				role_id:0,
				role_name:'',
				condition_list:[],//升级条件,
				up_condition:1,
			
				
			}
		},
		onLoad() {
		},
		onShow: function() {
			//获取登录会员信息
			this.$u.api.getProxyInfo().then(res => {
				this.proxyInfo = res.data.proxyInfo;
				if (this.proxyInfo.supply_headimgurl){
					this.supply_headimgurl = this.config.baseUrl+this.proxyInfo.supply_headimgurl;
				}
			});
			this.getUpgradeLevel();
		},
		computed: {},
		onReady() {},
		methods: {
			getUpgradeLevel(){//获取可升级的层级
				this.$u.post('channel/api.proxy_users/getUpgradeList').then(res => {
					if (res.data.length > 0){
						this.roleList = res.data;
						this.role_name = res.data[0].role_name;
						this.role_id = res.data[0].role_id;
						this.up_condition = res.data[0].up_condition;
						this.condition_list = res.data[0].condition_list;
					}else{
						this.isMaxLevel = 1;
					}
				});
			},
			confirmRole(e){
				let that = this;
				this.roleList.forEach((item,index)=>{
					if (item.role_id == e[0].value){
						that.roleDefault = [index];
						this.role_name = item.role_name;
						this.role_id = item.role_id;
						this.up_condition = item.up_condition;
						this.condition_list = item.condition_list;
						return true;
					}
				})
			}
			
		}
	}
</script>

<style lang="scss">
	.superior_box{
		position: relative;
		display: block;
		overflow: hidden;
		.headimgurl {
			width: 100rpx !important;
			height: 100rpx !important;
			border: 5rpx solid #FFFFFF;
		}
		.level{
			margin-left: 10rpx;
			font-size: 24rpx;
			color: $base-color;
			border-radius: 10rpx;
			padding: 4rpx 10rpx;
			border: 1rpx solid $base-color;
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
	.up_box{
		border-bottom: 1px solid #f1f1f1;
		line-height: 60rpx;
		.title{
			font-size: 28rpx;
		}
		.levle_box{
			text-align: center;
			.img {
				margin: 0rpx auto;
				margin-bottom: 30rpx;
			}
			.select_level{
				font-size: 36rpx;
				font-weight: 600;
			}
			.tip{
				font-size: 23rpx;
				color: $font-color-light;
			}
		}
		.condition_box{
			.tit{
				text-align: left;
				font-size: 32rpx;
			}
			.tip{
				font-size: 26rpx;
				color: $font-color-light;
			}
		}
	}
</style>