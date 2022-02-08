var not_kindeditor = false;
var edui_editor_id = null;

define(assets_path+"/assets/dist/form/init", ["$", "./controls", "dist/application/app", "./validate", "./tip"],
function(a, b) {
    "use strict";
    var c = (a("$"), a("./controls")),
    d = a("./validate");
    b.init = function(a) {
        c.init(a),
        d.init(a)
    }
}),
define(assets_path+"/assets/dist/form/controls", ["$", "dist/application/app"],
function(a, b) {
    "use strict";
    var c = a("$"),
    d = a("dist/application/app"),
    e = d.config,
    f = d.method;
    b.init = function(b) {

		function d() {

        }
        var ug = c('[data-toggle="ueditor"]', b);
        if (ug.length > 0){

            var ueditoroption = {
                'autoClearinitialContent': false,
                'toolbars': [[ 'source', '|','undo', 'redo','|', 'bold', 'italic', 'underline',  'forecolor', 'backcolor',  '|', 'insertorderedlist', 'insertunorderedlist',  'emotion', 'removeformat', '|','lineheight', 'fontsize','justifyleft', 'justifycenter', 'justifyright',  '|', 'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', '|', 'link']],
                'elementPathEnabled': false,
                'initialFrameHeight': 300,
                'focus': false,
                'autoHeightEnabled':false,
                'maximumWords': 9999999
            };

            UE.registerUI('myinsertimage', function (editor, uiName) {
                editor.registerCommand(uiName, {
                    execCommand: function () {
                        require(['fileUploader'], function (uploader) {
                            uploader.show(function (imgs) {
                                if (imgs.length == 0) {
                                    return
                                } else if (imgs.length == 1) {
                                    editor.execCommand('insertimage', {
                                        'src': imgs[0]['url'],
                                        '_src': imgs[0]['url'],
                                        'width': '100%',
                                        'alt': imgs[0].filename
                                    })
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
                                    editor.execCommand('insertimage', imglist)
                                }
                            }, opts)
                        })
                    }
                });
                var btn = new UE.ui.Button({
                    name: '插入图片',
                    title: '插入图片',
                    cssRules: 'background-position: -726px -77px',
                    onclick: function () {
                        edui_editor_id = $("#"+this.id).parents('.edui-editor').parent().attr('id');
                        c("#webuploader_ajaxModal").remove();
                        var g = c('<div class="modal fade upload_modal" id="webuploader_ajaxModal" data-filenumlimit="20"><div class="modal-body "></div></div>');
                        c(document).append(g),
                            g.modal(),
                            g.append2('<div class="modal-dialog"><div class="modal-content" style="min-width: 700px;">' +
                                '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
                                '<ul class="nav nav-pills" role="tablist">' +
                                '<li id="li_upload" class="active" role="presentation"><a href="#upload" aria-controls="upload" role="tab" data-toggle="tab">上传图片</a></li>' +
                                '<li id="li_history_image" class="" role="presentation"><a href="#history_image" aria-controls="history_image" role="tab" data-toggle="tab">浏览图片</a></li>' +
                                '</ul></div> ' +
                                '<div class="modal-body tab-content"">' +
                                '<div role="tabpanel" class="tab-pane upload active" id="upload"><div id="uploader" class="wu-example">' +
                                '<div class="queueList"><div id="dndArea" class="placeholder"><div id="filePicker"></div><p>或将照片拖到这里，单次最多可选20张</p></div></div>' +
                                '<div class="statusBar" style="display:none;"><div class="progress"><span class="text">0%</span><span class="percentage"></span></div><div class="info"></div>' +
                                '<div class="btns"><div id="filePicker2"></div><div class="uploadBtn">开始上传</div></div></div></div></div>' +
                                '<div role="tabpanel" class="tab-pane history " id="history_image">' +
                                '<div id="select-year" style="margin-bottom:10px;"><div class="btn-group"></div></div>' +
                                '<div id="select-month"><div class="btn-group"></div></div>' +
                                '<div class="history-content" style="height: 270px;margin-top: 10px; overflow-y: auto; text-align: center;"><ul class="img-list clearfix"></ul></div>' +
                                '<nav id="image-list-pager" class="text-right we7-margin-vertical"></nav>'+
                                '<div class="modal-footer" style="background-color: #fbfbfb;"><button type="submit" class="btn btn-primary">确认</button><button type="button" class="btn btn-default" data-dismiss="modal">取消</button></div>' +
                                '</div>' +
                                '</div></div>', function () {
                                a.async([assets_path+"/js/webuploader/webuploader.js",assets_path+"/js/webuploader/default.js",assets_path+"/js/webuploader/default.css"])

                            });
                    }
                });

                return btn
            }, 48);
            UE.registerUI('mylink', function (editor, uiName) {
                var btn = new UE.ui.Button({
                    name: 'selectUrl',
                    title: '系统链接',
                    cssRules: 'background-position: -622px 80px;',
                    onclick: function () {
                        edui_editor_id = $("#"+this.id).parents('.edui-editor').parent().attr('id');
                        $("#" + this.id).attr({"data-toggle": "ajaxModal","data-remote":selectLinkUrl+'?linksfun=editorBack'})
                    }
                });
                editor.addListener('selectionchange', function () {
                    var state = editor.queryCommandState(uiName);
                    if (state == -1) {
                        btn.setDisabled(true);
                        btn.setChecked(false)
                    } else {
                        btn.setDisabled(false);
                        btn.setChecked(state)
                    }
                });
                return btn
            });
            ug.each(function () {
                var a = c(this),
                    v = a.data('div');
                if (typeof(UE) != 'undefined') {
                    UE.delEditor(v)
                }
                if (a.data("height") > 0) {
                    ueditoroption.initialFrameHeight = a.data("height");
                }
                if (a.data("maximumwords") > 0) {
                    ueditoroption.maximumWords = a.data("maximumwords");
                }
                var ue = UE.getEditor(v, ueditoroption);
                ue.ready(function () {
                    var temp = document.createElement("div");
                    temp.innerHTML = a.html();
                    ue.setContent(temp.innerText || temp.textContent);
                    temp = null;
                    ue.addListener('contentChange', function () {
                        var newContent = ue.getContent();
                        a.html(newContent).trigger('change')
                    })
                })
            })
        }

        var i = c('[data-toggle="method"]');
        if (i.length > 0) {
            var j = c("#method"),
            k = i.data("confirm");
            i.on("click",
            function() {
                var a = c(this),
                b = a.data("method"),
                d = function() {
                    j.val(b),
                    a.button("loading"),
                    i.closest("form").submit()
                };
                k && G.ui.tips.confirm(i.data("msg") || e.lang.confirmCloseOrder,
                function(a) {
                    a && d()
                }),
                !k && d()
            })
        }
        var l = c('[data-toggle="location"]', b);
        l.length > 0 && a.async("dist/location/init.js",
        function(a) {
            c.each(l,
            function() {
                var b = c(this),
                d = c('[data-location="provinces"]', b),
                e = c('[data-location="city"]', b),
                f = c('[data-location="district"]', b),
                g = new a.select({
                    data: a.data
                });
                g.bind(d),
                g.bind(e),
                g.bind(f)
            })
        });
        var m = c('[data-toggle="select_level2"]', b);
        if (m.length > 0) {
            var n = c('[data-location="select_level_1"]', b),
            o = c('[data-location="select_level_2"]', b),
            p = new f.select({
                data: window.select_level2_data
            });
            p.bind(n),
            p.bind(o)
        }
        var q = c(".js_sortable");
        q.length &&
        function() {
            q.sortable()
        } ()
    }
}),
define(assets_path+"/assets/dist/form/validate", ["$", "dist/application/app", assets_path+"/assets/dist/form/tip"],
function(a, b) {
    "use strict";
    var c = a("$"),
    d = a("dist/application/app"),
    e = d.config;
    b.init = function(b) {
        var d = b.hasClass("form-modal"),
        g = null;
        d && (g = b.parents(".modal"), g.on("hidden",
        function() {
            b.resetForm()
        }));
        var h = {
            errorElement: "span",
            errorClass: "help-block error",
            errorPlacement: function(a, b) {
                var c = b.parents(".input-group");
                c.length > 0 ? c.after(a) : b.after(a)
            },
            highlight: function(a) {
               c(a).removeClass("error has-success").addClass("error")
            },
            success: function(a) {
                a.addClass("valid")
            },
            onkeyup: function(a) {
                c(a).valid()
            },
            onfocusout: function(a) {
                c(a).valid()
            },
            submitHandler: function(a) {
                    $('#fallr-wrapper').remove()
                    var d = c("button[type='submit']", a);
                    var e = c("button[type='button']", a);
                    var fn = d.data("fun");
                    var notsubmit = d.data("notsubmit");
                    if (notsubmit){
                        return true;
                    }

                d.button("loading"),
                    c(a).ajaxSubmit({
                        dataType: "json",
                        success: function(a) {
                           if (a.code != 0){
                                g && g.modal("hide");
                                if (fn) eval(fn + '()');
                            }
                            f.run(a);
                            d.button("reset"),e.button("reset")
                        },
                        error: function(a) {
                            d.button("reset"),e.button("reset"),
                            g && g.modal("hide"),
                             G.ui.tips.info(a.responseText)
                        }
                    })
             }
        },
        i = b.data("ignore");
        i && (h.ignore = i);
        var j = c("div.errorContainer"),
        k = {
            errorContainer: c("div.errorContainer"),
            errorLabelContainer: c("div.errorLabelContainer"),
            errorElement: "label"
        };
        j.length > 0 && c.extend(h, k),
        a.async(["validate", "jform"],
        function() {
            b.each(function() {
                c(this).validate(h)
            })
        })
    };
    var f = a(assets_path+"/assets/dist/form/tip")
}),
define(assets_path+"/assets/dist/form/tip", ["$", "dist/application/app"],
function(require, exports, module) {
    "use strict";
    var $ = require("$"),
    app = require("dist/application/app"),
    config = app.config;
    return exports.run = function(data) {

        switch (data.code) {
        case 1:
			if (data._fun) eval(data._fun+'(data)');
			$(".modal-dialog .close").trigger("click");
            G.ui.tips.suc(data.msg || config.lang.saveSuccess, data.url);
            break;
        default:
            if (data.msg != 'no') G.ui.tips.suc(data.msg || config.lang.exception, data.url)
        }
        data.callback && eval(data.callback);

    },
    exports
});