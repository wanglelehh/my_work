define("dist/goods/init", ["./classify", "$", "dist/application/app",  "./goods", "chosen"],
function(a) {
    "use strict";
    a("./classify"),
    a("./goods")
}),
define("dist/goods/classify", ["$", "dist/application/app"],
function(a) {
    "use strict";
    var b = a("$"),
    c = a("dist/application/app"),
    e = c.config,
    f = b("#dragClassify"),
    g = (b("#noClassify"), b("#save_class")),
    h = b("#nestableMenu");
    f.length > 0 && a.async("nestable",
    function() {
        f.nestable({
            maxDepth: 3
        }),
        b(".dd-handle a").on("mousedown",
        function(a) {
            a.stopPropagation()
        })
    }),
    g.on("click",
    function() {
        var a = b(this),
        c = a.data("remote") || a.attr("href");
        a.button("loading");
        var g = window.JSON.stringify(f.nestable("serialize"));
        b.post(c, {
            data: g
        }).done(function(b) {
            a.button("reset"),
            e.issucceed(b) ? G.ui.tips.suc(b.message || e.lang.saveSuccess, b.url) : G.ui.tips.suc(b.message || e.lang.saveError, b.url)
        }).fail(function() {
            a.button("reset"),
            G.ui.tips.suc(e.lang.exception, d.url)
        })
    }),
    h.on("click",
    function() {
        var a = b(this).hasClass("active");
        f.nestable(a ? "expandAll": "collapseAll")
    })
	var v = b(".js_quota");
    v.on("click",
    function() {
        var a = b(this).hasClass("js_quota_show"),
        c = b("."+b(this).attr('name')+"_container");
        c.toggleClass("hd", !a).toggleClass("inline", a)
    })
}),

