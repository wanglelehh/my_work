<template>
	<view class="diy-fixedsearch fixed">
	    <view class="background" :style="{background:diyitem.style.background,opacity:diyitem.style.opacity}"></view>
	    <view class="inner">
	        <view>
	            <view v-if="diyitem.params.leftnav > 0">
	                <view  class="leftnav"  @click="app.goPage(diyitem.params.leftnavlink)">
	                    <view>
	                        <view v-if="diyitem.params.leftnav == 1">
	                            <text class="icon" :class="diyitem.params.leftnavicon" :style="{color:diyitem.style.leftnavcolor}"></text>
	                        </view>
	                    </view>
	                    <view>
	                        <view v-if="diyitem.params.leftnav == 2">
	                            <image :src="diyitem.params.leftnavimg"></image>
	                        </view>
	                    </view>
	                </view>
	            </view>
	        </view>
	        <view class="center" :class="diyitem.params.searchstyle" @click="app.goPage('/pages/shop/goods/search')" >
	            <view :style="{background:diyitem.style.searchbackground,color:diyitem.style.searchtextcolor}">
	                {{diyitem.params.placeholder}}
	              </view>
	        </view>
	        <view>
	            <view v-if="diyitem.params.rightnav > 0">
	                <view  class="rightnav" @click="app.goPage(diyitem.params.rightnavlink)">
	                    <view>
	                        <view v-if="diyitem.params.rightnav == 1">
	                            <text class="icon" :class="diyitem.params.rightnavicon" :style="{color:diyitem.style.rightnavcolor}"></text>
	                        </view>
	                    </view>
	                    <view>
	                        <view v-if="diyitem.params.rightnav == 2">
	                            <image :src="diyitem.params.rightnavimg"></image>
	                        </view>
	                    </view>
	                </view>
	            </view>
	        </view>
	    </view>
		<view class="diy-fixedsearch-back"></view>
	</view>
</template>


<script>
	export default {
		name: "tpl_fixedsearch",
		props: {
			diyitem: {
				type: Object,
				default:function(){
					return {};
				}
			},
		},
		data() {
			return {
			};
		},
		watch: {},
		computed: {},
		methods: {
			
		}
	}
</script>

<style lang='scss'>
</style>
