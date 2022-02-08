<template>
	<view class="page-body" :class="[app.setCStyle()]">
		<!-- 空白页 -->
		<view v-if="empty===true" class="empty">
			<image src="/static/public/images/img_01.png" mode="aspectFit"></image>
			<view class="empty-tips">
				{{app.langReplace('哦豁~您的购物车空空如也')}}
			</view>
			<button size="default" class="primarybtn mt60 w100"  type="warning" @click="app.goPage('/pages/shop/goods/index')">{{app.langReplace('前往购物')}}</button>
			
		</view>
		<view v-else>
			<view class="">
				<view class="bg-white p20 fs30 color-99">
					{{app.langReplace('当前购物车共')}}:<text class="base-color">{{allNum}}</text>{{app.langReplace('件')}}，{{app.langReplace('选中')}}:<text class="base-color">{{selectNum}}</text>{{app.langReplace('件')}}
				</view>
				<view v-if="showBonus == true" class="bg-white p20 mt20 fs30" @click="toggleBonusReceive()">
					<text class="fs28">{{app.langReplace('先领券再下单能享受更多优惠哦')}}!</text>
					<view class="fr fs28 base-color" >{{app.langReplace('领券')}}<u-icon name="arrow-right" size="28"></u-icon>
					</view>
				</view>
			</view>
			<!-- 列表 -->
			<view class="cart_list mt20 base-select">
				<u-swipe-action v-for="(item, index) in cartList" :key="item.rec_id" @click="deleteCartItem(index)" :options="options">
					<view class="cart-item" :class="{'b-b': index!==cartList.length-1}">
						<view class="image-wrapper">
							<image :src="baseUrl+item.pic" :class="[item.loaded]" mode="aspectFill" lazy-load @load="onImageLoad('cartList', index)"
							 @error="onImageError('cartList', index)" ></image>
							<u-icon class="checkbox" :class="{current: item.is_select}" @click="check('item', index)" name="checkmark-circle-fill" 
							 size="46"></u-icon>
						</view>
						<view class="item-right">
							<text class="title" @click="app.goPage('/pages/shop/goods/info?goods_id='+item.goods_id)">{{item.goods_name}}</text>
							<view class="attr flex mt10">
								<text class="flex_bd">{{item.sku_name}}</text>
								<text  v-if="item.use_integral > 0" class="base-color">+{{item.use_integral}}{{app.langReplace('积分')}}</text>
							</view>
							<view class="bottom">
								<text class="price ff base-color">{{item.sale_price}}</text>
								<u-number-box class="number-box" :long-press="false" :value="item.goods_number" :index="index" :min="1" :step="1"
								 @change="numberChange"></u-number-box>
							</view>
						</view>
					</view>
				</u-swipe-action>
			</view>
			<!-- 底部菜单栏 -->
			<view class="action-section" :style="{'margin-bottom': app.iphonexBottom()}">
				<view class="checkbox base-select">
					<u-icon :class="{current: allChecked}" @click="check('all')" name="checkmark-circle-fill" size="48"></u-icon>
					<view class="clear-btn" :class="{show: allChecked}" @click="clearCart">
						{{app.langReplace('清空')}}
					</view>
				</view>
				<view class="total-box base-color mt5">
					<text class="price ff">{{total}}</text>
					<text v-if="totalIntegral > 0" class="fs26">+{{totalIntegral}}{{app.langReplace('积分')}}</text>
				</view>
				<button type="primary" class="no-border confirm-btn primarybtn" @click="app.goPage('/pages/shop/flow/checkOut')">{{app.langReplace('去结算')}}</button>
			</view>
		</view>
		
		<bonusReceive  @toggleBonusReceive="toggleBonusReceive" @getBonusListReceivable="getBonusListReceivable" :bonusList="bonusList"  :bonusReceiveClass="bonusReceiveClass" ></bonusReceive>
		<tabbar :now_page="now_page" :getCartNum="0"></tabbar>
	</view>
</template>

