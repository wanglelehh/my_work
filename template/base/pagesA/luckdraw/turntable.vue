<template>
	<view class="page-body" :class="[app.setCStyle()]" :style="{'background-image':'url('+baseUrl+turntableInfo.bg_img+')','background-color':turntableInfo.bg_color}">
		<view class="title">
			抽奖轮盘
		</view>
		<view class="uni-btn-icon" @tap="app.goPage(-1)"></view>
		<view class="header">
			<view class="rule" @tap="showRuleDialog">
				<text >规则</text>
			</view>
			
			<view class="info">
			<view class="times">
				还可抽奖： <text>{{luckdraw_num}}</text> 次
			</view>
			<view class="my-prize" @click="showMyPrizeDialog">
				我的奖品<u-icon name="arrow-right color-ff" :size="30"></u-icon>
			</view>
			</view>
			 
		</view>
		<view class="cu-modal cu-modal-transform" :class="showRule?'show':''"  @tap="hideRuleDialog">
			<view class="cu-dialog rule-dialog"> 
				<!-- 规则 -->
				<view class="rule-container" >
					<view class="title" >
						规则说明
					</view>
					<rich-text class="g_item"  :nodes="rule"></rich-text>
				</view>
			</view>
		</view>
		
		<view class="lottery">
			<pt-lottery v-if="show"
				ref="pt-lottery"
				:lotteryBg="baseUrl+turntableInfo.turntable_bg"
				:lotteryBtn="baseUrl+turntableInfo.turntable_btn"
				:times="3"
				:prizeList="prizeList"
				:showTimes="false"
				@start="start"
				@end="end">
			</pt-lottery>
		</view>
		<view class="mission-list">
			<image class="mission-title" src="../static/images/mission_title.png"  mode="widthFix"></image>
			<view class="title grid col-3">
				<view>内容</view>
				<view>奖励次数</view>
				<view>状态</view>
			</view>
			<view class="mission grid col-3">
				<view>每日签到</view>
				<view>1</view>
				<view @click="app.goPage('/pages/member/center/sign')"><text class="mission-button" >前往</text></view>
			</view>
			<view class="mission grid col-3">
				<view>兑换次数</view>
				<view>-</view>
				<view><text class="mission-button" @tap="showChangeDialog">前往</text></view>
			</view>
			<view class="clearfix"></view>
		</view>
		<view class="cu-modal cu-modal-transform" :class="showMyPrize?'show':''"  @tap="hideMyPrizeDialog">
			<view class="cu-dialog my-prize-dialog">
				<view class="title">
					我的奖品
				</view>
				<view class="itembox">
					<view class="item" v-for="(items,i) in myPrizelist" :key="i">
						<text class="left">{{items.prize_name}}</text>
						<text v-if="items.prize_type == 'entity'" class="left receive" @tap="app.goPage('receive?log_id='+items.log_id)">
							{{items._status}}
						</text>
						<text class="right">{{items.add_time}}</text>
					</view>
				</view>
				<view class="no-prize" v-if="myPrizelist.length==0">
					您还没有获得奖品
				</view>
			</view>
		</view>
		<view class="cu-modal cu-modal-transform" :class="showChange?'show':''" >
			<view class="cu-dialog rule-dialog"> 
			<u-icon name="close" class="cu-dialog-close"  @tap="hideChangeDialog"></u-icon>
				<view class="rule-container" >
					<view class="title" >
						兑换抽奖次数
					</view>
					<view>当前可用积分：{{use_integral}}</view>
					<view class="mt20"><text class="font-w700 fs36 mr10">{{integral_add_luckdrawnum}}</text>积分兑换<text class="font-w700 fs36 mlr10">1</text>次抽奖次数</view>
					<view class="mt20">兑换抽奖次数：<u-number-box :min="1" :max="100" :value="change_num" @change="numberChange"></u-number-box></view>
					<button type="warning" size="mini" class="mt30"  @click="changeLuckdrawNum">兑换</button>
				</view>
			</view>
		</view>
		<view class="clearfix"></view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				show:false,
				showChange:false,
				baseUrl:this.config.baseUrl,
				rule:'',
				luckdraw_num:0,
				_luckdraw_num:0,
				turntableInfo:[],
				prizeIndex: 0,
				prizeList: [],
				showRule:false,
				isRuning:false,
				isthink:0,
				showMyPrize:false,
				myPrizelist:[],
				log_id:0,
				use_integral:0,
				integral_add_luckdrawnum:0,
				change_num:1
			}
		},
		onLoad() {
		},
		onShow(){
			this.getInfo();
		},
		methods: {
			getInfo(){
				let baseUrl = this.baseUrl;
				this.$u.post('luckdraw/api.turntable/getInfo').then(res => {
					this.luckdraw_num = res.data.luckdraw_num;
					this.turntableInfo = res.data;
					this.rule = this.app.textFormat(this.turntableInfo.rule);
					let prizeList = [];
					this.turntableInfo.prizeList.forEach(function(item,key){
						let arr = {};
						arr.prizeName = item.prize_name;
						arr.prizeIcon = baseUrl + item.prize_img;
						prizeList.push(arr);
					})
					this.prizeList = prizeList;
					this.show = true;
				})
			},
			start(){
				if (this.isRuning == true){
					return false;
				}
				this.isRuning = true;
				this.$u.post('luckdraw/api.turntable/start',{'luck_id':this.turntableInfo.luck_id}).then(res => {
					this.luckdraw_num--;
					this._luckdraw_num = res.data.luckdraw_num;
					this.prizeIndex = res.data.prize_index;
					this.isthink = res.data.isthink;
					this.log_id = res.data.log_id;
					this.$refs['pt-lottery'].init(this.prizeIndex);
				}).catch(res=>{
					this.isRuning = false;
				})
			},
			end(){
				if (this.isthink == 1){
					this.app.showModal('未中奖，再接再厉!');
				}else{
					this.luckdraw_num = this._luckdraw_num;
					let prize = this.prizeList[this.prizeIndex];
					if (prize == 'entity'){
						this.app.showModal('恭喜您获取'+prize.prizeName,'receive?log_id='+this.log_id);
					}else{
						this.app.showModal('恭喜您获取'+prize.prizeName);
					}
				}
				this.isRuning = false;
			},
			showRuleDialog(){
				this.showRule=true;
			},
			hideRuleDialog(){
				this.showRule=false;
			},
			getMyPrize(){
				this.$u.post('luckdraw/api.turntable/getMyPrize',{'luck_id':this.turntableInfo.luck_id}).then(res => {
					this.myPrizelist = res.data.list;
					this.showMyPrize=true;
				}).catch(res=>{
					
				})
			},
			showMyPrizeDialog(){
				this.getMyPrize();
			},
			hideMyPrizeDialog(){
				this.showMyPrize=false;
			},
			showChangeDialog(){
				this.$u.post('luckdraw/api.turntable/getChangeInfo').then(res => {
					this.integral_add_luckdrawnum = res.data.integral_add_luckdrawnum;
					this.use_integral = res.data.use_integral;
					this.showChange=true;
				}).catch(res=>{
					
				})
			},
			hideChangeDialog(){
				this.showChange=false;
			},
			numberChange(data){
				this.change_num = data.value;
			},
			changeLuckdrawNum(){
				this.$u.post('luckdraw/api.turntable/changeLuckdrawNum',{'change_num':this.change_num}).then(res => {
					this.luckdraw_num = res.data.luckdraw_num;
					this.showChange=false;
				}).catch(res=>{
					
				})
			}
		}
	}
