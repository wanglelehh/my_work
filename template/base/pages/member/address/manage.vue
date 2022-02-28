<template>
	<view class="page-body" :class="[app.setCStyle(from)]">
		<view class="top">
			<view class="item mlr20 br20 mt20">
				<view class="left">{{app.langReplace('收货人')}}</view>
				<input type="text" v-model="addressData.consignee" placeholder-class="line" :placeholder="app.langReplace('请填写收货人姓名')" />
			</view>
			<view class="item mlr20 br20 mt20">
				<view class="left">{{app.langReplace('手机号码')}}</view>
				<input type="number" v-model="addressData.mobile" placeholder-class="line" :placeholder="app.langReplace('请填写收货人手机号')" />
			</view>
			<view class="item mlr20 br20 mt20" @tap="region_show = true;">
				<view class="left">{{app.langReplace('所在地区')}}</view>
				<input disabled type="text" v-model="addressData.merger_name" placeholder-class="line" :placeholder="app.langReplace('省市区县')" />
				<text class="right-arrow"></text>
			</view>
			<view class="item address mlr20 br20 mt20">
				<view class="left">{{app.langReplace('详细地址')}}</view>
				<textarea type="text" v-model="addressData.address" placeholder-class="line" :placeholder="app.langReplace('详细地址：如道路、小区、栋楼号等')" />
			</view>
			<view class="isdefault mlr20 bg-white p20 br20 mt20">
				<view class="left">
					<view class="set">{{app.langReplace('设置默认地址')}}</view>
					<view class="tips">{{app.langReplace('每次下单会默认推荐该地址')}}</view>
				</view>
				<view class="right">
					<u-switch  v-model="addressData.is_default" active-color="#fc4a5b"></u-switch>
				</view>
			</view>
			<view v-if="addressData.address_id < 1" class="item mt50">
				<textarea class=" h150" type="text" v-model="distinguishAddress" placeholder-class="line" :placeholder="app.langReplace('贴贴地址信息,进行识别')" />
			    <u-button size="mini" type="warning" @click="runDistinguishAddress">{{app.langReplace('识别')}}</u-button>
			</view>
		</view>
		<u-toast ref="uToast" />
		<u-picker mode="region" ref="uPicker" v-model="region_show" :area-code="region_code" @confirm="confirmRegion"/>
		<button type="warning" shape="circle"  class="bottom-btn primarybtn"   @click="submit">
			{{app.langReplace('提交')}}
		</button>
	</view>
