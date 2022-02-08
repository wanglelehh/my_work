<template>
	<view>
		<view  :index="index"  v-for="(item, index) in nowList" :key="index">
			<view class="collapse_item">	
				<view class="title" :style="plStyle" @click="collapseChange(index)">
					<text v-if="pl > 0" >├</text>
					<text v-if="item.real_name">{{item.real_name}}</text>
					<text v-else class="color-cc">-未填写-</text>
					<text class="count">({{item.count}})</text>
					<view class="fr">
						<text class="role_name">{{item.role_name}}</text>
						<view v-if="item.show == 1">
							<u-icon name="arrow-up" size="28"></u-icon>
						</view>
						<view v-else>
							<u-icon name="arrow-down" size="28"></u-icon>
						</view>
					</view>
				</view>
				
			</view>
			<view v-if="item.show == 1">
				<tempSub :pid="item.user_id" :pl="pl+1" :underUserlist="item.underList"></tempSub>
			</view>
		</view>
	</view>
</template>

<script>
	import tempSub from '@/pagesB/channel/team/tempSub';
	export default {
		components: {
			tempSub
		},
		name: "tempSub",
		props: {
			pid: {
				type: Number,
				default: '0',
			},
			underUserlist: {
				type: Array,
				default:function(){
					return {};
				}
			},
			pl: {
				type: Number,
				default: '0',
			},
		},
		data() {
			return {
				nowList:this.underUserlist,
				plStyle: this.pl > 0 ? 'padding-left:'+(this.pl * 20)+'rpx':''
			}
		},
		methods: {
			collapseChange(index){
				let nowList  = this.nowList[index];
				if (nowList.count < 1){
					return false;
				}
				if (nowList.show == 1){
					nowList.show = 0;
					this.$forceUpdate()
					return false;
				}else{
					nowList.show = 1;
				}
				if (nowList.underList.length > 0){
					this.$forceUpdate()
					return false;
				}
				this.$u.post('channel/api.team/getUnderUserlist',{pid:nowList.user_id}).then(res => {
					nowList.underList = res.data.list;
					this.$forceUpdate()
				})
			}
		}
	}
</script>

<style lang="scss">
	.collapse_item{
		display: block;
		position: relative;
		width: 100%;
		border: 1px solid rgb(230, 230, 230);
		margin-top: 10rpx;
		.title{
			padding-left: 20rpx;
			line-height: 80rpx;
			padding-right: 20rpx;
			color: #000000;
			font-size: 32rpx;
			.count{
				color: #98979a;
				font-size: 28rpx;
				margin-left: 5rpx;
			}
		}
		.role_name{
			position: absolute;
			line-height: 30rpx;
			font-size: 26rpx;
			color: $base-color;
			top: 25rpx;
			right: 80rpx;
			border: 1rpx solid $base-color;
			border-radius: 10rpx;
			padding: 2rpx 10rpx;
		}
	}
	
</style>
