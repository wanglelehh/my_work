<template>
	<!-- 规格-模态层弹窗 -->
	<view class="popup " :class="bonusSelectClass" @touchmove.stop.prevent="stopPrevent" @click="toggleBonusSelect">
		<!-- 遮罩层 -->
		<view class="mask"></view>
		<view class="layer attr-content" @click.stop="stopPrevent">
			<view class="fs36 font-w700">{{app.langReplace('选择优惠券')}}</view>
				<radio-group @change="selectBonus">
					<view class="item mt20">
						<label class="flex" >
							<view class="flex_bd left fs30 ">{{app.langReplace('不使用优惠券')}}</view>
							<view class="right "><radio  value="b0" style="transform:scale(0.7)" :color="defaultColor" /></view>
						</label>
					</view>
					<scroll-view class="selbonus_list mt20" scroll-y>
					<view class="line_tip ">
						<view class="line"></view>
						<view class="tip"><text >{{app.langReplace('可使用')}}({{bonusList.ableNum}})</text></view>
					</view>
					<view v-for="(item, index) in bonusList.able" :key="item.bonus_id" class="item mt20">
						<label class="w100 flex">
							<view class="flex_bd">
								<view class="color-00 fs32 font-w700"><text class="color-66 fs28">￥</text><text class="color-66 mr20">{{item.type_money}}</text> {{item.type_name}}</view>
								<view class="fs26 mt5 color-cc">{{app.langReplace('满')}}￥{{item.min_amount}}{{app.langReplace('可用')}}</view>
								<view class="fs26 mt5 color-cc">{{item._use_start_date}}-{{item._use_end_date}}</view>
							</view>
							<view class="right smll mr10"><radio  :value="'b'+item.bonus_id" style="transform:scale(0.7)" :color="defaultColor" /></view>
						</label>
					</view>
					<view v-if="bonusList.ableNum < 1" class="h100 smll"><view class="w100 text-center fs28 color-66">---{{app.langReplace('您没有可使用的优惠券')}}---</view></view>
					<view class="line_tip mt20">
						<view class="line"></view>
						<view class="tip"><text >{{app.langReplace('不可使用')}}({{bonusList.unableNum}})</text></view>
					</view>
					<view v-for="(item, index) in bonusList.unable" :key="item.bonus_id" class="item mt20">
						<label class="w100 flex color-cc">
							<view class="flex_bd">
								<view class=" fs32 font-w700"><text class=" fs28">￥</text><text class=" mr20">{{item.type_money}}</text> {{item.type_name}}</view>
								<view class="fs26 mt5 ">{{app.langReplace('满')}}￥{{item.min_amount}}{{app.langReplace('可用')}}</view>
								<view class="fs26 mt5 ">{{item._use_start_date}}-{{item._use_end_date}}</view>
							</view>
						</label>
					</view>
				</scroll-view>
				</radio-group>
			<view class="btn-box">
				<button class="btn primarybtn" @click="toggleBonusSelect">{{app.langReplace('完成')}}</button>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		name: "bonusSelect",
		props: {
			bonusSelectClass: {
				type: String,
				default: 'none',
			},
			bonusList: {
				type: Object,
				default: function() {
					return {}
				}
			}
		},
		data() {
			return {
				isLoading: false,
				defaultColor: this.app.getColor(this),
				used_bonus_id:0,
				bonusMoney: -1
			}
		},
		methods: {
			//规格弹窗开关
			toggleBonusSelect() {
				this.$emit("toggleBonusSelect",this.bonusMoney,this.used_bonus_id);
			},
			//选择优惠券
			selectBonus(evt){
				this.used_bonus_id = 0;
				if (evt.detail.value == 'b0'){
					this.bonusMoney = 0;
					return false;
				}
				for (let i = 0; i < this.bonusList.able.length; i++) {
					if ('b'+this.bonusList.able[i].bonus_id === evt.detail.value) {
						this.bonusMoney = this.bonusList.able[i].type_money;
						this.used_bonus_id = this.bonusList.able[i].bonus_id;
						break;
					}
				}
			},
			stopPrevent() {}
		}
	}
</script>

<style lang='scss'>
	.selbonus_list{
		height: 600rpx;
		.item{
			width: 100%;
			margin-top: 10rpx;
			.right{
				width: 50rpx;
			}
		}
		.line_tip{
			position: relative;
			padding: 20rpx 0rpx;
			.line{
				border-top:1rpx #cccccc dotted;
			}
			.tip{
				position: absolute;
				top: 0rpx;
				padding: 0rpx 20rpx;
				text-align: center;
				width: 100%;
				text{
					background-color: #FFFFFF;
					color: #999999;
					font-size: 26rpx;
				}
			}
		}
	}
</style>
