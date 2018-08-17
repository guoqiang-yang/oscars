(function(){

	function main() {
		_initSelector();
		_initUploader('#_j_btn_select_pic','pic','#_j_upload_view_img');
	};

	/*  错误信息    */
	var ErrTip = {};
	ErrTip['typeError']     = '图片格式错误';
	ErrTip['sizeError']     = '图片大小不能超过5M';
	ErrTip['emptyError']    = '图片文件为空文件';
	ErrTip['uploadFail']    = '上传失败';
	ErrTip['networkErr']    = '网络传输错误，请检查网络连接';
	ErrTip['cameraAbsent']  = '未检测到摄像头，请确认连接后再试';
	ErrTip['errShortWidth']  = '图片宽度不能小于230像素';
	ErrTip['errShortHeight']  = '图片高度不能小于230像素';

	var uploader = null;         //上传类实例.暂时没用到
	var g_imgHeight = 0, g_imgWith = 0;

	function _initUploader(aid,name,imgId) {
	    var path = $('#_j_btn_select_pic').attr('data-path');
		this.uploader = new qq.FileUploaderBasic({
			button: $(aid).get(0),
			action: '/common/ajax/upload_pic.php',
			allowedExtensions: [ 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'img' ],
			sizeLimit: 512000, // 500K
			debug: true,
			message: ErrTip,
			maxConnections: 10,
			multiple: false,
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
					$('input[name=pic_tag]').val(data.pictag);
					//$(imgId).prop("src",data.picurl);
					_loadLogoPic(data.picurl);
					K.log(data.picurl);
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
		//$('._j_panel_submit').show();
		$(id).text('重新上传');
	}

	function _showError(error) {
		var errMsg = ErrTip[error];
		//todo: 错误提示
		alert( errMsg );
	}

	function _initSelector() {
		var wrap = $('._j_logo_wrap'),
			purl = wrap.data('url');

		if (purl && purl.length > 0) { //如果有logo
			var x1 = wrap.data('x') || 0,
				y1 = wrap.data('y') || 0,
				w = wrap.data('w') || 0,
				h = wrap.data('h') || 0;
			_loadLogoPic(purl, x1, y1, w, h);
		}
	}

	function _loadIMG( src, callback ) {
		var image = new Image();

		callback = typeof callback === 'function' ? callback : function() {};
		image.onload = function() {
			image.onload = null;
			callback.call( null, image );
		};
		if ( image.readyState == "complete" ) {
			callback.call(null, image);
		}
		image.src = src;
	}

	function _loadLogoPic(picURL, x1, y1, w, h) {
		var logoWrap = $('._j_logo_wrap');

		_loadIMG(picURL, function(img) {
			img.onload = null;      // 避免ie下得死循环（ie下$(img).appendTo(logoWrap)又会触发img.onload）
			_clearSelectArea();
			logoWrap.empty();
			var headImg = $(img).appendTo(logoWrap);
			headImg.attr('id', '_j_upload_view_img');

			//imageAreaSelect
			//var areaWidth = 120;
			var opt = {
				'aspectRatio' : '1:1',
				'handles' : true,
				'fadeSpeed' : 200,
				'instance' : true,
				'persistent' : true
			};

			if (w>0 && h>0) {
				opt.x1 = x1;
				opt.y1 = y1;
				opt.x2 = x1 + w;
				opt.y2 = y1 + h;
			} else {
				var hh = img.height || img.clientHeight;
				var ww = img.width || img.clientWidth;
				if ( hh / ww <= 1 ) {	//稍扁
					opt.x1 = (ww - hh) / 2;
					opt.y1 = 0;
					opt.x2 = (ww + hh) / 2;
					opt.y2 = hh;
				} else {	//稍高
					opt.x1 = 0;
					opt.y1 = (hh - ww) / 2;
					opt.x2 = ww;
					opt.y2 = (hh + ww) / 2;
				}
			}
			K = K || {};
			K.headAreaSelector = headImg.imgAreaSelect(opt);
		});
	}

	function _clearSelectArea() {
		var headImg = $('#_j_upload_view_img');
		if(headImg.length) {
			headImg.imgAreaSelect({'remove' : true});
		}
	}
	main();
}());
