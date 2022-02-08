<template>
	<view class="page-body" :class="[app.setCStyle(from)]">
		<u-form :model="model" ref="uForm" :errorType="errorType">
			
			<view class="p20 bg-white pt50 mlr20 br20 mt20">
				<view class="fs30 font-w600">
					请填写您的身份证号码
				</view>
				<u-form-item class="" label-width="0" :border-bottom="false" prop="id_card_number">
					<u-input :border="border" class="bg-fa" placeholder="请输入身份证号码" v-model="model.id_card_number" type="text"></u-input>
				</u-form-item>
				<u-form-item class="hide" prop="id_card_positive">
					<u-input v-model="model.id_card_positive" type="text"></u-input>
				</u-form-item>
				<u-form-item class="hide" prop="id_card_back">
					<u-input v-model="model.id_card_back" type="text"></u-input>
				</u-form-item>
			</view>


			<view class="mt20 p20 bg-white mlr20 br20">
				<view class="fs30 font-w600">
					请上传你的身份证照片
				</view>
				<view class="mt30 flex">
					<view class="ml20" @click="upload('positive')">
						<image class="imagemz" :src="id_card_positive" @error="id_card_positive='/static/public/images/id_card/img_front.jpg'"></image>
					</view>
					<view class="ml20" @click="upload">
						<image class="imagemz" :src="id_card_back"  @error="id_card_back='/static/public/images/id_card/img_back.jpg'"></image>
					</view>
				</view>
			</view>
			<view class="mt20 p20 bg-white mlr20 br20">
				<view class="fs30 font-w600">
					拍摄身份证要求：
				</view>
				<view class="mt30 fs26 color-99">
					<view>只能上传不超过5M、jpg、png格式的图片</view>
					<view>拍摄时确保身份证<text style="color: #e73146;">边框完整，字体清晰，亮度均匀；</text></view> 	
				</view>
				<view class="mt60">
					<image mode="widthFix" style="width: 100%;" src="/static/public/images/id_card/img_long.jpg"></image>
				</view>
			</view>
			
			<view class="mt40 p20">
				<button v-if="check_id_card == 0" size="default" shape="circle" type="warning"class="primarybtn" @click="submit">提交认证</button>
				<button v-else-if="check_id_card == 1" size="default" shape="circle" type="success" class="graybtn">已认证</button>
				<button v-else-if="check_id_card == 2" size="default" shape="circle" type="warning" class="graybtn">审核中</button>
				<button v-else-if="check_id_card == 3" size="default" shape="circle" type="warning" class="primarybtn" @click="submit">重新提交</button>
			</view>
		</u-form>
	</view>
</template>

<script>
	export default {
		data() {
			let that = this;
			return {
				from:'',
				upsize: 5 * 1024 * 1024,
				check_id_card: 0,
				id_card_positive: "/static/public/images/id_card/img_front.jpg",
				id_card_back: "/static/public/images/id_card/img_back.jpg",
				model: {
					id_card_number: '',
					id_card_positive: '',
					id_card_back: ''
				},
				border: false,
				labelPosition: 'left',
				errorType: ['toast'],
				rules: {
					id_card_number: [{
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
					id_card_positive: [{
						required: true,
						message: '请上传身份证头像面.',
						trigger: ['change', 'blur'],
					}],
					id_card_back: [{
						required: true,
						message: '请上传身份证国微面.',
						trigger: ['change', 'blur'],
					}]
				},
			};
		},
		onLoad(options) {
			this.app.isLogin(this);//强制登陆
			this.getUserInfo();
			this.from = options.from;
		},
		computed: {
			borderCurrent() {
				return this.border ? 0 : 1;
			},

		},
		onReady() {
			this.$refs.uForm.setRules(this.rules);
		},
		methods: {
			getUserInfo() {
				//获取登录会员信息
				this.$u.api.getUserInfo().then(res => {
					this.check_id_card = res.data.check_id_card;
					this.model.id_card_number = res.data.id_card_number;
					if (res.data.id_card_positive) {
						this.id_card_positive = this.config.baseUrl + res.data.id_card_positive;
					}
					if (res.data.id_card_back) {
						this.id_card_back = this.config.baseUrl + res.data.id_card_back;
					}
				});
			},
			upload(type) {
				var that = this;
				if (this.check_id_card == 1) {
					this.$u.toast('已认证不能重新上传.');
					return false;
				}
				if (this.check_id_card == 2) {
					this.$u.toast('正在审核中，不能重新上传.');
					return false;
				}
				uni.chooseImage({
					count: 1,
					sizeType: ['original', 'compressed'], //可以指定是原图还是压缩图，默认二者都有
					sourceType: ['album'], //从相册选择
					success: function(res) {
						const tempFilePaths = res.tempFilePaths;
						if (res.tempFiles[0].size > that.upsize) {
							that.$u.toast('图片不能大于5MB.');
							return false;
						}
						uni.uploadFile({
							url: that.config.upImageUrl,
							filePath: tempFilePaths[0],
							name: 'file',
							success: (resb) => {
								resb = JSON.parse(resb.data);
								if (resb.code < 1) {
									that.$u.toast(resb.msg);
									return false;
								}
								if (type == 'positive') {
									that.id_card_positive = that.config.baseUrl + resb.file;
									that.model.id_card_positive = resb.file;
								} else {
									that.id_card_back = that.config.baseUrl + resb.file;
									that.model.id_card_back = resb.file;
								}
							}
						});

					},
					error: function(e) {
						console.log(e);
					}
				});
			},
			submit() {
				this.$refs.uForm.validate(valid => {
					if (!valid) {
						return false;
					}
					this.$u.post('member/api.users/postIdCard', this.model).then(res => {
						uni.showModal({
							title: '提示',
							content: res.msg,
							showCancel: false,
							success: function(res) {
								if (res.confirm) {
									uni.switchTab({
										url: '/pages/channel/center/index'
									});
								}
							}
						});
					})
				})
			}
		}
	}
</script>
<style lang="scss">
	

	.top_tip_icon {
		width: 150rpx !important;
		height: 150rpx !important;
		margin: 0rpx auto;
	}

	
	.imagemz {
		width: 324rpx;
		height: 279rpx;
	}

	.color-text {
		color: $base-color;
	}
</style>
