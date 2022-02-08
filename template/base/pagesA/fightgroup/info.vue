<template>
	<view class="page-body" :class="[app.setCStyle()]">
		
		<view class="carousel">
			<swiper indicator-dots circular=true duration="400">
				<swiper-item class="swiper-item" v-for="(item,index) in imgList" :key="index">
					<view class="image-wrapper">
						<image :src="baseUrl+item.goods_img" class="loaded" mode="aspectFill"></image>
					</view>
				</swiper-item>
			</swiper>
		</view>

		<view class="introduce-section">
			<view class="smll mb20">
				<view class="price-box base-color flex_bd">
					<text class="price-tip fs28">¥</text>
					<text class="price ff fs52">{{selectFGoods.show_price}}</text>
					<text class="ml10 fs32 font-w600 color-f6" v-if="selectGoods.is_spec==1">{{app.langReplace('起')}}</text>
					<text class="bg-base color-ff plr10 fs22 ml10">{{app.langReplace('拼团价')}}</text>
					
				</view>
				<view v-if="selectFGoods.buy_goods_num > 0" class="fs22">
					{{app.langReplace('已拼')}}:{{selectFGoods.buy_goods_num}}{{app.langReplace('件')}}
				</view>
			</view>
			<text class="title"><text class="font-w700 color-f6">【{{selectFGoods.success_num}}人团】</text>{{selectGoods.goods_name}}</text>
			<view v-if="selectFGoods.share_desc" class="fs28 color-99">{{selectFGoods.share_desc}}</view>
		</view>
		<view class="bg-white mt20 p20 fs flex fs30">
			<view class="flex_bd">
				<text v-if="selectFGoods.fg_status==0">{{app.langReplace('距开始')}}：{{liveCountdown[0]}}</text>
				<text v-else-if="selectFGoods.fg_status==1" class="color-red">{{app.langReplace('距结束')}}：{{liveCountdown[0]}}</text>
				<text v-else-if="selectFGoods.fg_status==9">{{app.langReplace('已结束')}}</text>
			</view>
			<view class="fr color-99 fs28" @click="toggletipUp">{{app.langReplace('参团规则')}}<u-icon name="arrow-right"></u-icon></view>
		</view>
		<view v-if="fgingListArr.length > 0"  class="bg-white mt20 p20">
			<view class="smll mb20">
				<view class="flex_bd fs30 font-w600">{{app.langReplace('正在拼单')}}</view>
				<view class="color-99 fs28" @click="closeFgingBox">{{app.langReplace('查看全部')}}<u-icon name="arrow-right"></u-icon></view>
			</view>
			<view style="height: 180rpx; overflow: hidden;">
				<swiper autoplay="true" circular="true" :interval="5000" vertical="true" disable-touch="true">
				   <swiper-item v-for="(fgarr,index) in fgingListArr" :key="index">
					   <view  v-for="(fgitem,indexb) in fgarr" :key="indexb" class="flex mt10 fs28 smll">
							<view class="flex_bd">
								<view class="smll">
									<view class="fgUserImg"  >
										<image :src="(fgitem.order[0].headimgurl?baseUrl+fgitem.order[0].headimgurl:'/static/public/images/headimgurl.jpg')" class="w100"  mode="widthFix"></image>
									</view>
									<text class="flex_bd ml20" >{{fgitem.order[0].nick_name}}</text>
									<view class="mr10 color-99">{{app.langReplace('还差')}}：<text class="base-color mr10">{{fgitem.last_num}}</text></view>
								</view>
							</view>
							<view v-if="selectFGoods.fg_status==1" class="p10 plr20 color-ff bg-base br10" @click="toggleSpec('fgbuy',fgitem.gid)">{{app.langReplace('去拼单')}}</view>
							<view v-else-if="selectFGoods.fg_status==0" class="p10 plr20 color-ff bg-gray br10">{{app.langReplace('待开团')}}</view>
							<view v-else class="p10 plr20 color-ff bg-gray br10">{{app.langReplace('已结束')}}</view>
					   </view>
				   </swiper-item>
				</swiper>
			</view>
		</view>
		<!-- 用户评价 -->
		<view id="comment_box" >
			<view v-if="selectGoods.shop_goods_comment == 1" class="comment_box mt20">
				<view class="title smll">
					<view class="flex_bd">{{app.langReplace('用户评论')}}<text class="num">({{comment.count}})</text></view>
					<view class="more" @click="app.goPage('/pages/shop/comment/index?goods_id='+goods_id)">{{app.langReplace('更多')}}<u-icon name="arrow-right"></u-icon>
					</view>
				</view>
				<view v-if="comment.loaded === true && comment.list.length === 0" class="empty-box"></view>
				<view class="list mt20" v-for="(item,index) in comment.list" :key="index">
					<view class="tit smll">
						<view class="image ">
							<image :src="baseUrl + item.headimgurl"></image>
						</view>
						<view class="flex_bd">
							{{item.user_name}}
						</view>
						<view class="time">{{item._time}}</view>
					</view>
					<view class="content">
						{{item.content}}
					</view>
					<view class="images">
						<u-grid :col="4" class="u_grid" :border="false">
							<u-grid-item v-for="(img,imgIndex) in item.imgs" :key="imgIndex" @click="app.showImg(item.imgs,imgIndex,config.baseUrl)">
								<image :src="baseUrl+img"></image>
							</u-grid-item>
						</u-grid>
					</view>
				</view>
			</view>
		</view>
		<view id="detail_desc" class="detail_desc mt20">
			<view class="title ">
				<view >{{app.langReplace('详情')}}</view>
			</view>
			<view  v-if="selectGoods.video_position == 2 && selectGoods.video_url != ''" >
				<view v-if="videoPaly == false" class="video_cover" style="min-height:500rpx;">
					<image class="bg" :src="baseUrl+selectGoods.video_cover" style="height:100%;width:100%"></image>
					<image class="play" src="/static/public/images/video_icon/video_play.png" style="" @click="startplay"></image>
				</view>
				<video v-else :enable-progress-gesture="false" autoplay="true" @pause="videoPaly = false"  @ended="videoPaly = false"  :muted="sound" :src="baseUrl+selectGoods.video_url" style="width: 100%;" >
					<cover-image @click="changevoice" class="voice" :src="sound?mute:voice"></cover-image>
				</video>
			</view>
			<u-parse :content="desc" :imageProp="{'mode':'widthFix'}" noData="" @navigate="goUrl" ></u-parse>
		</view>

		<!-- 底部操作菜单 -->
		<view class="page-bottom flex">
			<view @click="app.goPage('/pages/shop/index/index')" class="p-b-btn " >
				<u-icon  name="home"  size="46" color="#000"></u-icon>
				<text>{{app.langReplace('首页')}}</text>
			</view>
			<view @click="app.goPage('/pages/shop/flow/cart')" class="p-b-btn relative">
				<u-icon name="shopping-cart" size="46" color="#000"></u-icon>
				<text>{{app.langReplace('购物车')}}</text>
				<view class="cartNum bg-base">{{cartNum}}</view>
			</view>
			<view class="p-b-btn " @click="collect">
				<u-icon v-if="selectGoods.is_collect==1" name="heart-fill" size="46" class="base-color" ></u-icon>
				<u-icon v-else name="heart" size="46" color="#000" @click="collect"></u-icon>
				<text>{{app.langReplace('收藏')}}</text>
			</view>
			
			<view class=" fg_btn_group flex_bd fs32 flex">
				<view v-if="selectGoods.is_alone_sale == 1" class=" action_btn no-border block bg-light "  @click="toggleSpec('onbuycart')">
					￥{{selectGoods.min_price}}
					<view >{{app.langReplace('单独购买')}}</view>
				</view>
				<view class=" action_btn flex_bd no-border block bg-base" @click="toggleSpec('fgbuy',d_join_id)">
					￥{{selectFGoods.show_price}}
					<view v-if="d_join_id == 0" >{{app.langReplace('发起拼单')}}</view>
					<view v-else >{{app.langReplace('参与拼单')}}</view>
				</view>
			</view>
		</view>
		<!--end 底部按钮 -->
		<goodsSpec :specClass="specClass" @selectSpec="selectSpec" @toggleSpec="toggleSpec" :specSelected="specSelected"
		 :selectGoods="selectGoods" :selectGoodsPrice="selectGoodsPrice" :selectGoodSmarketPrice="selectGoodSmarketPrice"
		 :selectGoodsNumber="selectGoodsNumber" :selectGoodsImg="selectGoodsImg" :specList="specList" :specChildList="specChildList"
		 :selectSkuId="selectSkuId"  :byType="byType" :fg_id="fg_id" :join_id="join_id"></goodsSpec>
		<view class="popup" :class="tipUpClass" @touchmove.stop.prevent="stopPrevent" @click="toggletipUp">
			<!-- 遮罩层 -->
			<view class="mask"></view>
			<view class="layer attr-content" @click.stop="stopPrevent">
				<u-icon name="close" size="28" class="close" @click="toggletipUp"></u-icon>
				<text class="fs36 font-w700">{{app.langReplace('参团规则')}}</text>
				<view class="h200 mt20 fw28 color-66">
					1、{{app.langReplace('如规定时间内团内人数不足，则订单取消，支付款原路返还')}}
				</view>
			</view>
		</view>
		<view class="fgingbox" :class="fgingboxClass"  @touchmove.stop.prevent="stopPrevent"  >
			<view class="content" >
				<u-icon name="close" size="26" class="close" @click="closeFgingBox"></u-icon>
				<view class="title">{{app.langReplace('可参与的拼单')}}</view>
				<view class="fglist" @touchmove.stop.prevent="stopPrevent">
					<view  v-for="(fgitem,index) in fgingList" :key="index" v-if="index < 10" class="flex mt10 fs28 smll">
						<view class="flex_bd">
							<view class="smll">
								<view class="fgUserImg"  >
									<image :src="(fgitem.order[0].headimgurl?baseUrl+fgitem.order[0].headimgurl:'/static/public/images/headimgurl.jpg')" class="w100"  mode="widthFix"></image>
								</view>
								<text class="flex_bd ml20" >{{fgitem.order[0].nick_name}}</text>
								<view class="mr10 color-99">{{app.langReplace('还差')}}：<text class="base-color">{{fgitem.last_num}}</text></view>
							</view>
						</view>
						<view class="p10 plr20 color-ff bg-base br10" @click="toggleSpec('fgbuy',fgitem.gid)">{{app.langReplace('去拼单')}}</view>
					</view>
					<view class="text-center color-cc fs28 mt20 hide">仅显示10个可参与的拼单</view>
				</view>
			</view>
		</view>
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
				fg_id:0,
				join_id:0,
				d_join_id:0,
				goods_id: 0,
				cartNum: 0,
				scrollTop: 0,
				tabActive: 0,
				detailDesclTop: 0,
				commentTop: 0,
				baseUrl: this.config.baseUrl,
				specClass: 'none',
				specSelectedArr: {},
				specSelected: [],
				byType: '',
				imgList: [],
				desc: '',
				specList: [],
				specChildList: [],
				selectFGoods:{
					fg_cate:{}
				},//拼团商品信息
				selectGoods: {}, //选择的商品
				selectSkuId:0,
				selectGoodsPrice: '0.00',
				selectGoodSmarketPrice: '0.00',
				selectGoodsNumber: 0,
				selectGoodsImg: '',
				comment: {
					count: 0,
					list: [],
				},
				isLoading:false,
				liveCountTimes:'',
				liveCountdown:[],
				tipUpClass:'none',
				fgingboxClass:'hide',
				fgingList:[],
				fgingListArr:[],
				getFGingNum:0,
				isGetFGing:true
			};
		},
		async onLoad(options) {
			let title = this.app.langReplace('拼团详情');
			uni.setNavigationBarTitle({
				title
			})
			this.fg_id = parseInt(options.fg_id);
			if (typeof(options.join_id) == 'undefined'){
				this.d_join_id = 0;
			}else{
				this.d_join_id = parseInt(options.join_id);
			}
			this.getGoodsInfo();
			
			this.getComment();
			this.loadCartNum();
		},
		onShow() {
			this.isGetFGing = true;
			this.getFGing();
		},
		onHide(){
			this.isGetFGing = false;
		},
		computed: {
		},
		methods: {
			goUrl(url){
				this.app.goPage(url);
			},
			//获取商品信息
			getGoodsInfo() {
				this.$u.post('fightgroup/api.goods/info', {
					'fg_id': this.fg_id
				}).then(res => {
					this.selectFGoods = res.data;
					this.selectGoods = res.data.goods;
					this.selectGoods.show_price = this.selectFGoods.show_price;
					this.selectGoods.show_stock_num = res.data.show_stock_num;
					this.goods_id = res.data.goods.goods_id;
					this.imgList = res.data.goods.imgList;
					this.desc = res.data.goods.m_goods_desc;
					this.getLiveTimeCount([res.data.downDate]);
					if (this.selectGoods.is_spec == 0) {
						this.selectGoodsImg = this.config.baseUrl + res.data.goods.goods_thumb;
						this.selectGoodsPrice = res.data.goods.fg_sale_price;
						this.selectGoodSmarketPrice = res.data.goods.market_price;
					} else {
						this.specList = res.data.goods.specList;
					}
					
				})
			},
			//获取正在拼团列表
			getFGing() {
				if (this.isGetFGing == false || this.getFGingNum > 5){
					return false;
				}
				this.$u.post('fightgroup/api.goods/getFGing', {
					'showLoading':false,
					'fg_id': this.fg_id
				}).then(res => {
					this.getFGingNum++;
					this.fgingList = res.data;
					this.fgingListArr = [];
					let that = this;
					let fglist = [];
					this.fgingList.forEach(function(item,key) {
						fglist.push(item);
						if (key % 2 == 1){
							that.fgingListArr.push(fglist);
							fglist = [];
						}
					})
					if (fglist.length > 0){
						that.fgingListArr.push(fglist);
					}
					let num = 1;
					if (this.fgingList.length > 0){
						num = this.fgingList.length;
					}
					setTimeout(()=>{
						this.getFGing();
					},  num * 5000);
				})
			},
			//获取评论
			getComment() {
				this.$u.post('shop/api.comment/getListByGoods', {
					'goods_id': this.goods_id,
					'limit': 3
				}).then(res => {
					this.comment.count = res.data.total_count;
					this.comment.list = res.data.list;
					this.$set(this.comment, 'loaded', true);
				})
			},
			collect() { //收藏
				if (this.isLoading == true){
					return false;
				}
				this.isLoading = true;
				this.$u.post('shop/api.goods/collect', {
					'goods_id': this.goods_id
				}).then(res => {
					this.isLoading = false;
					let is_collect = this.selectGoods.is_collect;
					this.selectGoods.is_collect = is_collect == 1 ? 0 : 1;
				}).catch(res=>{
					this.isLoading = false;
				})
			},
			toggletipUp(){
				if (this.tipUpClass === 'show') {
					this.tipUpClass = 'hide';
					setTimeout(() => {
						this.tipUpClass = 'none';
					}, 250);
				} else if (this.tipUpClass === 'none') {
					this.tipUpClass = 'show';
				}
			},
			//规格弹窗开关
			toggleSpec(type = 'fgbuy',join_id = 0) {
				this.join_id = join_id;
				if (this.specClass === 'show') {
					if (type == 'onbuycart'){
						this.loadCartNum();
					}
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
					if (type == 'fgbuy'){
						this.selectGoodsPrice = this.selectGoods.fg_sale_price;
						this.selectGoodsNumber = this.selectGoods.fg_sale_number;
					}else{
						this.selectGoodsPrice = this.selectGoods.sale_price;
						this.selectGoodsNumber = this.selectGoods.goods_number;
					}
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
						if (this.byType == 'fgbuy'){
							if (item.fg_sale_price > 0){
								this.selectSkuId = parseInt(item.sku_id);
								this.selectGoodsPrice = item.fg_sale_price;
								this.selectGoodsNumber = item.fg_goods_number;
							}
						}else if (item.sale_price > 0){
							this.selectSkuId = parseInt(item.sku_id);
							this.selectGoodsPrice = item.sale_price;
							this.selectGoodsNumber = item.goods_number;
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
					let liveCountdownArr = [];
					startTime.forEach(function(time,key) {
						let liveCountdown = '';
						//注：不论安卓还是ios，请将时间如 2020-02-02 20:20:20 转化为 2020/02/02 20:20:20 这种形式后再使用，否则无法转换，如下转换即可↓
						let transedPreTime= time.replace(/-/g,'/') //这里转化时间格式为以/分隔形式
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
								if (that.selectFGoods.fg_status == 1){
									that.selectFGoods.fg_status = 9;
									liveCountdown = '已结束';
								}else{
									that.selectFGoods.fg_status = 1;
									this.getLiveTimeCount([that.selectFGoods._end_date])
									liveCountdown = '已开团'
								}
							}
						}else{
							liveCountdown = '已结束';
						}  
						liveCountdownArr[key] = liveCountdown;
					})
					that.liveCountdown = liveCountdownArr;
				},100)
			},
			closeFgingBox(){
				if (this.fgingboxClass === 'show') {
					this.fgingboxClass = 'hide';
				} else {
					this.fgingboxClass = 'show';
				}
			},
			//加载购物车中商品数
			async loadCartNum() {
				this.$u.post('shop/api.flow/getCartInfo').then(res => {
					this.cartNum = res.data.num;
				})
			},
			stopPrevent() {}
		},

	}
</script>

<style lang='scss'>
	@import 'pages/shop/goods/info.scss';
	.fgingbox{
		position: fixed;
		top:0;
		left: 0;
		z-index: 99;
		width: 100%;
		height: 100vh;
		background-color: rgba($color: #000000, $alpha: 0.5);
		.content{
			position: absolute;
			left: 10%;
			top:20%;
			width: 80%;
			height: 800rpx;
			background-color: #FFFFFF;
			border-radius: 20rpx;
			.close{
				float: right;
				background-color: #cccccc;
				padding: 15rpx;
				border-radius: 50%;
				margin-top: -25rpx;
				margin-right: -25rpx;
				text-align: center;
			}
			.title{
				margin-top: 20rpx;
				height: 70rpx;
				font-size: 36rpx;
				text-align: center;
				border-bottom: 1rpx solid #f1f1f1;
			}
			.fglist{
				padding: 20rpx;
				height: calc(100% - 91rpx);
				overflow: hidden;
				overflow-y: auto;
			}
		}
	}
	
	.fgUserImg{
		width: 80rpx;
		height: 80rpx;
		border-radius: 50%;
		border: 1rpx solid #cccccc;
		overflow: hidden;
	}
	
</style>
