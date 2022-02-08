<template>
	<view class="page-body signIn" :class="[app.setCStyle()]">
	       <view class="top">
				 <view class="bg" :style="{'background-image':'url('+top_bg+')'}"></view>
				 <view class="goLog" @click="app.goPage('signLog')">签到记录</view>
				<view class="integral">
					<view class="fs60 font-w600">
						{{my_integral}}
						<image style="width: 47rpx;" class="ml20" mode="widthFix" :src="img_icon_integral"></image>
					</view>
					<view class="color-99 fs28 mt10" @click="app.goPage('/pages/member/wallet/points')">我的积分 <u-icon name="arrow-right" color="#999999" size="28"></u-icon></view>
				</view>
				<view class="signInfo">
					<view class="pt30 ml30 fs32">您已连续签到{{constant}}天</view>
					<view v-if="!isSign">
						<view class="pt20 ml30 fs28 color-99"  >今天签到可获<text class="add_integral">+{{integral}}</text>积分{{luckdraw_num>0?',+'+luckdraw_num+'次抽奖':''}}</view>
						<view class="signBtn primarybtn" @click='doSign'>立即签到</view>
					</view>
					<view v-else>
						<view class="pt20 ml30 fs28 color-99">明天签到可获<text class="add_integral">+{{integral_morn}}</text>积分{{luckdraw_num_morn>0?',+'+luckdraw_num_morn+'次抽奖':''}}</view>
						<view class="signBtn no"  >明日再来</view>
					</view>
					
				</view>
			</view>
	        <view class="Calendar">
	            <view id="toyear" class="flex flex-pack-center fs38 color_9">
	                <view class="year-month fs30 font-w700 color_3">
	                    {{year}}年{{month}}月
	                </view>
	            </view>
	            <view class='tou fs28 font-w700 color_3'>
	                <text>日</text>
	                <text>一</text>
	                <text>二</text>
	                <text>三</text>
	                <text>四</text>
	                <text>五</text>
	                <text>六</text>
	            </view>
	            <view class="dateBox">
	                <block v-for="(item, index) in dateArr" :key="index">
	                    <view class="fs30 font-w700 " :class="index<firstDay||index>=lastDay?'notMonth':''">
	                        <text :class="item.isSame?'signActive':''">{{item.date}}</text>
	                    </view>
	                </block>
	            </view>
	        </view>
	    <view class="model" v-if='modelType'>
	        <view class="modelBg" @click='closeModel'></view>
	        <view class="canter">
				<view class="colse" @click='closeModel'>
					 <u-icon name="close" color="#999999" size="48"></u-icon>
				</view>
				<view class="sign_ok_img">
					<image style="width: 400rpx;" mode="widthFix" :src="sign_ok_img"></image>
				</view>
	            <view class="text-center fs30 color-33 mt30">获得<text class="add_integral">{{integral}}</text>积分{{luckdraw_num>0?','+luckdraw_num+'次抽奖':''}}</view>
				<view class="text-center fs26 color-99 mt20">明天签到可获<text class="add_integral">{{integral_morn}}</text>积分{{luckdraw_num_morn>0?',+'+luckdraw_num_morn+'次抽奖':''}}</view>
				<view class="primarybtn mt30 p20 w50 text-center" @click='closeModel'>我知道了</view>
	        </view>
	      
	    </view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				isSign: false,
				year: '',
				month: '',
				day: '',
				dateArr: [],
				firstDay: '',
				lastDay: '',
				signDay: [], //环形签到日期
				dayString: '', //当天时间戳
				modelType: false,
				sign_constant:{},
				integral:0,
				luckdraw_num:0,
				integral_morn:0,
				luckdraw_num_morn:0,
				constant:0,
				my_integral:0,
				top_bg:'',
				img_icon_integral:'',
				sign_ok_img:''
			}
		},
		onLoad(options) {
			this.app.isLogin(this);//强制登陆
			this.signIndex();
		},
		methods: {
			//获取签到信息
			signIndex() {
				this.$u.post('member/api.Center/signIndex').then(res => {
					if (res.code == 1 && res.data.signDay) {
						this.isSign = res.data.isSign == 1?true:false;
						this.my_integral = res.data.my_integral;
						this.constant = res.data.constant;
						this.sign_constant = res.data.sign_constant;
						this.signDay = res.data.signDay;
						this.dayString = res.data.timeData*1000;
						this.integral = res.data.sign_integral;
						this.luckdraw_num = res.data.luckdraw_num;
						this.integral_morn = res.data.sign_integral_morn;
						this.luckdraw_num_morn = res.data.luckdraw_num_morn;
						this.top_bg = this.config.baseUrl + res.data.img_user_sign_top_bg;
						this.sign_ok_img = this.config.baseUrl + res.data.img_user_sign_ok;
						this.img_icon_integral = this.config.baseUrl + res.data.img_icon_integral;
					}
					this.setDate();
				});
			},
			//画日历
			setDate() {
				let mydate = new Date(this.dayString);
				this.year = mydate.getFullYear();
				this.month = mydate.getMonth() + 1;
				this.day = mydate.getDate();
				let firstDay = new Date(this.year, this.month - 1, 1).getDay();
				let Arr = [];
				let LastDay = new Date(this.year, this.month, 0).getDate();
				let isSame = false
				let signDay = this.signDay
				//用当月第一天在一周中的日期值作为当月离第一天的天数
				let tianNums = new Date(this.year, this.month - 1, 0).getDate();
		
				for (let i = 1; i <= firstDay; i++) {
					Arr.unshift({
						date: tianNums - i + 1
					});
				}
				
				//用当月最后一天在一个月中的日期值作为当月的天数
				for (var i = 1; i < LastDay + 1; i++) {
					isSame = false;
					for (var ii = 0; ii < signDay.length; ii++) {
						//是否签到
						if (signDay[ii] == i){
							isSame = true;
							break
						}
					}
					Arr.push({
						'date': i,
						'isSame': isSame
					});
				}
				//当月最后一天未满7天
				for (let i = 1, lastDayWeek = new Date(this.year, this.month - 1, LastDay).getDay(); i < 7 - lastDayWeek; i++) {
					Arr.push({
						date: i
					});
				}
				this.dateArr = Arr;
				this.firstDay = firstDay;
				this.lastDay = firstDay + LastDay;
			},
			//签到
			doSign() {
				if (this.isSign == false) {
					this.$u.post('member/api.Center/signIng').then(res => {
						if (res.code == 1) {
							this.modelType = true;
							this.total_integral = res.data.total_integral;
							this.total_luckdraw_num = res.data.total_luckdraw_num;
							this.signIndex();
						}
					});
				} else {
					uni.showToast({
					    title: '今天已经签到过！',
					    duration: 2000
					});
				}
			},
			closeModel(){
				this.modelType = false;
			}
		}
	}
