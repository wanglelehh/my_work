<template>
	<view class="page-body p50 pt100 bg-white" :class="[app.setCStyle()]">
		<text class="block fs42 font-w600 mt40">{{app.langReplace('注册帐户')}}</text>
		<u-form :model="model"  ref="uForm" :errorType="errorType" class="mt60">
			<view v-if="phoneNumber">
				<u-form-item :rightIconStyle="{color: '#888', fontSize: '32rpx'}" right-icon="phone" :label-position="labelPosition" :label="app.langReplace('手机号码')" label-width="160">
					<u-input :border="border"  disabled="true" v-model="phoneNumber" type="number"></u-input>
				</u-form-item>
			</view>
			<view v-else>
					<u-form-item :rightIconStyle="{color: '#888', fontSize: '32rpx'}" right-icon="phone" :label-position="labelPosition" :label="app.langReplace('手机号码')" prop="mobile" label-width="160">
						<u-input :border="border" :placeholder="app.langReplace('请输入手机号')" v-model="model.mobile" type="number"></u-input>
					</u-form-item>
					
					<u-form-item  v-if="setting.sms_public_register" :label-position="labelPosition" :label="app.langReplace('验证码')" prop="code" label-width="160">
						<u-input :border="border" :placeholder="app.langReplace('请输入验证码')" v-model="model.code" type="text"></u-input>
						<button slot="right" type="success" size="mini" class="bg-base color-ff" @click="getCode">{{codeTips}}</button>
					</u-form-item>
			</view>
					<u-form-item :label-position="labelPosition" :label="app.langReplace('密码')" prop="password" label-width="160">
						<u-input :password-icon="true" :border="border" type="password" v-model="model.password" :placeholder="app.langReplace('请输入密码')"></u-input>
					</u-form-item>
					<u-form-item :label-position="labelPosition" :label="app.langReplace('确认密码')" label-width="160" prop="rePassword">
						<u-input :border="border" type="password" v-model="model.rePassword" :placeholder="app.langReplace('请再次输入密码')"></u-input>
					</u-form-item>
					<u-form-item v-if="showVerify" :label-position="labelPosition" :label="app.langReplace('图形验证码')" prop="verifyCode" label-width="180">
						<u-input :border="border" :placeholder="app.langReplace('输入验证码')" v-model="model.verifyCode" type="text"></u-input>
						<u-image width="200rpx" height="80rpx" :src="verifyUrl" @click="changeVerifyUrl"></u-image>
					</u-form-item>
					<u-form-item v-if="setting.register_invite_code == 1" :rightIconStyle="{color: '#888', fontSize: '32rpx'}" right-icon="account" :label-position="labelPosition" :label="app.langReplace('邀请手机/邀请码')" prop="invite_code" label-width="220">
						<u-input :border="border" :disabled="invite_code_disabled" @click="showInviteCodeTip()" :placeholder="app.langReplace('请输入')+app.langReplace('邀请手机/邀请码')" v-model="model.invite_code" type="text"></u-input>
					</u-form-item>
					<view class="mt60 relative fs28">
						<u-checkbox class="not-mr" shape="circle"  active-color="#FE385E"  v-model="is_agree"  @change="agreeRegister"></u-checkbox>
						<text @click="agree">{{app.langReplace('勾选同意本站')}}</text>
						<text class="base-color" @click="app.goPage('/pages/public/regagreement?type=register')">
							《{{app.langReplace('注册协议')}}》
						</text>
					</view>
					<button size="default" shape="circle" class="primarybtn mt60" type="warning"  @click="getShareUserInfo">{{app.langReplace('提交')}}</button>
					<view @click="app.goPage('login')" class="mt50 text-right fs26 color-66">{{app.langReplace('已有帐号')}}，<text class="base-color">{{app.langReplace('前往登录')}}</text></view>
					<u-verification-code seconds="60" ref="uCode" @change="codeChange"></u-verification-code>
				</u-form>
				
				<view class="model" v-if='shareModelType'>
				    <view class="canter">
						<view class="headimgurl">
							<image style="width:120rpx; height:120rpx;" mode="aspectFill" :src="shareUser.headimgurl?shareUser.headimgurl:'/static/public/images/headimgurl.jpg'"></image>
						</view>
						<view class="fs28 text-center mt20">
							<view class="mt10" v-if="shareUser.nick_name">昵称：{{shareUser.nick_name}}</view>
							<view class="mt10" v-if="shareUser.real_name">真实姓名：{{shareUser.real_name}}</view>
							<view class="mt10" v-if="shareUser.mobile">手机号码：{{shareUser.mobile}}</view>
						</view>
						<view class="text-center fs26 color-99 mt20">以上为邀请人的信息,请核实是否正确.</view>
						<view class="smll w100 mt30 p20">
							<view class="w50 p10 mlr10 text-center primarybtn " style="background:#CCCCCC !important;" @click="shareModelType=false">重新填写</view>
							<view class="w50 p10 mlr10 text-center primarybtn" @click="submit()">确认</view>
						</view>
				    </view>
				</view>
				
				<copyright></copyright>
	</view>
</template>

