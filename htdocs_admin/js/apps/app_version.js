/**
 * Created by qihua on 16/12/27.
 */
(function() {

	function main() {
		var uploader = new plupload.Uploader({
			runtimes : 'html5,flash,silverlight,html4',
			browse_button : 'select_file',
			multi_selection: false,
			container: document.getElementById('select_file_container'),
			flash_swf_url : 'lib/plupload-2.1.2/js/Moxie.swf',
			silverlight_xap_url : 'lib/plupload-2.1.2/js/Moxie.xap',
			url : host,
			filters : [
				{title : "apk files", extensions : "apk"}
			],

			init: {
				PostInit: function() {
					document.getElementById('file_name').innerHTML = '';
					document.getElementById('upload_file').onclick = function() {
						uploader.start();
						return false;
					};
				},

				FilesAdded: function(up, files) {
					plupload.each(files, function(file) {
						document.getElementById('file_name').innerHTML = '';
						document.getElementById('file_name').innerHTML += '<div id="' + file.id + '">' + file.name + ' (' + plupload.formatSize(file.size) + ')<b></b>'
							+'<div style="width:200px;" class="progress"><div class="progress-bar" style="width: 0%"></div></div>'
							+'</div>';
					});
				},

				BeforeUpload: function(up, file) {
					fileName = 'app/' + calculate_object_name(file.name);
					var new_multipart_params = {
						'key' : fileName,
						'policy': policyBase64,
						'OSSAccessKeyId': accessid,
						'success_action_status' : '200', //让服务端返回200,不然，默认会返回204
						'signature': signature,
					};

					up.setOption({
						'multipart_params': new_multipart_params
					});
				},

				UploadProgress: function(up, file) {
					var d = document.getElementById(file.id);
					d.getElementsByTagName('b')[0].innerHTML = '<span>' + file.percent + "%</span>";
					var prog = d.getElementsByTagName('div')[0];
					var progBar = prog.getElementsByTagName('div')[0]
					progBar.style.width= 2*file.percent+'px';
					progBar.setAttribute('aria-valuenow', file.percent);
				},

				FileUploaded: function(up, file, info) {
					if (info.status == 200)
					{
						$('#apk_file_name').val(fileName);
					}
					else
					{
						alert(info.response);
					}
				},

				Error: function(up, err) {
					alert(err.response);
				}
			}
		});

		uploader.init();
	}

	main();

} )();