<template>
	<view class="page-body" :class="[app.setCStyle(from)]">
		<u-form v-if="show" :model="model"  ref="uForm" :errorType="errorType">
			<view class="pbox bg-white mlr20 mt22 br20 mt30">
				<u-form-item :rightIconStyle="{color: '#888', fontSize: '32rpx'}" right-icon="phone" :label-position="labelPosition" :label="app.langReplace('手机号码')" prop="mobile" label-width="180">
					<u-input :border="border" :placeholder="app.langReplace('请输入手机号')" v-model="model.mobile" type="number"></u-input>
				</u-form-item>
				
				<u-form-item  :label-position="labelPosition" :label="app.langReplace('验证码')" prop="code" label-width="180">
					<u-input :border="border" :placeholder="app.langReplace('请输入验证码')" v-model="model.code" type="text"></u-input>
					<button slot="right" type="success" size="mini" class="bg-base color-ff" @click="getCode">{{codeTips}}</button>
				</u-form-item>
				<u-form-item :label-position="labelPosition" :label="app.langReplace('密码')" prop="password" label-width="180">
					<u-input :password-icon="true" :border="border" type="password" v-model="model.password" :placeholder="app.langReplace('请输入密码')"></u-input>
				</u-form-item>
				<u-form-item :label-position="labelPosition" :label="app.langReplace('确认密码')" label-width="180" prop="rePassword">
					<u-input :border="border" type="password" v-model="model.rePassword" :placeholder="app.langReplace('请再次输入密码')"></u-input>
				</u-form-item>
				<block v-if="setting.sys_model_pay_password == 1">
				<u-form-item :label-position="labelPosition" :label="app.langReplace('支付密码')" prop="pay_password" label-width="180">
					<u-input :password-icon="true" :border="border" type="password" v-model="model.pay_password" :placeholder="app.langReplace('请输入支付密码')"></u-input>
				</u-form-item>
				<u-form-item :label-position="labelPosition" :label="app.langReplace('确认支付密码')" label-width="180" prop="repay_password">
					<u-input :border="border" type="password" v-model="model.repay_password" :placeholder="app.langReplace('请再次输入支付密码')"></u-input>
				</u-form-item>
				</block>
			</view>
			<view class="pbox mt50">
			<button size="default" shape="circle"  type="warning"  class="primarybtn" @click="submit">{{app.langReplace('绑定手机')}}</button>
			</view>
			<u-verification-code seconds="60" ref="uCode" @change="codeChange"></u-verification-code>
		</u-form>
	</view>
</template>

