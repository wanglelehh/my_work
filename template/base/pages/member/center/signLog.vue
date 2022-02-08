<template>
	<view class="page-body" :class="[app.setCStyle()]">
		<u-select :default-value="defaultYearMonth" mode="mutil-column" v-model="showYearMonth" :list="yearMonthlist" @confirm="confirmYearMonth"></u-select>
		<view class="year_month p20"  @click="showYearMonth = true">
			{{yearMonth}}月份<u-icon class="ml10" name="arrow-down-fill" size="22"></u-icon>
			<text class="fs24 color-cc ml10">请按月份进行筛选</text>
		</view>
		<view class="relative p20" style="height: calc(100vh - 90rpx);">
			<empty v-if="logList.length === 0"></empty>
			<view v-for="(item,index) in logList" :key="index" class="flex bg-white p20 mb20">
				<view class="smll text-center fs60 font-w700 p20">{{index}}</view>
				<view class="smll flex_bd ml20 color-33">
					签到获赠积分+{{item.total_integral}}，签到默认获赠积分+{{item.sign_integral}}，连续签到{{item.constant_num}}天获赠积分+{{item.constant_integral}}
				</view>
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
				logList:[],
				showYearMonth:false,
				defaultYearMonth:[],
				yearMonthlist:[],
				yearMonth:''
			}
		},
		onLoad(options) {
			this.app.isLogin(this);//强制登陆
			this.getyearMonth();
		},
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
				this.loadData();
			},
			confirmYearMonth(e){//选择日期
				this.yearMonth = e[0].label+'-'+e[1].label;
				this.defaultYearMonth = [e[0].value,e[1].value];
				this.loadData();
			},
			loadData(){
				this.$u.post('member/api.Center/signLog',{'yearMonth':this.yearMonth}).then(res => {
					this.logList = res.data;
				})
			}
		}
	}
</script>

<style>
</style>
