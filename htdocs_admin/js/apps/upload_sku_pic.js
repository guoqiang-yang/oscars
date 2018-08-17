(function() {
	var replaceId = "";

	function main() {
		_initUploader('#_j_btn_select_pic','pic','#_j_upload_view_img');
	};

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
			action: '/common/ajax/upload_sku_pic.php',
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
				K.log('selected ' + num + ' file');
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
					var picids = $('input[name=pic_ids]').val();
					if (!K.isEmpty(picids)) {
						picids += ',' + data.pictag;
					} else {
						picids = data.pictag;
					}

					$('input[name=pic_ids]').val(picids);
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
		var img = '<div class="img_item brick" data-pic="' + picTag + '" style="float: left; width: 200px; height: 244px;">' +
			'<img src="' + picURL + '" />' +
			'<div style="margin-bottom: 5px; margin-top: 5px;">' +
			'<a style="margin-right:10px;" href="#" class="btn btn-default">删除</a>' +
			'</div>' +
			'</div>';

		$('#img_list').append(img);

		$('.gridly').gridly('layout');
	}

	main();
}());