<script>
	export default {
		data() {
			let that = this;
			return {
				model: {
					setting : uni.getStorageSync('setting'),
					mobile:'',
					code: '',
					password: '',
					rePassword:'',
					pay_password: '',
					repay_password:'',
				},
				codeTips: '',
				border: false,
				labelPosition: 'left',
				errorType: ['toast'],
				show:false,
				rulesgetCode:{
					mobile: [{
							required: true,
							message: this.app.langReplace('请输入手机号'),
							trigger: ['change', 'blur'],
						},
						{
							validator: (rule, value, callback) => {
								// 调用uView自带的js验证规则，详见：https://www.uviewui.com/js/test.html
								return this.$u.test.mobile(value);
							},
							message: this.app.langReplace('手机号码不正确'),
							// 触发器可以同时用blur和change，二者之间用英文逗号隔开
							trigger: ['change', 'blur'],
						}
					]
				},
				rules: {
					mobile: [
						{
							required: true,
							message: this.app.langReplace('请输入手机号'),
							trigger: ['change','blur'],
						},
						{
							validator: (rule, value, callback) => {
								// 调用uView自带的js验证规则，详见：https://www.uviewui.com/js/test.html
								return this.$u.test.mobile(value);
							},
							message: this.app.langReplace('手机号码不正确'),
							// 触发器可以同时用blur和change，二者之间用英文逗号隔开
							trigger: ['change','blur'],
						}
					],
					code: [
						{
							min: 6,
							required: true,
							message: this.app.langReplace('请输入6位验证码'),
							trigger: ['change','blur'],
						},
						{
							type: 'number',
							message: this.app.langReplace('验证码只能为数字'),
							trigger: ['change','blur'],
						}
					],
					password: [
						{
							required:  true,
							message: this.app.langReplace('请输入密码'),
							trigger: ['change','blur'],
						},
						{
							min: 8,
							message: this.app.langReplace('密码不能少于8个字符'),
							trigger: 'change' ,
						}
					],
					rePassword: [
						{
							required: true,
							message: this.app.langReplace('请再次输入密码'),
							trigger: ['change','blur'],
						},
						{
							validator: (rule, value, callback) => {
								return value === this.model.password;
							},
							message: this.app.langReplace('两次输入的密码不相等'),
							trigger: ['change','blur'],
						}
					],
					pay_password: [
						{
							required:  true,
							message: this.app.langReplace('请输入支付密码'),
							trigger: ['change','blur'],
						},
						{
							min: 6,
							max: 6,
							type: 'number',
							message: this.app.langReplace('支付密码只能为6位数字'),
							trigger: ['change','blur'],
						}
					],
					repay_password: [
						{
							required: true,
							message: this.app.langReplace('请再次输入支付密码'),
							trigger: ['change','blur'],
						},
						{
							validator: (rule, value, callback) => {
								return value === this.model.pay_password;
							},
							message: this.app.langReplace('两次输入的支付密码不相等'),
							trigger: ['change','blur'],
						}
					],
				},
			};
		},
		onLoad(options) {
			let title = this.app.langReplace('绑定手机');
			uni.setNavigationBarTitle({
				title
			})
			this.from = options.from;
			this.getUserInfo();
			
		},
		computed: {
		
		},
		onReady() {
			
		},
		methods: {
			getUserInfo(){
				//获取登录会员信息
				this.$u.api.getUserInfo().then(res => {
					let userInfo = res.data;
					if (userInfo.mobile != ''){
						uni.showModal({
						    title: this.app.langReplace('提示'),
						    content: this.app.langReplace('当前帐号已绑定手机：'+userInfo.mobile),
							confirmText: this.app.langReplace('确认'),
							showCancel:false,
						    success: function (res) {
						        if (res.confirm) {
						           uni.redirectTo({
						           	url: 'index'
						           });
						        } 
						    }
						});
					}else{
						this.show = true;
					}
				});
			},
			submit() {
				if (this.setting.sys_model_pay_password != 1){
					delete this.rules.pay_password;
					delete this.rules.repay_password;
				}
				this.$refs.uForm.setRules(this.rules);
				this.$refs.uForm.validate(valid => {
					if (!valid) {
						return false;
					}
					//绑定手机
					this.$u.post('member/api.users/bindMobile',this.model).then(res => {
						if (res.code < 0){
							this.$u.toast(res.msg);
							return false;
						}
						 uni.showModal({
						     title: this.app.langReplace('提示'),
						     content: this.app.langReplace('绑定成功'),
						 	confirmText: this.app.langReplace('确认'),
						 	showCancel:false,
						     success: function (res) {
						         if (res.confirm) {
						            uni.redirectTo({
						            	url: 'index'
						            });
						         } 
						     }
						 });
					})
					
				});
			},
			codeChange(text) {
				this.codeTips = this.app.langReplace(text);
			},
			// 获取验证码
			getCode() {
				if(this.$refs.uCode.canGetCode) {
					this.$refs.uForm.setRules(this.rulesgetCode);
					this.$refs.uForm.validate(valid => {
						if (valid) {
							// 向后端请求验证码
							uni.showLoading({
								title: '正在获取验证码',
								mask: true
							})
							this.$u.get('publics/api.sms/sendCode', {
								'type': 'bind_mobile',
								'mobile': this.model.mobile
							}).then(res => {
								uni.hideLoading();
								// 这里此提示会被this.start()方法中的提示覆盖
								if (res.data.code){
									this.$u.toast('测试状态，验证码自动填充');
									this.model.code = res.data.code;
									delete this.rules.code;
								}else{
									this.$u.toast(this.app.langReplace('验证码已发送'));
								}
								this.user_exist = res.data.user_exist;
								// 通知验证码组件内部开始倒计时
								this.$refs.uCode.start();
							})
						}
					});
				} else {
					this.$u.toast(this.app.langReplace('倒计时结束后再请求'));
				}
			},
		}
	}
</script>

<style lang='scss'>

</style>