define("dist/goods/goods", ["$", "dist/application/app", "chosen"],
function(a) {
    "use strict";
    var b = a("$"),
    c = a("dist/application/app"),
    d = c.config,
    e = c.method;
    a("chosen");
    var f = b('[data-toggle="attribute"]');
    window.goods_data ? (template.helper("specifications_checked",
    function(a, c) {
        return b.inArray(c, window.goods_data.specifications[a]) > -1 ? 'checked="checked"': d.empty
    })) : (template.helper("specifications_checked",
    function() {
        return d.empty
    }));
	var fn_a;
	var cf = b('[data-toggle="changeSkuModelId"]');
    var h = b('[data-toggle="specifications"]'),
    i = b("div.specificationstable"),
    ii = b("div.specimgstable"),
    j = b("div.nospecifications"),
	fn = function(){
		var skuModelId = cf.val();
		if (skuModelId < 1) return false;
		e.post(window.goods_setting.specifications_path,
        function(d) {
		    d.is_supplyer = window.goods_data.is_supplyer,
            fn_a.SpecificationsList = d.data,
            b.each(d.data,
            function(b, c) {
                fn_a.TableData.ths.push(c)
            }),
            h.html(template("specifications_template", d)),
            fn_a.init(),
            c && fn_a.acquire()

        },
        d.lang.specificationsError,0,'skuModelId='+skuModelId);
	};
	b(document).on("change", '[data-toggle="changeSkuModelId"]',function(c) {fn(j)})
   var k = function() {
        this.specVals = null,
        this.SpecificationsList = null,
        this.TableData = {
            ths: []
        },
        this.specificationstable = b('[data-toggle="specificationstable"]'); {
            var a = this;
            b('[data-toggle="specvals"]')
        }
        b('[data-toggle="specification-enable"]').on("click",
        function() {
            var a = b(this).data("enable");
            h.toggle(a),
            a ? (i.show(), j.hide()) : (i.hide(),j.show());
            fn();
        });
        var c = b('[data-toggle="specification-enable"]:checked').data("enable");		
        c ? (h.show(), i.show(), j.hide()) :  i.hide(),fn();
        fn_a = a;
    },
    l = function() {};

    l.prototype.result = function(a, c, e) {
        var f = b("#" + a.attrid).find(".js_custom_list:first");
        b("#" + a.attrid).find("li").removeClass("active"),
        b("#" + a.attrid).find("li input[type='radio']").removeAttr("checked"),
        b(template("customattrval_tem", a)).insertBefore(f),
        b("#label_" + a.attrid).html(a.title),
        b("#label_" + a.attrid).removeClass("error"),
        b(document).trigger("click"),
        c.val(""),
        c.next(".js_limit").find("em").html(0),
        e ? (b(".js_add_attr_input").val(""), b(".js_add_attr_input").next(".js_limit").find("em").html(0), this.closeAttrForm()) : (f.addClass("hide"), f.next(".js_custom_list").removeClass("hide")),
        b("#" + a.attrid).find(".js_search_result").addClass("hide"),
        G.ui.tips.suc(d.lang.saveSuccess)
    },

    l.prototype.customInput = function() {
        var a = this;
        b(document).on("keyup", ".js_custom_input",
        function() {
            var c = a.getInputLen(b(this)),
            d = b(this).data("limit") - 0,
            e = b(this).next(".js_limit");
            e.find("em").html(Math.ceil(c / 2)),
            c > 2 * d ? e.addClass("error") : e.removeClass("error")
        })
    },
    l.prototype.getInputLen = function(a) {
        return a.val().replace(/[^\x00-\xff]/g, "xx").length
    },


    k.prototype.acquire = function() {
        this.selectSpecifications_vals = new Array;
        var a = this;
        a.TableData.ths = [],
        b.each(this.SpecificationsList,
        function(c, d) {
            var e = d.id,
            f = b("#specvals_" + e).find('[type="checkbox"]:checked'),
            g = new Array;
            b.each(f,
            function(a, b) {
                g.push({
                    val: b.title,
                    key: b.value,
                    code: $(b).data('code')
                })
            });
            var h = g.length > 0;
            h && a.TableData.ths.push(d),
            h && a.selectSpecifications_vals.push(g)
        }),
        this.showtable = this.selectSpecifications_vals.length,
        this.showtable && this.generate(),
        this.setimg(),
        this.showtable || this.specificationstable.html('<span class="help-inline p-t">请选择规格</span>');

    },
    k.prototype.setimg = function() {
        var skuImg = [];
        var selVal = [];
        b.each(this.TableData.ths, function(c, d) {
            var e = d.id,
                f = b("#specvals_" + e).find('[type="checkbox"]:checked'),
                g = new Array;
            b.each(f,
                function(a, b) {
                    g.push({
                        val: b.title,
                        key: b.value
                    })
                });
            if (d.img_type == 1){
                d._all_val = g;
                selVal.push(d);
            }else if (d.img_type == 2){
                skuImg.push(g);
            }
        })
        ii.hide();
        if (skuImg.length > 0){
            skuImg = this.combine(skuImg.reverse());
            var _skuImg = [];
            b.each(skuImg, function(c, d) {
                var skuId = [], skuVal = [];
                b.each(d, function(cc, dd) {
                    skuId.push(dd.key);
                    skuVal.push(dd.val);
                })
                _skuImg.push({
                    val: skuVal.join(" + "),
                    key: skuId.join(":")
                })
            })
            skuImg = _skuImg;
            ii.show();
        }
        if (selVal.length > 0){
            ii.show();
        }
        b('[data-toggle="specimgstable"]').html(template("specimgs_table_template", {skuImgList:window.goods_data.skuImgList,goods_id:window.goods_data.goods_id,uploadpath:window.goods_data.uploadpath,skuImg:skuImg,selVal:selVal}));
    },
    k.prototype.skuLimit = function(){
        var link_spec = [];
        b.each(this.SpecificationsList, function (i, v) {
            b.each(v.all_val, function (ib, vb) {
                if (vb.link_spec.length < 1) return true;
                var g = {
                    key:vb.key,
                    spec:vb.link_spec
                }
                link_spec.push(g);
            })
        })
        this.link_spec = link_spec;

    }
    k.prototype.generate = function() {
        this.res = this.combine(this.selectSpecifications_vals.reverse()),
        this.rowspan();
        this.TableData.trs = [];
        this.skuLimit();
        var a = this;
        var skuId = [];
        var exceptNum = 0;
        b.each(this.res,
        function(c, d) {
            var e = [],
            f = [],
            sname=[];
            b.each(d,
            function(b, d) {
                var g = [];
                g.rowspan = a.row[b],
                g.key = d.key,
                g.val = d.val,
                g.index = c,
                g.code = d.code,
                sname.push(d.val),
                e.push(g),
                f.push(d.key);

            });
            var f_length = f.length;
            window.goods_data.sku_length = f_length;
            d.isExcept = 0;
            if (a.link_spec.length > 0){
              b.each(a.link_spec,
                    function(si, sv) {
                       var key = sv.key.toString();
                       var inNum = $.inArray(key,f) + 1;
                        if (inNum > 0 ){
                            if (inNum == f_length){
                                return true;
                            }
                           var fv = f[inNum];
                           if ($.inArray(fv,sv.spec) < 0 ){
                                d.isExcept = 1;
                                exceptNum++;
                                return false;
                            }
                        }
                    })
            }


            var g = f.join(":"),
                h = {
                    isExcept: d.isExcept,
                    isEdit:0,
                    tds: e,
                    index: c,
                    sku_val: g,
                    sku_name:sname.join(",")
                };

            if (a.changeval(g), window.goods_data || (window.goods_data = {
                products: {}
            }), window.goods_data && window.goods_data.products) {
                var i = window.goods_data.products[g];
                i && (h = b.extend({},
                h, i))
            }

            if (h.sku_id && h.status == 1){//add by iqgmy
                skuId.push(h.sku_id);
            }
            if (!h.isSale && h.isSale !== 0){
                h.isSale = 1;
            }
            a.TableData.trs.push(h)
        }),
        a.TableData.goods_id = window.goods_data.goods_id, a.TableData.is_supplyer = window.goods_data.is_supplyer, a.TableData.uploadpath=window.goods_data.uploadpath;
        var is_spec = $("input[name='is_spec']:checked").val();
        a.TableData.allGoodsNum = this.TableData.trs.length - exceptNum;
        if (a.TableData.allGoodsNum <= 40){
            this.specificationstable.html(template("specifications_table_template", this.TableData));
        }else{
            this.specificationstable.html(template("spec_more_template",{allGoodsNum:a.TableData.allGoodsNum,SpecificationsList:this.SpecificationsList}));
        }

        i.show();
        //add by iqgmy
        var diffProducts = [];
        if (window.goods_data.goods_id > 0){
           $.each(window.goods_data.products,function (i,v) {
               if (v.sku_id > 0 && $.inArray(v.sku_id,skuId) < 0){
                   diffProducts.push(v);
               }
           })
        }
        b('[data-toggle="diff_spec_box"]').html(template("diff_spec_table_template", {products:diffProducts}));

    },
    b(document).on("change", '[data-toggle="search_spec"]',function() {
       var search_spec_box = b('[data-toggle="search_spec_box"]');
        search_spec_box.html('');
        var searchSpec = [];
        b('[data-toggle="search_spec"]').each(function () {
           if($(this).val() > 0){
               searchSpec.push($(this).val());
           }
        });
        if (searchSpec.length < 1) return false;
        var products = [];
        b.each(fn_a.TableData.trs, function (i, v) {
            if (v.isExcept == 1 ){
                return true;
            }
            var keys = v.sku_val.split(':');
            var isExist = true;
            b.each(searchSpec, function (si, sv) {
                if ($.inArray(sv,keys) < 0){
                    isExist = false;
                    return false;
                }
            });
            if (isExist == true){
                if (!v.sku_id){
                    var sku_name = [];
                    b.each(v.tds, function (ti, tv) {
                        sku_name.push(tv.val);
                    })
                    v.sku_name = sku_name.join(',');
                }
                products.push(v);
            }
        })
        b('[data-toggle="search_spec_box"]').html(template("search_spec_template", {products:products}));
    }),
    b(document).on("click", '[data-toggle="diff_spec_display"]',function() {
        var obj = $('#diff_spec_table');
        if (obj.hasClass('hd')){
            obj.removeClass('hd');
            $(this).html('【关闭】');
        }else{
            obj.addClass('hd');
            $(this).html('【查看】');
        }
    }),
    b(document).on("click", '[data-toggle="set_is_sale"]',function() {
        var index = $(this).data('index');
        fn_a.TableData.trs[index].isEdit = 1;
        if (fn_a.TableData.trs[index].isSale == 0){
            fn_a.TableData.trs[index].isSale = 1;
            $(this).addClass('active');
        }else{
            fn_a.TableData.trs[index].isSale = 0;
            $(this).removeClass('active');
        }
    }),
    b(document).on("click", '[data-toggle="batch_public_spec"]',
        function (c) {
            b("#ajaxModal").remove(),c.preventDefault();
            var d = b(this),
                e =  d.attr("href"),
                g = b('<div class="modal fade" id="ajaxModal"><div class="modal-body "></div></div>');
            b(document).append(g),
                g.modal(),
                g.append2(template(e,{SpecificationsList:fn_a.TableData.ths,is_supplyer:window.goods_data.is_supplyer}),
                    function () {
                        var c = b("form.form-validate", g);
                        c.length > 0 && a.async("dist/form/init",
                            function (a) {
                                b("button[type='submit']", g).length && b("button[type='submit']", g).removeAttr("disabled"),
                                    a.init(c)
                            }),
                            d.trigger("init", g);
                    })
    }),
    b(document).on("click", '[data-toggle="edit_spec"]',
            function (c) {
                b("#ajaxModal").remove(),c.preventDefault();
                var d = b(this),
                    e =  d.attr("href"),
                    index = d.data("index"),
                    g = b('<div class="modal fade" id="ajaxModal"><div class="modal-body "></div></div>');
                b(document).append(g),
                    g.modal(),
                    g.append2(template(e,fn_a.TableData.trs[index]),
                        function () {
                            var c = b("form.form-validate", g);
                            c.length > 0 && a.async("dist/form/init",
                                function (a) {
                                    b("button[type='submit']", g).length && b("button[type='submit']", g).removeAttr("disabled"),
                                        a.init(c)
                                }),
                                d.trigger("init", g);
                  })
    }),
    b(document).on("click", '[data-toggle="edit_spec_submit"]',function () {
        setTimeout(function(){
           if ($('#edit_spec_form').find('input.error').length > 0){
               return false;
           }
            var spec = $('#edit_spec_form').toJson();
            fn_a.TableData.trs[spec.index].isEdit = 1;
            fn_a.TableData.trs[spec.index].isSale = parseInt(spec.isSale);
            fn_a.TableData.trs[spec.index].ProductSn = spec.ProductSn;
            if (spec.sku_id){
                fn_a.TableData.trs[spec.index].addStore = spec.addStore;
            }else{
                fn_a.TableData.trs[spec.index].Store = spec.Store;
            }
            fn_a.TableData.trs[spec.index].Price = spec.Price;
            if (spec.SettlePrice > 0){
                fn_a.TableData.trs[spec.index].SettlePrice = spec.SettlePrice;
            }
            fn_a.TableData.trs[spec.index].MarketPrice = spec.MarketPrice;
            fn_a.TableData.trs[spec.index].Weight = spec.Weight;
            var g = b(document).find(".modal");
            g.modal("hide");
            if (fn_a.TableData.allGoodsNum <= 40){
                fn_a.specificationstable.html(template("specifications_table_template", fn_a.TableData));
            }else{
                b(document).find('[data-toggle="search_spec"]').trigger("change");
            }
       },300)
    }),
    b(document).on("click", '[data-toggle="batch_create_spec_sn_submit"]',function () {
        setTimeout(function(){
            if ($('#batch_create_spec_sn_form').find('input.error').length > 0){
                return false;
            }
            var data = $('#batch_create_spec_sn_form').toJson();
            var create_index = data.create_index.split(',');
            $.each(fn_a.TableData.trs,function (i,v) {
                var code = [];
                $.each(create_index,function (ci,cv) {
                    if (cv < 0){
                        return true;
                    }
                    code.push(v.tds[cv].code);
                })
                v.ProductSn = data.model_number+code.join("");
            })

            var g = b(document).find(".modal");
            g.modal("hide");
            if (fn_a.TableData.allGoodsNum <= 40){
                fn_a.specificationstable.html(template("specifications_table_template", fn_a.TableData));
            }else{
                b(document).find('[data-toggle="search_spec"]').trigger("change");
            }
        },300)
    }),
    b(document).on("click", '[data-toggle="batch_edit_spec_info_submit"]',function () {
        setTimeout(function(){
            var spec = $('#batch_edit_spec_info_form').toJson();
            var searchSpec = [];
            var edit_spec = spec.edit_spec.split(',');
            $.each(edit_spec,function (i,v) {
                if (v < 1) return true;
                searchSpec.push(v);
            })
            b.each(fn_a.TableData.trs, function (i, v) {
                if (v.isExcept == 1) {
                    return true;
                }
                var keys = v.sku_val.split(':');
                var isExist = true;
                b.each(searchSpec, function (si, sv) {
                    if ($.inArray(sv, keys) < 0) {
                        isExist = false;
                        return false;
                    }
                });
                if (isExist == false) return true;
                if (spec.goods_type == 1 && v.sku_id > 0) return true;

                if (spec.Store > 0){
                    if (v.sku_id > 0){
                        v.addStore = spec.Store;
                    }else{
                        v.Store = spec.Store;
                    }
                }
                if (spec.Price > 0){
                    v.Price = spec.Price;
                }
                if (spec.SettlePrice > 0){
                    v.SettlePrice = spec.SettlePrice;
                }
                if (spec.MarketPrice > 0){
                    v.MarketPrice = spec.MarketPrice;
                }
                if (spec.Weight > 0){
                    v.Weight = spec.Weight;
                }
            })
            var g = b(document).find(".modal");
            g.modal("hide");
            if (fn_a.TableData.allGoodsNum <= 40){
                fn_a.specificationstable.html(template("specifications_table_template", fn_a.TableData));
            }else{
                b(document).find('[data-toggle="search_spec"]').trigger("change");
            }
        },300)
    }),
    b(document).on("click", '[data-toggle="recoverySku"]',function() {
        var sku = $(this).data('sku');
        var sku_val = $(this).data('sku_val');
        var sku_arr = [];
        var skuval_arr = [];
        if ($.isNumeric(sku_val)){
            sku_arr.push(sku);
            skuval_arr.push(sku_val);
        }else{
            sku_arr = sku.split(":");
            skuval_arr = sku_val.split(":");
        }
        if (skuval_arr.length != window.goods_data.sku_length){
            _alert('与当前规格不一致，无法恢复.');
            return false;
        }
        window.goods_data.products[sku_val].status = 1;
        var specifications = window.goods_data.specifications;
        $.each(sku_arr,function (i,v) {
            if (typeof(specifications[v]) == 'undefined'){

            }else{
                if ($.inArray(skuval_arr[i],specifications[v]) < 0){
                    specifications[v].push(skuval_arr[i]);
                }
            }
        })
        fn()
    }),
    k.prototype.changeval = function(a) { ! window.goods_data.products[a] && (window.goods_data.products[a] = {}),
        b(document).on("change", '[data-id="' + a + '"]',
        function() {
            var a = b(this),
            c = a.data("id"),
            d = a.data("name"),
            e = b('[data-id="' + c + '"]'),
            f = {};
			
            b.each(e,
            function() {
                var c = {},
                e = a.val();
                c[d] = e,
                f = b.extend({},
                f, c)
            });
            var g = a.closest("tr").siblings().find('[data-name="' + d + '"]');
            a.valid() && b.each(g,
            function() {
                var c = b(this);
                if (c.data('nochangeval') != 1) {
                    c.val().length < 1 && (c.val(a.val()), c.valid())
                }
            }),
            window.goods_data.products[c] = f
			
        })
    },
    k.prototype.rowspan = function() {
        for (var a = [], b = this.res.length, c = this.selectSpecifications_vals.length - 1; c > -1; c--)
            a[c] = parseInt(b / this.selectSpecifications_vals[c].length), b = a[c];
        this.row = a.reverse()
    },
    k.prototype.refresh = function() {
        var a = this;
        a.TableData.ths = [],
        b.each(a.SpecificationsList,
        function(b, c) {
            a.TableData.ths.push(c)
        }),
        this.acquire()
    },

    k.prototype.combine = function(a) {
        var b = [];
        return function c(a, d, e) {
            if (0 == e) return b.push(a);
            for (var f = 0; f < d[e - 1].length; f++) c(a.concat(d[e - 1][f]), d, e - 1)
        } ([], a, a.length),
        b
    },
    k.prototype.init = function() {
        var a = this;
        b(document).off("click", '[data-toggle="specvals"] input[type="checkbox"]');
        b(document).on("click", '[data-toggle="specvals"] input[type="checkbox"]',
        function() {
            a.acquire()
        });
        var c = new l;
		c.customInput();//add by iqgmy

    },

    f.length > 0 && g(new l);
    var m;
    h.length > 0 && (m = new k);
    var n = b(".js_undertake");
    if (n.length > 0) {
        var o = b(".js_freight_container"),
        p = b(".js_freight_type"),
        q = b(".js_unify_container"),
        r = b(".js_template_container"),
        s = (b(".js_freight_template_loading"), b(".js_freight_template")),
        t = b(".js_freight_template_refresh"),
        u = b(".js_freight_item");
        n.on("click",
        function() {
            o.toggle(b(this).hasClass("js_freight_container_show"))
        }),
        p.on("click",
        function() {
            var a = b(this);
            q.toggle(a.hasClass("js_unify_container_show")),
            r.toggle(a.hasClass("js_template_container_show"))
        }),
        u.on("click",
        function() {
            var a = b(this),
            c = a.closest("dl");
            b('input[type="text"]', c).addAttr("disabled", !a.prop("checked"))
        }),
        t.on("click",
        function(a) {
            a && a.preventDefault();
            var c = b(this),
            e = c.data("remote") || c.attr("href"),
            f = c.find("i");
            f.addClass("fa-spin");
            var g = ' <option value="{0}">{1}</option>';
            s.empty(),
            b.post(e).done(function(a) {
                a.data && (s.append('<option value="">请选择</option>'), b.each(a.data,
                function(a, b) {
                    s.append(g.format(b.id, b.name))
                })),
                f.removeClass("fa-spin"),
                s.focus()
            }).fail(function() {
                f.removeClass("fa-spin"),
                G.ui.tips.suc(d.lang.exception)
            })
        })
    }
    b(document).on("click", ".js_save_submit",
    function() {
        return b(".js_fileList li.imgbox").length < 1 ? (G.ui.tips.suc("至少选择一个商品图片"), !1) : void 0
	   return void 0;
    }),
    b(document).on("click", ".js_save_submit",
    function() {
		var is_package = $('#is_package').val();
		if (is_package == 1) return void 0;
        var a = b('[data-toggle="specifications"]'),
        c = a.find("input[type='checkbox']:checked").length;
        if (! a.is(":hidden") && 1 > c){
            return (G.ui.tips.suc("至少选择一个商品规格"), !1);
        }
        var newData = new Object();
        newData.trs = [];
        b.each(fn_a.TableData.trs,function (i,v) {
            if (v.isExcept == 1) return true;
            var row = {};
            b.each(v,function (ib,vb) {
                if (ib == 'tds' || ib =='isExcept') return true;
                row[ib] = vb;
            })
            newData.trs.push(row);
        })
        var sku = [];
        b.each(fn_a.TableData.ths,function (i,v) {
            sku.push(v.id);
        })
        newData.sku = sku.join(':');
        $('#spec_data').val(JSON.stringify(newData));//手动写入数据到文本
        return void 0;
    });
    $(document).on('click', '#editStore',function () {
        var arr = new Object();
        arr.goods_id = window.goods_data.goods_id;
        var newData = new Object();
        newData.trs = [];
        b.each(fn_a.TableData.trs,function (i,v) {
            if (v.isExcept == 1) return true;
            var row = {};
            b.each(v,function (ib,vb) {
                if (ib == 'tds' || ib =='isExcept') return true;
                row[ib] = vb;
            })
            newData.trs.push(row);
        })
        var sku = [];
        b.each(fn_a.TableData.ths,function (i,v) {
            sku.push(v.id);
        })
        newData.sku = sku.join(':');
        arr.spec_data = JSON.stringify(newData);
        jq_ajax($(this).data('url'), arr, function (res) {
            if (res.code == 0) {
                return _alert(res.msg);
            }
            window.location.reload();
        })
    })
    var v = b(".js_quota");
    v.on("click",
    function() {
        var a = b(this).hasClass("js_quota_show"),
        c = b(".js_quota_container");
        c.toggleClass("hd", !a).toggleClass("inline", a)
    }),
    b(document).on("click", ".js_submit",
    function() {
        b(this).closest("form").submit()
    }),
    b(document).on("keypress",
    function(a) {
		if (b(".js_spe_speval").length < 1) return true;
        var a = a || event,
        c = a.keyCode || a.which || a.charCode;
        return 13 == c ? (b(".js_spe_speval").closest(".js_enter_div").hasClass("hide") || b(".js_spe_speval").trigger("click"), b(".js_add_speval").each(function() {
            b(this).closest(".js_enter_div").hasClass("hide") || b(this).trigger("click")
        }), !1) : void 0
    }),
    b(document).on("click", ".js_save_submit",
    function() {
        var a = b(this),
        c = b(this).data("confirm"),
        e = a.data("confirmMsg");
        if (c) {
            var f = b("#js_store_way"),
            g = b(".js_way_select:checked").val();
            if (0 == g && f.val() != g) return  G.ui.tips.confirm_flag(a.data("confirmText") || "确定修改",
             	function(b) {
                   a.submit()
                }
            ),
            !1
        }
    }),
    b(document).on("click", ".js_search_add_submit",
    function() {
        var a = b(this).closest("form"),
        c = a.attr("action"),
        d = b(".talbe-search").serialize();
        a.attr("action", c.format(d))
    }),
    b(document).on("click", ".js_edit_search",
    function() {
        var a = b(this),
        c = a.attr("href"),
        d = b(".talbe-search").serialize();
        a.attr("href", c.format(d))
    })
});