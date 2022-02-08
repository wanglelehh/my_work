$(document).on('hover','#webuploader_ajaxModal',function () {//兼容编缉器使用
    webuploaderChanges();
})
function webuploaderChanges(){
    var _this = $('#webuploader_ajaxModal');
    if ($(_this).data('is_uploader') == 'true' || $(_this).find('#uploader').length < 1) {
        return false;
    }
    $('#ajaxModal').hide();
    $(_this).data('is_uploader', 'true');
    webuploader(_this);
    var e = (new Date).getFullYear(),
        m = (new Date).getMonth() + 1,
        searchyear = {
            0 : {
                year: e - 0
            },
            1 : {
                year: e - 1
            },
            2 : {
                year: e - 2
            },
            3 : {
                year: e - 3
            },
            4 : {
                year: e - 4
            }
        };
    for (i in searchyear){
        $(_this).find('#select-year .btn-group').append('<a href="javascript:;" data-id="'+searchyear[i].year+'" data-type="year" class="btn btn-default btn-select '+(i==0?'btn-info':'')+'">'+searchyear[i].year+'年</a>');
    }
    $(_this).find('#select-year .btn-group').append('<a href="javascript:;" data-id="icon" data-type="icon" class="btn btn-default btn-select">默认图标</a>');
    for (i=1;i<=12;i++){
        $(_this).find('#select-month .btn-group').append('<a href="javascript:;" data-id="'+i+'" data-type="month" class="btn btn-default btn-select '+(m==i?'btn-info':'')+'">'+i+'</a>');
    }

    getWebuploadbymanager(1);
}
$(document).on('click','#history_image .btn-group a',function () {
    $(this).parent().find('a').removeClass('btn-info');
    $(this).addClass('btn-info');
    getWebuploadbymanager(1);
});

function getWebuploadbymanager(page){
    var data = {};
    data.year = $('#select-year .btn-info').data('id');
    data.month = $('#select-month .btn-info').data('id');
    data.page = page;

    if (data.year == 'icon'){
        data.type = 'icon';
    }
    $.getJSON(webUploadByManagerJ, data,
        function(b) {
            b = b.message.message,
            j = $('#history_image').find('.img-list');
            p = $('#history_image').find('#image-list-pager');
            j.html('');p.html(b.page);
            if (b.items.length < 1){
                j.css("text-align", "left").html('<i class="fa fa-info-circle"></i> 暂无数据');
            }
            p.find(".pagination a").click(function() {
                getWebuploadbymanager($(this).attr("page"));
            })
            $.each(b.items,function(i,v){
                j.append('<li class="img-item" attachid="'+v.id+'" data-img="'+v.attachment+'"  data-filename="'+v.filename+'" title="'+v.filename+'"><div class="img-container" style="background-image: url(\''+v.attachment+'\');"><div class="select-status"><span></span></div></div><div class="btnClose" data-id="26"><a href=""><i class="fa fa-times"></i></a></div></li>');
            })
            j.find('.img-item').click(function(){
                var uptype = $('#webuploader_ajaxModal').data('type');
                if (uptype == 'uploaderImg'){
                    j.find('.img-item').removeClass('img-item-selected');
                }
                if ($(this).hasClass('img-item-selected')){
                    $(this).removeClass('img-item-selected');
                }else{
                    $(this).addClass('img-item-selected');
                }
            })
        })
}

$(document).on('click','#history_image .btn-primary',function () {
    var imgs = [];
    $('#history_image .img-item-selected').each(function () {
        imgs.push({url:$(this).data('img'),filename:$(this).data('filename')});
    })
    if (imgs.length < 1){
        return _alert('请选择图片.',true);
    }
    if (imgs.length == 1) {
        var uptype = $('#webuploader_ajaxModal').data('type');
        if (uptype == 'uploaderImg'){
            uploaderImgToggle.find('input').val(imgs[0]['url']);
            uploaderImgToggle.find('img').attr('src',imgs[0]['url']);
            var fun = uploaderImgToggle.data("fun");
            if (fun){
                eval(fun + '("'+imgs[0]['url']+'")');
            }
        }else{
            UE.getEditor(edui_editor_id).execCommand('insertimage', {
                'src': imgs[0]['url'],
                '_src': imgs[0]['url'],
                'width': '100%',
                'alt': imgs[0].filename
            })
        }
    } else {
        var imglist = [];
        for (i in imgs) {
            imglist.push({
                'src': imgs[i]['url'],
                '_src': imgs[i]['url'],
                'width': '100%',
                'alt': imgs[i].filename
            })
        }
        UE.getEditor(edui_editor_id).execCommand('insertimage', imglist)
    }
    $("#webuploader_ajaxModal .close").trigger("click");
    if ($('#ajaxModal').hasClass('in')){
        $('#ajaxModal').show();
    }
})

