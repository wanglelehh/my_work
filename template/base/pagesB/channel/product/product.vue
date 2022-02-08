<template>
	<view class="page-body">
		<view class="carousel">
			<swiper indicator-dots circular=true duration="400">
				<swiper-item class="swiper-item" v-for="(item,index) in imgList" :key="index">
					<view class="image-wrapper">
						<image
							:src="baseUrl+item.goods_img" 
							class="loaded" 
							mode="aspectFill"
						></image>
					</view>
				</swiper-item>
			</swiper>
		</view>

		<view class="introduce-section">
			<text class="title">{{selectGoods.goods_name}}</text>
			<view class="price-box">
				<text class="price-tip">¥</text>
				<text class="price">{{selectGoodsPrice}}</text>
			</view>
			<view class="bot-row">
				<text>库存: {{selectGoodsNumber}}</text>
			</view>
			
		</view>
		
		<view v-if="selectGoods.is_spec == 1" class="c-list">
			<view class="c-row b-b fs30" @click="toggleSpec('byCart')">
				<text class="tit">选择规格</text>
				<view class="con">
					<text class="selected-text" v-for="(sItem, sIndex) in specSelected" :key="sIndex">
						{{sItem.name}}
					</text>
				</view>
				<text class="yticon icon-you"></text>
			</view>
			
		</view>
		
		
		<view class="detail-desc">
			<view class="d-header">
				<text>图文详情</text>
			</view>
			<rich-text :nodes="desc"></rich-text>
		</view>
		
		<!-- 底部操作菜单 -->
		<view class="page-bottom">
			<view @click="app.goPage('/pagesB/channel/center/index')" class="p-b-btn">
				<u-icon name="home" size="50rpx"></u-icon>
				<text>首页</text>
			</view>
			<view @click="navToCartPage()"   class="p-b-btn">
				<u-icon name="shopping-cart" size="50rpx"></u-icon>
				<text>购物车</text>
			</view>
			
			
			<view class="action-btn-group">
				<button type="primary" class=" action-btn  buy-now-btn" @click="toggleSpec('byBuy')">立即购买</button>
				<button type="primary" class=" action-btn  add-cart-btn" @click="toggleSpec('byCart')">加入购物车</button>
			</view>
		</view>
		
		<prductSpec :specClass="specClass" @selectSpec="selectSpec" @toggleSpec="toggleSpec" :specSelected="specSelected"
		:selectGoods="selectGoods" :selectGoodsPrice="selectGoodsPrice" :selectGoodsNumber="selectGoodsNumber" 
		:specGoodsImg="specGoodsImg"  :specList="specList" :specChildList="specChildList"
		:purchaseType="purchaseType" :byType="byType"
		 ></prductSpec>
		 
	</view>
</template>

