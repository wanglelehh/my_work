<template>
	<view class="page-body p50 pt100 bg-white" :class="[app.setCStyle()]">
		<view class="top_logo" style="width: 150rpx;height: 150rpx;margin: 0rpx auto;">
			<u-image width="100%" height="100%"  :src="baseUrl+settings.logo" shape="circle"></u-image>
		</view>
		<text class="block fs36 font-w600 mt30 text-center">{{settings.site_name}}</text>
		<view class="mt80"></view>
		<u-form :model="model" ref="uForm" :errorType="errorType" >
			<u-form-item :rightIconStyle="{color: '#888', fontSize: '32rpx'}" right-icon="phone" :label-position="labelPosition"
			 :label="app.langReplace('手机号码')" prop="mobile" label-width="150">
				<u-input :border="border" :placeholder="app.langReplace('请输入手机号')" v-model="model.mobile" type="number"></u-input>
			</u-form-item>
			<view v-if="model.login_type==0">
				<u-form-item :label-position="labelPosition" :label="app.langReplace('密码')" prop="password" label-width="150">
					<u-input :password-icon="true" :border="border" type="password" v-model="model.password" :placeholder="app.langReplace('请输入密码')"></u-input>
				</u-form-item>

				<u-form-item :label-position="labelPosition" :label="app.langReplace('记住密码')"  prop="remember" label-width="550">
					<u-switch v-model="model.remember" slot="right" active-color="#fe0079"></u-switch>
				</u-form-item>
			</view>
			<u-form-item  v-if="model.login_type==1" :label-position="labelPosition" :label="app.langReplace('验证码')"
			 prop="code" label-width="150">
				<u-input :border="border" :placeholder="app.langReplace('请输入验证码')" v-model="model.code" type="text"></u-input>
				<button slot="right" type="success" size="mini" class="bg-base color-ff" @click="getCode">{{codeTips}}</button>
			</u-form-item>
			<u-form-item v-if="showVerify" :label-position="labelPosition" :label="app.langReplace('图形验证码')" prop="verifyCode" label-width="150">
				<u-input :border="border" :placeholder="app.langReplace('输入验证码')" v-model="model.verifyCode" type="text"></u-input>
				<u-image width="200rpx" height="80rpx" :src="verifyUrl" @click="changeVerifyUrl"></u-image>
			</u-form-item>
			<view class=" color-66 smll mt40 fs26">
				<view class="flex_bd ">
					<text v-if="settings.sms_public_login"  @click="changeLogin">{{logintype}}</text>
				</view>
				<view v-if="settings.sms_public_forget_password" @click="goForget">{{app.langReplace('忘记密码')}}？</view>
			</view>
			<button size="default" class="mt80 " :class="[getLoginBtnClass()]" type="warning"  @click="submit">{{app.langReplace('登录')}}</button>
			<!-- #ifdef MP-WEIXIN -->
			<button  size="default"  class=" mt40 primarybtnD fs32"  open-type="getPhoneNumber" @getphonenumber="getPhoneNumber">
				<u-icon name="weixin-fill" color="#00c802" size="46" class="mr10 color-66"></u-icon> {{app.langReplace('手机授权登录')}}
			</button>
			<!-- #endif -->
			
			<view v-if="settings.register_status == 1" class="mt60 clearfix  text-center fs26 color-66">
				<view @click="goRegister">{{app.langReplace('还没有帐号')}}，<text class="base-color">{{app.langReplace('立即注册')}}</text></view>
			</view>

			<u-verification-code seconds="60" ref="uCode" @change="codeChange"></u-verification-code>
			<copyright></copyright>
		</u-form>
	</view>
</template>

