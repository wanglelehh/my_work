<template>
	<view class="page-body">
		<view class="grid_menu p10 bg-white">
			<u-grid :col="3" class="u-grid" :border="false">
				<u-grid-item @click="app.goPage('topicCourse')">
					<view class="grid-text"><u-icon size="100" name="/static/public/images/material/menu_01.png"></u-icon></view>
					<view class="grid-text mt20">专题课程</view>
				</u-grid-item>
				<u-grid-item @click="app.goPage('hdPic')">
					<view class="grid-text"><u-icon size="100" name="/static/public/images/material/menu_02.png"></u-icon></view>
					<view class="grid-text mt20">高清海报</view>
				</u-grid-item>
				<u-grid-item @click="app.goPage('graphicMaterial')">
					<view class="grid-text"><u-icon size="100" name="/static/public/images/material/menu_03.png"></u-icon></view>
					<view class="grid-text mt20">发圈素材</view>
				</u-grid-item>
			</u-grid>
		</view>
		<view class=" mt20">
			<view class="smll p20 bg-white"  @click="app.goPage('topicCourse')">
				<view class="flex_bd">
					<view class="smll">
						<view class=" fs32 font-w600 color-33 mr10">最新课单</view>
						<view class="flex_bd">
							<text class="newsd bg-color color-ff">new</text>
						</view>
					</view>
					<view class="fs22 color-94 mt10">热门课程 大家都在看</view>
				</view>
				<u-icon name="arrow-right"></u-icon>
			</view>
			
			<view  v-for="(item,index) in trainTopicList" :key="index"  class="toppicCourse p20 smll bg-white" @click="app.goPage('topicCourseInfo?id='+item.id)">
				<u-image width="256rpx" height="150rpx" :src="baseUrl+item.title_img"></u-image>
				<view class="flex_bd ml20">
					<view class="he150 ">
						<view class="flex_bd">
							<view class="fs30 color-33 title">{{item.title}}</view>
							<view class="fs26 color-94 description">{{item.description}}</view>
						</view>
						<view class="fs22 color-94">阅读：{{item.view_num}}</view>
					</view>
				</view>
			</view>
			
		</view>
		<view class="bg-white mt20">
			<view class="smll p20">
				<view class="flex_bd">
					<view class="smll" @click="app.goPage('hdPic')">
						<view class=" fs32 font-w600 color-33 mr10">高清大图</view>
					</view>
				</view>
				<u-icon name="arrow-right"></u-icon>
			</view>
			<view class="big_img p10">
				<u-grid :col="2" class="u-grid" :border="false">
					<u-grid-item  v-for="(item,index) in hdPicList" :key="index" class="p0">
						<view class="img">
							<u-image width="100%" mode="widthFix" :src="baseUrl+item.image" @click="app.goPage('hdPicInfo?hdPic='+item.image);"></u-image>
						</view>
						<view class="grid-text mt20">{{item.title}}</view>
					</u-grid-item>
				</u-grid>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				baseUrl:this.config.baseUrl,
				trainTopicList:[],
				hdPicList:[]
			}
		},
		onLoad() {
			this.getTrainTopicList();
			this.getHdPicList();
		},
		onShow() {
		},
		onReady() {},
		methods: {
			getTrainTopicList() {
				this.$u.post('school/api.train_topic/getList').then(res => {
					this.trainTopicList = res.data.list;
				})
			},
			getHdPicList() {
				this.$u.post('school/api.hd_pic/getList').then(res => {
					this.hdPicList = res.data.list;
				})
			},
		}
	}
</script>
<style lang="scss">
	.grid_menu{
		.u-grid-item{
			border: 10rpx solid #FFFFFF;
			background-color: $page-color-base !important;
		}
	}
	.newsd {
	    border-radius: 17px 17px 17px 4px;
		padding: 6rpx;
	}
	.he150{
		height: 150rpx;
		display: -webkit-box;
		    display: -webkit-flex;
		    display: flex;
		    -webkit-box-orient: vertical;
		    -webkit-box-direction: normal;
		    -webkit-flex-direction: column;
		    flex-direction: column;
	}
	.toppicCourse{
		margin-bottom: 5rpx;
		.title{
			height: 34rpx;
			overflow: hidden;
		}
		.description{
			margin-top: 10rpx;
			height: 74rpx;
			overflow: hidden;
		}
	}
	.big_img{
		.u-grid-item{
			border: 10rpx solid #FFFFFF;
			.img{
				width: 100%;
				height: 580rpx;
				overflow: hidden;
			}
		}
	}
</style>
