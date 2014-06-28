/*  1,87,249 2012-10-30 22:47:00 */
//version 1.2.1

(function(){
    try {document.domain = 'sina.com.cn';}
    catch (__err) {return;}

    var flashData;
    if (!window.tmpUidev || !window.tmpUidev._flashData) {
        var tmpUidev = window.tmpUidev || {};
        window.tmpUidev = tmpUidev;
        //数据
        flashData = {
            flashObj : null,
            id : "uidevGuessInfoDataFrame",
            status : "loading",
            clear : function(){
                this.flashObj.write("");
            },
            read : function(name){
                if(this.status != "ok"){return "";};
                var nameRule = /[=,;]+/i;
                if(nameRule.test(name)){return "";};
                var dataScring = this.flashObj.read();
                if(dataScring == null){dataScring = ''};
                var reg = new RegExp("(?:^|;)" + name + "=(.*?),expires=([^;]*)","i");
                var value = dataScring.match(reg);
                if(value == null){
                    value = "";
                }else if(value.length >= 3){
                    var now = new Date();
                    var dd = now.getTime();
                    if(new Number(value[2]) > dd){
                        value = value[1];
                        value = value.replace(/\[\$\=\]/g,"=");
                        value = value.replace(/\[\$\;\]/g,";");
                        value = value.replace(/\[\$\,\]/g,",");
                    }else{
                        value = "";
                    };
                }else{
                    value = "";
                };
                return value;
            },
            write : function(name,value,hours){
                if(this.status != "ok"){return;};
                var nameRule = /[=,;]+/i;
                if(nameRule.test(name)){return;};
                value = value.toString();
                value = value.replace(/\\/g,"\\\\");
                value = value.replace(/\&/g,"&amp;");
                value = value.replace(/\=/g,"[$$=]");
                value = value.replace(/\;/g,"[$$;]");
                value = value.replace(/\,/g,"[$$,]");
                if(typeof(hours) != "number"){hours = 24*30*3}; //默认3个月
                var now = new Date();
                var expire = new Date(now.getTime() + hours * 3600000);
                expire = ",expires=" + expire.getTime();

                var list = this.flashObj.read();
                if(list == null){list = ''};
                list = list.split(/;(?!\])/i);

                var newList = [],update = false;
                var data;
                for(var i=0;i<list.length;i++){
                    data = list[i].match(/(.*?)\=(.*?),expires=(\d*)/i);
                    if(data == null){continue};
                    if(data.length < 4){continue};
                    if(data[1] == name){
                        if(value != ""){newList.push(name + "=" + value + expire)};
                        update = true;
                    }else{
                        if(new Date(new Number(data[3])) > now && data[2] != ""){
                            newList.push(list[i]);
                        };
                    };
                };
                if(!update){ //添加新的
                    newList.push(name + "=" + value + expire);
                };
                var newStr = "";
                for(var i=0;i<newList.length;i++){
                    if(newStr != ""){newStr += ";"};
                    newStr += newList[i];
                };
                this.flashObj.write(newStr);

            },
            load : function(){
                var i;
                try{
                    this.flashObj = document.getElementById(this.id).contentWindow.flashData.flashObj;
                    this.status = "ok";
                    this.myLoaded = true;
                    if (this.myLoadFn && this.myLoadFn.length > 0) {
                        for (i = 0; i < this.myLoadFn.length; i++) {
                            this.myLoadFn[i](); 
                        }
                    }
                    if(this.onload){this.onload()};
                }catch(e){
                }
            },
            myLoadFn: null,
            myLoad: function (fn) {
                if (flashData.myLoaded) {
                    (typeof fn === 'function') && (fn());
                    return;
                }
                if (!flashData.myLoadFn) {
                    flashData.myLoadFn = []; 
                }
                (typeof fn === 'function') && (flashData.myLoadFn.push(fn));
            },
            myLoaded: false
        };

        //expose
        tmpUidev._flashData = flashData;

        //load flash iframe
        if (!document.getElementById(flashData.id)) {
            function loadDataIframe () {
                var body = document.getElementsByTagName('body');
                if (!body || body.length === 0) {
                    setTimeout(loadDataIframe, 100); 
                    return;
                }
                body = body[0];
                if (!body.children || body.children.length === 0) {
                    setTimeout(loadDataIframe, 100); 
                    return;
                }
                var div = document.createElement('div');
                div.style.width = '1px';
                div.style.height = '1px';
                div.style.overflow = 'hidden';
                div.style.position = 'absolute';
                div.style.left = '-2000px';
                div.innerHTML = '<iframe src="http://www.sina.com.cn/iframe/fdata/data.html" id="uidevGuessInfoDataFrame" style="height:0px;width:1px;overflow:hidden;" frameborder="0" scrolling="no"></iframe>';
                body.insertBefore(div, body.children[0]);
            };
            loadDataIframe();
        }
    } else {
        flashData = window.tmpUidev._flashData; 
    }

    var KEY = 'URLBASED-INTEREST';
    var READ_KEY = 'SINA-READ-MEM';
    var MEM_MAX_LENGTH = 100;
    var DAY_LENGTH = 7;
    var SYNC_API = 'http://interest.mix.sina.com.cn/index.php/default/upload?format=json',
        DOWNLOAD_API = 'http://interest.mix.sina.com.cn/index.php/default/download?format=json&uid=',
        INTEREST_API = 'http://interest.mix.sina.com.cn/default/get?format=json',
        REMOTE_INTEREST_API = 'http://interest.mix.sina.com.cn/default/check?format=json&uid=';

    var ENUM_TYPE = {
        '0': 'news',
        '1': 'slide',
        '2': 'video',
        '3': 'blog'
    };

    var md5 = (function(){
        var hex_chr = "0123456789abcdef";
        var str, j;
        function rhex(num)
        {
          var str = "";
          for(j = 0; j <= 3; j++)
            str += hex_chr.charAt((num >> (j * 8 + 4)) & 0x0F) +
                   hex_chr.charAt((num >> (j * 8)) & 0x0F);
          return str;
        }

        /*
         * Convert a string to a sequence of 16-word blocks, stored as an array.
         * Append padding bits and the length, as described in the MD5 standard.
         */
        function str2blks_MD5(str)
        {
          var nblk = ((str.length + 8) >> 6) + 1;
          var blks = new Array(nblk * 16);
          var i;
          for(i = 0; i < nblk * 16; i++) blks[i] = 0;
          for(i = 0; i < str.length; i++)
            blks[i >> 2] |= str.charCodeAt(i) << ((i % 4) * 8);
          blks[i >> 2] |= 0x80 << ((i % 4) * 8);
          blks[nblk * 16 - 2] = str.length * 8;
          return blks;
        }

        /*
         * Add integers, wrapping at 2^32. This uses 16-bit operations internally 
         * to work around bugs in some JS interpreters.
         */
        function add(x, y)
        {
          var lsw = (x & 0xFFFF) + (y & 0xFFFF);
          var msw = (x >> 16) + (y >> 16) + (lsw >> 16);
          return (msw << 16) | (lsw & 0xFFFF);
        }

        /*
         * Bitwise rotate a 32-bit number to the left
         */
        function rol(num, cnt)
        {
          return (num << cnt) | (num >>> (32 - cnt));
        }

        /*
         * These functions implement the basic operation for each round of the
         * algorithm.
         */
        function cmn(q, a, b, x, s, t)
        {
          return add(rol(add(add(a, q), add(x, t)), s), b);
        }
        function ff(a, b, c, d, x, s, t)
        {
          return cmn((b & c) | ((~b) & d), a, b, x, s, t);
        }
        function gg(a, b, c, d, x, s, t)
        {
          return cmn((b & d) | (c & (~d)), a, b, x, s, t);
        }
        function hh(a, b, c, d, x, s, t)
        {
          return cmn(b ^ c ^ d, a, b, x, s, t);
        }
        function ii(a, b, c, d, x, s, t)
        {
          return cmn(c ^ (b | (~d)), a, b, x, s, t);
        }

        /*
         * Take a string and return the hex representation of its MD5.
         */
        function calcMD5(str)
        {
          var x = str2blks_MD5(str);
          var a =  1732584193;
          var b = -271733879;
          var c = -1732584194;
          var d =  271733878;
          var olda, oldb, oldc, oldd, i;

          for(i = 0; i < x.length; i += 16)
          {
            olda = a;
            oldb = b;
            oldc = c;
            oldd = d;

            a = ff(a, b, c, d, x[i+ 0], 7 , -680876936);
            d = ff(d, a, b, c, x[i+ 1], 12, -389564586);
            c = ff(c, d, a, b, x[i+ 2], 17,  606105819);
            b = ff(b, c, d, a, x[i+ 3], 22, -1044525330);
            a = ff(a, b, c, d, x[i+ 4], 7 , -176418897);
            d = ff(d, a, b, c, x[i+ 5], 12,  1200080426);
            c = ff(c, d, a, b, x[i+ 6], 17, -1473231341);
            b = ff(b, c, d, a, x[i+ 7], 22, -45705983);
            a = ff(a, b, c, d, x[i+ 8], 7 ,  1770035416);
            d = ff(d, a, b, c, x[i+ 9], 12, -1958414417);
            c = ff(c, d, a, b, x[i+10], 17, -42063);
            b = ff(b, c, d, a, x[i+11], 22, -1990404162);
            a = ff(a, b, c, d, x[i+12], 7 ,  1804603682);
            d = ff(d, a, b, c, x[i+13], 12, -40341101);
            c = ff(c, d, a, b, x[i+14], 17, -1502002290);
            b = ff(b, c, d, a, x[i+15], 22,  1236535329);    

            a = gg(a, b, c, d, x[i+ 1], 5 , -165796510);
            d = gg(d, a, b, c, x[i+ 6], 9 , -1069501632);
            c = gg(c, d, a, b, x[i+11], 14,  643717713);
            b = gg(b, c, d, a, x[i+ 0], 20, -373897302);
            a = gg(a, b, c, d, x[i+ 5], 5 , -701558691);
            d = gg(d, a, b, c, x[i+10], 9 ,  38016083);
            c = gg(c, d, a, b, x[i+15], 14, -660478335);
            b = gg(b, c, d, a, x[i+ 4], 20, -405537848);
            a = gg(a, b, c, d, x[i+ 9], 5 ,  568446438);
            d = gg(d, a, b, c, x[i+14], 9 , -1019803690);
            c = gg(c, d, a, b, x[i+ 3], 14, -187363961);
            b = gg(b, c, d, a, x[i+ 8], 20,  1163531501);
            a = gg(a, b, c, d, x[i+13], 5 , -1444681467);
            d = gg(d, a, b, c, x[i+ 2], 9 , -51403784);
            c = gg(c, d, a, b, x[i+ 7], 14,  1735328473);
            b = gg(b, c, d, a, x[i+12], 20, -1926607734);

            a = hh(a, b, c, d, x[i+ 5], 4 , -378558);
            d = hh(d, a, b, c, x[i+ 8], 11, -2022574463);
            c = hh(c, d, a, b, x[i+11], 16,  1839030562);
            b = hh(b, c, d, a, x[i+14], 23, -35309556);
            a = hh(a, b, c, d, x[i+ 1], 4 , -1530992060);
            d = hh(d, a, b, c, x[i+ 4], 11,  1272893353);
            c = hh(c, d, a, b, x[i+ 7], 16, -155497632);
            b = hh(b, c, d, a, x[i+10], 23, -1094730640);
            a = hh(a, b, c, d, x[i+13], 4 ,  681279174);
            d = hh(d, a, b, c, x[i+ 0], 11, -358537222);
            c = hh(c, d, a, b, x[i+ 3], 16, -722521979);
            b = hh(b, c, d, a, x[i+ 6], 23,  76029189);
            a = hh(a, b, c, d, x[i+ 9], 4 , -640364487);
            d = hh(d, a, b, c, x[i+12], 11, -421815835);
            c = hh(c, d, a, b, x[i+15], 16,  530742520);
            b = hh(b, c, d, a, x[i+ 2], 23, -995338651);

            a = ii(a, b, c, d, x[i+ 0], 6 , -198630844);
            d = ii(d, a, b, c, x[i+ 7], 10,  1126891415);
            c = ii(c, d, a, b, x[i+14], 15, -1416354905);
            b = ii(b, c, d, a, x[i+ 5], 21, -57434055);
            a = ii(a, b, c, d, x[i+12], 6 ,  1700485571);
            d = ii(d, a, b, c, x[i+ 3], 10, -1894986606);
            c = ii(c, d, a, b, x[i+10], 15, -1051523);
            b = ii(b, c, d, a, x[i+ 1], 21, -2054922799);
            a = ii(a, b, c, d, x[i+ 8], 6 ,  1873313359);
            d = ii(d, a, b, c, x[i+15], 10, -30611744);
            c = ii(c, d, a, b, x[i+ 6], 15, -1560198380);
            b = ii(b, c, d, a, x[i+13], 21,  1309151649);
            a = ii(a, b, c, d, x[i+ 4], 6 , -145523070);
            d = ii(d, a, b, c, x[i+11], 10, -1120210379);
            c = ii(c, d, a, b, x[i+ 2], 15,  718787259);
            b = ii(b, c, d, a, x[i+ 9], 21, -343485551);

            a = add(a, olda);
            b = add(b, oldb);
            c = add(c, oldc);
            d = add(d, oldd);
          }
          return rhex(a) + rhex(b) + rhex(c) + rhex(d);
        }

        return calcMD5;
    })();

    var getJsonp = (function(){
        var rep = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j'];

        function randomStr () {
            var i, c,
                rdm = Math.round(Math.random() * 100000000) + '',
                output = '';
            for (i = rdm.length - 1; i >= 0; i--) {
                c = rdm.charAt(i);
                output += rep[c];
            }
            return output;
        };

        function jsonp (url, callback, callbackName, lastParam) {
            if( !url )
                return;

            var rdm = 'sinajsonp' + randomStr();
            while (window[rdm] != undefined) {
                rdm = 'sinajsonp' + randomStr();
            }
            window[rdm] = function (rt) {
                if (typeof callback === 'function')
                    callback(rt);
                setTimeout(function(){
                    try {delete window[rdm]}
                    catch (__err) {}
                }, 500);
            };
            callbackName = callbackName || 'callback';

            _getJsData(url + (url.indexOf('?') >= 0 ? '&' : '?') + callbackName + '=' + rdm + (lastParam ? ('&' + lastParam) : ''));
        };

        return jsonp;
    })();

    var inArray = function(array, elem) {
        var i;
        if (!array || array.length === 0) {
            return false; 
        } 
        for (i = 0; i < array.length; i++) {
            if (array[i] === elem) {
                return true; 
            } 
        }
        return false;
    };

    var cdPost = function (url, data) {
        if (!url || !data) 
            return;
        var html = '',
            name,
            div,
            rdm = Math.ceil( Math.random() * 100000 ),
            pre = 'ifmHidden',
            divId = pre + 'Div' + rdm,
            frmId = pre + 'Frm' + rdm,
            formId = pre + 'Form' + rdm,
            body = document.getElementsByTagName('body')[0],
            form;

        while (document.getElementById(pre + 'Div' + rdm)) {
            rdm = Math.ceil( Math.random() * 100000 );
        }

        div = document.createElement('div');
        div.id =  divId;
        div.style.height = '0px';
        div.style.width = '1px';
        div.style.overflow = 'hidden';
        div.style.fontSize = '0px';
        div.innerHTML =  
            '<iframe name="' + frmId + '" id="' + frmId + '"></iframe>' +
            '<form id="' + formId + '" target="' + frmId + '" method="post" action="">' + 
            '</form>';
        body.insertBefore(div, body.childNodes[0]);
        document.getElementById(frmId).onload = function(){
            setTimeout( function(){ try{body.removeChild(div)} catch(err){} }, 1000);
        };

        for (name in data) {
            html += '<input name="' + name + '" type="hidden" value="' + encodeURIComponent(data[name]) + '" />';
        }
        //$( '#' + formId ).html( html ).attr( 'action', url )[0].submit();
        form = document.getElementById(formId);
        form.innerHTML = html;
        form.setAttribute('action', url);
        form.submit();

    };

    var JSON = JSON || {};
    JSON.stringify = JSON.stringify || function (obj) {
        var t = typeof (obj);
        if (t != "object" || obj === null) {
            // simple data type
            if (t == "string") obj = '"'+obj+'"';
            return String(obj);
        }
        else {
            // recurse array or object
            var n, v, json = [], arr = (obj && obj.constructor == Array);
            for (n in obj) {
                v = obj[n]; t = typeof(v);
                if (t == "string") v = '"'+v+'"';
                else if (t == "object" && v !== null) v = JSON.stringify(v);
                json.push((arr ? "" : '"' + n + '":') + String(v));
            }
            return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
        }
    };
    JSON.parse = JSON.parse || function (str) {
        if (str === "") str = '""';
        eval("var p=" + str + ";");
        return p;
    };

    function _getJsData (url,dispose) {
        var scriptNode = document.createElement("script");
        scriptNode.type = "text/javascript";
        scriptNode.onreadystatechange = scriptNode.onload = function(){
            if (!this.readyState || this.readyState == "loaded" || this.readyState == "complete"){
                if(dispose){dispose()};
                scriptNode.onreadystatechange = scriptNode.onload = null;
                scriptNode.parentNode.removeChild(scriptNode);
            }
        };
        scriptNode.src = url;
        document.getElementsByTagName("head")[0].appendChild(scriptNode);
    };

    //load muti js resources and then exec callback
    //with cache
    var getJsResource = (function(){
        var loaded = {};

        return function (url, callback) {
            var totalCount, curCount = 0, i;
            if (typeof url === 'string') {
                if (loaded[url]) {
                    callback();
                } else {
                    _getJsData(url, callback);
                    loaded[url] = true;
                }
            } else if (url.length > 0) {
                totalCount = url.length;
                function oneLoaded(){
                    curCount++; 
                    if (curCount === totalCount) 
                        (typeof callback === 'function') && callback();
                };
                for (i = 0; i < totalCount; i++) {
                    if (loaded[url[i]]) {
                        setTimeout(oneLoaded, 0)
                    } else {
                        (function(url){
                            _getJsData(url, function(){ 
                                oneLoaded();
                                loaded[url] = true;
                            });
                        })(url[i]);
                    }
                }
            } else {
                (typeof callback === 'function') && callback(); 
            }
        };
    })();

    //取得并整理PATH_DICTIONARY
    var pathReady = {
        fininshed: false,
        fn: [],
        add: function (fn) {
            if (typeof fn === 'function') {
                if (this.fininshed) {
                    fn(); 
                } else {
                    this.fn.push(fn);
                }
            }
        },
        ready: function() {
            var i;
            if (this.fininshed) {
                return;
            }
            this.fininshed = true;
            for (i = 0; i < this.fn.length; i++) {
                this.fn[i](); 
            }
        }
    };

    var CATE_TYPE, CATE_CHANNEL, CATE_CATENAME;
    (function(){
        //getJsResource('http://interest.mix.sina.com.cn/default/generate', function(){
        getJsResource('http://sports.sina.com.cn/iframe/js/2012/interest_dict.js?ver=2', function(){
            var output = {};
            for (var i = 0; i < PATH_DICTIONARY.length; i++) {
                output[PATH_DICTIONARY[i]['url_md5']] = PATH_DICTIONARY[i]; 
            }
            PATH_DICTIONARY = output;

            var hash;
            CATE_TYPE = {};
            CATE_CHANNEL = {};
            CATE_CATENAME = {};
            for (hash in PATH_DICTIONARY) {
                CATE_TYPE[PATH_DICTIONARY[hash].cate_id] = PATH_DICTIONARY[hash].type; 
                CATE_CHANNEL[PATH_DICTIONARY[hash].cate_id] = PATH_DICTIONARY[hash].channel_id; 
                CATE_CATENAME[PATH_DICTIONARY[hash].cate_id] = PATH_DICTIONARY[hash].url; 
            }

            pathReady.ready();
        });
    })();

    //数据初始化, 创建，下载
    //数据收集[if(pageId)]
    //not working in iframe
    (function(){
        if (window.top !== window.self)
            return;

        var today = (new Date()).getTime();

        function UserData (uid) {
            this.id = uid;
            this.data = {
                days: [],
                lastTimeStamp: today,
                recentTotal: {},
                total: {}
            };
        };

        if (window.sinaSSOController) {
            pathReady.add(function(){
                setTimeout(function(){
                    if (window.sinaSSOController) fullInit(); 
                }, 10);
            });
        } else {
            getJsResource('http://i.sso.sina.com.cn/js/ssologin.js', function(){
                pathReady.add(function(){
                    setTimeout(function(){
                        if (window.sinaSSOController) fullInit(); 
                    }, 10);
                });
            });
        }

        function fullInit () {
            var pageId, onflastDataLoad;

            pageId = (function(){
                var url = location.href,
                    tmp; 
                if (url.indexOf('http://') == 0)
                    url = url.substring(7);
                else if (url.indexOf('https://') == 0)
                    url = url.substring(8);
                tmp = md5(url).substring(0,8);
                if (PATH_DICTIONARY[tmp] != undefined)
                    return PATH_DICTIONARY[tmp].cate_id;
                while (url.indexOf('/') >= 0) {
                    url = url.substring(0, url.lastIndexOf('/'));
                    if (url.indexOf('/') < 0) {
                        tmp = md5(url + '/').substring(0,8);
                        if (PATH_DICTIONARY[tmp] != undefined)
                            return PATH_DICTIONARY[tmp].cate_id;
                        tmp = md5(url).substring(0,8);
                        if (PATH_DICTIONARY[tmp] != undefined)
                            return PATH_DICTIONARY[tmp].cate_id;
                        break;
                    }
                    tmp = md5(url + '/').substring(0,8);
                    if (PATH_DICTIONARY[tmp] != undefined)
                        return PATH_DICTIONARY[tmp].cate_id; 
                }
                tmp = md5(url).substring(0,8);
                if (PATH_DICTIONARY[tmp] != undefined)
                    return PATH_DICTIONARY[tmp].cate_id; 
            })();

            if (pageId === undefined)
                return;

            onflastDataLoad = function(){
                var i, uid, dataRef, store, anonymousUid,
                    userFinished = false, anonymousFinished = false;
                function finish () {
                    if (!userFinished || !anonymousFinished) {
                        return; 
                    }
                    //local update
                    flashData.write(KEY, JSON.stringify(store), 24*365*5);
                };
                store = flashData.read(KEY) || {
                    anonymous: null,
                    user: null
                }; 
                if (typeof store === 'string')
                    store = JSON.parse(store);
                //user data
                if( sinaSSOController.getSinaCookie() ) {
                //logined
                    uid = sinaSSOController.getSinaCookie().uid;
                    if (!store.user || store.user.id != uid) {
                    //new user or another user
                        //do transfer pre user data to server !if needed.
                        if (store.user && (today - store.user.data.lastTimeStamp > 0)) {
                            (function(){
                                var cateId;
                                var syncData = {
                                    uid: store.user.id,
                                    time: store.user.data.lastTimeStamp,
                                    data: {}
                                }; 
                                for (cateId in store.user.data.days[0]) {
                                    syncData.data[cateId] = {
                                        day_visit: store.user.data.days[0][cateId],
                                        total: store.user.data.recentTotal[cateId],
                                        sum: store.user.data.total[cateId]
                                    };
                                }
                                syncData = JSON.stringify(syncData);
                                cdPost(SYNC_API, {
                                    data: syncData 
                                });
                            })();
                        }

                        //create new user storage
                        //try get user data from server
                        getJsonp(DOWNLOAD_API + uid, function(rt){
                            if (!rt || !rt.result || !rt.result.data || rt.result.data.length === 0) {
                            //user never sync before
                                store.user = new UserData(uid);
                                store.user.data.days.push({});
                            } else {
                            //old user
                                (function(){
                                    var data, cateId, i, t, t2,
                                        time = {}, rTime = [], latestTime;
                                    store.user = new UserData(uid); 
                                    data = rt.result.data;
                                    for (cateId in data) {
                                        data[cateId]['day_visit'] = JSON.parse(data[cateId]['day_visit']);     
                                        for (t in data[cateId]['day_visit']) {
                                            time[t] = true;
                                        }
                                        store.user.data.recentTotal[cateId] = data[cateId]['total'];
                                        store.user.data.total[cateId] = data[cateId]['sum'];
                                    }
                                    for (t in time) {
                                        rTime.push(parseInt(t)); 
                                    }
                                    rTime.sort(function(a, b){ return b - a; });
                                    if (rTime.length > DAY_LENGTH) {
                                        rTime = rTime.slice(0, DAY_LENGTH); 
                                    }
                                    store.user.data.lastTimeStamp = rTime[0];
                                    for (i = 0; i < rTime.length; i++) {
                                        (function(){
                                            var tData = {}; 
                                            for (cateId in data) {
                                                if (data[cateId]['day_visit'][rTime[i]] != undefined) {
                                                    tData[cateId] = data[cateId]['day_visit'][rTime[i]];
                                                }
                                            }
                                            store.user.data.days.push(tData);
                                        })();
                                    }
                                })();
                            }
                            afterSetData(uid, store.user.data);
                            userFinished = true;
                            finish();
                        });
                    } else {
                    //still me
                        afterSetData(uid, store.user.data);
                        userFinished = true;
                        finish();
                    }
                } else {
                    userFinished = true;
                    finish();
                }

                //anonymous 
                if (!store.anonymous) {
                //new machine
                    anonymousUid = today + '_' + md5(Math.random().toString()).substring(0,8);
                    store.anonymous = new UserData(anonymousUid);
                    store.anonymous.data.days.push({});
                }
                else {
                    anonymousUid = store.anonymous.id; 
                }
                afterSetData(anonymousUid, store.anonymous.data);
                anonymousFinished = true;
                finish();

                function afterSetData (uid, dataRef) {
                    var needSync = false, name, i;
                    if (today - dataRef.lastTimeStamp > 86400000) {
                        if (dataRef.days.length == DAY_LENGTH) 
                            dataRef.days = dataRef.days.splice(0, dataRef.days.length);
                        dataRef.days = [{}].concat(dataRef.days);
                        needSync = true;
                    }
                    //store obj udpate
                    dataRef.days[0][pageId] = ++dataRef.days[0][pageId] || 1;
                    dataRef.total[pageId] = ++dataRef.total[pageId] || 1;
                    dataRef.recentTotal = {};
                    //this need to be counted dynamically
                    for (i = 0; i < dataRef.days.length; i++) {
                        for (name in dataRef.days[i]) {
                            dataRef.recentTotal[name] = dataRef.recentTotal[name] || 0; 
                            dataRef.recentTotal[name] += dataRef.days[i][name];
                        }
                    }
                    //sync
                    if (needSync) {
                    //if (true) {  //temp
                        (function(){
                            var cateId;
                            var syncData = {
                                uid: uid,
                                time: dataRef.lastTimeStamp,
                                data: {}
                            }; 
                            for (cateId in dataRef.days[1]) {
                            //for (cateId in dataRef.days[0]) { //temp
                                syncData.data[cateId] = {
                                    day_visit: dataRef.days[1][cateId],
                                    //day_visit: dataRef.days[0][cateId],  //temp
                                    total: dataRef.recentTotal[cateId],
                                    sum: dataRef.total[cateId]
                                };
                            }
                            syncData = JSON.stringify(syncData);
                            cdPost(SYNC_API, {
                                data: syncData 
                            });
                        })();
                        dataRef.lastTimeStamp = today;
                    }
                };
            };

            flashData.myLoad(onflastDataLoad);
        };
    })();

    //have read
    //只cache最近阅读的100篇
    (function(){
        if (window.top !== window.self)
            return;

        flashData.myLoad(function(){
            var store = flashData.read(READ_KEY);
            var i, url = location.href, haveMem = false;
            if (!store) {
                store = []; 
            } else {
                try {
                    store = JSON.parse(store); 
                } catch (_err) {
                    store = []; 
                }
            }
            for (i = 0; i < store.length; i++) {
                if (store[i] === url) {
                    haveMem = true;
                    break;
                }
            }
            if (!haveMem) {
                if (store.length >= MEM_MAX_LENGTH) {
                    store.splice(0, 1);
                }
                store.push(url);
            }
            flashData.write(READ_KEY, JSON.stringify(store));
        });
    })();

    //猜[Class]
    (function(){
        function Guess (opt) {
            this._init.call(this, opt); 
        };
        Guess.prototype = {
            type: 'all',
            uid: null,  //if there is a uid, a remote interest will be called
            showCount: 10,
            typeWeight: null,  // { 'news': 1.0, 'video': 1.0, 'slide': 1.0, 'blog': 1.0 } by default
            onload: null,
            debug: false,

            result: null,  //this is final data, an array with at least one child, every child count must equal this.showCount
            interest: null,
            interestWeight: null,
            interestWeight2: null,  //for test
            info: null,  //info from server based on interest

            _init: function (opt) {
                var that = this;
                this.typeWeight = {'news': 1.0, 'slide': 1.0, 'video': 1.0, 'blog': 1.0};
                this.result = [];
                this._config.call(this, opt);
                if (!this.uid) {
                //feed from local
                    if (!window.tmpUidev._flashData) {
                        this._fail.call(this, 'failed, no flashData Object');
                        return;
                    }

                    if (window.sinaSSOController) {
                        pathReady.add(function(){
                            flashData.myLoad(function(){
                                that._interest.call(that);
                            }); 
                        });
                    } else {
                        getJsResource('http://i.sso.sina.com.cn/js/ssologin.js', function(){
                            pathReady.add(function(){
                                flashData.myLoad(function(){
                                    that._interest.call(that);
                                }); 
                            });
                        });
                    }
                } else {
                //feed from server 
                    this._interestRemote.call(this); 
                }
            }, 
            _config: function (opt) {
                var name, weight;
                if (!opt) {
                    return;
                }
                if (opt.type != undefined && (opt.type in {'all':null, 'news':null, 'video':null, 'slide':null, 'blog':null})) {
                    this.type = opt.type;
                }
                if (opt.uid != 'undefined') {
                    this.uid = opt.uid; 
                }
                if (typeof opt.showCount === 'number') {
                    this.showCount = opt.showCount; 
                }
                if (typeof opt.onload === 'function') {
                    this.onload = opt.onload; 
                }
                if (typeof opt.typeWeight) {
                    for (name in opt.typeWeight) {
                        weight = parseFloat(opt.typeWeight[name]);
                        if (!window.isNaN(weight) && (name in {'news':null, 'video':null, 'slide':null, 'blog':null})) {
                            this.typeWeight[name] = weight;
                        }
                    }
                }
                if (typeof opt.cateWeight) {
                }
            },
            _interest: function(){
                var that = this;
                var store = JSON.parse(flashData.read(KEY));
                var data, interest;
                if (store.anonymous) {
                    data = store.anonymous.data;
                } else {
                    //abort
                    if (this.debug) {
                        this._fail.call(this, 'new machine');
                    } else {
                        this._renderDefault.call(this);
                    }
                    return;
                }
                this._parseInterset.call(this, data);
                //this._getInfo.call(this, interest);
            },
            _interestRemote: function () {
                var that = this,
                    output,
                    uid = this.uid,
                    store, interest;
                getJsonp(DOWNLOAD_API + uid, function(rt){
                    if (!rt || !rt.result || !rt.result.data || rt.result.data.length === 0) {
                        if (that.debug) {
                            that._fail.call(that, 'no user data on server');
                        } else {
                            that._renderDefault.call(that);
                        }
                    } else {
                    //old user
                        store = {};
                        (function(){
                            var data, cateId, i, t, t2,
                                time = {}, rTime = [], latestTime;
                            store.user = {
                                id: uid,
                                data: {
                                    days: [],
                                    lastTimeStamp: null,
                                    recentTotal: {},
                                    total: {}
                                }
                            }; 
                            data = rt.result.data;
                            for (cateId in data) {
                                data[cateId]['day_visit'] = JSON.parse(data[cateId]['day_visit']);     
                                for (t in data[cateId]['day_visit']) {
                                    time[t] = true;
                                }
                                store.user.data.recentTotal[cateId] = data[cateId]['total'];
                                store.user.data.total[cateId] = data[cateId]['sum'];
                            }
                            for (t in time) {
                                rTime.push(parseInt(t)); 
                            }
                            rTime.sort(function(a, b){ return b - a; });
                            if (rTime.length > DAY_LENGTH) {
                                rTime = rTime.slice(0, DAY_LENGTH); 
                            }
                            store.user.data.lastTimeStamp = rTime[0];
                            for (i = 0; i < rTime.length; i++) {
                                (function(){
                                    var tData = {}; 
                                    for (cateId in data) {
                                        if (data[cateId]['day_visit'][rTime[i]] != undefined) {
                                            tData[cateId] = data[cateId]['day_visit'][rTime[i]];
                                        }
                                    }
                                    store.user.data.days.push(tData);
                                })();
                            }
                        })();
                        that._parseInterset.call(that, store.user.data);
                        //that._getInfo.call(that, interest);
                    }
                });
            },
            _parseInterset: function (data) {
                var sum = 0, 
                    interest, 
                    cate, channelData, channel, type, typeWeight;
                if (!data) {
                    return;
                }
                data = data.total;
                //parse int
                (function(){
                    var cate;
                    for (cate in data) {
                        data[cate] = parseInt(data[cate]); 
                    }
                })();
                for (cate in data) {
                    if (this.type != 'all' && ENUM_TYPE[CATE_TYPE[cate]] != this.type) {
                        continue; 
                    }
                    sum += data[cate];
                }
                for (cate in data) {
                    type = ENUM_TYPE[CATE_TYPE[cate]];
                    if (this.type != 'all' && type != this.type) {
                        continue; 
                    }
                    typeWeight = this.typeWeight[type] || 1.0;
                    // intersts(* typeWeight) that are >= 10%, are counted as real interest
                    if (data[cate] * typeWeight / sum >= 0.1) {
                        interest = interest || {type:'cate_id', data: []};
                        interest.data.push(parseInt(cate));

                        this.interestWeight = this.interestWeight || {};
                        this.interestWeight[cate] = data[cate] * typeWeight / sum;

                        //for test
                        this.interestWeight2 = this.interestWeight2 || {};
                        this.interestWeight2[cate] = data[cate];
                    }
                }
                //i suggest to remove this
                if (!interest) {
                    channelData = {};
                    for (cate in data) {
                        if (this.type != 'all' && ENUM_TYPE[CATE_TYPE[cate]] != this.type) {
                            continue; 
                        }
                        channelData[CATE_CHANNEL[cate]] = channelData[CATE_CHANNEL[cate]] || 0;
                        channelData[CATE_CHANNEL[cate]] += data[cate];
                    }
                    for (channel in channelData) {
                        if (channelData[channel] / sum >= 0.1) {
                            interest = interest || {type:'channel_id', data:[]}; 
                            interest.data.push(parseInt(channel));

                            this.interestWeight = this.interestWeight || {};
                            this.interestWeight[channel] = channelData[channel] / sum;

                            //for test
                            this.interestWeight2 = this.interestWeight2 || {};
                            this.interestWeight2[channel] = channelData[channel];
                        } 
                    }
                }
                interest && interest.data.sort();
                this.interest = interest; //just cache
                this._getInfo.call(this, interest);
            },
            _getInfo: function (interest) {
                var that = this;
                if (!interest) {
                    //abort
                    if (this.debug) {
                        this._fail.call(this, 'no interest');
                    } else {
                        this._renderDefault.call(this);
                    }
                    return; 
                }
                switch (interest.type) {
                    case 'cate_id':
                        getJsonp(INTEREST_API + '&cate_ids=' + interest.data.join(','), function(rt){
                            that._renderOutput.call(that, rt);
                        }, null, 'dpc=1');
                        break;
                    case 'channel_id': 
                        getJsonp(INTEREST_API + '&channel_ids=' + interest.data.join(','), function(rt){
                            that._renderOutput.call(that, rt);
                        }, null, 'dpc=1');
                        break;
                }
            }, 
            _renderOutput: function (rt) {
                var item,
                    readCache = JSON.parse(flashData.read(READ_KEY)),
                    sum = 0, getLength, curGetLength, i, i2,
                    output = [], singleOutput, randomIndex, type, seq = [], tmpData;
                var haveHandled = {}; //去重 + 多页output
                var data, toHandleCount = 0, handledCount = 0, isFirstPage = true, stillThisPage = false;
                if (!rt || !rt.result || !rt.result.data) {
                    if (this.debug) {
                        this._fail.call(this, 'get result failed');
                    } else {
                        this._renderDefault.call(this);
                    }
                    return; 
                }
                data = rt.result.data;
                //去重, calculate sum, sort, calculate toHandleCount
                for (item in data) {
                    if (item == 'default') {
                        continue; 
                    }
                    tmpData = [];
                    for (i = 0; i < data[item].length; i++) {
                        if (haveHandled[data[item][i].url]) {
                            //data[item].splice(i, 1); 
                            continue;
                        }
                        haveHandled[data[item][i].url] = true;
                        tmpData.push(data[item][i]);
                    }
                    data[item] = tmpData;
                    sum += this.interestWeight[item]; //may be some cate doesnt have data come back, so sum need to be caled again.
                    toHandleCount += data[item].length; 
                    seq.push({id:item, weight:this.interestWeight[item]});
                }
                seq.sort(function(a,b){ return b.weight - a.weight; });
                //single page
                if (toHandleCount <= this.showCount) {
                    singleOutput = [];
                    for (i = 0; i < seq.length; i++) {
                        item = seq[i].id;
                        for (i2 = 0; i2 < data[item].length; i2++) {
                            type = data[item][i2].interest_type;
                            this._renderSingleData(type, item, data[item][i2], singleOutput);
                        }
                    } 
                    if (singleOutput.length < this.showCount 
                    && data['default'] && data['default'].length > 0) {
                        for (i = 0; i < data['default'].length; i++) {
                            if (haveHandled[data['default'].url]) {
                                data['default'][i].__haveHandled = true;
                                continue; 
                            }
                            if (readCache && inArray(readCache, data['default'][i].url)) {
                                continue;    
                            }
                            type = data['default'][i].interest_type;
                            this._renderSingleData(type, 'default', data['default'][i], singleOutput);
                            data['default'][i].__haveHandled = true;
                            if (singleOutput.length === this.showCount) {
                                break; 
                            }
                        }
                    }
                    //again!
                    if (singleOutput.length < this.showCount 
                    && data['default'] && data['default'].length > 0) {
                        for (i = 0; i < data['default'].length; i++) {
                            if (data['default'][i].__haveHandled) {
                                continue; 
                            } 
                            type = data['default'][i].interest_type;
                            this._renderSingleData(type, 'default', data['default'][i], singleOutput);
                            data['default'][i].__haveHandled = true;
                            if (singleOutput.length === this.showCount) {
                                break; 
                            }
                        }
                    }
                    if (singleOutput.length === this.showCount) {
                        output.push(singleOutput); 
                    } else if (data['default'] && data['default'].length > 0) {
                        singleOutput = [];
                        for (i = 0; i < data['default'].length; i++) {
                            type = data['default'][i].interest_type;
                            this._renderSingleData(type, 'default', data['default'][i], singleOutput);
                            if (singleOutput.length === this.showCount) {
                                break;
                            }
                        }
                    } else {
                        //to log
                        this._fail.call(this, 'empty data & not enougth default data!');
                    }
                } else {
                    var _whilecount = 0;
                    while (handledCount < toHandleCount && _whilecount < 1000) {
                        _whilecount++;
                        singleOutput = stillThisPage ? singleOutput : [];
                        for (i = 0; i < seq.length; i++) {
                            item = seq[i].id;
                            //getLength = Math.ceil(this.showCount * (this.interestWeight[item] / sum));
                            getLength = Math.round(this.showCount * (this.interestWeight[item] / sum));
                            curGetLength = 0;
                            for (i2 = 0; i2 < data[item].length; i2++) {
                                if (singleOutput.length >= this.showCount) {
                                    break; 
                                }
                                if (data[item][i2].__hasHandled) {
                                    continue; 
                                }
                                data[item][i2].__hasHandled = true; 
                                handledCount++;
                                //if (isFirstPage && readCache && inArray(readCache, data[item][i2].url)) {
                                if ((readCache && inArray(readCache, data[item][i2].url)) || location.href.indexOf(data[item][i2].url) >= 0) {
                                    continue;    
                                }
                                type = data[item][i2].interest_type;
                                this._renderSingleData(type, item, data[item][i2], singleOutput);
                                curGetLength++;
                                if (curGetLength >= getLength) {
                                    break;
                                }
                            }
                        }
                        if (singleOutput.length < this.showCount) {
                            stillThisPage = true; 
                        } else {
                            stillThisPage = false; 
                            output.push(singleOutput);
                        }
                        //isFirstPage = false;
                    }
                    if (output.length === 0 && singleOutput.length < this.showCount) {
                        var alreadyIn = false;
                        for (i = 0; i < data['default'].length; i++) {
                            if (singleOutput.length === this.showCount) {
                                break; 
                            }
                            if (location.href.indexOf(data['default'][i].url) >= 0) {
                                continue; 
                            }
                            alreadyIn = false;
                            for (i2 = 0; i2 < singleOutput.length; i2++) {
                                if (data['default'][i].url === singleOutput[i2].url) {
                                    alreadyIn = true; 
                                } 
                            }
                            if (alreadyIn) {
                                continue; 
                            }
                            this._renderSingleData(data['default'][i].interest_type, 'default', data['default'][i], singleOutput);
                        }
                        if (singleOutput.length === this.showCount) {
                            output.push(singleOutput); 
                        }
                    }
                }
                this.result = output;  //just cache
                this.onload && this.onload({status:'success', data:output});
            },
            _renderSingleData: function (type, item, data, toPush) {
                switch (type) {
                    case 'news': 
                        toPush.push({
                            title: data.title,
                            url: data.url,
                            type: type,
                            cateOrChannelId : item
                        });
                        break;
                    case 'slide': 
                        toPush.push({
                            title: data.name,
                            shortTitle: data.short_name,
                            url: data.url,
                            imgUrl: data.img_url,
                            type: type,
                            cateOrChannelId : item
                        });
                        break;
                    case 'video':
                        toPush.push({
                            title: data.title,
                            url: data.url,
                            type: type,
                            cateOrChannelId : item
                        });
                        break;
                    case 'blog':
                        toPush.push({
                            title: data.title,
                            url: data.url,
                            type: type,
                            cateOrChannelId : item
                        });
                        break;
                }
            },
            _fail: function (msg) {
                this.onload && this.onload({status:msg, data:null}); 
            },
            _renderDefault: function(){
                var that = this;
                this.interest = {type:'default', data:null};
                getJsonp(INTEREST_API, function(rt){
                    that._renderOutput.call(that, rt);
                });
            }
        };
        window.GuessInfo = Guess;
        //console.log(new GuessInfo({ type:'all', onload: function(rt){ console.log(rt) } }));
    })();

    //GuessList [UI] [Class]
    //opt
    //  contId [MUST]
    //
    //tips
    //  cont element must have width and height.
    (function(){
        function addEventListener (elem, evt, fn) {
            if (!elem) {
                return; 
            }
            if (elem.addEventListener) {
                elem.addEventListener(evt, fn, false);
            } else if (elem.attachEvent) {
                elem.attachEvent('on' + evt, fn); 
            }
        };

        var tplLoading = '<div style="width:100%; height:100%; font-size:0px; overflow:hidden; background:url(http://i3.sinaimg.cn/dy/stencil/sysimages/sysimg/loading_01.gif) 50% 50% no-repeat;"></div>',
            tplItem = '<li class="@@liCssClass@@"><a data-type="@@dataType@@" class="@@aCssClass@@" href="@@url@@" target="_blank">@@title@@</a></li>',
            tplLine = '<li class="@@liCssClass@@"></li>';

        function GuessList(){
            this._init.apply(this, arguments); 
        };
        GuessList.prototype = {
            valid: false,
            guessInfo: null,
            guessResult: null,
            curGuessResultIndex: 0,
            isChanging: false,

            id: null,
            showCount: 10,
            domChange: null,
            domCont: null,
            changeId: null,
            contId: null,
            type: 'all',
            typeWeight: null, 
            onload: null,
            onchange: null,
            onfail: null,
            liCssClass: '',  //css class for li
            newsCssClass: '',
            slideCssClass: '',
            videoCssClass: '',
            blogCssClass: '',
            lineCssClass: '',
            ulCssClass: '',
            needLine: false,

            _config: function (opt) {
                if (!opt) {
                    return; 
                }
                if (typeof opt.contId != 'undefined') {
                    this.contId = opt.contId;
                    this.domCont = document.getElementById(opt.contId); 
                    if (!this.domCont) {
                        return;
                    }
                } else {
                    return;
                }
                if (typeof opt.showCount === 'number') {
                    this.showCount = opt.showCount; 
                }
                if (typeof opt.changeId != 'undefined') {
                    this.changeId = opt.changeId;
                    this.domChange = document.getElementById(opt.changeId);
                }
                if (typeof opt.type != 'undefined' && (opt.type in {'all':null, 'news':null, 'slide':null, 'video': null, 'blog':null})) {
                    this.type = opt.type; 
                }
                if (typeof opt.typeWeight != 'undefined') {
                    this.typeWeight = opt.typeWeight; 
                }
                if (typeof opt.onload === 'function') {
                    this.onload = opt.onload; 
                }
                if (typeof opt.onchange === 'function') {
                    this.onchange = opt.onchange; 
                }
                if (typeof opt.onfail === 'function') {
                    this.onfail = opt.onfail; 
                }
                if (typeof opt.newsCssClass != 'undefined') {
                    this.newsCssClass = opt.newsCssClass; 
                }
                if (typeof opt.slideCssClass != 'undefined') {
                    this.slideCssClass = opt.slideCssClass; 
                }
                if (typeof opt.videoCssClass != 'undefined') {
                    this.videoCssClass = opt.videoCssClass; 
                }
                if (typeof opt.blogCssClass != 'undefined') {
                    this.blogCssClass = opt.blogCssClass; 
                }
                if (typeof opt.lineCssClass != 'undefined') {
                    this.lineCssClass = opt.lineCssClass; 
                }
                if (typeof opt.liCssClass != 'undefined') {
                    this.liCssClass = opt.liCssClass; 
                }
                if (typeof opt.ulCssClass != 'undefined') {
                    this.ulCssClass = opt.ulCssClass; 
                }
                if (opt.needLine === true) {
                    this.needLine = true; 
                }
                this.valid = true;
            },

            _bindChangeEvent: function(){
                var that = this;
                if (this.domChange) {
                    if (this.domChange.nodeName.toLowerCase() === 'a') {
                        this.domChange.setAttribute('href', '#action');
                    }
                    addEventListener(this.domChange, 'click', function(e){
                        e = e || event;
                        if (e.preventDefault) {
                            e.preventDefault(); 
                        } else {
                            e.retureValue = false; 
                        }
                        if (!that.isChanging) {
                            that._render.call(that, that.onchange);
                        }
                        return false;
                    });
                }
            },

            _render: function (onend) {
                var data = this.guessResult[this.curGuessResultIndex], 
                    i, html = '', 
                    that = this;
                this.domCont.innerHTML = tplLoading;
                this.isChanging = true;
                setTimeout(function(){
                    html += '<ul class="' + that.ulCssClass + '">';
                    for (i = 0; i < data.length; i++) {
                        switch (data[i].type) {
                            case 'news':
                                html += tplItem.replace('@@aCssClass@@', that.newsCssClass)
                                        .replace('@@liCssClass@@', that.liCssClass)
                                        .replace('@@dataType@@', data[i].type)
                                        .replace('@@url@@', data[i].url)
                                        .replace('@@title@@', data[i].title);
                                break;
                            case 'slide':
                                html += tplItem.replace('@@aCssClass@@', that.slideCssClass)
                                        .replace('@@liCssClass@@', that.liCssClass)
                                        .replace('@@dataType@@', data[i].type)
                                        .replace('@@url@@', data[i].url)
                                        .replace('@@title@@', data[i].title);
                                break;
                            case 'video':
                                html += tplItem.replace('@@aCssClass@@', that.videoCssClass)
                                        .replace('@@liCssClass@@', that.liCssClass)
                                        .replace('@@dataType@@', data[i].type)
                                        .replace('@@url@@', data[i].url)
                                        .replace('@@title@@', data[i].title);
                                break;
                            case 'blog':
                                html += tplItem.replace('@@aCssClass@@', that.blogCssClass)
                                        .replace('@@liCssClass@@', that.liCssClass)
                                        .replace('@@dataType@@', data[i].type)
                                        .replace('@@url@@', data[i].url)
                                        .replace('@@title@@', data[i].title);
                                break;
                        }
                        if (that.needLine && i < data.length - 1) {
                            html += tplLine.replace('@@liCssClass@@', that.lineCssClass); 
                        }
                    }
                    html += '</ul>';
                    that.curGuessResultIndex++;
                    if (that.curGuessResultIndex >= that.guessResult.length) {
                        that.curGuessResultIndex = 0;
                    }
                    that.domCont.innerHTML = html;
                    that.isChanging = false;
                    if (typeof onend === 'function') {
                        onend(); 
                    }
                }, Math.round(500 * Math.random()));
            },

            _onGuessInfoLoad: function(result){
                if (!result.data || result.data.length == 0) {
                    if (typeof this.onfail === 'function') {
                        this.onfail.call(this); 
                    } 
                }
                this.guessResult = result.data;
                this._render.call(this, this.onload);
                this._bindChangeEvent.call(this);
            },

            _init: function (opt) {
                var that = this, 
                    infoOpt;

                this._config.call(this, opt);
                if (!this.valid) {
                    return; 
                }

                this.domCont.innerHTML = tplLoading;
                infoOpt = {
                    showCount: this.showCount,
                    onload: function(data){
                        that._onGuessInfoLoad.call(that, data); 
                    },
                    type: this.type,
                    typeWeight: this.typeWeight
                };
                this.guessInfo = new window.GuessInfo(infoOpt);
            }
        };

        window.GuessList = GuessList;
    })();
})();
