<template>
	<view class="page-body" :class="[app.setCStyle()]">
		<view class="navbar base-select">
			<view v-for="(item, index) in navList" :key="index" class="nav-item" :class="{current: tabCurrentIndex === index}"
			 @click="tabClick(index)">
				{{item.text}}
				<text class="fs28 ">({{item.num}})</text>
			</view>
		</view>

		<swiper :current="tabCurrentIndex" class="swiper-box" duration="300" @change="changeTab">
			<swiper-item class="tab-content" v-for="(tabItem,tabIndex) in navList" :key="tabIndex">
				<scroll-view class="list-scroll-content  " scroll-y >
					<!-- 空白页 -->
					<empty v-if="tabItem.bonusList.length === 0"></empty>
					<view v-else class="bonus_list p20">
						<!-- 列表 -->
						<view v-for="(item,index) in tabItem.bonusList" :key="index" class="item flex mt20" :class="tabIndex > 0 ? 'color-cc' : ''">
							<view class="left">
								<view class="fs32 font-w700" :class="tabIndex > 0 ? 'color-cc' : 'color-00'">{{item.bonus.type_name}}</view>
								<view class="fs26 mt5">{{app.langReplace('满')}}￥{{item.bonus.min_amount}}{{app.langReplace('可用')}}</view>
								<view class="fs26 mt5">{{item.bonus._use_start_date}}-{{item.bonus._use_end_date}}</view>
							</view>
							<view class="center"></view>
							<view class="right flex_bd smll ">
								<view class="w100 text-center">
									<view class="fs60 font-w700 "><text class="fs36">￥</text>{{item.bonus.type_money}}</view>
									<text v-if="item.status == 0" class="receive_btn fs22" @click="app.goPage('glist?type_id='+item.type_id)">{{app.langReplace('去使用')}}</text>
									<text v-else-if="item.status == 4" class="receive_btn fs22">{{app.langReplace('未到使用时间')}}</text>
								</view>
							</view>
						</view>
					</view>
				</scroll-view>
			</swiper-item>
		</swiper>
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
				tabCurrentIndex: 0,
				navList: [{
						type: 'unused',
						text: this.app.langReplace('待用'),
						bonusList: [],
						num:0,
					},
					{
						type: 'expired',
						text: this.app.langReplace('已失效'),
						bonusList: [],
						num:0,
					},
					{
						type: 'used',
						text: this.app.langReplace('已使用'),
						bonusList: [],
						num:0,
					}
				],
			};
		},
		onLoad(options) {
			this.app.isLogin(this);//强制登陆
			let title = this.app.langReplace('我的优惠券');
			uni.setNavigationBarTitle({
				title
			})
			this.loadData();
		},
		methods: {
			//获取优惠券列表
			loadData() {
				this.$u.post('shop/api.bonus/getList').then(res => {
					this.navList[0].bonusList = this.navList[0].bonusList.concat(res.data.unused);
					this.navList[0].num = res.data.unusedNum;
					this.navList[1].bonusList = this.navList[1].bonusList.concat(res.data.expired);
					this.navList[1].num = res.data.expiredNum;
					this.navList[2].bonusList = this.navList[2].bonusList.concat(res.data.used);
					this.navList[2].num = res.data.usedNum;
					this.$forceUpdate();//刷新数据
				})
			},

			//swiper 切换
			changeTab(e) {
				this.tabCurrentIndex = e.target.current;
			},
			//顶部tab点击
			tabClick(index) {
				this.tabCurrentIndex = index;
			},
			
		},
	}
</script>

<style lang="scss">
	page,
	.page-body {
		background: #FFFFFF;
		height: 100%;
	}
	.navbar{
		.nav-item{
			position: relative;
			color: #999999;
			&.current{
				&:after{
					border-bottom: 4rpx solid ;
				}
			}	
			
		}
	}
	.swiper-box {
		height: calc(100% - 40px);
	}

	.list-scroll-content {
		height: 100%;
	}
	
</style>
