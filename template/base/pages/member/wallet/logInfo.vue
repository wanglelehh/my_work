<template>
	<view class="page-body bg-white" :class="[app.setCStyle()]">
		<view class="top p20">
			<view class="title text-center">{{logInfo.change_desc}}</view>
			<view class="money text-center mt20 font-w500 fs40"><text v-if="logInfo.change_val > 0">+</text>{{logInfo.change_val}}</view>
		</view>
		<view class="">
			<view class="list-cell b-b" >
				<text class="cell-tit">类型</text>
				<view class="fs32 flex_bd">{{logInfo.log_type=='income'?'收入':'支出'}}</view>
			</view>
			<view class="list-cell b-b" >
				<text class="cell-tit">时间</text>
				<view class="fs32 flex_bd">{{logInfo.change_time}}</view>
			</view>
			<view class="list-cell b-b" >
				<text class="cell-tit">{{type=='points'?'积分余额':'余额'}}</text>
				<view class="fs32 flex_bd">{{logInfo.balance}}</view>
			</view>
			<view class="list-cell b-b" v-if="logInfo.desc">
				<text class="cell-tit">详细</text>
				<view class="fs32 flex_bd">{{logInfo.desc}}</view>
			</view>
		</view>
	</view>
</template>

<script>
export default {
	data() {
		return {
			log_id:0,
			type:'',
			logInfo:{}
		}
	},
	onLoad(options) {
		let log_id = parseInt(options.log_id);
		if (isNaN(log_id) == true || log_id < 1) {
			this.app.showModal(this.app.langReplace('ID传值错误'), -1);
			return false;
		}
		this.log_id = log_id;
		this.type = options.type;
		this.getLog();
	},
	methods: {
		//获取信息
		getLog(){
			this.$u.post('member/api.wallet/getLogInfo', {
				log_id: this.log_id,
				type: this.type,
			}).then(res => {
				this.logInfo = res.data;
			})
		}
	}
}
</script>

<style>
	.cell-tit{
		flex:inherit;
		width: 150rpx;
	}
</style>
