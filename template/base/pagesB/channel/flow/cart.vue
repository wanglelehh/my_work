<template>
	<view class="page-body">
		<!-- 空白页 -->
		<view v-if="empty===true" class="empty">
			<image src="/static/public/images/img_01.png" mode="aspectFit"></image>
			<view class="empty-tips">
				空空如也
			</view>
		</view>
		<view v-else>
			<!-- 列表 -->
			<view class="cart-list">
				<u-swipe-action v-for="(item, index) in cartList" :key="item.rec_id" @click="deleteCartItem(index)" :options="options">
					<view class="cart-item" :class="{'b-b': index!==cartList.length-1}">
						<view class="image-wrapper">
							<image :src="baseUrl+item.pic" :class="[item.loaded]" mode="aspectFill" lazy-load @load="onImageLoad('cartList', index)"
							 @error="onImageError('cartList', index)"></image>
							<u-icon class="checkbox" :class="{checked: item.is_select}" @click="check('item', index)" name="checkmark-circle-fill"
							 size="48"></u-icon>

						</view>
						<view class="item-right">
							<text class=" title">{{item.goods_name}}</text>
							<text class="attr">{{item.sku_name}}</text>
							<view class="bottom">
								<text class="price">¥{{item.sale_price}}</text>
								<text class="unit">{{item.goods_unit_name}}</text>
								<u-number-box class="number-box" :long-press="false"  :value="item.goods_number" :index="index"
								 :min="1" :step="1" @change="numberChange"></u-number-box>
							</view>
						</view>
					</view>
				</u-swipe-action>
			</view>
			<!-- 底部菜单栏 -->
			<view class="action-section">
				<view class="checkbox">
					<u-icon :class="{checked: allChecked}" @click="check('all')" name="checkmark-circle-fill" size="62"></u-icon>
					<view class="clear-btn" :class="{show: allChecked}" @click="clearCart">
						清空
					</view>
				</view>
				<view class="total-box">
					<text class="price">¥{{total}}</text>
				</view>
				<button type="primary" class="no-border confirm-btn" @click="createOrder">去结算</button>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		components: {},
		data() {
			return {
				baseUrl: this.config.baseUrl,
				total: 0, //总价格
				allChecked: false, //全选状态  true|false
				empty: false, //空白页现实  true|false
				cartList: [],
				purchaseType: 0, //进货类型
				options: [{
					text: '删除',
					style: {
						backgroundColor: '#dd524d'
					}
				}],
				isUpNum:0,
				upNumList:{}
			};
		},
		onLoad(options) {
			if (options.purchaseType < 1 || options.purchaseType > 2) {
				uni.showModal({
					title: '提示',
					content: '进货类型错误.',
					showCancel: false,
					success: function(res) {
						if (res.confirm) {
							uni.switchTab({
								url: '/pagesB/channel/product/selectAdd'
							});
						}
					}
				});
				return false;
			}
			this.purchaseType = options.purchaseType;
		
		},
		onShow() {
			this.loadData();
		},
		watch: {/* 监听方法  开始*/
			//显示空白页
			cartList(e) {
				let empty = e.length === 0 ? true : false;
				if (this.empty !== empty) {
					this.empty = empty;
				}
			}
		},
		computed: {},
		methods: {
			//请求数据
			async loadData() {
				let that = this;
				this.$u.post('channel/api.flow/getCartList', {
					purchaseType: this.purchaseType
				}).then(res => {
					this.cartList = res.data.cartList;
					this.calcTotal(); //计算总价
					setInterval(function() {
						that.editNum();
					},2000)
				})
			},
			//监听image加载完成
			onImageLoad(key, index) {
				this.$set(this[key][index], 'loaded', 'loaded');
			},
			//监听image加载失败
			onImageError(key, index) {
				this[key][index].image = '/static/public/images/errorImage.jpg';
			},
			//选中状态处理
			check(type, index) {
				if (type === 'item') {
					let recItem = this.cartList[index];
					recItem.is_select = recItem.is_select == 1 ? 0 : 1;
					this.$u.post('channel/api.flow/setSel',{rec_id:recItem.rec_id,is_select:recItem.is_select}).then(res => {})
				} else {
					const checked = !this.allChecked
					const list = this.cartList;
					list.forEach(item => {
						item.is_select = checked ? 1 : 0;
					})
					this.allChecked = checked;
				}
				this.calcTotal(type);
			},
			//数量
			numberChange(data) {
				if (this.cartList[data.index].goods_number == data.value){
					return false;
				}
				this.isUpNum = 1;
				let rec_id = this.cartList[data.index].rec_id;
				this.cartList[data.index].goods_number = data.value;
				this.calcTotal();
				this.upNumList[rec_id] = data.value;
			},
			//更新购物车数据
			editNum(){
				if (this.isUpNum == 0){
					return false;
				}
				this.isUpNum = 0;
				let that = this;
				Object.keys(this.upNumList).forEach(function(key) {
					let num = that.upNumList[key];
					that.$u.post('channel/api.flow/editNum', {
						rec_id: key,
						num: num
					});
					delete that.upNumList[key];
				})
				
			},
			//删除
			deleteCartItem(index) {
				let list = this.cartList;
				let row = list[index];
				this.$u.post('channel/api.flow/delGoods', {
					rec_id: row.rec_id
				}).then(res => {
					this.cartList.splice(index, 1);
					this.calcTotal();
				})
			},
			//清空
			clearCart() {
				uni.showModal({
					content: '清空购物车？',
					success: (e) => {
						if (e.confirm) {
							this.cartList = [];
							this.$u.post('channel/api.flow/clearCart', {
								purchaseType: this.purchaseType
							}).then(res => {
								this.calcTotal();
							})
						}
					}
				})
			},
			//计算总价
			calcTotal() {
				let list = this.cartList;
				if (list.length === 0) {
					this.empty = true;
					return;
				}
				let total = 0;
				let checked = true;
				list.forEach(item => {
					if (item.is_select === 1) {
						total += item.sale_price * (item.goods_number * item.convert_unit_val);
					} else if (checked === true) {
						checked = false;
					}
				})
				this.allChecked = checked;
				this.total = total.toFixed(2);
			},
			//创建订单
			createOrder() {
				uni.navigateTo({
					url: `/pagesB/channel/flow/checkOut?purchaseType=${this.purchaseType}`
				})
			}
		}
	}