</script>

<style lang="scss">
	.signIn{
		background: #FFFFFF;
	}
	.signIn .top{
		width: 100%;
		height: 528rpx;
		background:linear-gradient(180deg, #FFC1C6 0%, #FEF6F6 100%);
	}
	.signIn .top .bg{
		position: absolute;
		width: 330rpx;
		height: 240rpx;
		right: 40rpx;
		top: 60rpx;
	}
	.signIn .top .goLog{
		width: 160rpx;
		height: 52rpx;
		background: #FFFBFB;
		border-radius: 26px 0px 0px 26px;
		color: #FE6E8C;
		text-align: right;
		font-size: 28rpx;
		padding-right: 20rpx;
		line-height: 52rpx;
		position: absolute;
		right: 0rpx;
		top:32rpx;
	}
	.signIn .top .integral{
		padding-top: 80rpx;
		padding-left: 75rpx;
	}
	.signIn .top .signInfo{
		position: relative;
		margin:0rpx 24rpx;
		margin-top: 61rpx;
		height: 160rpx;
		background: #FFFFFF;
		box-shadow: 0px 2rpx 37rpx 3rpx #FEE3E7;
		border-radius: 16rpx;
		.add_integral{
			color: #FE385E;
		}
		.signBtn{
			width: 226rpx;
			height: 80rpx;
			box-shadow: 0rpx 8rpx 27rpx 0rpx rgba(254, 58, 95, 0.5);
			border-radius: 40rpx;
			text-align: center;
			line-height: 80rpx;
			color: #FFFFFF;
			position: absolute;
			right: 20rpx;
			top:40rpx;
		}
		.no{
			background: #C2C2C2;
			box-shadow:none;
		}
	}

	.signIn .Calendar {
	    height: 52vh;
	    margin: -5vh 3vw 0 3vw;
	    width: 94vw;
	    background-color: #fff;
	   box-shadow: 0px 2px 37px 3px #FEE3E7;
	   border-radius: 16px;
	}
	
	.signIn #toyear {
	    display: flex;
	    align-items: center;
	    justify-content: space-between;
	    height: 8vh;
	    padding: 0 40rpx;
	}
	
	.notMonth {
	    color: #999;
	}
	.signActive {
	    width: 80rpx;
	    height: 80rpx;
	    background: rgba(246, 82, 54, 0.14);
	    border-radius: 50%;
	    color: #f65236;
	    display: flex;
	    align-items: center;
	    justify-content: center;
	}
	
	.tou {
	    display: flex;
	    width: 100%;
	}
	
	.tou text {
	    flex: 1;
	    display: flex;
	    align-items: center;
	    justify-content: center;
	    height: 5vh;
	}
	
	.dateBox {
	    display: flex;
	    flex-wrap: wrap;
	    width: 100%;
	    height: 36vh;
	    justify-content: space-between;
	}
	
	
	
	.dateBox view {
	    width: 14.2857%;
	    display: flex;
	    align-items: center;
	    justify-content: center;
	}
	
	.signIn .model {
	    position: fixed;
	    top: 0;
	    left: 0;
	    height: 100vh;
	    width: 100vw;
	    z-index: 999998;
	}
	
	.signIn .modelBg {
	    position: absolute;
	    background-color: rgba(0, 0, 0, 0.6);
	    z-index: 99999;
	    top: 0;
	    left: 0;
	    height: 100vh;
	    width: 100vw;
	}
	
	.signIn .canter {
	    width: 520rpx;
	    margin: 25vh 15vw 0 15vw;
	    display: flex;
	    flex-direction: column;
	    align-items: center;
	    position: relative;
	    z-index: 99999;
		background-color: #FFFFFF;
		color: #000000;
		border-radius: 20rpx;
		margin-bottom: 20rpx;
		position: relative;
		padding-top: 80rpx;
		padding-bottom: 40rpx;
	}
	.signIn .canter .colse{
		position: absolute;
		right: 10rpx;
		top:10rpx;
	}
	.signIn .canter .sign_ok_img {
	    width: 100%;
		text-align: center;
	}
</style>
