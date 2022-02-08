<template>
	<view class="page-body" :class="[app.setCStyle()]">
		<view class=" ostatus mt0">
			<!-- 售后状态 -->
			<view v-if="asInfo.status==0" >
				<view class="smll">
					<u-icon name="/static/public/images/icon_pay.png" size="48"></u-icon>
					<text class="fs34 color-ff ml20">{{app.langReplace(asInfo.ostatus)}}</text>
				</view>
				<view class="mt10 color-ff2">{{app.langReplace('售后申请正等待审核.')}}</view>
			</view>
			<view v-else-if="asInfo.status==1" >
				<view class="smll">
					<u-icon name="/static/public/images/icon_wrong.png" size="48"></u-icon>
					<text class="fs34 color-ff ml20">{{app.langReplace(asInfo.ostatus)}}</text>
				</view>
				<view class="mt10 color-ff2">{{app.langReplace('您的售后申请，审核不通过.')}}</view>
			</view>
			<view v-else-if="asInfo.status==2" >
				<view class="smll">
					<u-icon name="/static/public/images/icon_wait.png" size="48"></u-icon>
					<text class="fs34 color-ff ml20">{{app.langReplace(asInfo.ostatus)}}</text>
				</view>
				<view class="mt10 color-ff2">{{app.langReplace('请您把需要售后的商品寄回商家.')}}</view>
			</view>
			<view v-else-if="asInfo.status==3" >
				<view class="smll">
					<u-icon name="/static/public/images/icon_wait.png" size="48"></u-icon>
					<text class="fs34 color-ff ml20">{{app.langReplace(asInfo.ostatus)}}</text>
				</view>
				<view class="mt10 color-ff2">{{app.langReplace('待商家验收售后商品.')}}</view>
			</view>
			<view v-else-if="asInfo.status==9">
				<view class="smll">
					<u-icon name="/static/public/images/icon_right.png" size="48"></u-icon>
					<text class="fs34 color-ff ml20">{{app.langReplace(asInfo.ostatus)}}</text>
				</view>
			</view>
		</view>
		
		<view class=" bg-white p20">
			<!-- 商品列表 -->
			<view class="flex mt20">
				<image :src="goodsInfo.pic" style="width:140rpx;height:140rpx;"></image>
				<view class="flex_bd ml20">
					<view class="flex">
						<view class="goods_name flex_bd">{{goodsInfo.goods_name}}</view>
						<view class="ml10">
							<view><text class="fs22">￥</text>{{goodsInfo.sale_price}}</view>
							<view class="text-right color-99">x{{goodsInfo.goods_number}}</view>
						</view>
					</view>
					<view class="flex mt10">
						<view v-if="goodsInfo.sku_id > 0" class="color-cc fs26">{{goodsInfo.sku_name}}</view>
						<view class="text-right flex_bd">
						</view>
					</view>
				</view>
			</view>
		</view>

		<view class="mt20 p20 fs28 bg-white">
			<view class="flex ">
				<text class="flex_bd">{{app.langReplace('售后编号')}}</text>
				<text class=" ">{{asInfo.as_sn}}</text>
			</view>
			<view class="flex mt50">
				<text class="flex_bd">{{app.langReplace('售后类型')}}</text>
				<text class="base-color">{{app.langReplace(asInfo.astype)}}</text>
			</view>
			<view class="flex mt50">
				<text class="flex_bd">{{app.langReplace('退换数量')}}</text>
				<text class="base-color">{{asInfo.goods_number}}</text>
			</view>
			<view v-if="asInfo.return_money > 0" class="flex mt50">
				<text class="flex_bd">{{app.langReplace('退款金额')}}</text>
				<text class="money-label base-color">{{asInfo.return_money}}</text>
			</view>
			<view class="flex mt50">
				<view>{{app.langReplace('售后原因')}}</view>
				<view class="ml20 flex_bd">
					<rich-text :nodes="asInfo.return_desc"></rich-text>
				</view>
			</view>
			<view class="flex mt50">
				<text class="flex_bd">{{app.langReplace('关联订单')}}</text>
				<view class="color-99" @click="app.goPage('/pages/shop/order/info?order_id='+asInfo.order_id)">{{asInfo.order_sn}}<u-icon name="arrow-right" color="#cccccc" size="28"></u-icon></view>
			</view>
		</view>
		<view class="mt20 p20 fs28 bg-white">
			<view class="fs30 font-w600">{{app.langReplace('上传图片')}}</view>
			<view class="mt20">
				<view v-for="(pic, index) in imgList" :key="index" class="mlr10 fl mt20">
					<image :src="pic"  style="width:210rpx;height:210rpx;"  @click="app.showImg(imgList,index)"></image>
				</view>
				<view class="clearfix"></view>
			</view>
		</view>
		<view v-if="asInfo.status == 1" class="flex mt20 p20 fs28 bg-white">
			<view>{{app.langReplace('售后拒绝')}}</view>
			<view class="flex_bd ml20 ">
				<rich-text :nodes="asInfo.remark"></rich-text>
			</view>
		</view>
		<view v-else-if="asInfo.status >= 2 " class="mt20 p20 fs28 bg-white">
			<view class="fs30 font-w600">{{app.langReplace('请将商品寄回以下地址')}}</view>
			<view class="flex mt20">
				<view>{{app.langReplace('收货人')}}：</view>
				<view class="ml10 flex_bd">
					<rich-text :nodes="returnInfo.return_consignee"></rich-text>
				</view>
			</view>
			<view class="flex mt20">
				<view>{{app.langReplace('联系电话')}}：</view>
				<view class="ml10 flex_bd smll">
					<rich-text :nodes="returnInfo.return_mobile"></rich-text>
				</view>
			</view>
			<view class="flex mt20">
				<view>{{app.langReplace('退货地址')}}：</view>
				<view class="ml10 flex_bd">
					<rich-text :nodes="returnInfo.return_address"></rich-text>
				</view>
			</view>
			<view class="bg-fa color-99 mt20 p20">
				{{returnInfo.return_desc}}
			</view>
			<view v-if="asInfo.status == 2">
				<view class="flex mt20">
					<view>{{app.langReplace('选择快递')}}：</view>
					<view class="ml10 flex_bd smll" @click="shippingShow = true">
						{{shippingName}}
						<u-select v-model="shippingShow" :list="shippingList" :default-value="d_shipping_id" @confirm="confirmShipping"></u-select>
					</view>
				</view>
				<view class="flex mt20 smll">
					<view>{{app.langReplace('快递单号')}}：</view>
					<view class="ml10 flex_bd">
						<u-input v-model="shipping_no" type="text" :placeholder="app.langReplace('请输入快递单号')" />
					</view>
				</view>
				<button class="primarybtn mt20" type="warning"  :loading="isLoading" @click="postShipping">{{app.langReplace('确认提交')}}</button>
			</view>
			<view v-else-if="asInfo.status >= 3">
				<view class="flex mt20">
					<view>{{app.langReplace('快递公司')}}：</view>
					<view class="ml10 flex_bd smll" >
						{{asInfo.shipping_name}}
					</view>
				</view>
				<view class="flex mt20 smll">
					<view>{{app.langReplace('快递单号')}}：</view>
					<view class="ml10 flex_bd smll">
						{{asInfo.shipping_no}}
					</view>
				</view>
			</view>
		</view>
		<view class="mt20 p20 bg-white h100">
			<button v-if="asInfo.status == 1" class="fr action-btn recom" @click="app.goPage('apply?rec_id='+asInfo.rec_id)">{{app.langReplace('重新申请')}}</button>
		</view>
		<view class="h50"></view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				setting: {},
				as_id: 0,
				asInfo: {},
				goodsInfo:{},
				returnInfo:{},
				remark: '',
				imgList: [],
				shippingShow:false,
				shippingList:[],
				shipping_id:0,
				d_shipping_id:[0],
				shippingName:this.app.langReplace('请选择寄回快递'),
				shipping_no:'',
				isLoading:false
			}
		},
		onLoad(options) {
			this.app.isLogin(this);//强制登陆
			let title = this.app.langReplace('售后详情');
			uni.setNavigationBarTitle({
			　　title:title
			})
			let as_id = parseInt(options.as_id);
			if (isNaN(as_id) == true || as_id < 1) {
				this.app.showModal(this.app.langReplace('ID传值错误'), -1);
				return false;
			}
			this.as_id = as_id;
			this.setting = uni.getStorageSync("setting");
			this.getAsInfo();
		},
		onShow() {
		},
		methods: {
			getAsInfo() {
				let that = this;
				this.$u.post('shop/api.after_sale/info', {
					id: this.as_id
				}).then(res => {
					this.goodsInfo = res.data.goods;
					this.returnInfo = res.data.returnInfo;
					delete res.data.goods;
					delete res.data.returnInfo;
					this.asInfo = res.data;
					this.goodsInfo.pic = this.config.baseUrl + this.goodsInfo.pic;
					this.imgList = [];
					this.asInfo.imgs.forEach(url => {
						that.imgList.push(that.config.baseUrl + url);
					})
					if (this.asInfo.status == 2){
						this.asInfo.shippingList.forEach(item => {
							let arr = {};
							arr.value = item.shipping_id;
							arr.label = item.shipping_name;
							that.shippingList.push(arr);
						})
					}
				}).catch(res => {
				})
			},
			confirmShipping(e){
				let that = this;
				this.shippingName = e[0].label;
				this.shipping_id = e[0].value;
				this.shippingList.forEach(function(item,key){
					if (item.value == that.shipping_id){
						that.d_shipping_id = [key];
					}
				})
			},
			postShipping(){
				if (this.isLoading == true) {
					return false;
				}
				if (this.shipping_id < 1){
					return this.$u.toast(this.app.langReplace('请选择寄回快递'));
				}
				if (this.shipping_no == ''){
					return this.$u.toast(this.app.langReplace('请输入快递单号'));
				}
				if (this.shipping_no.length < 8){
				    return this.$u.toast(this.app.langReplace('请正确的快递单号'));
				}
				this.isLoading = true;
				let data = {};
				data.shipping_id = this.shipping_id;
				data.shipping_no = this.shipping_no;
				data.as_id = this.as_id;
				this.$u.post('shop/api.after_sale/shipping', data).then(res => {
					this.isLoading = false;
					this.getAsInfo();
				}).catch(res => {
					this.isLoading = false;
				})
			}
		}
	}
</script>

<style lang="scss">
	page {
		background: $page-color-base;
		padding-bottom: 100rpx;
	}

	.ostatus {
		padding: 50rpx 30rpx;
		color: #ffffff;
		background: linear-gradient(131deg, #FF4261 0%, #FE6584 98%);
		background-size: 100%;
	}

	
	
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