</script>

<style lang='scss'>
	.container {
		padding-bottom: 134rpx;

		/* 空白页 */
		.empty {
			position: fixed;
			left: 0;
			top: 0;
			width: 100%;
			height: 100vh;
			padding-bottom: 100rpx;
			display: flex;
			justify-content: center;
			flex-direction: column;
			align-items: center;
			background: #fff;

			image {
				width: 240rpx;
				height: 160rpx;
				margin-bottom: 30rpx;
			}

			.empty-tips {
				display: flex;
				font-size: $font-sm+2rpx;
				color: $font-color-disabled;

				.navigator {
					color: $uni-color-primary;
					margin-left: 16rpx;
				}
			}
		}
	}

	/* 购物车列表项 */
	.cart-item {
		display: flex;
		position: relative;
		padding: 30rpx 40rpx;
		padding-right: 20rpx;

		.image-wrapper {
			width: 230rpx;
			height: 230rpx;
			flex-shrink: 0;
			position: relative;

			image {
				width: 100%;
				height: 100%;
				border-radius: 8rpx;
			}
		}

		.checkbox {
			position: absolute;
			left: -16rpx;
			top: -16rpx;
			z-index: 8;
			font-size: 44rpx;
			line-height: 1;
			padding: 4rpx;
			color: $font-color-disabled;
			background: #fff;
			border-radius: 50px;
		}

		.item-right {
			display: flex;
			flex-direction: column;
			flex: 1;
			overflow: hidden;
			position: relative;
			padding-left: 30rpx;
			height: 230rpx;

			.title,
			.price {
				font-size: $font-base + 2rpx;
				color: $font-color-dark;
				height: 40rpx;
				line-height: 40rpx;
			}

			.title {
				max-height: 120rpx;
				height: auto;
				overflow: hidden;
			}

			.attr {
				font-size: $font-sm + 2rpx;
				color: $font-color-light;
				height: 50rpx;
				line-height: 50rpx;
			}

			.bottom {
				position: absolute;
				bottom: 0px;
				width: 430rpx;

				.number-box,
				.unit {
					float: right;
				}

				.unit {
					line-height: 50rpx;
					margin-left: 10rpx;
				}
			}

			.price {
				height: 50rpx;
				line-height: 50rpx;
				color: $font-color-base;
			}
		}

		.del-btn {
			padding: 4rpx 10rpx;
			font-size: 34rpx;
			height: 50rpx;
			color: $font-color-light;
		}
	}

	/* 底部栏 */
	.action-section {
		/* #ifdef H5 */
		margin-bottom: 100rpx;
		/* #endif */
		position: fixed;
		left: 30rpx;
		bottom: 30rpx;
		z-index: 95;
		display: flex;
		align-items: center;
		width: 690rpx;
		height: 100rpx;
		padding: 0 30rpx;
		background: rgba(255, 255, 255, .9);
		box-shadow: 0 0 20rpx 0 rgba(0, 0, 0, .5);
		border-radius: 16rpx;

		.checkbox {
			height: 52rpx;
			position: relative;

			.u-icon {
				width: 52rpx;
				height: 100%;
				position: relative;
				z-index: 5;
			}
		}

		.clear-btn {
			position: absolute;
			left: 26rpx;
			top: 0;
			z-index: 4;
			width: 0;
			height: 52rpx;
			line-height: 52rpx;
			padding-left: 38rpx;
			font-size: $font-base;
			color: #fff;
			background: $font-color-disabled;
			border-radius: 0 50px 50px 0;
			opacity: 0;
			transition: .2s;

			&.show {
				opacity: 1;
				width: 120rpx;
			}
		}

		.total-box {
			flex: 1;
			display: flex;
			flex-direction: column;
			text-align: right;
			padding-right: 40rpx;

			.price {
				font-size: $font-lg;
				color: $font-color-dark;
			}

			.coupon {
				font-size: $font-sm;
				color: $font-color-light;

				text {
					color: $font-color-dark;
				}
			}
		}

		.confirm-btn {
			padding: 0 38rpx;
			margin: 0;
			border-radius: 100px;
			height: 76rpx;
			line-height: 76rpx;
			font-size: $font-base + 2rpx;
			background: $uni-color-primary;
			box-shadow: 1px 2px 5px rgba(59, 123, 246, 0.75);
		}
	}

	/* 复选框选中状态 */
	.action-section .checkbox .checked,
	.cart-item .checkbox.checked {
		color: $uni-color-primary;
		background-color: #FFFFFF;
		border-radius: 50%;
	}
</style>