<script>
	let that = this;
	import JSEncrypt from '@/common/jsencrypt.js';
	import copyright from '@/pages/public/copyright';
	export default {
		components: {
			copyright
		},
		data() {
			return {
				settings: uni.getStorageSync('setting'),
				baseUrl:this.config.baseUrl,
				returnUrl:'',
				logintype: this.app.langReplace('短信登录'),
				verifyUrl: '',
				showVerify: false,
				rememberPassword:this.app.langReplace('记住密码已被加密,无法查看'),
				model: {
					mobile: uni.getStorageSync("loginMobile"),
					wx_openid: this.app.getWxOpenId(),
					code: '',
					password: uni.getStorageSync("loginPassword")?'记住密码已被加密,无法查看':'',
					login_type: 0,
					remember: uni.getStorageSync("loginPassword") ? true : false,
					verifyCode: '',
				},
				pwrules: {
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
					],
					password: [{
							required: true,
							message: this.app.langReplace('请输入密码'),
							trigger: ['change', 'blur'],
						},
						{
							min: 8,
							message: this.app.langReplace('密码不能少于8个字符'),
							trigger: 'change',
						}
					]
				},
				msmrules: {
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
					],
					code: [{
							min: 6,
							required: true,
							message: this.app.langReplace('请输入6位验证码'),
							trigger: ['change', 'blur'],
						},
						{
							type: 'number',
							message: this.app.langReplace('验证码只能为数字'),
							trigger: ['change', 'blur'],
						}
					]
				},
				rulesgetCode: {
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
				border: false,
				check: false,
				labelPosition: 'left',
				codeTips: '',
				errorType: ['toast']
			};
		},
		onLoad(options) {
			uni.removeStorageSync("isNotLoginTip");//未登陆提示，此值会限制网络请求
			if (options.scene){//获取小程序的场景值，用于获取分享者的token
				let scene = options.scene.split('_');
				uni.setStorageSync("share_token",scene[0]);
			}
			if (typeof(options.returnUrl) == 'undefined'){
				this.returnUrl = uni.getStorageSync("loginReturnUrl");
			}else{
				this.returnUrl = options.returnUrl;
				uni.setStorageSync("loginReturnUrl",options.returnUrl);
			}
			let that = this;
			setTimeout(function(){
				that.wxlogin();
			},500)
			this.verifyUrl = this.config.baseUrl + '/index.php/publics/api.index/verify/session_id/' + uni.getStorageSync("session_id");
		},
		computed: {
			borderCurrent() {
				return this.border ? 0 : 1;
			},
		},
		onShow() {
			
		},
		onReady() {
			this.$refs.uForm.setRules(this.pwrules);
		},
		methods: {
			getLoginBtnClass(){
				let style = [];
				if (this.model.login_type == 0){
					if (this.model.mobile == '' || this.model.password == ''){
						style.push('primarybtnC');
						return style;
					}
				}
				if (this.model.login_type == 1){
					if (this.model.mobile == '' || this.model.code == ''){
						style.push('primarybtnC');
						return style;
					}
				}
				style.push('primarybtn');
				return style;
			},
			wxlogin(){
				if (this.app.getAuthCode()){
					uni.redirectTo({
					    url: '/pages/member/center/index'
					});
				}
			},
			submit() {
				let n_pwd = '';
				this.$refs.uForm.validate(valid => {
					if (valid) {
						uni.setStorageSync("loginMobile", this.model.mobile); //记住手机号
						if (this.model.password == this.rememberPassword){
							this.model.password = uni.getStorageSync("loginPassword");
						}else if (this.model.password.length < 30){
							var en = new JSEncrypt()
							en.setPublicKey(this.settings.rsa_public);
							n_pwd = this.model.password;
							this.model.password = en.encrypt(this.model.password);
						}
						if (this.model.remember == true) {
							uni.setStorageSync("loginPassword", this.model.password); //记住密码
						} else {
							try {
								uni.removeStorageSync('loginPassword'); //销毁记住密码
							} catch (e) {}
						}
						this.$u.post('member/api.passport/login', this.model).then(res => {
							if (res.code < 0) {
								if (n_pwd != ''){
									this.model.password = n_pwd;
								}
								this.showVerify = res.data.showVerify;
								this.$u.toast(res.msg);
								return false;
							}
							this.loginGo(res);
						})
						
					} else {
						console.log('验证失败');
					}
				});
			},
			getPhoneNumber (e) {
				//小程序手机授权登陆
				if (e.detail.errMsg == "getPhoneNumber:ok") {
				    this.$u.post('weixin/api.index/loginByMpPhone', {
						iv:e.detail.iv,
						encryptedData:e.detail.encryptedData,
						openid:this.app.getWxOpenId()
					}).then(res => {
						if (res.data.must_reg == 1){
							let phoneNumber = res.data.phoneNumber;
							uni.showModal({
								title: this.app.langReplace('提示'),
								content: this.app.langReplace('手机号码未注册会员'),
								showCancel: true,
								confirmText: this.app.langReplace('立即注册'),
								cancelText: this.app.langReplace('暂不注册'),
								success: function(res) {
									if (res.confirm) {
										uni.setStorageSync("wxEncryptedData", e.detail); //记住微信授权加密信息
										uni.redirectTo({
											url: `register?phoneNumber=${phoneNumber}`
										});
									}
								}
							});
							return false;
						}
						if (res.data.token){
							this.loginGo(res);
						}
					})
				}
			 },
			loginGo(res){
				//登录成功后执行
				this.app.setAuthCode(res.data.token,res.data.share_token);
				uni.removeStorageSync("loginReturnUrl");
				if (this.returnUrl == '' || typeof(this.returnUrl) == 'undefined') { //未读取到页面信息
					uni.redirectTo({
					    url: '/pages/member/center/index'
					});
					return false;
				}
				uni.redirectTo({
					url:'/'+decodeURIComponent(this.returnUrl),
				});
			},
			changeLogin() {
				if (this.model.login_type == 0) {
					this.logintype = this.app.langReplace('密码登录');
					this.model.login_type = 1;
					this.$refs.uForm.setRules(this.msmrules);
				} else {
					this.logintype = this.app.langReplace('短信登录');
					this.model.login_type = 0;
					this.$refs.uForm.setRules(this.pwrules);
				}
			},
			changeVerifyUrl() {
				if (this.verifyUrl.indexOf('rand=') > -1) {
					this.verifyUrl = this.verifyUrl.substr(0, this.verifyUrl.length - 19);
				}
				this.verifyUrl += '?rand=' + new Date().getTime();
			},
			codeChange(text) {
				this.codeTips = this.app.langReplace(text);
			},
			// 获取验证码
			getCode() {
				let that = this;
				if (this.$refs.uCode.canGetCode) {
					this.$refs.uForm.setRules(this.rulesgetCode);
					this.$refs.uForm.validate(valid => {
						if (valid) {
							// 向后端请求验证码
							uni.showLoading({
								title: that.app.langReplace('正在获取验证码'),
								mask: true
							})
							this.$u.get('publics/api.sms/sendCode', {
								'type': 'login',
								'mobile': this.model.mobile
							}).then(res => {
								uni.hideLoading();
								// 这里此提示会被this.start()方法中的提示覆盖
								if (res.data.code){
									this.$u.toast('测试状态，验证码自动填充');
									this.model.code = res.data.code;
								}else{
									this.$u.toast(that.app.langReplace('验证码已发送'));
								}
								this.user_exist = res.data.user_exist;
								// 通知验证码组件内部开始倒计时
								this.$refs.uCode.start();
							})
						}
					});
				} else {
					this.$u.toast(this.app.langReplace('倒计时结束后再发送'));
				}
			},
			
			goForget() {
				uni.navigateTo({
					url: '/pages/member/passport/forget'
				});
			},
			goRegister() {
				uni.navigateTo({
					url: '/pages/member/passport/register'
				});
			}
		}
	}
</script>
<style lang="scss">
	.u-form-item{
		line-height: 30rpx;
	}
</style>
