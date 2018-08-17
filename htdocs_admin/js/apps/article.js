/**
 * Created by joker on 16/9/22.
 */
$(function () {
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
        maximumWords:100000,
        /*      retainOnlyLabelPasted:true,
              pasteplain:true,
              filterTxtRules:true,*/
    });

    $('#btn_save_article').on('click',function () {
        var cityIdNum = $("input[name='city_ids']").length;
        var checkedCityIdNum = $("input[name='city_ids']:checked").length;
        var city_ids;

        if (cityIdNum == checkedCityIdNum){
            city_ids = '1';
        }else{
            var city_ids = $("input:checkbox[name='city_ids']:checked").map(function(index,elem) {
                return $(elem).val();
            }).get().join(',');
        }
        var para = {
            city_ids: city_ids,
            article_type:$('#article_type').val(),
            aid: $('#aid').val(),
            title: $('#title').val(),
            content: UE.getEditor('editor').getContent(),
            pic_url: $('#_j_upload_view_img').attr('src'),
        }
        if(K.isEmpty(para.city_ids)){
            alert('请选择城市');
            return false;
        }
        if(para.article_type == 0){
            alert('请选择文章类型');
            return false;
        }
        if (K.isEmpty(para.title)) {
            alert('请输入文章标题');
            return false;
        }
        if (K.isEmpty(para.content)) {
            alert('请输入文章正文');
            return false;
        }
        if (para.pic_url == '/i/nopic100.jpg') {
            para.pic_url = '';
        }
        $.post('/activity/ajax/save_article.php', para, function (ret) {
            alert('保存成功');
            window.location.href = '/activity/article_list.php';
        });
    })

})
function checkAll(obj) {
    $("input[type='checkbox']").prop('checked', $(obj).prop('checked'));
}

function checkAllCity(obj) {
    var cityIdNum = $("input[name='city_ids']").length;
    var checkedCityIdNum = $("input[name='city_ids']:checked").length;

    if (cityIdNum == checkedCityIdNum){

         $("input[name='city_all']").prop("checked",true);
     }else{
         $("input[name='city_all']").prop("checked",false);
    }
}
