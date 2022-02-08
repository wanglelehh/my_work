<template>
	<view class="page-body bg-white">
		<view class="top_box">
			<view class="img">
				<u-image  width="150rpx" height="150rpx" src="/pagesB/static/channel/images/title_icon/icon_01.png"></u-image>
			</view>
			<text>邀请代理</text>
		</view>
		<view class="type_radio mt60">
			<radio-group @change="select">
				<label class="radio_box" v-for="(item, index) in roleList" :key="index">
					<view class="label ">
						{{item.label}}
						<view class="tip">
							{{item.limit_tip}}
						</view>
					</view>
					<radio class="radio" :value="item.value.toString()" />
				</label>
			</radio-group>
			<u-button size="default" shape="circle" type="primary" class="block mt80" @click="next">确定邀请</u-button>
		</view>
	</view>
</template>


<script>
	export default {
		data() {
			return {
				roleList: {},
				select_role_id: 0
			}
		},
		onLoad() {
			//获取可邀请的代理层级
			this.$u.post('channel/api.proxy_users/getInviteRole').then(res => {
				this.roleList = res.data.roleList;
			});
		},
		onShow: function() {},
		computed: {},
		onReady() {},
		methods: {
			select(evt) {
				this.select_role_id = evt.target.value;
			},
			next() {
				if (this.select_role_id < 1){
					this.$u.toast('请选择邀请代理层级.');
					return false;
				}
				this.app.goPage('/pagesB/channel/team/invitePoster?role_id='+this.select_role_id);
			}
		}
	}
</script>

<style lang="scss">
	.top_box {
		text-align: center;
		padding-top: 100rpx;
		color: #333;
		font-size: 36rpx;
		font-weight: 600;
		.img {
			width: 150rpx;
			display: block;
			margin: 0rpx auto;
			margin-bottom: 30rpx;
		}
	}

	.type_radio {
		padding: 0rpx 50rpx;
		.radio_box {
			display: flex;
			width: 90%;
			padding: 30rpx;
			margin-top: 30rpx;
			background-color: #f5f5f5;
			border-radius: 50rpx;
			align-items: center;
			.label{
				flex:1;
				.tip{
					display: block;
					font-size: 26rpx;
					color: $font-color-light;
				}
			}
		}
	}
	
</style>
