<template>
	<!-- 规格-模态层弹窗 -->
	<view class="popup " :class="bonusReceiveClass" @touchmove.stop.prevent="stopPrevent" @click="toggleBonusReceive">
		<!-- 遮罩层 -->
		<view class="mask"></view>
		<view class="layer " @click.stop="stopPrevent">
			<view class="fs34 font-w700">{{app.langReplace('领取优惠券')}}</view>
			<scroll-view class="bonus_list mt20" style="height: 600rpx;" scroll-y>
				<view  v-for="(item, index) in bonusList" :key="item.type_id" class="item flex mt10">
					<view class="left">
						<view class="color-00 fs32 font-w700">{{item.type_name}}</view>
						<view class="fs26 mt5">{{app.langReplace('满')}}￥{{item.min_amount}}{{app.langReplace('可用')}}</view>
						<view class="fs26 mt5">{{item._use_start_date}}-{{item._use_end_date}}</view>
					</view>
					<view class="center"></view>
					<view class="right flex_bd">
						<view class="fs60 font-w700 mt10"><text class="fs36">￥</text>{{item.type_money}}</view>
						<text v-if="item.receive_status == 0" class="receive_btn fs22 " @click="receivefree(item.type_id)">{{app.langReplace('立即领取')}}</text>
						<text v-else-if="item.receive_status == 1" class="receive_btn fs22" @click="app.goPage('/pages/shop/bonus/glist?type_id='+item.type_id)">{{app.langReplace('去使用')}}</text>
						<text v-else-if="item.receive_status == 2" class="receive_btn over fs22">{{app.langReplace('已抢光')}}</text>
					</view>
				</view>
			</scroll-view>
			<view class="btn-box">
				<button class="btn primarybtn" @click="toggleBonusReceive">{{app.langReplace('关闭')}}</button>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		name: "bonusReceive",
		props: {
			bonusReceiveClass: {
				type: String,
				default: 'none',
			},
			bonusList: {
				type: Array,
				default: function() {
					return [];
				}
			},
		},
		data() {
			return {
				isLoading: false
			}
		},
		methods: {
			//规格弹窗开关
			toggleBonusReceive() {
				this.$emit("toggleBonusReceive");
			},
			//领取优惠券
			receivefree(id){
				let that = this;
				this.$u.post('shop/api.bonus/receiveFree',{'id':id}).then(res => {
					this.$emit("getBonusListReceivable");
				})
			},
			stopPrevent() {}
		}
	}
</script>

<style>
</style>
