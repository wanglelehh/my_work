<template>
	<view class="page-body p20">
		<view class="navbar">
			<view class="nav-item " :class="{current: tabCurrentIndex === 0}" @click="tabClick(0)">推荐上级</view>
			<view class="nav-item" :class="{current: tabCurrentIndex === 1}" @click="tabClick(1)">拿货上级</view>
		</view>
		<swiper :current="tabCurrentIndex" class="swiper-box" duration="300" @change="changeTab">
			<swiper-item class="tab-content">
				<view class="top_box">
					 <image  style="width: 100%;" mode="widthFix" src="/pagesB/static/channel/images/center-bg-01.jpg" ></image>
					<view class="headimgurl">
						<u-image width="150rpx" height="150rpx" :src="parent_headimgurl?parent_headimgurl:'/static/public/images/headimgurl.jpg'" shape="circle">
						</u-image>
					</view>
				</view>
				<view class="info">
					<text class="name">{{superiorInfo.parent_name}}</text>
					<text class="level">{{superiorInfo.parent_level}}</text>
					<view class="mt50">
						<view><u-icon name="phone" color="#ccc" size="28" class="mr20"></u-icon>手机：{{superiorInfo.parent_mobile}}</view>
						<view class="mt30"><u-icon name="clock" color="#ccc" size="28" class="mr20"></u-icon>时间：{{superiorInfo.parent_reg_time}}</view>
						<u-button class="block mt60" type="primary"  @click="call(superiorInfo.parent_mobile)">拨打电话</u-button>
					</view>
				</view>
			</swiper-item>
			<swiper-item class="tab-content">
				<view class="top_box">
					 <image  style="width: 100%;" mode="widthFix" src="/pagesB/static/channel/images/center-bg-01.jpg" ></image>
					<view class="headimgurl">
						<u-image width="150rpx" height="150rpx" :src="supply_headimgurl?supply_headimgurl:'/static/public/images/headimgurl.jpg'" shape="circle">
						</u-image>
					</view>
				</view>
				<view class="info">
					<text class="name">{{superiorInfo.supply_name}}</text>
					<text class="level">{{superiorInfo.supply_level}}</text>
					<view  class="mt50"><u-icon name="phone" color="#ccc" size="28" class="mr20"></u-icon>手机：{{superiorInfo.supply_mobile}}</view>
					<view class="mt30"><u-icon name="clock" color="#ccc" size="28" class="mr20"></u-icon>时间：{{superiorInfo.supply_reg_time}}</view>
					<u-button class="block mt60" type="primary" @click="call(superiorInfo.supply_mobile)">拨打电话</u-button>
				</view>
			</swiper-item>
		</swiper>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				tabCurrentIndex:0,
				parent_headimgurl:'',
				supply_headimgurl:'',
				superiorInfo:{}
			}
		},
		onLoad() {
			//获取上级信息
			this.$u.post('channel/api.proxy_users/getSuperiorInfo').then(res => {
				this.superiorInfo = res.data;
				if (this.superiorInfo.parent_headimgurl){
					this.parent_headimgurl = this.config.baseUrl+this.superiorInfo.parent_headimgurl;
				}
				if (this.superiorInfo.supply_headimgurl){
					this.supply_headimgurl = this.config.baseUrl+this.superiorInfo.supply_headimgurl;
				}
				
			});
		},
		methods: {
			//swiper 切换
			changeTab(e){
				this.tabCurrentIndex = e.target.current;
			},
			//顶部tab点击
			tabClick(index){
				this.tabCurrentIndex = index;
			},
			call(phoneNumber){
				
				uni.makePhoneCall({
				 	// 手机号
				    phoneNumber: phoneNumber+'', 
					// 成功回调
					success: (res) => {
						console.log('调用成功!')	
					},
					// 失败回调
					fail: (res) => {
						console.log('调用失败!')
					}
				 });
			}
		}
	}
</script>

<style lang="scss">
	.page-body{
		height: calc(100% - 40px);
	}
	.swiper-box{
		height: 100%;
	}
	.top_box{
		margin-top: 50rpx;
		width: 100%;
		height: 500rpx;
		background-size: 100%;
		position: relative;
		background-color: #ffffff;
		.headimgurl {
			position: absolute;
			top:200rpx;
			left: 100rpx;
			width: 150rpx;
			height: 150rpx;
			overflow: hidden;
			margin-left: 20rpx;
			border-radius: 50%;
			border: 3rpx solid #cccccc;
		}
	}
	.info{
		background-color: #ffffff;
		padding: 80rpx;
		.name{
			display: block;
			font-size: 35rpx;
			font-weight: 700;
			line-height: 60rpx;
		}
		.level{
			font-size: 18rpx;
			color: $base-color;
			border-radius: 10rpx;
			padding: 1rpx 10rpx;
			border: 1rpx solid $base-color
		}
	}
</style>
