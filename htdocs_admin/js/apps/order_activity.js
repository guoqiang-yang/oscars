/**
 * Created by zouliangwei on 2018/1/9.
 */
(function () {
    var get_activity_product_nums = 0;
    function getOrderActivityProducts() {
        var para = {
            oid: $('form').attr('data-oid'),
            gift_products: new Array(),
            discount_products: new Array(),
            check_num: get_activity_product_nums
        };
        $('input[name=gift_pid]').each(function () {
            if ($(this).is(':checked')) {
                var product = [$(this).val(), 0, $(this).attr('data-num')];
                para.gift_products.push(product);
            }
        });
        $('input[name=special_price_pid]').each(function () {
            if ($(this).is(':checked')) {
                var num = $(this).parent().next().next().next().next().children('input[name=special_price_num]').val();
                if (num > 0) {
                    var product = [$(this).val(), $(this).attr('data-price'), num];
                    para.discount_products.push(product);
                } else {
                    $(this).removeAttr('checked');
                    return false;
                }
            }
        });
        para.gift_products = JSON.stringify(para.gift_products);
        para.discount_products = JSON.stringify(para.discount_products);
        K.post('/order/ajax/get_order_activity_products.php', para, _onGetOrderAcitivityProductsSucc);
    }

    function _onGetOrderAcitivityProductsSucc(data) {
        $('#order_activity_products_list').html(data.html);
        $('input[name=gift_pid]').each(function () {
            $(this).on('click', getOrderActivityProducts);
        });
        $('input[name=special_price_pid]').each(function () {
            $(this).on('click', getOrderActivityProducts);
        });
        get_activity_product_nums++;
    }
    getOrderActivityProducts();
})();