<script>
	import prductSpec from '@/pagesB/channel/product/spec';
	export default{
		components: {
			prductSpec
		},
		data() {
			return {
				baseUrl:this.config.baseUrl,
				purchaseType:0,//进货类型
				specClass:'none',
				specSelected:[],
				byType:'',
				imgList: [],
				desc: '',
				specList: [],
				specChildList: [],
				selectGoods:{},//选择的商品
				selectGoodsPrice:'0.00',
				selectGoodsNumber:0,
				specGoodsImg:'',
			};
		},
		async onLoad(options){
			if (typeof(options.purchaseType) == 'undefined' ){
				uni.showModal({
				    title: '提示',
				    content: '进货类型错误.',
					showCancel:false,
				    success: function (res) {
				        if (res.confirm) {
				          uni.navigateTo({
				              url: '/pagesB/channel/product/selectAdd'
				          });
				        } 
				    }
				});
				return false;
			}
			this.purchaseType = options.purchaseType;
			this.$u.post('channel/api.goods/getGoodsInfo', {'goods_id':options.goods_id,'purchaseType':this.purchaseType}).then(res => {
				this.selectGoods = res.data;
				this.imgList = res.data.imgList;
				this.desc = res.data.m_goods_desc;
				if (this.selectGoods.is_spec == 0){
					this.specGoodsImg = this.config.baseUrl+res.data.goods_thumb;
					this.selectGoodsPrice = res.data.price;
					this.selectGoodsNumber = res.data.goods_number;
				}else{
					this.specList = res.data.specList;
					this.specChildList = res.data.specChildList;
					this.specSelected = [];
					//规格 默认选中第一条
					this.specList.forEach(item=>{
						for(let cItem of this.specChildList){
							if(cItem.pid === item.id){
								this.$set(cItem, 'selected', true);
								this.specSelected.push(cItem);
								break; //forEach不能使用break
							}
						}
					})
					console.log(this.specChildList);
					this.showSelectSpec();
				}
			})
		},
		methods:{
			//规格弹窗开关
			toggleSpec(type = 'byCart'){
				if(this.specClass === 'show'){
					this.specClass = 'hide';
					setTimeout(() => {
						this.specClass = 'none';
					}, 250);
				}else if(this.specClass === 'none'){
					this.specClass = 'show';
				}
				this.byType = type;
			},
			//选择规格
			selectSpec(list){
				//存储已选择
				/**
				 * 修复选择规格存储错误
				 * 将这几行代码替换即可
				 * 选择的规格存放在specSelected中
				 */
				this.specSelected = []; 
				list.forEach(item=>{ 
					if(item.selected === true){ 
						this.specSelected.push(item); 
					} 
				})
				this.showSelectSpec();
			},
			showSelectSpec(){
				let selSkuArr = [];
				this.specSelected.forEach(item=>{ 
					selSkuArr.push(item.id); 
				})
				let selSku = selSkuArr.join(":");
				this.selectGoods.sub_goods.forEach(item=>{
					if (selSku == item.sku_val){
						this.selectGoodsPrice = item.price;
						this.selectGoodsNumber = item.goods_number;
						return false;
					}
				})
				this.specGoodsImg = this.config.baseUrl + this.selectGoods.goods_thumb;
				if (selSkuArr.length < 1) {
					return false;
				}
				let selSkuImgArr = [];
				this.specList.forEach(function(item,index){
					if (item.img_type == 2){
						selSkuImgArr.push(selSkuArr[index]);
					}
				})
				let selSkuImg = selSkuImgArr.join(":");
				this.selectGoods.imgSkuList.forEach(item => {
					if (selSkuImg == item.sku_val) {
						this.selectGoodsImg = this.config.baseUrl + item.goods_thumb;
						return false;
					}
				})
			},
			navToCartPage(){
				let purchaseType = this.purchaseType;
				uni.navigateTo({
					url: `/pagesB/channel/flow/cart?purchaseType=${purchaseType}`
				})
			},
			stopPrevent(){}
		},

	}
</script>

