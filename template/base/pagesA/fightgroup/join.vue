<template>
	<view class="page-body" :class="[app.setCStyle()]">
		<view class="fs30 font-w600 p20 bg-white">{{app.langReplace('商品信息')}}</view>
			<!-- 商品 -->
			<view class="g-item" @click="app.goPage('info?fg_id='+fgInfo.fg_id+'&join_id='+d_join_id)">
				<image :src="goods_thumb"></image>
				<view class="right">
					<text class="title ">{{fgInfo.goods.goods_name}}</text>
					<view >
						<text class="fs28">¥</text>
						<text class="ff fs42">{{fgInfo.show_price}}</text>
						<text class="ml10 fs32 font-w600 color-f6" v-if="fgInfo.goods.is_spec==1">{{app.langReplace('起')}}</text>
						<text class="bg-base color-ff plr10 fs22 ml10">{{app.langReplace('拼团价')}}</text>
						<text class="color-33 fs24 ml10" >{{app.langReplace('单买价')}}: ￥{{fgInfo.goods.min_price}}</text>
					</view>
				</view>
			</view>
		
		<view class="mt10 mb20 bg-white p20 fs28">
			<view class="smll">
				<text class="bg-base color-ff plr10 ptm5 mr10">{{fgInfo.success_num}} {{app.langReplace('人团')}}</text>
				<view v-if="fgInfo.status == 1">
					<text class="base-color">{{app.langReplace('还差')}}：{{fgJoinInfo.last_num}}，</text>{{app.langReplace('剩余时间')}}：<text class="color-red">{{liveCountdown}}</text>
				</view>
				<view v-else-if="fgInfo.status == 2">
					<text class="base-color">{{app.langReplace('拼团满团，待完成支付')}}，</text>{{app.langReplace('剩余时间')}}：<text class="color-red">{{liveCountdown}}</text>
				</view>
				<view v-else-if="fgInfo.status == 3">
					<text class="base-color">{{app.langReplace('拼团成功')}}</text>
				</view>
				<view v-else-if="fgInfo.status == 9">
					<text class="base-color">{{app.langReplace('拼团失败')}}</text>
				</view>
			</view>
			<view v-if="fgInfo.status < 9" class="clearfix">
				<view  v-for="(item, index) in fgJoinInfo.order" :key="index" class="fguser border-color">
					<image :src="(item.headimgurl?baseUrl+item.headimgurl:'/static/public/images/headimgurl.jpg')"></image>
					<text v-if="index == 0" class="tip color-ff bg-base fs24 w100 text-center">{{app.langReplace('团长')}}</text>
				</view>
			</view>
		</view>
		<view class="p20 bg-white">
			<view class="fs30 font-w600">{{app.langReplace('参团规则')}}</view>
			<view class="fs28 color-66 mt20">{{app.langReplace('如规定时间内团内人数不足，则订单取消，支付款原路返还')}}</view>
		</view>
		<view class="p20">
			<view v-if="fgInfo.status == 1">
				<button  size="default" class="primarybtn mt40" @click="toggleSpec('fgbuy',d_join_id)">{{app.langReplace('参与拼单')}}</button>
				<button size="default" class="primarybtnB mt40" @click="app.copy(location_href)">{{app.langReplace('复制链接')}}</button>
				<button  size="default" class="primarybtnB mt40" @click="app.goPage('info?fg_id='+fgInfo.fg_id+'&join_id='+d_join_id)">{{app.langReplace('查看拼团商品')}}</button>
			</view>
			<view v-else>
				<view class="color-red">{{app.langReplace('当前拼单非【拼团中】状态，您不能参与拼单，您可以自行发起拼团，或者前往拼团商品中参与其它人发起的拼单')}}</view>
				<button  size="default" class="primarybtn mt40" @click="toggleSpec('fgbuy',0)">{{app.langReplace('发起拼单')}}</button>
				<button  size="default" class="primarybtnB mt40" @click="app.goPage('info?fg_id='+fgInfo.fg_id)">{{app.langReplace('查看拼团商品')}}</button>
			</view>
		</view>
		<goodsSpec :specClass="specClass" @selectSpec="selectSpec" @toggleSpec="toggleSpec" :specSelected="specSelected"
		 :selectGoods="selectGoods" :selectGoodsPrice="selectGoodsPrice" :selectGoodSmarketPrice="selectGoodSmarketPrice"
		 :selectGoodsNumber="selectGoodsNumber" :selectGoodsImg="selectGoodsImg" :specList="specList" :specChildList="specChildList"
		 :selectSkuId="selectSkuId"  :byType="byType" :fg_id="fg_id" :join_id="join_id"></goodsSpec>
	</view>