<script>
	import copyright from '@/pages/public/copyright';
	export default {
		components: {
			copyright
		},
			data() {
				let that = this;
				return {
					is_agree:false,
					setting: uni.getStorageSync('setting'),
					verifyUrl: '',
					showVerify:false,
					phoneNumber:'',
					invite_code_disabled:false,
					model: {
						phone: uni.getStorageSync("loginPhone"),
						mobile:'',
						code: '',
						password: '',
						rePassword:'',
						invite_code:'',
						openid:'',
						iv:'',
						encryptedData:''
					},
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
					},
					border: false,
					check: false,
					labelPosition: 'left',
					codeTips: '',
					errorType: ['toast'],
					shareModelType: false,
					shareUser:{
						headimgurl:'',
						nick_name:'',
						real_name:'',
						mobile:'',
					}
				};
			},
			onLoad(options) {
				if (options.scene){//获取小程序的场景值，用于获取分享者的token
					let scene = options.scene.split('_');
					uni.setStorageSync("share_token",scene[0]);
				}
				if (options.phoneNumber){
					this.phoneNumber = options.phoneNumber;
					let wxEncryptedData = uni.getStorageSync("wxEncryptedData");
					this.model.encryptedData = wxEncryptedData.encryptedData;
					this.model.iv = wxEncryptedData.iv;
					delete this.rules.mobile;
					delete this.rules.code;
				}
				if (this.setting.sms_public_register == 0){
					delete this.rules.code;
				}
				let that = this;
				setTimeout(() => {
					that.getShareToken();
				},500);
				this.verifyUrl = this.config.baseUrl + '/index.php/publics/api.index/verify/session_id/'+uni.getStorageSync("session_id");
			},
			computed: {
				borderCurrent() {
					return this.border ? 0 : 1;
				},
			},
			onReady() {
				
			},
			methods: {
				agree(){
					this.is_agree = this.is_agree == false ? true : false;
				},
				getShareToken(){
					let share_token = uni.getStorageSync("share_token");
					if (share_token){
						this.model.invite_code = share_token
						this.invite_code_disabled = true;//邀请码存在，不允许自行输入，如需更换邀请人，请重新扫码
					}
				},
				showInviteCodeTip(){
					if (this.invite_code_disabled == true){
						this.app.showModal('如需更换邀请人，请重新扫码.');
					}
				},
				getShareUserInfo(){
					let invite_code_length = this.model.invite_code.length;
					if (this.setting.register_invite_code == 0 || invite_code_length < 6){
						return this.submit();
					}
					this.$u.get('member/api.passport/getShareInfo',{invite_code:this.model.invite_code,showLoading:false}).then(res => {
						if (res.data.share_user.user_id > 0){
							if (res.data.share_user.headimgurl != '') {
								this.shareUser.headimgurl = this.config.baseUrl + res.data.share_user.headimgurl;
							}
							this.shareUser.nick_name = res.data.share_user.nick_name;
							this.shareUser.real_name = res.data.share_user.real_name;
							this.shareUser.mobile = res.data.share_user.mobile;
							this.shareModelType = true;
						}else{
							return this.submit();
						}
					})
				},
				submit() {
					this.shareModelType = false;
					this.$refs.uForm.setRules(this.rules);
					this.$refs.uForm.validate(valid => {
						if (!valid) {
							return false;
						}
						if (this.is_agree == false){
							uni.showModal({
							    title: this.app.langReplace('提示'),
							    content: this.app.langReplace('请勾选同意本站【注册协议】'),
								confirmText: this.app.langReplace('确认'),
								showCancel:false,
							    success: function (res) {
							    }
							});
							return false;
						}
						this.model.openid = this.app.getWxOpenId();
						//请求注册
						this.$u.post('member/api.passport/register',this.model).then(res => {
							if (res.code < 0){
								this.showVerify = res.data.showVerify;
								this.$u.toast(res.msg);
								this.changeVerifyUrl();
								this.model.verifyCode = '';
								return false;
							}
							let _url = '/pages/member/passport/login';
							if (res.data.token){
								_url = '/pages/shop/index/index';//自动登陆，跳转地址
								this.app.setAuthCode(res.data.token,res.data.share_token);
							}
							uni.removeStorageSync("share_token");//注册完成清理来源邀请码
							uni.showModal({
							    title: this.app.langReplace('提示'),
							    content: this.app.langReplace('注册成功'),
								confirmText: this.app.langReplace('确认'),
								showCancel:false,
							    success: function (res) {
							        if (res.confirm) {
							           uni.redirectTo({
							           	url: _url
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
				changeVerifyUrl() {
					if (this.verifyUrl.indexOf('rand=') > -1) {
						this.verifyUrl = this.verifyUrl.substr(0, this.verifyUrl.length - 19);
					}
					this.verifyUrl += '?rand=' + new Date().getTime();
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
									'type': 'register',
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
				agreeRegister(e){
					this.is_agree = e.value 
				}
			}
		}
</script>
<style>
	.u-form-item{
		line-height: 30rpx;
	}
	.model {
	    position: fixed;
	    top: 0;
	    left: 0;
	    height: 100vh;
	    width: 100vw;
	    z-index: 999998;
	}
	
	
	.model .canter {
	    width: 520rpx;
	    margin: 25vh 15vw 0 15vw;
	    display: flex;
	    flex-direction: column;
	    align-items: center;
	    position: relative;
	    z-index: 99999;
		background-color: #FFFFFF;
		color: #000000;
		border-radius: 20rpx;
		margin-bottom: 20rpx;
		position: relative;
		padding-top: 80rpx;
		padding-bottom: 20rpx;
	}
	.model .canter .headimgurl {
		margin-top: -120rpx;
		width: 120rpx;
		height: 120rpx;
		border-radius: 50%;
		overflow: hidden;
		border: 3rpx solid #FFFFFF;
		background-color: #FFFFFF;
		text-align: center;
		
	}
	.model .canter .headimgurl image{
		width: 100%;
		height: 100%;
		overflow: hidden;
		border-radius: 50%;
	}
	.regagreement{
		position: absolute;
		left: 220rpx;
		top:7rpx;
	}
</style>
