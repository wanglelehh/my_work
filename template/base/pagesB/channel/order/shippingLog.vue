<template>
	<view class="page-body" :class="[app.setCStyle()]">
		<view v-if="shipping_name != ''" class="p20">
			<text style="color:#19be6b">{{shipping_name}}</text>: {{invoice_no}}
			<text class="b-allcc br20 fs28 fr" @click="app.copy(invoice_no)">复制</text>
		</view>
		<view class="wrap ">
			<u-time-line>
				<u-time-line-item  v-for="(item, index) in shippingLog" :key="index"  nodeTop="2">
					<template v-slot:node>
						<view v-if="index == 0" class="u-node" style="background: #19be6b;">
							<u-icon name="pushpin-fill" color="#fff" :size="24"></u-icon>
						</view>
						<view v-else class="u-node" >
								<u-icon name="car" color="#fff" :size="24"></u-icon>
						</view>
					</template>
					<template v-slot:content>
						<view>
							<view class="u-order-desc">{{item.context}}</view>
							<view class="u-order-time">{{item.time}}</view>
						</view>
					</template>
				</u-time-line-item>
			</u-time-line>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				order_id: 0,
				shipping_name: '',
				invoice_no: '',
				shippingLog:[]
			};
		},
		onLoad(options) {
			let order_id = parseInt(options.order_id);
			if (isNaN(order_id) == true || order_id < 1) {
				this.app.showModal('订单ID传值错误.',-1);
				return false;
			}
			this.order_id = order_id;
			this.getShippingLog();
		},
		methods: {
			//获取物流信息
			getShippingLog(){
				this.$u.post('publics/api.shipping/getLog', {
					order_id: this.order_id,
					type:'channel'
				}).then(res => {
					if (res.code == 1){
						this.shippingLog = res.data.data;
						this.shipping_name = res.data.shipping_name;
						this.invoice_no = res.data.invoice_no;
						delete this.shippingLog.invoice_no;
						delete this.shippingLog.shipping_name;
					}
				}).catch(res=>{
					this.app.showModal(res.msg,-1);
				})
			},
			
		},
	}
</script>

<style lang="scss">
	.wrap {
		padding: 24rpx 24rpx 24rpx 40rpx;
	}
	.u-node {
		width: 44rpx;
		height: 44rpx;
		border-radius: 100rpx;
		display: flex;
		justify-content: center;
		align-items: center;
		background: #d0d0d0;
	}
	
	.u-order-title {
		color: #333333;
		font-weight: bold;
		font-size: 32rpx;
	}
	
	.u-order-title.unacive {
		color: rgb(150, 150, 150);
	}
	
	.u-order-desc {
		color: rgb(150, 150, 150);
		font-size: 28rpx;
		margin-bottom: 6rpx;
	}
	
	.u-order-time {
		color: rgb(200, 200, 200);
		font-size: 26rpx;
	}
	
	.tel {
		color: $u-type-primary;
	}
</style>
