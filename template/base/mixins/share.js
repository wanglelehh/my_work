// #ifdef H5
import jweixin from '@/common/jweixin';
// #endif
export default {
    data () {
        return {
            shareData: {
                title: '',
                imageUrl: '',
                content: '',
                desc: ''
            },
			share_token:uni.getStorageSync("my_share_token")
        }
    },

    //#ifdef MP-WEIXIN
    onShareAppMessage () {
		let pages = getCurrentPages();
		let curPage = pages[pages.length-1];
		curPage.options.share_token = this.share_token;
		let link = curPage.route+'?'+this.app.objectToQueryString(curPage.options);
        return {
            title: this.shareData.title,
            path: link,
            imageUrl: this.shareData.imageUrl,
            content: this.shareData.content,
            desc: this.shareData.desc,
            success: res => {
                console.info(res)
            }
        }
    },
    //#endif
	
    onLoad(option) {
		let setting = uni.getStorageSync('setting');
		this.shareData.title = setting.site_name;
		this.shareData.desc = setting.description;
		this.shareData.imageUrl = this.config.baseUrl + setting.logo;
		// #ifdef H5
		const ua = window.navigator.userAgent.toLowerCase();
		if (ua.match(/micromessenger/i) == 'micromessenger') {
			this.wxH5Share();
		} 
		// #endif
    },
	methods: {
		share(){
			//#ifdef MP-WEIXIN
			uni.showShareMenu({
			  menus: ['shareAppMessage', 'shareTimeline']
			})
			// #endif
			// #ifdef H5
			const ua = window.navigator.userAgent.toLowerCase();
			if (ua.match(/micromessenger/i) == 'micromessenger') {
				this.wxH5Share();
			} 
			// #endif
		},
		async wxH5Share(){
			let that = this;
			let link = window.location.href;
			await that.$u.post(`/weixin/api.Index/getShareSign`, {
					url: link
				})
				.then(r => {
					jweixin.config({
						debug: r.data.debug==1?true:false,
						appId: r.data.appId,
						timestamp: r.data.timestamp,
						nonceStr: r.data.nonceStr,
						signature: r.data.signature,
						jsApiList: [
						'updateAppMessageShareData',
						'updateTimelineShareData'
						]
					})
				});
				
			jweixin.ready(function () {
				jweixin.hideOptionMenu();
				if (typeof(that.shareData.title) == 'undefined' || that.shareData.title == '' || typeof(that.share_token) == 'undefined'){
					return false;
				}
				let pages = getCurrentPages();
				let curPage = pages[pages.length-1];
				curPage.options.share_token = that.share_token;
				let _link = that.config.baseUrl+curPage.route+'?'+that.app.objectToQueryString(curPage.options);
			
				// eslint-disable-next-line
				jweixin.updateAppMessageShareData({
				  title: that.shareData.title, // 分享标题
				  desc: that.shareData.desc, // 分享描述
				  link: _link, // 分享链接，该链接域名或路径必须与当前页面对应的公众号js安全域名一致
				  imgUrl: that.shareData.imageUrl, // 分享图标
				  success: function () {
				    // 用户确认分享后执行的回调函数
				  },
				  cancel: function () {
				    // 用户取消分享后执行的回调函数
				  }
				});
				// eslint-disable-next-line
				jweixin.updateTimelineShareData({
				  title: that.shareData.title + ' - ' + that.shareData.desc, // 分享标题
				  desc: that.shareData.desc, // 分享描述
				  link: _link, // 分享链接，该链接域名或路径必须与当前页面对应的公众号js安全域名一致
				  imgUrl: that.shareData.imageUrl, // 分享图标
				  success: function () {
				    // 用户确认分享后执行的回调函数
				  },
				  cancel: function () {
				    // 用户取消分享后执行的回调函数
				  }
				});
				jweixin.showOptionMenu();
			})
		},
	}
}