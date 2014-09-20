(function() {
    var editor_app	= window.iCMS.config.API + '?app=editor';
    window.UEDITOR_CONFIG = {
		UEDITOR_HOME_URL : window.iCMS.config.UI+'/ueditor/'
        //图片上传配置区
        ,imageUrl: editor_app + "&do=imageUp" //图片上传提交地址
        ,imagePath: '' //图片修正地址，引用了fixedImagePath,如有特殊需求，可自行配置
        ,imageFieldName: "upfile" //图片数据的key,若此处修改，需要在后台对应文件修改对应参数
        ,compressSide: 0 //等比压缩的基准，确定maxImageSideLength参数的参照对象。0为按照最长边，1为按照宽度，2为按照高度
        //,maxImageSideLength:900                    //上传图片最大允许的边长，超过会自动等比缩放,不缩放就设置一个比较大的值，更多设置在image.html中

        //涂鸦图片配置区
        ,scrawlUrl: editor_app + "&do=scrawlUp" //涂鸦上传地址
        ,scrawlPath: '' //图片修正地址，同imagePath

        //附件上传配置区
        ,fileUrl: editor_app + "&do=fileUp" //附件上传提交地址
        ,filePath: '' //附件修正地址，同imagePath
        ,fileFieldName: "upfile" //附件提交的表单名，若此处修改，需要在后台对应文件修改对应参数
        ,fileTypes: window.iCMS.config.fileTypes || '*.gif;*.jpg;*.rar;*.zip;*.jpeg;*.png' //允许的扩展名，多个扩展名之间用分号隔开，支持*通配符

        //远程抓取配置区
        //,catchRemoteImageEnable:true               //是否开启远程图片抓取,默认开启
        ,catcherUrl: editor_app + "&do=getremote" //处理远程图片抓取的地址
        ,catcherPath: '' //图片修正地址，同imagePath
        ,catchFieldName: "upfile" //提交到后台远程图片uri合集，若此处修改，需要在后台对应文件修改对应参数
        //,separater:'ue_separate_ue'               //提交至后台的远程图片地址字符串分隔符
        //,localDomain:[]                            //本地顶级域名，当开启远程图片抓取时，除此之外的所有其它域名下的图片都将被抓取到本地,默认不抓取127.0.0.1和localhost

        //图片在线管理配置区
        ,imageManagerUrl: editor_app + "&do=imageManager" //图片在线管理的处理地址
        ,imageManagerPath: '' //图片修正地址，同imagePath

        //屏幕截图配置区
        ,snapscreenHost: location.hostname //屏幕截图的server端文件所在的网站地址或者ip，请不要加http://
        ,snapscreenServerUrl: editor_app + "&do=imageUp" //屏幕截图的server端保存程序，UEditor的范例代码为“URL +"server/upload/php/snapImgUp.php"”
        ,snapscreenPath: ''
        ,snapscreenServerPort: location.port //屏幕截图的server端端口
        //,snapscreenImgAlign: ''                                //截图的图片默认的排版方式

        //word转存配置区
        ,wordImageUrl: editor_app + "&do=imageUp" //word转存提交地址
        ,wordImagePath: '' //
        ,wordImageFieldName: "upfile" //word转存表单名若此处修改，需要在后台对应文件修改对应参数

        //获取视频数据的地址
        ,getMovieUrl: editor_app + "&do=getMovie" //视频数据获取地址
        ,videoUrl:editor_app + "&do=fileUp"               //附件上传提交地址
        ,videoPath:''                   //附件修正地址，同imagePath
        ,videoFieldName:"upfile"                    //附件提交的表单名，若此处修改，需要在后台对应文件修改对应参数

        ,toolbars: [["fullscreen", "preview", "cleardoc","|",
        "pasteplain", "selectall", "undo", "redo", "searchreplace", "|",
        "insertorderedlist", "insertunorderedlist", "|",
        "unlink", "link", "|",
        "insertimage", "music", "insertvideo", "|",
        "date", "time", "|",
        "horizontal", "spechars", "blockquote", "highlightcode", "|",
        "formatmatch", "removeformat", "autotypeset", "|","help"
        ],
        ["fontfamily", "fontsize", "|",
        "bold", "italic", "underline", "strikethrough","|",
        "superscript", "subscript", "touppercase", "tolowercase", "|",
        "forecolor", "backcolor", "|",
        "justifyleft", "justifycenter", "justifyright", "justifyjustify", "|",
        "indent", "lineheight"]]
        //,theme:'default'
        //,themePath:URL +"themes/"
        ,initialContent: '' //初始化编辑器的内容,也可以通过textarea/script给值，看官网例子
        ,initialFrameWidth: "100%" //初始化编辑器宽度,默认1000
        ,initialFrameHeight: 520 //初始化编辑器高度,默认320
        ,textarea: 'body'
        ,focus: false //初始化时，是否让编辑器获得焦点true或false
        ,wordCount:true          //是否开启字数统计
        ,maximumWords: 500000
        ,pageBreakTag: '#--iCMS.PageBreak--#'
        ,autotypeset: {
            mergeEmptyline: true, //合并空行
            removeClass: true, //去掉冗余的class
            removeEmptyline: true, //去掉空行
            //textAlign : "left" ,           //段落的排版方式，可以是 left,right,center,justify 去掉这个属性表示不执行排版
            //imageBlockLine : 'none',      //图片的浮动方式，独占一行剧中,左右浮动，默认: center,left,right,none 去掉这个属性表示不执行排版
            pasteFilter: true, //根据规则过滤没事粘贴进来的内容
            clearFontSize: true, //去掉所有的内嵌字号，使用编辑器默认的字号
            clearFontFamily: true, //去掉所有的内嵌字体，使用编辑器默认的字体
            removeEmptyNode: true, // 去掉空节点
            //可以去掉的标签
            removeTagNames: 'div',
            indent: false, // 行首缩进
            indentValue: '2em' //行首缩进的大小
        }
        //启用自动保存
        //,enableAutoSave: true
        //自动保存间隔时间， 单位ms
        //,saveInterval: 500
        //highlightcode
        // 代码高亮时需要加载的第三方插件的路径
        //,highlightJsUrl:window.iCMS.config.UI+"/ueditor/third-party/SyntaxHighlighter/shCore.js"
        //,highlightCssUrl:window.iCMS.config.UI+"/ueditor/third-party/SyntaxHighlighter/shCoreDefault.css"
    };
})();