</template>
<script>
	export default {
		data() {
			return {
				from: '',
				region_code:[],
				addressData: {
					address_id:0,
					is_default:false
				},
				region_show:false,
				isLoading:false,
				distinguishAddress:''
			}
		},
		onLoad(options) {
			this.app.isLogin(this);//强制登陆
			this.from = options.from;
			
			let title = '新增地址';
			if (options.type === 'edit') {
				title = '编辑地址';
				this.manageType='edit';
				this.addressData = JSON.parse(options.data);
				this.region_code = [this.addressData.province, this.addressData.city, this.addressData.district];
				this.addressData.regionIds = this.addressData.province+','+this.addressData.city+','+this.addressData.district;
				if (this.addressData.is_default == 1){
					this.addressData.is_default = true;
				}else{
					this.addressData.is_default = false;
				}
			}
			title = this.app.langReplace(title);
			uni.setNavigationBarTitle({
				title
			})
			
		},
		methods: {
			switchChange(e) {
				this.addressData.is_default = e.detail;
			},
			confirmRegion(e){
				this.addressData.merger_name = e.province.label + ',' + e.city.label + ',' + e.area.label;
				this.addressData.region_code = [e.province.value, e.city.value, e.area.value];
				this.addressData.regionIds = e.province.value + ',' + e.city.value + ',' + e.area.value;
			},
			//识别地址
			runDistinguishAddress(){
				if (!this.distinguishAddress) {
					this.$refs.uToast.show({
						title: this.app.langReplace('请贴贴需要识别的地址信息'),
						type: 'error',
						position:'top'
					});
					return;
				}
				
				let data = {};
				data.address = this.distinguishAddress;
				this.isLoading  = true;
				this.$u.post('member/api.address/DistinguishAddress', data).then(res => {
					this.isLoading = false;
					if (res.code == 0){
						return false;
					}
					this.addressData.province = 0;
					let province_name = '';
					let city_name = '';
					let district_name = '';
					if (res.data.province.region_id > 0){
						this.addressData.province = res.data.province.region_id;
						province_name = res.data.province.region_name;
					}
					this.addressData.city = 0;
					if (res.data.city.region_id > 0){
						this.addressData.city = res.data.city.region_id;
						city_name = res.data.city.region_name;
					}
					this.addressData.district = 0;
					if (res.data.district.region_id > 0){
						this.addressData.district = res.data.district.region_id;
						district_name = res.data.district.region_name;
					}
					this.region_code = [this.addressData.province, this.addressData.city, this.addressData.district];
					this.addressData.regionIds = this.addressData.province+','+this.addressData.city+','+this.addressData.district;
					this.addressData.merger_name = province_name + ',' + city_name + ',' + district_name;
					
					this.addressData.consignee = res.data.name;
					this.addressData.mobile = res.data.mobile;
					this.addressData.address = res.data.info;
				}).catch(res=>{
					this.isLoading = false;
				})
			},
			//提交
			submit() {
				if (this.isLoading == true){
					return false;
				}
				let data = this.addressData;
				if (!data.consignee) {
					this.$refs.uToast.show({
						title: this.app.langReplace('请填写收货人姓名'),
						type: 'error',
						position:'top'
					});
					return;
				}
				if (!/(^1[0-9]{10}$)/.test(data.mobile)) {
					this.$refs.uToast.show({
						title: this.app.langReplace('请输入正确的手机号码'),
						type: 'error',
						position:'top'
					});
					return;
				}
				if (!data.address) {
					this.$refs.uToast.show({
						title: this.app.langReplace('请输入详细地址'),
						type: 'error',
						position:'top'
					});
					return;
				}
				this.isLoading  = true;
				var pages = getCurrentPages(); //当前页
				var beforePage = pages[pages.length - 2]; //上个页面
				console.log(beforePage);
				var url = this.manageType=='edit' ? 'member/api.address/edit' : 'member/api.address/add';
				this.$u.post(url, data).then(res => {
					this.isLoading = false;
					let title = (this.manageType=='edit' ? '修改': '添加')+'成功';
					title = this.app.langReplace(title);
					this.$refs.uToast.show({
						title: title,
						type: 'success',
						position:'top'
					})
					uni.setStorageSync("address_id", res.data.address_id);
					setTimeout(() => {
						// #ifdef  MP-WEIXIN
						beforePage.$vm.getAddress();
						// #endif
						// #ifdef  APP-PLUS || H5
						beforePage.getAddress();
						// #endif
						uni.navigateBack();
					}, 800)
				}).catch(res=>{
					this.isLoading = false;
				})
			},
		}
	}
</script>

<style lang="scss" scoped>
/deep/ .line {
	color: $u-light-color;
	font-size: 28rpx;
}
.page-body {
	.top {
		.item {
			padding: 0rpx 20rpx;
			background-color: #ffffff;
			display: flex;
			font-size: 28rpx;
			line-height: 100rpx;
			align-items: center;
			border-bottom: solid 2rpx $border-color-light;
			position: relative;
			.left {
				width: 200rpx;
			}
			input {
				flex: 1;
				text-align: left;
				font-size: 28rpx;
			}
		}
		.right-arrow{
			display: inline-block;
			width: 16rpx;
			height: 16rpx;
			border-top: 1rpx solid #666666;
			border-right: 1rpx solid #666666;
			transform: rotate(45deg);
			position: absolute;
			right: 20rpx;
		}
		.address {
			textarea {
				height: 100rpx;
				line-height: 50rpx;
				padding: 10rpx 40rpx;
				font-size: 28rpx;
			}
		}
		.site-clipboard {
			padding-right: 40rpx;
			textarea {
				// width: 100%;
				height: 150rpx;
				background-color: #f7f7f7;
				line-height: 60rpx;
				margin: 40rpx auto;
				padding: 20rpx;
			}
			.clipboard {
				display: flex;
				justify-content: center;
				align-items: center;
				font-size: 26rpx;
				color: $u-tips-color;
				height: 80rpx;
				.icon {
					margin-top: 6rpx;
					margin-left: 10rpx;
				}
			}
		}
		.isdefault {
			display: flex;
			justify-content: space-between;
			.tips {
				font-size: 24rpx;
				color: #999999;
			}
			.right {
			}
		}
	}
	
}

</style>
