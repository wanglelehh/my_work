<template>
	<view class="page-body">
		<view class="top_box">
			<view class="type_image">
				<u-image width="100rpx" height="100rpx" :src="type_image"></u-image>
			</view>
			<view class="type_name font-w700 mt30">{{type_name}}</view>
			<view class="type_tip fs22">{{type_tip}}</view>
		</view>
		<view class="type_radio mt60">
			<radio-group @change="selectOrderType">
				<label class="radio_box" v-for="(item, index) in type_list" :key="index">
				   <radio class="radio" :value="index.toString()" :checked="index === 0" />
				{{item.name}}</label>
			</radio-group>
			<u-button size="default" shape="circle" type="primary" class="block mt80" @click="go">去下单</u-button>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			let that = this;
			return {
				type_list: [{
						name: '云仓订单',
						value: 1,
						image:"/pagesB/static/channel/images/order_type01.png",
						tip:'提供一站式云仓服务'
					},
					{
						name: '现货订单',
						value: 2,
						image:"/pagesB/static/channel/images/order_type02.png",
						tip:'提供一站式实体仓服务'
					}
				],
				order_type: 0,
				type_image:'',
				type_name:'',
				type_tip:''
			}
		},
		onLoad() {
			
			//获取登录会员信息
			this.$u.api.getProxyInfo().then(res => {
				if (res.data.proxyInfo.proxyLevel.purchase_setting == 1){
					that.type_list.splice(1,1);
				}else if(res.data.proxyInfo.proxyLevel.purchase_setting == 2){
					that.type_list.splice(0,1);
				}
				this.type_image = this.type_list[0].image;
				console.log(this.type_list);
				this.type_name = this.type_list[0].name;
				this.type_tip = this.type_list[0].tip;
			});	
			
			let that = this;
		},
		computed: {},
		onReady() {},
		methods: {
			selectOrderType:function(evt){
				this.order_type = evt.target.value;
				this.type_image = this.type_list[evt.target.value].image;
				this.type_name = this.type_list[evt.target.value].name;
				this.type_tip = this.type_list[evt.target.value].tip;
			},
			go(){
				uni.navigateTo({
					url: '/pagesB/channel/product/list?purchaseType='+this.type_list[this.order_type].value
				});
			}
		}
	}
</script>

<style lang="scss">
	.top_box {
		padding-top: 150rpx;
		text-align: center;
		.type_image {
			width: 100rpx;
			display:block;
			margin: 0rpx auto;
		}
		.type_name {
			font-size: 34rpx;
		}
		.type_tip {
			color: #999999;
			margin-top: 10rpx;
		}
	}
	
	.type_radio{
		padding: 0rpx 50rpx;
		.radio_box{
			display: block;
			width: 90%;
			padding: 30rpx ;
			margin-top: 30rpx;
			background-color: #f5f5f5;
			border-radius:50rpx;
			.radio{
				float: right;
			}
		}
	}
</style>