function webuploader(obj){
    var BASE_URL = '/a_static/js/webuploader';
    var $wrap = $(obj).find('#uploader'),
        imgs = [],
        // 图片容器
        $queue = $('<ul class="filelist"></ul>')
            .appendTo( $wrap.find('.queueList') ),

        // 状态栏，包括进度和控制按钮
        $statusBar = $wrap.find('.statusBar'),

        // 文件总体选择信息。
        $info = $statusBar.find('.info'),

        // 上传按钮
        $upload = $wrap.find('.uploadBtn'),

        // 没选择文件之前的内容。
        $placeHolder = $wrap.find('.placeholder'),

        // 总体进度条
        $progress = $statusBar.find('.progress').hide(),

        //上传限制数量
        upLimit = $(obj).data('filenumlimit')?$(obj).data('filenumlimit'):20,

        //单个文件上传大小限制
        fileSingleSizeLimit = 1 * 1024 * 1024,
        //全部文件上传大小限制
        fileSingleAllSizeLimit = 20 * 1024 * 1024,

    // 添加的文件数量
        fileCount = 0,

        // 添加的文件总大小
        fileSize = 0,

        // 优化retina, 在retina下这个值是2
        ratio = window.devicePixelRatio || 1,

        // 缩略图大小
        thumbnailWidth = 110 * ratio,
        thumbnailHeight = 110 * ratio,

        // 可能有pedding, ready, uploading, confirm, done.
        state = 'pedding',

        // 所有文件的进度信息，key为file id
        percentages = {},

        supportTransition = (function(){
            var s = document.createElement('p').style,
                r = 'transition' in s ||
                    'WebkitTransition' in s ||
                    'MozTransition' in s ||
                    'msTransition' in s ||
                    'OTransition' in s;
            s = null;
            return r;
        })(),

        // WebUploader实例
        uploader;

    if ( !WebUploader.Uploader.support() ) {
        alert( 'Web Uploader 不支持您的浏览器！如果你使用的是IE浏览器，请尝试升级 flash 播放器');
        throw new Error( 'WebUploader does not support the browser you are using.' );
    }
    // 实例化
    uploader = WebUploader.create({
        pick: {
            id: '#filePicker',
            label: '点击选择图片'
        },
        dnd: '#uploader .queueList',
        paste: document.body,

        accept: {
            title: 'Images',
            extensions: 'gif,jpg,jpeg,bmp,png',
            mimeTypes: 'image/!*'
        },
        // swf文件路径
        swf: BASE_URL + '/Uploader.swf',

        disableGlobalDnd: true,

        chunked: true,
        // server: 'http://webuploader.duapp.com/server/fileupload.php',
        server: uploadJ+ "?type=image",
        fileNumLimit: upLimit,
        fileSizeLimit: fileSingleAllSizeLimit,    // 总文件大小20 M
        fileSingleSizeLimit:  fileSingleSizeLimit,  // 单个文件1 M
        threads:1,//同时并发数，
        compress:false,//禁用上传压缩
    });

    if (upLimit > 1){
        // 添加“添加文件”的按钮，
        uploader.addButton({
            id: '#filePicker2',
            label: '继续添加'
        });
    }


    // 当有文件添加进来时执行，负责view的创建
    function addFile( file ) {
        var $li = $( '<li id="' + file.id + '">' +
                '<p class="title">' + file.name + '</p>' +
                '<p class="imgWrap"></p>'+
                '<p class="progress"><span></span></p>' +
                '</li>' ),

            $btns = $('<div class="file-panel">' +
                '<span class="cancel">删除</span>' +
                '<span class="rotateRight hide">向右旋转</span>' +
                '<span class="rotateLeft hide">向左旋转</span></div>').appendTo( $li ),
            $prgress = $li.find('p.progress span'),
            $wrap = $li.find( 'p.imgWrap' ),
            $info = $('<p class="error"></p>'),

            showError = function( code ) {
                switch( code ) {
                    case 'exceed_size':
                        text = '文件大小超出';
                        break;

                    case 'interrupt':
                        text = '上传暂停';
                        break;

                    default:
                        text = '上传失败，请重试';
                        break;
                }

                $info.text( text ).appendTo( $li );
            };

        if ( file.getStatus() === 'invalid' ) {
            showError( file.statusText );
        } else {
            // @todo lazyload
            $wrap.text( '预览中' );
            uploader.makeThumb( file, function( error, src ) {
                if ( error ) {
                    $wrap.text( '不能预览' );
                    return;
                }

                var img = $('<img src="'+src+'">');
                $wrap.empty().append( img );
            }, thumbnailWidth, thumbnailHeight );

            percentages[ file.id ] = [ file.size, 0 ];
            file.rotation = 0;
        }
        // 完成上传完了
        uploader.on('uploadSuccess', function (file, response) {
            for (i in imgs) {
                if (response.url == imgs[i].url){
                    return false;
                }
            }
            imgs.push(response);
        });

        file.on('statuschange', function( cur, prev ) {
            if ( prev === 'progress' ) {
                $prgress.hide().width(0);
            } else if ( prev === 'queued' ) {
                $li.off( 'mouseenter mouseleave' );
                $btns.remove();
            }

            // 成功
            if ( cur === 'error' || cur === 'invalid' ) {
                console.log( file.statusText );
                showError( file.statusText );
                percentages[ file.id ][ 1 ] = 1;
            } else if ( cur === 'interrupt' ) {
                showError( 'interrupt' );
            } else if ( cur === 'queued' ) {
                percentages[ file.id ][ 1 ] = 0;
            } else if ( cur === 'progress' ) {
                $info.remove();
                $prgress.css('display', 'block');
            } else if ( cur === 'complete' ) {
                $li.append( '<span class="success"></span>' );
            }

            $li.removeClass( 'state-' + prev ).addClass( 'state-' + cur );
        });

        $li.on( 'mouseenter', function() {
            $btns.stop().animate({height: 30});
        });

        $li.on( 'mouseleave', function() {
            $btns.stop().animate({height: 0});
        });

        $btns.on( 'click', 'span', function() {
            var index = $(this).index(),
                deg;

            switch ( index ) {
                case 0:
                    uploader.removeFile( file );
                    return;

                case 1:
                    file.rotation += 90;
                    break;

                case 2:
                    file.rotation -= 90;
                    break;
            }

            if ( supportTransition ) {
                deg = 'rotate(' + file.rotation + 'deg)';
                $wrap.css({
                    '-webkit-transform': deg,
                    '-mos-transform': deg,
                    '-o-transform': deg,
                    'transform': deg
                });
            } else {
                $wrap.css( 'filter', 'progid:DXImageTransform.Microsoft.BasicImage(rotation='+ (~~((file.rotation/90)%4 + 4)%4) +')');
            }
        });

        $li.appendTo( $queue );
    }

    // 负责view的销毁
    function removeFile( file ) {
        var $li = $('#'+file.id);

        delete percentages[ file.id ];
        updateTotalProgress();
        $li.off().find('.file-panel').off().end().remove();
    }

    function updateTotalProgress() {
        var loaded = 0,
            total = 0,
            spans = $progress.children(),
            percent;

        $.each( percentages, function( k, v ) {
            total += v[ 0 ];
            loaded += v[ 0 ] * v[ 1 ];
        } );

        percent = total ? loaded / total : 0;

        spans.eq( 0 ).text( Math.round( percent * 100 ) + '%' );
        spans.eq( 1 ).css( 'width', Math.round( percent * 100 ) + '%' );
        updateStatus();
    }

    function updateStatus() {
        var text = '', stats;

        if ( state === 'ready' ) {
            text = '选中' + fileCount + '张图片，共' +
                WebUploader.formatSize( fileSize ) + '。';
        } else if ( state === 'confirm' ) {
            stats = uploader.getStats();
            if ( stats.uploadFailNum ) {
                text = '已成功上传' + stats.successNum+ '张照片至XX相册，'+
                    stats.uploadFailNum + '张照片上传失败，<a class="retry" href="#">重新上传</a>失败图片或<a class="ignore" href="#">忽略</a>'
            }

        } else {
            stats = uploader.getStats();
            text = '共' + fileCount + '张（' +
                WebUploader.formatSize( fileSize )  +
                '），已上传' + stats.successNum + '张';

            if ( stats.uploadFailNum ) {
                text += '，失败' + stats.uploadFailNum + '张';
            }
        }

        $info.html( text );
    }

    function setState( val ) {
        var file, stats;

        if ( val === state ) {
            return;
        }

        $upload.removeClass( 'state-' + state );
        $upload.addClass( 'state-' + val );
        state = val;

        switch ( state ) {
            case 'pedding':
                $placeHolder.removeClass( 'element-invisible' );
                $queue.parent().removeClass('filled');
                $queue.hide();
                $statusBar.addClass( 'element-invisible' );
                uploader.refresh();
                break;

            case 'ready':
                $placeHolder.addClass( 'element-invisible' );
                $( '#filePicker2' ).removeClass( 'element-invisible');
                $queue.parent().addClass('filled');
                $queue.show();
                $statusBar.removeClass('element-invisible');
                uploader.refresh();
                break;

            case 'uploading':
                $( '#filePicker2' ).addClass( 'element-invisible' );
                $progress.show();
                $upload.text( '暂停上传' );
                break;

            case 'paused':
                $progress.show();
                $upload.text( '继续上传' );
                break;

            case 'confirm':
                $progress.hide();
                $upload.text( '开始上传' ).addClass( 'disabled' );

                stats = uploader.getStats();
                if ( stats.successNum && !stats.uploadFailNum ) {
                    setState( 'finish' );
                    return;
                }
                break;
            case 'finish':
                stats = uploader.getStats();
                if ( stats.successNum == fileCount ) {
                    if (imgs.length == 1) {
                        var uptype = $('#webuploader_ajaxModal').data('type');
                        if (uptype == 'uploaderImg'){
                            uploaderImgToggle.find('input').val(imgs[0]['url']);
                            uploaderImgToggle.find('img').attr('src',imgs[0]['url']);
                            var fun = uploaderImgToggle.data("fun");
                            if (fun){
                                eval(fun + '("'+imgs[0]['url']+'")');
                            }
                        }else{
                            UE.getEditor(edui_editor_id).execCommand('insertimage', {
                                'src': imgs[0]['url'],
                                '_src': imgs[0]['url'],
                                'width': '100%',
                                'alt': imgs[0].filename
                            })
                        }
                    } else {
                        var imglist = [];
                        for (i in imgs) {
                            imglist.push({
                                'src': imgs[i]['url'],
                                '_src': imgs[i]['url'],
                                'width': '100%',
                                'alt': imgs[i].filename
                            })
                        }
                        UE.getEditor(edui_editor_id).execCommand('insertimage', imglist)
                    }
                    $("#webuploader_ajaxModal .close").trigger("click");
                    if ($('#ajaxModal').hasClass('in')){
                        $('#ajaxModal').show();
                    }
                } else {
                    // 没有成功的图片，重设
                    state = 'done';
                    location.reload();
                }
                break;
        }

        updateStatus();
    }

    uploader.onUploadProgress = function( file, percentage ) {
        var $li = $('#'+file.id),
            $percent = $li.find('.progress span');

        $percent.css( 'width', percentage * 100 + '%' );
        percentages[ file.id ][ 1 ] = percentage;
        updateTotalProgress();
    };

    uploader.onFileQueued = function( file ) {
        fileCount++;
        fileSize += file.size;
        if ( fileCount === 1 ) {
            $placeHolder.addClass( 'element-invisible' );
            $statusBar.show();
        }
        addFile( file );
        setState( 'ready' );
        updateTotalProgress();
    };

    uploader.onFileDequeued = function( file ) {
        fileCount--;
        fileSize -= file.size;

        if ( !fileCount ) {
            setState( 'pedding' );
        }

        removeFile( file );
        updateTotalProgress();

    };

    uploader.on( 'all', function( type ) {
        var stats;
        switch( type ) {
            case 'uploadFinished':
                setState( 'confirm' );
                break;

            case 'startUpload':
                setState( 'uploading' );
                break;

            case 'stopUpload':
                setState( 'paused' );
                break;

        }
    });

    uploader.onError = function( code ) {
        if (code == 'F_DUPLICATE'){
            return alert('已存在列表中，不能重复上传.');
        }
        if (code == 'Q_EXCEED_NUM_LIMIT'){
            return alert('单次只能上传'+upLimit+'张图片.');
        }
        if (code == 'F_EXCEED_SIZE'){
            return alert('单张图片不能超过'+(fileSingleSizeLimit/1024/1024)+'M.');
        }
        if (code == 'Q_EXCEED_SIZE_LIMIT'){
            return alert('全部图片不能超过'+(fileSingleAllSizeLimit/1024/1024)+'M.');
        }
        alert( 'Eroor: ' + code );
    };

    $upload.on('click', function() {
        if ( $(this).hasClass( 'disabled' ) ) {
            return false;
        }

        if ( state === 'ready' ) {
            uploader.upload();
        } else if ( state === 'paused' ) {
            uploader.upload();
        } else if ( state === 'uploading' ) {
            uploader.stop();
        }
    });

    $info.on( 'click', '.retry', function() {
        uploader.retry();
    } );

    $info.on( 'click', '.ignore', function() {
        alert( 'todo' );
    } );

    $upload.addClass( 'state-' + state );
    updateTotalProgress();

}