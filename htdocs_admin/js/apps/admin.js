(function(){
    function main(){

    }

    //添加登录IP所在城市
    $(document).ready(function () {
        var ips = [];
        $('.login_ip').each(function (i) {
            ips.push($(this).data('ip'));
        });
        var para = {
            ips: ips
        };
        K.post('/admin/ajax/get_city_by_ip.php', para, function (ret) {
            $.each(ret.res,function(_key){
                $('#'+_key).append(ret.res[_key].country + ' ' + ret.res[_key].city + ' ' + ret.res[_key].isp);
            });
        });
    });
    main();
})();