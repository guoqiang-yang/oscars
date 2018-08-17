<?php
/**
 * Created by PhpStorm.
 * User: libaolong
 * Date: 2018/3/27
 * Time: 下午3:14
 */

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $searchConf;

    protected function getPara()
    {
        $this->searchConf = array(
            'sid' => Tool_Input::clean('r', 'sid', TYPE_UINT),
            'payment_type' => Tool_Input::clean('r', 'payment_type', TYPE_UINT),
            'paid_source' => Tool_Input::clean('r', 'paid_source', TYPE_UINT),
            'from_date' => Tool_Input::clean('r', 'from_date', TYPE_STR),
            'end_date' => Tool_Input::clean('r', 'end_date', TYPE_STR),
        );
    }

    protected function checkPara()
    {
        if (empty($this->searchConf['sid']))
        {
            throw new Exception('请输入供应商ID！');
        }
    }

    protected function main()
    {
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . '供应商sid('. $this->searchConf['sid'] . ')-' . date('Ymd') . '账务清单' . '.csv');
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');

        $head = array(
            'id',
            '仓库ID',
            '单据类型',
            '供应商',
            '应付减少',
            '应付增加',
            '应付余额',
            '收款方式',
            '日期',
            '备注',
            '执行人',
        );
        Data_Csv::send($head);

        $start = 0;
        $step = 1000;

        do
        {
            $list = Finance_Api::getSupplierBillList($this->searchConf, $start, $step);
            $accountList = $list['list'];

            if (count($accountList) <= 0)
            {
                break;
            }

            $stDesc = Conf_Money_Out::$STATUS_DESC;
            $paidType = Conf_Finance::$MONEY_OUT_PAID_TYPES;

            foreach ($accountList as $item)
            {
                $documentType = $stDesc[$item['type']];
                if (!empty($item['objid']) && $item['type'] == 3)
                {
                    $documentType .= '-' . '[采]' . $item['objid'];
                }
                elseif (!empty($item['objid']) && $item['type'] == 1)
                {
                    $documentType .= '-' . '[入]' . $item['objid'];
                }

                if ($item['price'] > 0)
                {
                    $reduce = 0;
                    $increase = $item['price']/100;
                }
                else
                {
                    $increase = 0;
                    $reduce = $item['price']/100;
                }

                $amount = empty($item['amount']) ? 0 : $item['amount']/100;

                $arr = array(
                    $item['id'],
                    $item['wid'],
                    $documentType,
                    $item['_supplier']['name'],
                    $reduce,
                    $increase,
                    $amount,
                    $item['payment_name'] .' '. $paidType[$item['paid_source']],
                    $item['ctime'],
                    $item['note'],
                    $item['_operator']['name'],
                );

                Data_Csv::send($arr);
            }
            $start += $step;
        } while (count($accountList) > 0);
    }

    protected function outputHead()
    {
    }

    protected function outputBody()
    {
    }

    protected function outputTail()
    {
    }
}

$app = new App('pri');
$app->run();