</script>
<style>
	.page-body{
		background-size: 100%;
		background-repeat:no-repeat;
		padding-bottom: 100rpx;
		height: auto !important;
	}
	
	.title{
		position: relative;
		width:100%;
		text-align: center;
		padding-top:3vw;
		font-size: 18px;
		color: #fff;
	}
	
	.conbox {
		width: 750upx;
		height: 100vh;
		background-color: #FC3459;
		overflow-x: hidden;
		overflow-y: scroll;
	}
	
	.container,
	image.cont {
		width: 750upx;
		height: auto;
		position: relative;
	}
	
	image.cont {
		position: absolute;
		z-index: 0;
	}
	
	image.caidai {
		position: absolute;
		top: 20px;
		left: 0;
		width: 750upx;
		height: 1024upx;
	}
	
	.header {
		/* height: 246upx; */
		padding-top: 75upx;
		padding-bottom: 40upx;
		box-sizing: border-box;
		position: relative;
		z-index: 3;
	}
	
	
	
	
	.header .rule{
		padding:15upx 10upx 15upx 60upx;
		color:#FFFFFF;
		background-color: #FFC000;
		border-radius: 40upx;
		margin-left:-45upx;
		width:160upx
		
	}
	.header .info{
		padding: 100upx 30upx 0upx 30upx; 
		width:100%; 
		color:#FEE40B;
	}
	
	
	.header .times{
		float:left;
		font-size: 35upx;
		font-weight: bold;
	}
	.header .times text{
		color:#FFFFFF;
	}
	
	
	.header .my-prize{
		float:right;
		font-size: 35upx;
		font-weight: bold;
	}
	.header .my-prize text{
		color:#FFFFFF;
	}
	.rule-dialog{
		background-color: transparent!important;
	}
	
	.rule-container {
		min-height: 300upx;
		display: flex;
		flex-direction: column;
		z-index: 3;
		background-image: linear-gradient(-180deg, #F48549 0%, #F2642E 100%);
		border: 18upx solid #E4431A;
		border-radius: 16px;
		padding:30upx;
		/* box-sizing: border-box; */
		color: #fff;
	}
	
	.rule-container .title {
		text-align: center;
		margin-bottom: 26upx;
		font-size: 40upx;
	}
	
	.rule-container .g_item {
		font-size: 30upx;
		color: #FFFFFF;
		letter-spacing: 0.5px;
		text-align: justify;
		line-height: 25px;
		margin-bottom: 10px;
	}
	.my-prize-dialog {
		width: 600upx;
		min-height: 180upx;
		background: #FFEEDF;
		border: 10upx solid #F2692F;
		color: #333;
		font-size: 24upx;
		font-family: PingFang-SC-Medium;
		border-radius: 40upx;
		padding-bottom: 20upx;
	}
	
	.my-prize-dialog .title {
		font-family: PingFang-SC-Bold;
		font-size: 16px;
		color: #E4431A;
		letter-spacing: 0.57px;
		display: flex;
		padding: 36upx 0;
		justify-content: center;
	}
	.receive{
		color: #E4431A;
	}
	.my-prize-dialog .itembox {
		max-height: 1320upx;
		overflow-y: auto;
	}
	
	.my-prize-dialog .item {
		width: 100%;
		height: 66upx;
		padding: 0 32upx;
		box-sizing: border-box;
		display: flex;
		align-items: center;
		justify-content: space-between;
	}
	.my-prize-dialog  .no-prize{
		padding: 40upx 0;
	}
	.mission-title{
		width:90%;
		margin:10px 5%;
	}
	
	.mission-list{
		position: relative;
		z-index: 3;
		text-align: center;
		color:#ffffff;
		width:90%;
		margin:0 5% 50upx 5%;
	}
	
	.mission-list .title{
		margin-top:50upx;
		background-color: #FD9383;
		border: 1upx solid #FFFFFF;
	}
	.mission-list .title view{
		border-right: 1upx solid #FFFFFF;
		padding:15upx 10upx;	
	}
	.mission-list .title view:nth-child(3){
		border-right: 0upx solid #FFFFFF;
	}
	
	
	.mission-list .mission{
		border: 1upx solid #FFFFFF;
		border-top: 0upx solid #FFFFFF;
		line-height: 60upx;
		height:100upx;
	}
	.mission-list .mission view{
		border-right: 1upx solid #FFFFFF;
		padding:20upx 10upx;	
	}
	.mission-list .mission view:nth-child(3){
		border-right: 0upx solid #FFFFFF;
	}
	
	.mission-list .mission .mission-button{
		width:100upx;
		height:50upx;
		line-height: 50upx;
		padding:5upx 10upx;
		display: inline-block;
		background-color: #FDF3AB;
		color:#FD302D;
		font-size: 30upx;
		border-radius: 8upx;
	}
	
	
	.mission-list .mission .mission-button.disabled{
		background-color: #BEBEBE;
		color: #FFFFFF;
	}
</style>