<script>
	import bonusReceive from '@/pages/shop/bonus/receive';
	import tabbar from '@/pages/shop/tabbar';
	export default {
		components: {
			tabbar,
			bonusReceive
		},
		data() {
			return {
				now_page:'',
				setting: {},
				baseUrl: this.config.baseUrl,
				allNum: 0,
				selectNum: 0,
				total: 0, //总价格
				totalIntegral: 0, //总积分
				allChecked: false, //全选状态  true|false
				empty: false, //空白页现实  true|false
				cartList: [],
				options: [{
					text: this.app.langReplace('删除'),
					style: {
						backgroundColor: '#F65236'
					}
				}],
				isUpNum: 0,
				upNumList: {},
				showBonus:false,//setting.sys_model_shop_bonus
				bonusReceiveClass: 'none',
				bonusList:[]
			};
		},
		onLoad(options) {
			this.setting = uni.getStorageSync("setting");
			let title = this.app.langReplace('购物车');
			uni.setNavigationBarTitle({
				title
			})
			this.now_page = this.$mp.page.route;
		},
		onShow() {
			this.loadData();
			this.getBonusListReceivable();
		},
		watch: { /* 监听方法  开始*/
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
				this.$u.post('shop/api.flow/getList').then(res => {
					let cartList = [];
					let goodsList = res.data.goodsList;
					Object.keys(goodsList).forEach(function(key) {
						cartList.push(goodsList[key]);
					})
					this.cartList = cartList;
					this.evalNumCount();
					this.calcTotal(); //计算总价
					setInterval(function() {
						that.editNum();
					}, 2000)
				})
			},
			//获取可领取的优惠劵
			async getBonusListReceivable() {
				if (this.setting.sys_model_shop_bonus == 0){
					this.showBonus = false;
					return false;
				}
				let that = this;
				this.$u.post('shop/api.bonus/getBonusListReceivable').then(res => {
					if (res.list.length > 0){
						this.showBonus = true;
						this.bonusList = res.list;
					}
				});
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
					this.$u.post('shop/api.flow/setSel', {
						rec_id: recItem.rec_id,
						is_select: recItem.is_select
					}).then(res => {})
				} else {
					const checked = !this.allChecked
					const list = this.cartList;
					let is_select = checked ? 1 : 0;
					list.forEach(item => {
						item.is_select = is_select;
					})
					this.allChecked = checked;
					this.$u.post('shop/api.flow/setSel', {
						rec_id: 'all',
						is_select: is_select
					}).then(res => {})
				}
				this.calcTotal(type);
				this.evalNumCount();
			},
			//数量
			numberChange(data) {
				if (this.cartList[data.index].goods_number == data.value) {
					return false;
				}
				this.isUpNum = 1;
				let rec_id = this.cartList[data.index].rec_id;
				this.cartList[data.index].goods_number = data.value;
				this.calcTotal();
				this.upNumList[rec_id] = data.value;
			},
			//更新购物车数据
			editNum() {
				if (this.isUpNum == 0) {
					return false;
				}
				this.isUpNum = 0;
				let that = this;
				Object.keys(this.upNumList).forEach(function(key) {
					let num = that.upNumList[key];
					that.$u.post('shop/api.flow/editNum', {
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
				this.$u.post('shop/api.flow/delGoods', {
					rec_id: row.rec_id
				}).then(res => {
					this.cartList.splice(index, 1);
					this.calcTotal();
					this.evalNumCount();
				})
			},
			//清空
			clearCart() {
				uni.showModal({
					content: this.app.langReplace('清空购物车？'),
					confirmText: this.app.langReplace('确认'),
					cancelText: this.app.langReplace('取消'),
					success: (e) => {
						if (e.confirm) {
							this.cartList = [];
							this.$u.post('shop/api.flow/clearCart', {
								purchaseType: this.purchaseType
							}).then(res => {
								this.calcTotal();
							})
						}
					}
				})
			},
			//计算数量
			evalNumCount() {
				let that = this;
				this.allNum = 0;
				this.selectNum = 0;
				this.cartList.forEach(function(item) {
					that.allNum += 1;
					if (item.is_select == 1) {
						that.selectNum += 1;
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
				let totalIntegral = 0;
				let checked = true;
				list.forEach(item => {
					if (item.is_select === 1) {
						total += item.sale_price * item.goods_number;
						totalIntegral += item.use_integral * item.goods_number;
					} else if (checked === true) {
						checked = false;
					}
				})
				this.allChecked = checked;
				this.total = total.toFixed(2);
				this.totalIntegral = totalIntegral.toFixed(2);
			},
			//优惠弹窗开关
			toggleBonusReceive() {
				if (this.bonusReceiveClass === 'show') {
					this.bonusReceiveClass = 'hide';
					setTimeout(() => {
						this.bonusReceiveClass = 'none';
					}, 250);
				} else if (this.bonusReceiveClass === 'none') {
					this.bonusReceiveClass = 'show'
				}
			}
		}
	}
</script>

<style lang='scss'>
	
	/* 购物车列表项 */
	.cart_list {
		padding-bottom: 120rpx;
		z-index: 1;
	}

	.cart-item {
		display: flex;
		position: relative;
		padding: 30rpx;
		padding-right: 20rpx;

		.image-wrapper {
			width: 220rpx;
			height: 220rpx;
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
			left: 5rpx;
			top: 5rpx;
			z-index: 8;
			font-size: 40rpx;
			line-height: 1;
			background-color: #ffffff;
			border-radius: 50px;
		}

		.item-right {
			display: flex;
			flex-direction: column;
			flex: 1;
			overflow: hidden;
			position: relative;
			padding-left: 20rpx;
			height: 230rpx;

			.title,
			.price {
				font-size: $font-base + 4rpx;
				height: 40rpx;
				line-height: 40rpx;
			}

			.title {
				font-size: 28rpx;
				color: $font-color-dark;
				line-height: 40rpx;
				height: 80rpx;
				overflow: hidden;
				text-overflow: ellipsis;
				display: -webkit-box;
				-webkit-line-clamp: 2;
				-webkit-box-orient: vertical;
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
				font-size: 40rpx;
				line-height: 1;
				&:before {
					content: '￥';
					font-size: 24rpx;
				}
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
		/* #endif */
		position: fixed;
		z-index: 95;
		bottom: 98rpx;
		left: 0;
		right: 0;
		display: flex;
		align-items: center;
		height: 98rpx;
		padding: 0 30rpx;
		background: #FFFFFF;

		.checkbox {
			height: 52rpx;
			position: relative;
			display: block;

			.u-icon {
				width: 52rpx;
				height: 100%;
				position: relative;
				z-index: 5;
				background-color: #FFFFFF;
				border-radius: 50%;
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
			padding-right: 20rpx;

			.price {
				font-size: 40rpx;
				line-height: 1;

				&:before {
					content: '￥';
					font-size: 24rpx;
				}
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
			min-width: 160rpx;
			height: 80rpx;
			line-height: 80rpx;
			border-radius: 40rpx;
			font-size: 28rpx;
		}
	}

	.action-section .checkbox ,
	.cart-item .checkbox {
		color: #a2a2a2;
	}

	/* 复选框选中状态 */
	.action-section .checkbox .current,
	.cart-item .checkbox.current {
		padding: 0;
		border-radius: 50%;
	}
</style>
