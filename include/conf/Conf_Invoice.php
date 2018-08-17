<?php
/**
 * 发票
 */
class Conf_Invoice
{
    const INVOICE_STEP_NEW = 1;
    const INVOICE_STEP_HANDLING = 2;
    const INVOICE_STEP_FINISHED = 3;


    public static $INVOICE_STEP = array(
		self::INVOICE_STEP_NEW => '待确认',
        self::INVOICE_STEP_HANDLING => '处理中',
        self::INVOICE_STEP_FINISHED => '已完成',
	);

    const INVOICE_TYPE_NORMAL = 1;
    const INVOICE_TYPE_PURCHASE = 2;

    public static $INVOICE_TYPES = array(
        self::INVOICE_TYPE_NORMAL => '普通发票',
        self::INVOICE_TYPE_PURCHASE => '专用发票'
    );

    const INVOICE_OUTPUT_STEP_NEW = 1,
          INVOICE_OUTPUT_STEP_REBUT = 2,
          INVOICE_OUTPUT_STEP_SALES_AUDIT = 3,
          INVOICE_OUTPUT_STEP_FINANCE_CONFIRM = 4,
          INVOICE_OUTPUT_STEP_FINISHED = 5,
          INVOICE_OUTPUT_STEP_DELETE = 99;

    public static $INVOICE_OUTPUT_STEP_CUSTOMER = array(
        self::INVOICE_OUTPUT_STEP_NEW => '待审核',
        self::INVOICE_OUTPUT_STEP_REBUT => '已驳回',
        self::INVOICE_OUTPUT_STEP_SALES_AUDIT => '已审核',
        self::INVOICE_OUTPUT_STEP_FINANCE_CONFIRM => '已审核',
        self::INVOICE_OUTPUT_STEP_FINISHED => '已开票',
        self::INVOICE_OUTPUT_STEP_DELETE => '已删除'
    );

    public static $INVOICE_OUTPUT_STEP_FINANCE = array(
        self::INVOICE_OUTPUT_STEP_SALES_AUDIT => '待确认',
        self::INVOICE_OUTPUT_STEP_FINANCE_CONFIRM => '待开票',
        self::INVOICE_OUTPUT_STEP_FINISHED => '已开票'
    );

    const INVOCIE_OUTPUT_NORMAL_DAY = 365;
    const INVOICE_OUTPUT_PURCHASE_DAY = 365;
}
