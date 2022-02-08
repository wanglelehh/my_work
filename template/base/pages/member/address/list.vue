<template>
	<view class="page-body" :class="[app.setCStyle(from)]">
		<u-swipe-action :index="index" v-for="(res, index) in addressList" :key="res.address_id" @click="delAddress" :options="options"
		class="br20 mlr20 mt20"
		>
			<view class="item">
				<view class="top" @tap="checkAddress(res)">
					<view class="name">{{res.consignee}}</view>
					<view class="phone">{{res.mobile}}</view>
					<view class="tag">
						<text v-if="res.is_default" class="bg-base">{{app.langReplace('默认')}}</text>
					</view>
				</view>
				<view class="bottom">
					<view @tap="checkAddress(res)">
						{{res.merger_name}} {{res.address}}
					</view>
					<u-icon name="edit-pen" :size="40" color="#999999" @click="addAddress('edit', res)"></u-icon>
				</view>
			</view>
		</u-swipe-action>
		<view class="h200"></view>
		<button type="warning" shape="circle"  class="bottom-btn fs30 primarybtn"  @tap="addAddress('add')">
			<u-icon name="plus" color="#ffffff" :size="38"></u-icon>
			<text class="ml10">{{app.langReplace('新增地址')}}</text>
		</button>
	</view>
</template>
<script>
	export default {
		data() {
			return {
				source: 0,
				from: '',
				addressList: [],
				options: [{
					text: this.app.langReplace('删除'),
					style: {
						backgroundColor: '#F65236'
					}
				}]
			}
		},
		onLoad(options) {
			this.app.isLogin(this); //强制登陆
			let title = this.app.langReplace('收货地址');
			uni.setNavigationBarTitle({
				title
			})
			this.source = options.source;
			this.from = options.from;
		},
		onShow() {
			this.getAddress();
		},
		methods: {
			//获取地址列表
			getAddress() {
				this.$u.api.getUseraddress().then(res => {
					this.addressList = res.list;
				})
			},
			delAddress(index) {
				let row = this.addressList[index];
				this.$u.post('member/api.address/delete', {
					address_id: row.address_id
				}).then(res => {
					this.addressList.splice(index, 1);
				})
			},
			//选择地址
			checkAddress(item) {
				if (this.source == 1) {
					//this.$api.prePage()获取上一页实例，在App.vue定义
					var pages = getCurrentPages(); //当前页
					var beforePage = pages[pages.length - 2]; //上个页面
					beforePage.$vm.address_id = item.address_id;
					beforePage.$vm.addressData = item;
					uni.setStorageSync("address_id", item.address_id);
					if (beforePage.route == 'pages/shop/flow/checkOut') {
						beforePage.$vm.getAddress();
					}
					if (beforePage.route == 'pagesA/fightgroup/flow/checkOut') {
						beforePage.$vm.getAddress();
					}
					uni.navigateBack();
				}
			},
			addAddress(type, item) {
				let from = this.from;
				uni.navigateTo({
					url: `manage?from=${from}&type=${type}&data=${JSON.stringify(item)}`
				})
			}
		}
	}
</script>

<style lang="scss" scoped>
	.item {
		padding: 30rpx;

		.top {
			display: flex;
			font-weight: bold;
			font-size: 30rpx;

			.phone {
				margin-left: 60rpx;
			}

			.tag {
				display: flex;
				font-weight: normal;
				align-items: center;

				text {
					display: block;
					width: 60rpx;
					height: 34rpx;
					line-height: 34rpx;
					color: #ffffff;
					font-size: 20rpx;
					border-radius: 6rpx;
					text-align: center;
					margin-left: 30rpx;
				}

			}
		}

		.bottom {
			display: flex;
			margin-top: 20rpx;
			font-size: 28rpx;
			justify-content: space-between;
			color: #999999;
		}
	}

	
</style>
