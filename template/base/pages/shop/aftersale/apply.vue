<template>
	<view v-if="showPage==true" class="page-body" :class="[app.setCStyle()]">
		<view class=" p20 bg-white fs28">
			<view class="flex">
				<image :src="goodsImage" style="width:140rpx;height:140rpx;"></image>
				<view class="flex_bd ml20">
					<view class="flex">
						<view class="goods_name flex_bd">{{orderGoods.goods_name}}</view>
						<view class="ml10">
							<view><text class="fs22">￥</text>{{orderGoods.sale_price}}</view>
							<view class="text-right color-99">x{{orderGoods.goods_number}}</view>
						</view>
					</view>
					<view class="flex mt10">
						<view v-if="orderGoods.sku_id > 0" class="color-cc fs26">{{orderGoods.sku_name}}</view>
					</view>
				</view>
			</view>
			<view class="mt20 flex">
				<view class="flex_bd">{{app.langReplace('申请售后商品数量')}}</view>
				<view>
					<u-number-box class="number-box" :long-press="false" :value="aftersale_number" :min="1" :step="1"
					 :max="orderGoods.return_num" @change="numberChange"></u-number-box>
				</view>
			</view>
		</view>
		<view class="mt20 p20 bg-white">
			<view class="fs30">{{app.langReplace('服务类型')}}</view>
			<view class="flex mt20 fs30 text-center">
				<view class="flex_bd mlr10 b-allcc br20" @click="setAftersaleType(1)" :class="aftersaleType == 1?selectClass:''">{{app.langReplace('退货')}}</view>
				<view class="flex_bd mlr10 b-allcc br20" @click="setAftersaleType(2)" :class="aftersaleType == 2?selectClass:''">{{app.langReplace('换货')}}</view>
			</view>
		</view>
		<view class="mt20 p20 bg-white ">
			<view class="color-99 fs28">* {{app.langReplace('退款金额将扣除相关优惠金额，如果问题请联系客服')}}</view>
			<view class="flex mt10">
				<view class="flex_bd fs28">{{app.langReplace('退款金额')}}</view>
				<view><text class="fs24">￥</text>{{return_money}}</view>
			</view>
		</view>
		<view class="mt20 p20 bg-white flex fs28">
			<view><view style="width:140rpx;">{{app.langReplace('售后原因')}}</view></view>
			<view class="flex_bd">
				<textarea placeholder-style="color:#cccccc;font-size:28rpx;" v-model="return_desc" auto-height  :placeholder="app.langReplace('请描述一下售后原因')"/>
			</view>
		</view>
		<view class="bg-white p20 mt20">
			<view class="fs30">{{app.langReplace('上传图片')}}</view>
			<view class="mt20">
				<u-upload ref="upload" @on-change="uploaded" @on-remove="removeImg" :action="upAction" max-count="6"></u-upload>
			</view>
		</view>
		<view class="p20 mt20">
			<button class="primarybtn mt20" type="warning"  :loading="isLoading" @click="postApply">{{app.langReplace('确认提交')}}</button>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				isLoading:false,
				showPage:false,
				goodsImage: '',
				selectClass:'border-color base-color',
				upAction: this.config.upImageUrl,
				fileList: [],
				rec_id: 0,
				orderGoods:{},
				aftersale_number:1,
				aftersaleType:0,
				return_money:'0.00',
				return_desc:''
			}
		},
		onLoad(options) {
			this.app.isLogin(this);//强制登陆
			let title = this.app.langReplace('申请售后');
			uni.setNavigationBarTitle({
			　　title:title
			})
			let rec_id = parseInt(options.rec_id);
			if (isNaN(rec_id) == true || rec_id < 1) {
				this.app.showModal('传值错误.', -1);
				return false;
			}
			this.rec_id = options.rec_id;
			this.getInfo();
		},
		onShow() {
		},
		methods: {
			getInfo(){
				this.$u.post('shop/api.after_sale/orderGoods', {
					rec_id: this.rec_id,
				}).then(res => {
					this.orderGoods = res.data;
					this.goodsImage = this.config.baseUrl + this.orderGoods.pic;
					this.showPage = true;
				}).catch(res=>{
					this.app.showModal(res.msg, -1);
				})
			},
			setAftersaleType(type){//选择售后类型
				this.aftersaleType = type;
				this.evalReturnMoney();
			},
			//数量
			numberChange(data) {
				this.aftersale_number = data.value;
				this.evalReturnMoney();
			},
			evalReturnMoney(){//计算退款金额
				if (this.aftersaleType == 2){
					this.return_money = '0.00';//换货，退款为0
					return false;
				}
				let sale_price = this.orderGoods.sale_price * this.orderGoods.return_num;
				if (this.aftersale_number == this.orderGoods.return_num){//售后数量达最大时
					this.return_money = sale_price - this.orderGoods.last_bonus_price - this.orderGoods.last_discount;
					return false;
				}
				let bonus_price = this.aftersale_number * this.orderGoods.one_bonus_price;
				let discount_price = this.aftersale_number * this.orderGoods.one_discount;
				this.return_money = sale_price - bonus_price - discount_price;
			},
			uploaded(res, index, lists) {
				var res = JSON.parse(res.data);
				this.fileList.push(res.file);
			},
			removeImg(index, lists) {
				let file = this.fileList[index];
				this.fileList.splice(index, 1);
				this.$u.post(this.config.removeImageUrl, {
					file: file
				}).then(res => {})
			},
			postApply(){
				if (this.isLoading == true) {
					return false;
				}
				if (this.aftersaleType < 1){
					return this.$u.toast('请选择售后类型');
				}
				this.isLoading = true;
				let data = {};
				data.rec_id = this.rec_id;
				data.type = this.aftersaleType;
				data.goods_number = this.aftersale_number;
				data.return_desc = this.return_desc;
				data.fileList = this.fileList.join(',');
				this.$u.post('shop/api.after_sale/add', data).then(res => {
					this.isLoading = false;
					this.app.showModal(res.msg,'index',-1);
				}).catch(res => {
					this.isLoading = false;
				})
			}
		}
	}
</script>

<style>
	.goods_name {
		display: block;
		font-size: 28rpx;
		color: $font-color-dark;
		line-height: 45rpx;
		height: 90rpx;
		overflow: hidden;
		text-overflow: ellipsis;
		display: -webkit-box;
		-webkit-line-clamp: 2;
		-webkit-box-orient: vertical;
	}
</style>
