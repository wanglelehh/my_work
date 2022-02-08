<template>
	<view>
		<view  :index="index"  v-for="(item, index) in nowList" :key="index">
			<view class="collapse_item">	
				<view class="title" :style="plStyle" @click="collapseChange(index)">
					<text v-if="pl > 0" >├</text>
					{{item.nick_name}}
					<text> - {{item.mobile}}</text>
					<text class="count base-color">({{item.count}})</text>
					
					<view class="fr">
						<text class="role_name base-color">{{item.role_name}}</text>
						<view v-if="item.show == 1">
							<u-icon name="arrow-up" size="20"></u-icon>
						</view>
						<view v-else>
							<u-icon name="arrow-down" size="20"></u-icon>
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
	import tempSub from '@/pages/member/team/tempSub';
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
				this.$u.post('member/api.team/getUnderUserlist',{pid:nowList.user_id}).then(res => {
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
		padding: 0 20rpx;
		background-color: #ffffff;
		margin-bottom: 20rpx;
		box-shadow: 0px 10rpx 20rpx 0px rgba(0, 0, 0, 0.04);
		border-radius: 0px 0px 10rpx 10rpx;
		
		.title{
			line-height: 100rpx;
			color: $font-color-dark;
			font-size: 28rpx;
			.count{
				color: $font-color-dark;
				font-size: 28rpx;
				margin-left: 5rpx;
			}
		}
		.role_name{
			position: absolute;
			line-height: 40rpx;
			font-size: 26rpx;
			top: 25rpx;
			right: 80rpx;
			border: 1rpx solid ;
			border-radius: 10rpx;
			padding: 2rpx 10rpx;
		}
	}
	
</style>
