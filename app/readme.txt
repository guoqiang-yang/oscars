
/*******************************************************
                app目录：说明

    @使用方法：
        app/[directory]/[sub_directory]/fileName
        app/[directory]/fileName
    
    @文件名定义：
        不包含 [sub_directory]  模块名称_文件前缀_功能描述.php
                             eg: crm_st_curtomer_rebuy.php

        包含 [sub_directory] [文件前缀_]功能描述.php
                             eg: rpr_order_deliverydate.php
                                 sales_arrear_order.php

    @文件前缀：
        st/stat:        统计
        rpr/repair:     修复 repair
        im/import:      导入
        ex/export:      导出
        up/update:      更新
        modify:         更正/修改
        chk/check:      检查
        auto:           自动
        init:           初始化
        rback:          回滚 rollback
        analyze:        分析

    @notice
        每个目录下面都有一个readme.txt文件，每个脚本将名称+功能描述写入到该文件
*******************************************************/

appSche.php [f]             定时脚本配置的文件

crontab [d]                 定时程序实现逻辑
    - crm2
    ...

import [d]                  数据导入

export [d]                  数据导出

stat   [d]                  数据统计

spider [d]                  爬虫类脚本

update [d]                  检测&&更新；基础脚本放置的目录
    - crm
    - finance
    - warehouse
    - order
    ...

tool [d]                    工具类