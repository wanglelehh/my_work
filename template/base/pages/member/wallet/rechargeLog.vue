<template>
	<view class="page-body" :class="[app.setCStyle()]">
		<u-select :default-value="defaultYearMonth" mode="mutil-column" v-model="showYearMonth" :list="yearMonthlist" @confirm="confirmYearMonth"></u-select>
		<view class="mbr20 bg-white p20 flex">
			<view class="font-w600 flex_bd"  @click="showYearMonth = true">
				{{yearMonth}}<u-icon class="ml10" name="arrow-down-fill" size="22"></u-icon>
			</view>
			<u-icon name="calendar"  size="40"></u-icon>
		</view>
		<view class="relative " style="min-height: calc(100% - 120rpx);">
			<!-- 空白页 -->
			<empty v-if="loaded == true && itemList.length === 0"></empty>
			<view v-for="(item,index) in itemList" :key="index" class="mbr20 p20 bg-white fs30">
				<view class="flex">
					<view class="flex_bd font-w600">
						{{app.langReplace('充值金额')}}：<text class="fs26">￥</text>{{item.order_amount}}
					</view>
					<view class="color-99">{{item.status_lang}}</view>
				</view>
				<view class="flex fs26 color-99 mt10">
					<view class="flex_bd">{{item.pay_name}}</view>
					<view class="fs26">{{item._time}}</view>
				</view>
				<view v-for="(pic, index) in item.imglist" :key="index" class="img_item mt20 smll">
					<image :src="pic" mode="widthFix" @click="app.showImg(item.imglist,index)"></image>
				</view>
				<view class="clearfix"></view>
			</view>
		</view>
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
				showYearMonth:false,
				defaultYearMonth:[],
				yearMonthlist:[],
				yearMonth:'',
				itemList: [],
				imgList:[],
				loaded: false
			}
		},
		onLoad() {
			this.getyearMonth();
			this.loadData();
		},
		onShow() {
			this.app.isLogin(this); //强制登陆
			let title =  this.app.langReplace('充值明细');
			uni.setNavigationBarTitle({
			　　title:title
			})
		},
		onReady() {},
		methods: {
			getyearMonth(){//获取月份
				let nowDate = new Date();
				let nowYear = nowDate.getFullYear();
				let nowMonth = nowDate.getMonth();
				nowMonth = nowMonth + 1;
				if (nowMonth.toString().length == 1) {
				   nowMonth = "0" + nowMonth;
				}
				this.yearMonth = nowYear+'-'+nowMonth;
				let yearList = [];
				for(let yi=0;yi<=10;yi++){
					let year = {};
					year.label = (nowYear-yi).toString();
					year.value = yi;
					yearList.push(year);
				}
				this.yearMonthlist.push(yearList);
				let monthList = [];
				let selectMonth = 0;
				for(let mi=1;mi<=12;mi++){
					let month = {};
					month.label = mi.toString();
					if (month.label.length == 1) {
					   month.label = "0" + month.label;
					}
					month.value = mi-1;
					monthList.push(month);
					if (month.label == nowMonth){
						selectMonth = month.value;
					}
				}
				this.yearMonthlist.push(monthList);
				this.defaultYearMonth = [0,selectMonth];
			},
			confirmYearMonth(e){//选择日期
				this.defaultYearMonth = [e[0].value,e[1].value];
				this.yearMonth = e[0].label+'-'+e[1].label;
				this.loadData();
			},
			loadData() {
				let that = this;
				this.itemList = [];
				this.$u.post('member/api.wallet/getRechargeLog', {time:this.yearMonth}).then(res => {
					res.data.list.forEach(function(item,key) {
						item.imglist.forEach(function(img,keyb) {
							item.imglist[keyb] = that.config.baseUrl + img;
						})
						res.data.list[key] = item;
					})
					this.itemList = this.itemList.concat(res.data.list);
					this.loaded = true;
				})
			}
		}
	}
</script>

<style lang="scss">
	.img_item{
		width: 20%;
		float: left;
		image{
			margin: 0rpx auto;
			width: 100%;
		}
	}
</style>
