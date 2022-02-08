<template>
	<view>
		<view v-for="(diyitem, diyitemid) in pageInfo.items" :key="diyitemid">
			<view v-if="diyitem.id == 'fixedsearch'">
				<view :style="{height:app.pxToRpx(40)+'rpx'}"></view>
				<tpl_fixedsearch :diyitem="diyitem"></tpl_fixedsearch>
			</view>
			 <view v-if="diyitem.id=='search'">
				 <tpl_search :diyitem="diyitem"></tpl_search> 
			 </view>
			 <view v-if="diyitem.id=='notice'">
				 <tpl_notice :diyitem="diyitem"></tpl_notice> 
			 </view>
			 <view v-if="diyitem.id=='title'">
			     <tpl_title :diyitem="diyitem" :diyitemid="diyitemid"></tpl_title> 
			 </view>
			 <view v-if="diyitem.id=='line'">
			     <tpl_line :diyitem="diyitem" :diyitemid="diyitemid"></tpl_line> 
			 </view>
			 <view v-if="diyitem.id=='blank'">
			     <tpl_blank :diyitem="diyitem" :diyitemid="diyitemid"></tpl_blank> 
			 </view>
			 <view v-if="diyitem.id=='menu'">
			     <tpl_menu :diyitem="diyitem" :diyitemid="diyitemid"></tpl_menu> 
			 </view>
			 <view v-if="diyitem.id=='picture'">
			     <tpl_picture :diyitem="diyitem" :diyitemid="diyitemid"></tpl_picture> 
			 </view>
			 <view v-if="diyitem.id=='picturew'">
			     <tpl_picturew :diyitem="diyitem" :diyitemid="diyitemid"></tpl_picturew> 
			 </view>
			 <view v-if="diyitem.id=='pictures'">
			     <tpl_pictures :diyitem="diyitem" :diyitemid="diyitemid"></tpl_pictures> 
			 </view>
			 <view v-if="diyitem.id=='banner'">
			     <tpl_banner :diyitem="diyitem" :diyitemid="diyitemid"></tpl_banner> 
			 </view>
			 <view v-if="diyitem.id=='richtext'">
			     <tpl_richtext :diyitem="diyitem" :diyitemid="diyitemid"></tpl_richtext> 
			 </view>
			 <view v-if="diyitem.id=='video'">
			     <tpl_video @startplay="startplay" @videoEnd="videoEnd" :diyitem="diyitem" :diyitemid="diyitemid" :videoCover="videoCover"></tpl_video> 
			 </view>
			 <view v-if="diyitem.id=='videos'">
			     <tpl_videos @startplay="startplay"  @videoEnd="videoEnd"  :diyitem="diyitem" :diyitemid="diyitemid" :videoCover="videoCover"></tpl_videos> 
			 </view>
			 <view v-if="diyitem.id=='copyright'">
			     <tpl_copyright :diyitem="diyitem" :diyitemid="diyitemid"></tpl_copyright> 
			 </view>
			 <view v-if="diyitem.id=='goods'">
			     <tpl_goods :diyitem="diyitem" :diyitemid="diyitemid"></tpl_goods> 
			 </view>
			 <view v-if="diyitem.id=='fightgroup'">
			     <tpl_fightgroup :diyitem="diyitem" :diyitemid="diyitemid"></tpl_fightgroup> 
			 </view>
			 <view v-if="diyitem.id=='listmenu'">
			     <tpl_listmenu :diyitem="diyitem" :diyitemid="diyitemid"></tpl_listmenu> 
			 </view>
		</view>
	</view>
</template>


<script>
	import tpl_fixedsearch from '@/pages/shop/index/diyPage/template/tpl_fixedsearch';
	import tpl_search from '@/pages/shop/index/diyPage/template/tpl_search';
	import tpl_notice from '@/pages/shop/index/diyPage/template/tpl_notice';
	import tpl_title from '@/pages/shop/index/diyPage/template/tpl_title';
	import tpl_line from '@/pages/shop/index/diyPage/template/tpl_line';
	import tpl_blank from '@/pages/shop/index/diyPage/template/tpl_blank';
	import tpl_menu from '@/pages/shop/index/diyPage/template/tpl_menu';
	import tpl_picture from '@/pages/shop/index/diyPage/template/tpl_picture';
	import tpl_picturew from '@/pages/shop/index/diyPage/template/tpl_picturew';
	import tpl_pictures from '@/pages/shop/index/diyPage/template/tpl_pictures';
	import tpl_banner from '@/pages/shop/index/diyPage/template/tpl_banner';
	import tpl_richtext from '@/pages/shop/index/diyPage/template/tpl_richtext';
	import tpl_video from '@/pages/shop/index/diyPage/template/tpl_video';
	import tpl_videos from '@/pages/shop/index/diyPage/template/tpl_videos';
	import tpl_copyright from '@/pages/shop/index/diyPage/template/tpl_copyright';
	import tpl_goods from '@/pages/shop/index/diyPage/template/tpl_goods';
	import tpl_fightgroup from '@/pages/shop/index/diyPage/template/tpl_fightgroup';
	import tpl_listmenu from '@/pages/shop/index/diyPage/template/tpl_listmenu';
	export default {
		components: {
			tpl_fixedsearch,
			tpl_search,
			tpl_notice,
			tpl_title,
			tpl_line,
			tpl_blank,
			tpl_menu,
			tpl_picture,
			tpl_picturew,
			tpl_pictures,
			tpl_banner,
			tpl_richtext,
			tpl_copyright,
			tpl_video,
			tpl_videos,
			tpl_goods,
			tpl_fightgroup,
			tpl_listmenu
		},
		name: "diyPage",
		props: {
			pageInfo: {
				type: Object,
				default:function(){
					return {};
				}
			},
		},
		data() {
			return {
				videoCover:''
			};
		},
		watch: {},
		computed: {},
		methods: {
			videoEnd(){
				this.videoCover = '';
			},
			startplay(diyitemid){
				this.videoCover = diyitemid;
			}
		}
	}
</script>

<style lang='scss'>
</style>
