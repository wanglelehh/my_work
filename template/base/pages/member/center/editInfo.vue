<template>
	<view class="page-body" :class="[app.setCStyle(from)]">
		<u-form :model="model" ref="uForm" :errorType="errorType" class="edit-from">
			<view class="list-cell  mt20 mlr20 br20 headimgurl" hover-class="cell-hover" :hover-stay-time="50">
				<text class="cell-tit">{{app.langReplace('头像')}}</text>
				<view class="fr fs32 relative" >
					<view class="image">
						<u-image width="120rpx" height="120rpx" @click="chooseAvatar" ref="uImage"  mode="aspectFill" :src="model.headimgurl?model.headimgurl:'/static/public/images/headimgurl.jpg'" shape="circle">
						</u-image>
					</view>
					<u-icon name="arrow-right" size="20"></u-icon>
				</view>
			</view>
			
				<view class="list-cell mt20 mlr20 br20" hover-class="cell-hover" :hover-stay-time="50">
					<text class="cell-tit">{{app.langReplace('真实姓名')}}</text>
					<u-form-item prop="real_name">
						<u-input :border="border" :clearable="false" class="text-right"  v-model="model.real_name" type="text"></u-input>
					</u-form-item>
					<view class="fr fs32" >
						<u-icon name="arrow-right" size="20"></u-icon>
						
					</view>
				</view>
				<view class="list-cell mt20 mlr20 br20" hover-class="cell-hover" :hover-stay-time="50">
					<text class="cell-tit">{{app.langReplace('昵称')}}</text>
					<u-form-item prop="nick_name">
						<u-input :border="border" :clearable="false" class="text-right"  v-model="model.nick_name" type="text"></u-input>
					</u-form-item>
					<view class="fr fs32" >
						<u-icon name="arrow-right ml20" size="20"></u-icon>
						
					</view>
				</view>
				<view class="list-cell mt20 mlr20 br20" hover-class="cell-hover" :hover-stay-time="50" @click="sexListShow=true">
					<text class="cell-tit">{{app.langReplace('性别')}}</text>
					<view class="fr  fs30">{{model.sex_text}}<u-icon name="arrow-right ml20" size="20"></u-icon>
					</view>
				</view>
				<u-action-sheet :list="sexList" :cancel-btn="false" @click="selectSex" v-model="sexListShow"></u-action-sheet>
				<view class="list-cell mt20 mlr20 br20" hover-class="cell-hover" :hover-stay-time="50" @click="showRegion=true">
					<text class="cell-tit">{{app.langReplace('所在地区')}}</text>
					<view class="fr  fs30">
						{{defaultRegionText}}
						<u-icon name="arrow-right ml20" size="20"></u-icon>
					</view>
				</view>
				<u-picker
					mode="region"
					v-model="showRegion"
					:area-code="model.region_code"
					@confirm="confirmRegion"
				></u-picker>
			
			<view class="pbox mt40">
				<button size="default" :loading="isLoading" shape="circle" type="warning" class="primarybtn"  @click="submit">{{app.langReplace('确认修改')}}</button>
			</view>
		</u-form>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				from:'',
				isLoading:false,
				model: {
					headimgurl:'',
					edit_headimgurl:0,
					nick_name:'',
					real_name:'',
					sex:0,
					sex_text: this.app.langReplace('不公开'),
					region_code:[]
				},
				sexListShow:false,
				sexList:[
					{
						text: this.app.langReplace('不公开'),
						id:0
					},
					{
						text: this.app.langReplace('男'),
						id:1
					},
					{
						text: this.app.langReplace('女'),
						id:2
					}
				],
				defaultRegionText:'',
				showRegion:false,
				rules: {
					real_name: [{
							required: true,
							message: this.app.langReplace('请输入真实姓名'),
							trigger: 'blur',
						},
						{
							min: 2,
							max: 25,
							message: this.app.langReplace('姓名长度在2到25个字符'),
							trigger: ['change', 'blur'],
						}
						
					]
				},
				nick_name: [{
						required: true,
						message: this.app.langReplace('请输入')+' '+this.app.langReplace('昵称'),
						trigger: 'blur',
					}
				],
				border: false,
				errorType: ['toast'],
			};
		},
		//裁剪回调
		created() {
			uni.$on('uAvatarCropper', path => {
				this.$refs.uImage.isError = false;//图片加载失败时，裁剪回调重置
				this.model.headimgurl = path;
				this.model.edit_headimgurl = 1;
				let that = this;
				//#ifdef APP-PLUS
				 that.app.appReadFileToBase64(path,res=>{
					that.model.headimgurl = res
				});
				//#endif
				//#ifdef MP-WEIXIN
				 that.app.wxmpReadFileToBase64(path,res=>{
					that.model.headimgurl = res
				});
				//#endif

			})
		},
		onLoad(options){
			this.app.isLogin(this);//强制登陆
			let title = this.app.langReplace('基本资料');
			uni.setNavigationBarTitle({
				title
			})
			this.getUserInfo();
			this.from = options.from;
		},
		computed: {
		},
		methods: {
			chooseAvatar() {
				this.$u.route({
					url: '/uview-ui/components/u-avatar-cropper/u-avatar-cropper',
					params: {
						// 输出图片宽度，高等于宽，单位px
						destWidth: 300,
						// 裁剪框宽度，高等于宽，单位px
						rectWidth: 200,
						// 输出的图片类型，如果'png'类型发现裁剪的图片太大，改成"jpg"即可
						fileType: 'jpg',
					}
				})
			},
			getUserInfo(){
				//获取登录会员信息
				this.$u.api.getUserInfo().then(res => {
					let userInfo = res.data;
					if (userInfo.headimgurl){
						this.model.headimgurl = this.config.baseUrl+userInfo.headimgurl;
					}
					this.model.nick_name = userInfo.nick_name;
					this.model.real_name = userInfo.real_name;
					this.model.sex = userInfo.sex;
					this.model.sex_text = this.sexList[userInfo.sex].text;
					this.model.region_code = [userInfo.province,userInfo.city,userInfo.district]
					this.defaultRegionText = userInfo.region_text;
				});
			},
			// 预览图片
			preAvatar() {
				wx.previewImage({
					current: '', // 当前显示图片的 http 链接
					urls: [this.model.headimgurl] // 需要预览的图片 http 链接列表
				})
			},
			selectSex(index){
				var sexObj = this.sexList[index];
				this.model.sex = sexObj.id;
				this.model.sex_text = sexObj.text;
			},
			confirmRegion(e){
				//this.defaultRegion = [e.province.label,  e.city.label, e.area.label];
				this.defaultRegionText = e.province.label + ',' + e.city.label + ',' + e.area.label;
				this.model.region_code = [e.province.value, e.city.value, e.area.value];
			},
			submit() {
				var that = this;
				if (this.from != 'channel'){
					delete this.rules.real_name;
				}
				this.$refs.uForm.setRules(this.rules);
				this.$refs.uForm.validate(valid => {
					if (!valid) {
						return false;
					} 
					if (this.isLoading == true){
						return false;
					}
					this.isLoading = true;
					this.model._from = this.from;
					this.$u.post('member/api.users/editInfo', this.model).then(res => {
						this.isLoading = false;
						uni.showModal({
						    title: this.app.langReplace('提示'),
							confirmText: this.app.langReplace('确认'),
						    content: res.msg,
							showCancel:false,
						    success: function (res) {
								that.model.edit_headimgurl = 0;
						        if (res.confirm) {
						           that.getUserInfo();
						        } 
						    }
						});
					}).catch(res=>{
						this.isLoading = false;
					})
				});
			},
			
		}
	}
</script>

<style lang='scss'>
</style>
