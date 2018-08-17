(function () {
    var ue = UE.getEditor('editor', {
        toolbars: [
            ['fullscreen', 'source', '|', 'undo', 'redo', '|',
                'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',
                'rowspacingtop', 'rowspacingbottom', 'lineheight', '|',
                'customstyle', 'paragraph', 'fontfamily', 'fontsize', '|',
                'directionalityltr', 'directionalityrtl', 'indent', '|',
                'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|', 'touppercase', 'tolowercase', '|','link', 'unlink', 'anchor', '|','background', 'inserttable', 'deletetable',, 'emotion','map']
        ],
        autoHeightEnabled: false,
        autoFloatEnabled: true,
        initialFrameHeight: 300,
        elementPathEnabled:false,
        maximumWords:500
    });

    $('#btn_save_wiki').bind('click',_onSaveWiki);
    function _onSaveWiki(ev) {
        var para = {
            id: $('input[name=id]').val(),
            title: $('input[name=title]').val(),
            sub_title: $('input[name=sub_title]').val(),
            cover: $('input[name=cover]').val(),
            //cover: 'test_pic/2/35/23591.jpg@120w_120h_1e_0c',
            fid: $('input[name=fid]').val(),
            design: $('select[name=design]').val(),
            fit_step: $('select[name=fit_step]').val(),
            main_material: $('select[name=main_material]').val(),
            other_material: $('select[name=other_material]').val(),
            status: $('select[name=status]').val(),
            description: UE.getEditor('editor').getContent()
        };
        if(K.isEmpty(para.title)) {
            alert('请填写标题！');
            return false;
        }
        if(K.isEmpty(para.cover)) {
            alert('请选择封面！');
            return false;
        }
        if(parseInt(para.design) == 0) {
            alert('请选择设计！');
            return false;
        }
        if(parseInt(para.fit_step) == 0) {
            alert('请选择装修！');
            return false;
        }
        if(parseInt(para.main_material) == 0) {
            alert('请选择主材！');
            return false;
        }
        if(parseInt(para.other_material) == 0) {
            alert('请选择辅材！');
            return false;
        }
        if (K.isEmpty(para.description)) {
            alert('请填写正文！');
            return false;
        }

        $(this).attr('disabled', true);
        K.post('/activity/ajax/save_wiki.php', para, _onSaveWikiSucc);
    }

    function _onSaveWikiSucc(data) {
        alert('保存成功');
        window.location.href = '/activity/wiki_list.php';
    }


    var replaceId = "";

    function main() {
        _initUploader('#_j_btn_select_pic','pic','#_j_upload_view_img');
    }

    /*  错误信息    */
    var ErrTip = {};
    ErrTip['typeError']     = '图片格式错误';
    ErrTip['sizeError']     = '图片大小不能超过500K';
    ErrTip['emptyError']    = '图片文件为空文件';
    ErrTip['uploadFail']    = '上传失败';
    ErrTip['networkErr']    = '网络传输错误，请检查网络连接';
    ErrTip['cameraAbsent']  = '未检测到摄像头，请确认连接后再试';
    ErrTip['errShortWidth']  = '图片宽度不能小于230像素';
    ErrTip['errShortHeight']  = '图片高度不能小于230像素';

    var uploader = null;         //上传类实例.暂时没用到
    var g_imgHeight = 0, g_imgWith = 0;

    function _initUploader(aid,name,imgId) {
        replaceId = "";
        var path = $('#_j_btn_select_pic').attr('data-path');
        this.uploader = new qq.FileUploaderBasic({
            button: $(aid).get(0),
            action: '/common/ajax/upload_pic.php',
            allowedExtensions: [ 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'img' ],
            sizeLimit: 512000, // 500K
            debug: true,
            message: ErrTip,
            maxConnections: 10,
            multiple: true,
            multipart: true,
            fieldName: name,
            params:{path:path},
            onFileSelected: function(num) {
                _setUploadingMode();
                return true;
            },
            onSubmit: function(id, fileName) {
                K.log('onSubmit ' + id + ' ' + fileName);
            },
            onProgress: function(id, fileName, loaded, total) {
                K.log('onProgress ' + id + ' ' + fileName + ' ' + loaded + ' ' + total);
                if (loaded < total) {
                    var progress = Math.round(loaded / total * 100) + '%';
                    K.log('progress: ' + progress + '%');
                } else {
                    //succ
                }
            },
            onComplete: function(id, fileName, responseJSON) {
                K.log('id ' + id + ' ' + fileName + ' ' + responseJSON);
                K.log(responseJSON);

                if(responseJSON.error) {
                    K.log('server error:' + responseJSON.error.error);
                    _showError(responseJSON.error.error);
                    return;
                }
                if(responseJSON.payload) {
                    var data = responseJSON.payload;
                    $('input[name=cover]').val(data.pictag);
                    _loadLogoPic(data.picurl, data.pictag);

                    _setUploadedOk(aid+' span');
                } else {
                    _showError('uploadFail');
                }
            },
            onCancel: function(id, fileName) {
                K.log('onCancel ' + id + ' ' + fileName);
            },
            showMessage: function(message) {
                K.log('showMessage ' + message);
            }
        });
        this.uploader._error = function(code, fileName) {
            _showError(code);
        };
    }

    function _setUploadingMode() {
        //todo: 显示正在上传中的加载效果
    }

    function _setUploadedOk(id) {
        //todo: 结束正在上传中的加载效果
        $(id).text('重新上传');
    }

    function _showError(error) {
        var errMsg = ErrTip[error];
        //todo: 错误提示
        alert( errMsg );
    }

    function _loadLogoPic(picURL, picTag) {
        var img = '<div class="img_item brick" data-pic="' + picTag + '">' +
            '<img style="width: 100%;" src="' + picURL + '" />' +
            '</div>';

        $('#img_list').html(img);
    }

    main();
})();


function delete_pic(picTag) {
    $(".img_item").each(function(i){
        var pic = $(this).data('pic');
        if (pic == picTag) {
            $(this).remove();
        }
    });

    var pic_ids = $('input[name=pic_ids]').val();
    var picIdsArr = pic_ids.split(',');
    for (var i = 0; i < picIdsArr.length; i++) {
        if (picIdsArr[i] == picTag) {
            picIdsArr.splice(i, 1);
            break;
        }
    }

    var newPicIds = '';
    if (picIdsArr.length > 0) {
        newPicIds = picIdsArr.join(',');
    }

    $('input[name=pic_ids]').val(newPicIds);
}