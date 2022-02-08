<template>
	<view class="page-body" :class="[app.setCStyle(from)]">
		<view class="list-cell mt20 mlr20 br20" @click="app.goPage('editInfo?from='+from)" hover-class="cell-hover" :hover-stay-time="50">
			<view class="smll">
				<u-icon name="/static/public/images/personal_icon_person.png" class="mr20" size="48"></u-icon>
				<text class="cell-tit">{{app.langReplace('个人资料')}}</text>
			</view>
			<view class="cell-more">{{app.langReplace('编缉')}}<u-icon name="arrow-right ml20" :size="20"></u-icon>
			</view>
		</view>
		<view class="list-cell  mt20 mlr20 br20" @click="app.goPage('editPassWord?from='+from)" hover-class="cell-hover" :hover-stay-time="50">
			<view class="smll">
				<u-icon name="/static/public/images/personal_icon_safe.png" class="mr20" size="48"></u-icon>
				<text class="cell-tit">{{app.langReplace('帐户安全')}}</text>
			</view>
			<view class="cell-more">{{app.langReplace('修改密码')}}<u-icon name="arrow-right ml20" :size="20"></u-icon>
			</view>
		</view>
		
		<view v-if="setting.sys_model_pay_password == 1" class="list-cell  mt20 mlr20 br20" @click="app.goPage('editPayPassWord?from='+from)" hover-class="cell-hover" :hover-stay-time="50">
			<view class="smll">
				<u-icon name="/static/public/images/personal_icon_safe.png" class="mr20" size="48"></u-icon>
				<text class="cell-tit">{{app.langReplace('支付密码')}}</text>
			</view>
			<view class="cell-more">{{app.langReplace('修改')}}<u-icon name="arrow-right ml20" :size="20"></u-icon>
			</view>
		</view>
		
		<view v-if="app.isMPTextModel() == false">
			<view class="list-cell  mt20 mlr20 br20" @click="app.goPage('checkIdCard?from='+from)" hover-class="cell-hover" :hover-stay-time="50">
				<view class="smll">
					<u-icon name="/static/public/images/personal_icon_id.png" class="mr20" size="48"></u-icon>
					<text class="cell-tit">{{app.langReplace('实名认证')}}</text>
				</view>
				<view class="cell-more" v-if="userInfo.check_id_card == 0">{{app.langReplace('未认证')}}<u-icon name="arrow-right ml20" :size="20"></u-icon>
				</view>
				<view class="cell-more" v-if="userInfo.check_id_card == 1">{{app.langReplace('已认证')}}<u-icon name="arrow-right ml20":size="20"></u-icon>
				</view>
				<view class="cell-more" v-if="userInfo.check_id_card == 2">{{app.langReplace('审核中')}}<u-icon name="arrow-right ml20" :size="20"></u-icon>
				</view>
				<view class="cell-more red" v-if="userInfo.check_id_card == 3">{{app.langReplace('认证失败')}}<u-icon name="arrow-right ml20" :size="20"></u-icon>
				</view>
			</view>
			<view class="list-cell mt20 mlr20 br20" @click="app.goPage('editReceivePay?from='+from)" hover-class="cell-hover"
			 :hover-stay-time="50">
				<view class="smll">
					<u-icon name="/static/public/images/personal_icon_date.png" class="mr20" size="48"></u-icon>
					<text class="cell-tit">{{app.langReplace('收款信息')}}</text>
				</view>
				<view class="cell-more">
					<u-icon name="arrow-right" :size="20"></u-icon>
				</view>
			</view>
		</view>
		<view class="list-cell mt20 mlr20 br20" @click="app.goPage('/pages/member/address/list?from='+from)" hover-class="cell-hover"
		 :hover-stay-time="50">
			<view class="smll">
				<u-icon name="/static/public/images/personal_icon_where.png" class="mr20" size="48"></u-icon>
				<text class="cell-tit">{{app.langReplace('地址管理')}}</text>
			</view>
			<view class="cell-more">
				<u-icon name="arrow-right" :size="20"></u-icon>
			</view>
		</view>
		<view  v-if="wx_auto_login == 1">
			<view v-if="is_bind == 0" class="list-cell mt20 mlr20 br20" @click="binxWx" hover-class="cell-hover"
			 :hover-stay-time="50">
				<view class="smll">
					<u-icon name="/static/public/images/weixin.png" class="mr20" size="48"></u-icon>
					<view class="cell-tit">{{app.langReplace('绑定微信')}}：
						<text  class="color-cc">--{{app.langReplace('未绑定')}}--</text>
					</view>
				</view>
				<view class="cell-more">{{app.langReplace('绑定')}}<u-icon name="arrow-right ml20" :size="20"></u-icon></view>
			</view>
			<view v-if="is_bind == 1" class="list-cell mt20 mlr20 br20" @click="unbinxWx" hover-class="cell-hover"
			 :hover-stay-time="50">
				<view class="smll">
					<u-icon name="/static/public/images/weixin.png" class="mr20" size="48"></u-icon>
					<view class="cell-tit">{{app.langReplace('绑定微信')}}：
						<text>{{bind_wx}}</text>
					</view>
				</view>
				<view class="cell-more">{{app.langReplace('解绑')}}<u-icon name="arrow-right ml20" :size="20"></u-icon></view>
			</view>
		</view>
		<view class="p20 mt40">
			<button size="default" shape="circle" type="warning" class="primarybtn"  @click="toLogout">{{app.langReplace('退出登录')}}</button>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				setting : uni.getStorageSync('setting'),
				from: '',
				userInfo: {},
				wx_auto_login:0,
				is_bind:0,
				bind_wx:''
			};
		},
		onLoad(options) {
			this.app.isLogin(this);//强制登陆
			let title = this.app.langReplace('设置');
			uni.setNavigationBarTitle({
				title
			})
			this.getUserInfo();
			this.from = options.from;
			if (this.app.getAuthCode()){
				this.app.getWxOauthOpenId(this,{});//默认只有初次打开时触发获取openid,退出登陆后后清理openid后，再访问登陆页重新获取
			}
		},
		computed: {},
		onReady() {},
		methods: {
			getUserInfo() {
				//获取登录会员信息
				this.$u.api.getUserInfo().then(res => {
					this.userInfo = res.data;
					let platform = this.app.getPlatform();
					if (platform == 'H5-WX'){
						this.wx_auto_login = res.data.wx_auto_login;
					}
					this.bind_wx = res.data.wx_nickname;
					this.is_bind = res.data.wx_nickname ? 1 : 0
				});
			},
			//绑定微信
			binxWx(){
				uni.showModal({
					content: this.app.langReplace('确定绑定当前微信？'),
					confirmText: this.app.langReplace('确认'),
					cancelText: this.app.langReplace('取消'),
					success: (e) => {
						if (e.confirm) {
							let openid = this.app.getWxOpenId();
							this.$u.post('weixin/api.index/bindWxUser',{openid:openid}).then(res => {
								this.is_bind = 1;
								this.bind_wx = res.data.wx_nickname;
							}).catch(res => {
							})
						}
					}
				});
			},
			//解绑微信
			unbinxWx(){
				uni.showModal({
					content: this.app.langReplace('确定解绑微信？'),
					confirmText: this.app.langReplace('确认'),
					cancelText: this.app.langReplace('取消'),
					success: (e) => {
						if (e.confirm) {
							this.$u.post('weixin/api.index/unbindWxUser').then(res => {
								this.is_bind = 0;
							}).catch(res => {
							})
						}
					}
				});
			},
			//退出登录
			toLogout() {
				uni.showModal({
					content: this.app.langReplace('确定要退出登录么？'),
					confirmText: this.app.langReplace('确认'),
					cancelText: this.app.langReplace('取消'),
					success: (e) => {
						if (e.confirm) {
							this.app.delAuthCode(); //清除本地登陆缓存
							this.$u.post('member/api.passport/logout').then(res => {});
							let url = '/pages/member/passport/login';
							
							uni.redirectTo({
								url: url
							});
						}
					}
				});
			},
		}
	}
</script>

<style lang='scss'>
	.list-cell {
		justify-content: space-between;
	}

	.cell-tit {
		font-size: 28rpx;
	}

	.cell-more {
		font-size: 28rpx;
		color: $font-color-gray;
	}
</style>
