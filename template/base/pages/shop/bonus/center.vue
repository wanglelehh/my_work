<template>
	<view class="page-body p20" :class="[app.setCStyle()]">
		<scroll-view  scroll-y style="height: calc(100% - 80px);">
			<!-- 空白页 -->
			<empty v-if="bonusList.length === 0"></empty>
			<view v-else class="bonus_list ">
				<!-- 列表 -->
				<view  v-for="(item, index) in bonusList" :key="item.type_id" class="item flex mt10">
					<view class="left">
						<view class="color-00 fs32 font-w700">{{item.type_name}}</view>
						<view class="fs26 mt5">{{app.langReplace('满')}}￥{{item.min_amount}}{{app.langReplace('可用')}}</view>
						<view class="fs26 mt5">{{item._use_start_date}}-{{item._use_end_date}}</view>
					</view>
					<view class="center"></view>
					<view class="right flex_bd">
						<view class="fs60 font-w700 mt10"><text class="fs36">￥</text>{{item.type_money}}</view>
						<text v-if="item.receive_status == 0" class="receive_btn fs22 " @click="receivefree(index,item.type_id)">{{app.langReplace('立即领取')}}</text>
						<text v-else-if="item.receive_status == 1" class="receive_btn fs22" @click="app.goPage('/pages/shop/bonus/glist?type_id='+item.type_id)">{{app.langReplace('去使用')}}</text>
						<text v-else-if="item.receive_status == 2" class="receive_btn over fs22">{{app.langReplace('已抢光')}}</text>
					</view>
				</view>
			</view>
		</scroll-view>
		<button type="warning" shape="circle"  class="bottom-btn fs30 primarybtn" @click="app.goPage('index')">
			<text class="ml10">{{app.langReplace('查看我的优惠券')}}</text>
		</button>
	</view>
</template>


<script>
	import empty from "@/components/empty";
	export default {
		components: {
			empty
		},
		data() {
			return {
				bonusList:[]
			}
		},
		onLoad(options) {
			this.app.isLogin(this);//强制登陆
			let title = this.app.langReplace('领券中心');
			uni.setNavigationBarTitle({
				title
			})
			this.loadData();
		},
		methods: {
			//获取优惠券列表
			loadData() {
				this.$u.post('shop/api.bonus/getBonusListReceivable').then(res => {
					this.bonusList = res.list;
					this.$forceUpdate();//刷新数据
				})
			},
			//领取优惠券
			receivefree(index,id){
				let that = this;
				this.$u.post('shop/api.bonus/receiveFree',{'id':id}).then(res => {
					this.bonusList[index].receive_status = 1;
				})
			}
		}
	}
</script>

<style lang="scss">
</style>