</template>

<script>
	import goodsSpec from '@/pages/shop/goods/spec';
	export default {
		components: {
			goodsSpec,
		},
		data() {
			return {
				baseUrl: this.config.baseUrl,
				setting: {},
				d_join_id: 0,
				join_id: 0,
				fg_id:0,
				fgInfo:{
					goods:{}
				},
				fgJoinInfo:{},
				liveCountTimes:'',
				liveCountdown:'',
				goods_thumb:'',
				specClass: 'none',
				specSelectedArr: {},
				specSelected: [],
				byType: '',
				skuGNum: {},
				specList: [],
				specChildList: [],
				selectFGoods:{},//拼团商品信息
				selectGoods: {}, //选择的商品
				selectSkuId:0,
				selectGoodsPrice: '0.00',
				selectGoodSmarketPrice: '0.00',
				selectGoodsNumber: 0,
				selectGoodsImg: '',
				location_href:''
			}
		},
		onLoad(option) {
			let title = this.app.langReplace('参与拼单');
			uni.setNavigationBarTitle({
				title
			})
			let join_id = parseInt(option.join_id);
			if (isNaN(join_id) == true || join_id < 1) {
				this.app.showModal(this.app.langReplace('ID传值错误.'), '/pages/shop/center/index', true);
				return false;
			}
			this.d_join_id = join_id;
			this.location_href = window.location.href;
			this.setting = uni.getStorageSync("setting");
		},
		onShow() {
			this.getInfo();
		},
		methods: {
			getInfo() {
				this.$u.post('fightgroup/api.goods/fgjoin', {
					join_id: this.d_join_id
				}).then(res => {
					this.fgJoinInfo = res.data.fgJoinInfo;
					this.fgInfo = res.data.fgInfo;
					this.fg_id = this.fgInfo.fg_id;
					if (typeof(this.fgJoinInfo._fail_time) != ''){
						this.getLiveTimeCount(this.fgJoinInfo._fail_time);
					}
					this.goods_thumb = this.baseUrl+this.fgInfo.goods.goods_thumb
					this.selectGoods = this.fgInfo.goods;
					this.selectGoods.show_price = this.fgInfo.show_price;
					this.selectGoods.show_stock_num = res.data.show_stock_num;
					if (this.selectGoods.is_spec == 0) {
						this.selectGoodsImg = this.config.baseUrl + this.selectGoods.goods_thumb;
						this.selectGoodsPrice = this.selectGoods.fg_sale_price;
						this.selectGoodSmarketPrice = this.selectGoods.market_price;
					} else {
						this.specList = this.selectGoods.specList;
					}
					
				}).catch(res => {})
			},
			toggleSpec(type ,join_id = 0) {
				this.join_id = join_id;
				if (this.specClass === 'show') {
					this.specClass = 'hide';
					setTimeout(() => {
						this.specClass = 'none';
					}, 250);
				} else if (this.specClass === 'none') {
					this.specClass = 'show';
				}
				this.byType = type;
				if (this.selectGoods.is_spec == 1){
					this.specChildList = [];
					this.selectGoods.specChildList.forEach(item => {
						item.selected = false;
						this.specChildList.push(item);
					})
					
					if (typeof(this.specSelectedArr[type]) == 'undefined'){
						let specSelected = [];
						this.specSelectedArr[type] = specSelected;
					}else{
						this.specChildList.forEach(item => {
							for (let cItem of this.specSelectedArr[type]) {
								if (cItem.id === item.id) {
									this.$set(item, 'selected', true);
								}
							}
						})
					}
					this.specSelected = this.specSelectedArr[type];
					this.showSelectSpec();
				}else{
					this.selectGoodsPrice = this.selectGoods.fg_sale_price;
					this.selectGoodsNumber = this.selectGoods.fg_sale_number;
				}
			},
			//选择规格
			selectSpec(list) {
				//存储已选择
				/**
				 * 修复选择规格存储错误
				 * 将这几行代码替换即可
				 * 选择的规格存放在specSelected中
				 */
				let specSelected = [];
				list.forEach(item => {
					if (item.selected === true) {
						specSelected.push(item);
					}
				})
				this.specSelectedArr[this.byType] = specSelected;
				this.specSelected = specSelected;
				this.showSelectSpec();
			},
			showSelectSpec() {
				this.selectSkuId = 0;
				let selSkuArr = [];
				this.specSelected.forEach(item => {
					selSkuArr.push(item.id);
				})
				let selSku = selSkuArr.join(":");
				Object.keys(this.selectGoods.sub_goods).forEach(sku_id => {
					let item = this.selectGoods.sub_goods[sku_id];
					if (selSku == item.sku_val) {
						this.selectGoodSmarketPrice = item.market_price;
						if (item.fg_sale_price > 0){
							this.selectSkuId = parseInt(item.sku_id);
							this.selectGoodsPrice = item.fg_sale_price;
							this.selectGoodsNumber = item.fg_goods_number;
						}
						return false;
					}
				})
				if (this.selectSkuId < 1){
					this.selectGoodsImg = this.config.baseUrl + this.selectGoods.goods_thumb;
				}
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
			getLiveTimeCount(startTime){
				let that = this;
				if (typeof(this.liveCountTimes) != 'undefined'){
					clearInterval(this.liveCountTimes);
				}
				this.liveCountTimes=setInterval(()=>{
					let nowTime = new Date().getTime();
					let liveCountdown = '';
					//注：不论安卓还是ios，请将时间如 2020-02-02 20:20:20 转化为 2020/02/02 20:20:20 这种形式后再使用，否则无法转换，如下转换即可↓
					let transedPreTime= startTime.replace(/-/g,'/') //这里转化时间格式为以/分隔形式
					let preTime = new Date(transedPreTime).getTime()
					let obj = null;
					if(preTime - nowTime > 0){
						let time = (preTime - nowTime) / 1000;
						if (time > 0){
							let day = parseInt(time / (60 * 60 * 24));
							let hou = parseInt(time % (60 * 60 * 24) / 3600);
							let min = parseInt(time % (60 * 60 * 24) % 3600 / 60);
							let sec = parseInt(time % (60 * 60 * 24) % 3600 % 60);
							let msec = Math.floor(time * 1000 % 1000 / 100);
							obj = {
								day: day<10?'0'+day:day,
								hou: hou<10?'0'+hou:hou,
								min: min<10?'0'+min:min,
								sec: sec<10?'0'+sec:sec
							};
							if (day > 0){
								liveCountdown += obj.day + '天';
							}
							if (hou > 0 ){
								liveCountdown += obj.hou + ':';
							}
							liveCountdown += obj.min + ':' + obj.sec;
							liveCountdown += ':'+ msec;
						}else{
							liveCountdown = '已结束';
						}
					}else{
						liveCountdown = '已结束';
					}  
					that.liveCountdown = liveCountdown;
				},100)
			}
		}
	}
</script>

<style lang="scss">
	page {
		background: $page-color-base;
		padding-bottom: 100rpx;
	}

	.g-item {
		display: flex;
		padding: 20rpx;
		background-color: #FFFFFF;
		image {
			flex-shrink: 0;
			display: block;
			width: 140rpx;
			height: 140rpx;
			border-radius: 10rpx;
		}

		.right {
			flex: 1;
			padding-left: 20rpx;
			overflow: hidden;
			display: flex;
			justify-content: space-between;
			flex-direction: column;
		}

		.title {
			display: block;
			font-size: 28rpx;
			color: $font-color-dark;
			line-height: 40rpx;
			height: 80rpx;
			overflow: hidden;
			text-overflow: ellipsis;
			display: -webkit-box;
			-webkit-line-clamp: 2;
			-webkit-box-orient: vertical;
		}
	}
	.fguser{
		width: 120rpx;
		height: 120rpx;
		overflow: hidden;
		border-radius: 50%;
		position: relative;
		margin: 0rpx 10rpx;
		margin-top: 20rpx;
		float: left;
		border: 1rpx dotted;
		display:flex;
		justify-content: space-around;
		align-items: center;
		image{
			width: 100%;
		}
		.tip{
			position: absolute;
			bottom: 0rpx;
		}
	}
</style>