<style lang='scss'>
	page{
		background: $page-color-base;
		padding-bottom: 160rpx;
	}
	
	.carousel {
		height: 722rpx;
		swiper{
			height: 100%;
		}
		.image-wrapper{
			width: 100%;
			height: 100%;
		}
		.swiper-item {
			display: flex;
			justify-content: center;
			align-content: center;
			height: 750rpx;
			overflow: hidden;
			image {
				width: 100%;
				height: 100%;
			}
		}
		
	}
	
	/* 标题简介 */
	.introduce-section{
		background: #fff;
		padding: 20rpx 30rpx;
		position: relative;
		.title{
			font-size: 32rpx;
			color: $font-color-dark;
			height: 50rpx;
			line-height: 50rpx;
		}
		.price-box{
			display:flex;
			align-items:baseline;
			height: 64rpx;
			padding: 10rpx 0;
			font-size: 26rpx;
			color:$uni-color-primary;
		}
		.price{
			font-size: $font-lg + 2rpx;
		}
		.m-price{
			margin:0 12rpx;
			color: $font-color-light;
			text-decoration: line-through;
		}
		.coupon-tip{
			align-items: center;
			padding: 4rpx 10rpx;
			background: $uni-color-primary;
			font-size: $font-sm;
			color: #fff;
			border-radius: 6rpx;
			line-height: 1;
			transform: translateY(-4rpx); 
		}
		.bot-row{
			display:flex;
			align-items:center;
			height: 50rpx;
			font-size: $font-sm;
			color: $font-color-light;
			text{
				flex: 1;
			}
		}
	}
	
	
	.c-list{
		font-size: $font-sm + 2rpx;
		color: $font-color-base;
		background: #fff;
		.c-row{
			display:flex;
			align-items:center;
			padding: 20rpx 30rpx;
			position:relative;
		}
		.tit{
			width: 140rpx;
		}
		.con{
			flex: 1;
			color: $font-color-dark;
			.selected-text{
				margin-right: 10rpx;
			}
		}
		.bz-list{
			height: 40rpx;
			font-size: $font-sm+2rpx;
			color: $font-color-dark;
			text{
				display: inline-block;
				margin-right: 30rpx;
			}
		}
		.con-list{
			flex: 1;
			display:flex;
			flex-direction: column;
			color: $font-color-dark;
			line-height: 40rpx;
		}
		.red{
			color: $uni-color-primary;
		}
	}
	
	/*  详情 */
	.detail-desc{
		background: #fff;
		margin-top: 16rpx;
		.d-header{
			display: flex;
			justify-content: center;
			align-items: center;
			height: 80rpx;
			font-size: $font-base + 2rpx;
			color: $font-color-dark;
			position: relative;
				
			text{
				padding: 0 20rpx;
				background: #fff;
				position: relative;
				z-index: 1;
			}
			&:after{
				position: absolute;
				left: 50%;
				top: 50%;
				transform: translateX(-50%);
				width: 300rpx;
				height: 0;
				content: '';
				border-bottom: 1px solid #ccc; 
			}
		}
		img{
			width: 100%;
		}
	}
	
	
	/* 底部操作菜单 */
	.page-bottom{
		position:fixed;
		left: 30rpx;
		bottom:30rpx;
		z-index: 95;
		display: flex;
		justify-content: center;
		align-items: center;
		width: 690rpx;
		height: 100rpx;
		background: rgba(255,255,255,.9);
		box-shadow: 0 0 20rpx 0 rgba(0,0,0,.5);
		border-radius: 16rpx;
		
		.p-b-btn{
			display:flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			font-size: $font-sm;
			color: $font-color-base;
			width: 110rpx;
			height: 80rpx;
			.yticon{
				font-size: 40rpx;
				line-height: 48rpx;
				color: $font-color-light;
			}
			&.active, &.active .yticon{
				color: $uni-color-primary;
			}
			.icon-fenxiang2{
				font-size: 42rpx;
				transform: translateY(-2rpx);
			}
			.icon-shoucang{
				font-size: 46rpx;
			}
		}
		.action-btn-group{
			display: flex;
			height: 76rpx;
			border-radius: 100px;
			overflow: hidden;
			box-shadow: 0 20rpx 40rpx -16rpx #fa436a;
			box-shadow: 1px 2px 5px rgba(59, 123, 246, 0.4);
			background: linear-gradient(to right, #9dbfff,#377afa,#3b7bf6);
			margin-left: 20rpx;
			position:relative;
			&:after{
				content: '';
				position:absolute;
				top: 50%;
				right: 50%;
				transform: translateY(-50%);
				height: 28rpx;
				width: 0;
				border-right: 1px solid rgba(255,255,255,.5);
			}
			.action-btn{
				display:flex;
				align-items: center;
				justify-content: center;
				width: 180rpx;
				height: 100%;
				font-size: $font-base ;
				margin: 0rpx;
				padding: 0;
				border-radius: 0;
				background: transparent;
			}
		}
	}
	
</style>
