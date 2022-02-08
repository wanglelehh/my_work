<template>
	<view class="page-body" :class="[app.setCStyle(from)]">
		<u-form :model="model" ref="uForm" :errorType="errorType">
		<view  v-if="app.isMPTextModel() == true" class="pbox text-center lh80 mt80">
			正在开发中...
		</view>
		<view v-else class="pbox">
			<view v-if="setting.member_receive_wxpay == 1" class="p30 bg-white mt20 relative br20">
				<u-image class="pay_icon" width="60rpx" height="60rpx" src="/static/public/images/pay/weixin.png">
					<u-loading slot="loading"></u-loading>
					<view slot="error">加载失败</view>
				</u-image>
				<view class="info_box">
						<u-form-item class="fs28" label="微信收款" label-width="350">
								<u-switch style="position: absolute; top:20%; right: 0rpx;" active-color="#fe0079" v-model="model.weixin_usd"></u-switch>
						</u-form-item>
						<view v-if="model.weixin_usd == 1">
							<u-form-item label="微信昵称" prop="weixin_name" label-width="150">
								<u-input :border="border" placeholder="请输入微信昵称" v-model="model.weixin_name" type="text"></u-input>
							</u-form-item>
							<u-form-item label="微信号" prop="weixin_account" label-width="150">
								<u-input :border="border" placeholder="请输入微信号" v-model="model.weixin_account" type="text"></u-input>
							</u-form-item>
						</view>
				</view>
			</view>
			<view v-if="setting.member_receive_alipay == 1" class="p30 bg-white mt20 relative br20">
				<u-image class="pay_icon" width="60rpx" height="60rpx" src="/static/public/images/pay/alipay.png" >
					<u-loading slot="loading"></u-loading>
					<view slot="error">加载失败</view>
				</u-image>
				<view class="info_box" >
						<u-form-item class="fs28" label="支付宝收款" label-width="350">
								<u-switch  style="position: absolute; top:20%; right: 0rpx;" active-color="#fe0079" v-model="model.alipay_usd"></u-switch>
						</u-form-item>
						<view v-if="model.alipay_usd == 1">
							<u-form-item label="帐号姓名" prop="alipay_name" label-width="150">
								<u-input :border="border" placeholder="请输入支付宝帐号姓名" v-model="model.alipay_name" type="text"></u-input>
							</u-form-item>
							<u-form-item label="支付宝帐号" prop="alipay_account" label-width="150">
								<u-input :border="border" placeholder="请输入支付宝帐号" v-model="model.alipay_account" type="text"></u-input>
							</u-form-item>
						</view>
				</view>
			</view>
			<view v-if="setting.member_receive_bank == 1" class="p30 bg-white mt20 relative br20">
				<u-image class="pay_icon" width="60rpx" height="60rpx" src="/static/public/images/pay/bank.png" >
					<u-loading slot="loading"></u-loading>
					<view slot="error">加载失败</view>
				</u-image>
				<view class="info_box" >
						<u-form-item class="fs32" label="银行卡收款" label-width="350">
								<u-switch  style="position: absolute; top:20%; right: 0rpx;" active-color="#fe0079" v-model="model.bank_usd"></u-switch>
						</u-form-item>
						<view v-if="model.bank_usd == 1">
							<u-form-item label="开户银行" prop="bank_name" label-width="150">
								<u-input :border="border" placeholder="请输入开户银行" v-model="model.bank_name" type="text"></u-input>
							</u-form-item>
							<u-form-item label="所属省市"  prop="bankRegionText" label-width="150">
								<u-input :border="border" type="select" :select-open="showRegion" v-model="model.bankRegionText" placeholder="请选择地区" @click="showRegion = true"></u-input>
							</u-form-item>
							<u-picker
								mode="region"
								:params="picker_params"
								v-model="showRegion"
								:area-code="region_code"
								@confirm="confirmRegion"
							></u-picker>
							<u-form-item label="开户支行" prop="bank_subbranch" label-width="150">
								<u-input :border="border" placeholder="请输入开户支行" v-model="model.bank_subbranch" type="text"></u-input>
							</u-form-item>
							<u-form-item label="银行卡号" prop="bank_card_number" label-width="150">
								<u-input :border="border" placeholder="请输入银行卡号" v-model="model.bank_card_number" type="number"></u-input>
							</u-form-item>
							<u-form-item label="持卡人名" prop="bank_user_name" label-width="150">
								<u-input :border="border" placeholder="请输入持卡人名" v-model="model.bank_user_name" type="text"></u-input>
							</u-form-item>
							<u-form-item label="身份证" prop="bank_idcard_munber" label-width="150">
								<u-input :border="border" placeholder="请输入身份证" v-model="model.bank_idcard_munber" type="text"></u-input>
							</u-form-item>
						</view>
				</view>
			</view>
			<view class="p20 mt40">
				<button size="default" shape="circle"  type="warning" class="primarybtn" @click="submit">保存</button>
			</view>
		</view>
		
		<view  class="h50"></view>
		</u-form>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				setting: uni.getStorageSync("setting"),
				from: '',
				source:'',
				picker_params:{
					province: true,
					city: true,
					area:false
				},
				region_code:[],
				upAction:this.config.upImageUrl,
				alipayFileList:[],
				weixinFileList:[],
				showRegion:false,
				model: {
					weixin_usd: false,
					weixin_name:'',
					weixin_account:'',
					weixin_qrcode:'',
					edit_weixin_qrcode:0,
					alipay_usd: false,
					alipay_name:'',
					alipay_account:'',
					alipay_qrcode:'',
					edit_alipay_qrcode:0,
					bank_usd: false,
					bank_name:'',
					bank_province:'',
					bank_city:'',
					bank_subbranch:'',
					bank_card_number:'',
					bank_user_name:'',
					bank_idcard_munber:'',
					bankRegionText:''
				},
				border: false,
				errorType: ['toast'],
				weixin_rules:{
					weixin_name: [{
							required: true,
							message: '请输入微信昵称.',
							trigger: ['change', 'blur'],
						}
					],
					weixin_account: [{
							required: true,
							message: '请输入微信号.',
							trigger: ['change', 'blur'],
						}
					]
				},
				alipay_rules:{
					alipay_name: [{
							required: true,
							message: '请输入支付宝帐号姓名.',
							trigger: ['change', 'blur'],
						}
					],
					alipay_account: [{
							required: true,
							message: '请输入支付宝帐号.',
							trigger: ['change', 'blur'],
						}
					]
				},
				bank_rules:{
					bank_name: [{
							required: true,
							message: '请输入开户银行.',
							trigger: ['change', 'blur'],
						}
					],
					bankRegionText: [{
							required: true,
							message: '请输入所属省市.',
							trigger: ['change', 'blur'],
						}
					],
					bank_subbranch: [{
							required: true,
							message: '请输入开户支行.',
							trigger: ['change', 'blur'],
						}
					],
					bank_card_number: [{
							required: true,
							message: '请输入银行卡号.',
							trigger: ['change', 'blur'],
						},
						{
							min: 8,
							message: '银行卡号长度不能少于13位',
							trigger: 'change',
						}
					],
					bank_user_name: [{
							required: true,
							message: '请输入持卡人名',
							trigger: 'blur',
						},
						{
							min: 2,
							max: 15,
							message: '持卡人名长度在2到15个字符',
							trigger: ['change', 'blur'],
						},
						{
							// 此为同步验证，可以直接返回true或者false，如果是异步验证，稍微不同，见下方说明
							validator: (rule, value, callback) => {
								// 调用uView自带的js验证规则，详见：https://www.uviewui.com/js/test.html
								return this.$u.test.chinese(value);
							},
							message: '姓名必须为中文',
							// 触发器可以同时用blur和change，二者之间用英文逗号隔开
							trigger: ['change', 'blur'],
						}
					],
					bank_idcard_munber: [{
							required: true,
							message: '请输入身份证号码',
							trigger: ['change', 'blur'],
						},
						{
							validator: (rule, value, callback) => {
								// 调用uView自带的js验证规则，详见：https://www.uviewui.com/js/test.html
								return this.$u.test.idCard(value);
							},
							message: '身份证号码不正确',
							// 触发器可以同时用blur和change，二者之间用英文逗号隔开
							trigger: ['change', 'blur'],
						}
					],
				}
			};
		},
		onLoad(options) {
			this.app.isLogin(this);//强制登陆
			this.getUserInfo();
			this.source = options.source;
			this.from = options.from;
		},
		computed: {},
		onReady() {
		},
		methods: {
			getUserInfo(){
				//获取登录会员信息
				this.$u.api.getUserInfo().then(res => {
					let userInfo = res.data;
					this.model.weixin_usd = userInfo.weixin_usd == 1?true:false;
					this.model.weixin_name = userInfo.weixin_name;
					this.model.weixin_account = userInfo.weixin_account;
					this.model.weixin_qrcode = userInfo.weixin_qrcode;
					if (userInfo.weixin_qrcode != ''){
						this.weixinFileList = [{'url':this.config.baseUrl+userInfo.weixin_qrcode}];
					}
					this.model.alipay_usd = userInfo.alipay_usd == 1?true:false;
					this.model.alipay_name = userInfo.alipay_name;
					this.model.alipay_account = userInfo.alipay_account;
					this.model.alipay_qrcode = userInfo.alipay_qrcode;
					if (userInfo.alipay_qrcode != ''){
						this.alipayFileList = [{'url':this.config.baseUrl+userInfo.alipay_qrcode}];
					}
					
					this.model.bank_usd = userInfo.bank_usd == 1?true:false;
					this.model.bank_name = userInfo.bank_name;
					this.model.bank_province = userInfo.bank_province;
					this.model.bank_city = userInfo.bank_city;
					this.model.bank_subbranch = userInfo.bank_subbranch;
					this.model.bank_card_number = userInfo.bank_card_number;
					this.model.bank_user_name = userInfo.bank_user_name;
					this.model.bank_idcard_munber = userInfo.bank_idcard_munber;
					if (userInfo.bank_city > 0){
						this.region_code = [userInfo.bank_province,userInfo.bank_city];
						this.model.bankRegionText = userInfo.bank_region_text;
					}
					
				});
			},
			uploaded(res, index, lists){
				var res = JSON.parse(res.data);
				if (res.type == 'weixin_qrcode'){
					this.model.weixin_qrcode = res.file;
					this.model.edit_weixin_qrcode = 1;
				}else if (res.type == 'alipay_qrcode'){
					this.model.alipay_qrcode = res.file;
					this.model.edit_alipay_qrcode = 1;
				}
			},
			removeImg(type){
				if (type == 'weixin_qrcode'){
					this.model.weixin_qrcode = '';
					this.model.edit_weixin_qrcode = 1;
				}else if (type == 'alipay_qrcode'){
					this.model.alipay_qrcode = '';
					this.model.edit_alipay_qrcode = 1;
				}
			},
			confirmRegion(e){
				this.model.bankRegionText = e.province.label + ',' + e.city.label;
				this.model.bank_province = e.province.value;
				this.model.bank_city = e.city.value;
			},
			submit(){
				var rules = {};
				if (this.model.weixin_usd == 1){
					Object.assign(rules,this.weixin_rules);
				}
				if (this.model.alipay_usd == 1){
					Object.assign(rules,this.alipay_rules);
				}
				if (this.model.bank_usd == 1){
					Object.assign(rules,this.bank_rules);
				}
				if (Object.keys(rules).length == 0){
					return this._post();
				}
				this.$refs.uForm.setRules(rules);
				this.$refs.uForm.validate(valid => {
					if (!valid) {
						return false;
					}
					this._post();
				})
			},
			_post(){
				let that = this;
				this.$u.post('member/api.users/editReceivePay', this.model).then(res => {
					uni.showModal({
					    title: '提示',
					    content: res.msg,
						showCancel:false,
					    success: function (res) {
					        if (res.confirm) {
								if (that.source == 'channel_withdraw'){
								   uni.redirectTo({
									url: '/pagesB/channel/wallet/withdraw'
								   });
								}else if (that.source == 'withdraw'){
									uni.redirectTo({
										url: '/pages/member/wallet/withdraw'
									});
								}else{
									uni.redirectTo({
										url: '/pages/member/center/set'
									});
								}
					        } 
					    }
					});
				})
			}
		}
	}
</script>

<style lang='scss'>
	.pay_icon{
		position: absolute;
		top:50rpx;
	}
	.info_box{
		padding-left: 80rpx;
		.u-form-item{
			padding: 10rpx 0;
			line-height: none;
		}
	}
	.u-switch{
		font-size: 40rpx !important;
	}
</style>
