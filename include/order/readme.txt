本部分各文件功能如下：

    Order_Api：提供接口供htdocs层调用；

    Order_Order：Order的func类，封装了对Dao的访问，以及一些通用的和订单相关的方法；

    Order_Refund：退款单；

    Order_Helper：Order辅助类，提供一些和数据库，业务逻辑关系不大的辅助类方法；

    Order_Privilege：订单优惠相关的func类；