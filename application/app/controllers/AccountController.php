<?php
use Phalcon\Mvc\View;

class AccountController extends ControllerBase
{
    public function onConstruct()
    {
        if ($this->session->has("user")) 
        {
            if($this->session->get("role") == 'administrator'){
                
            }else{
                $this->response->redirect("");
            }
        } 
        else 
        {
            $this->response->redirect("");
        }
    }

    // * OK
    public function indexAction()
    {
        $this->view->setMainView('desktop-layout');
        $this->tag->setTitle("| ประเสริฐรัตน์บริการ");
        $this->tag->prependTitle("บัญชีรายรับ-รายจ่าย ");
        $this->assets->addJs("js/account/index.js");

        if($this->request->getPost('start_date'))
        {
            $start_date = $this->request->getPost('start_date');
            $start_date = str_replace('/', '-', $start_date);
            $start_date = date('Y-m-d',strtotime($start_date));
        }
        else if ($this->request->getPost('start_date') == " ")
        {
            $start_date = date('Y-m-1',strtotime('today'));
            $start_date = date('1-m-Y',strtotime('today'));
        }
        else
        {
            $start_date = date('Y-m-1',strtotime('today'));
        }

        if($this->request->getPost('end_date'))
        {
            $end_date = $this->request->getPost('end_date');
            $end_date = str_replace('/', '-', $end_date);
            $end_date = date('Y-m-d',strtotime($end_date));
        }
        else if ($this->request->getPost('end_date') == " ")
        {
            $end_date = date('Y-m-1',strtotime('today'));
        }
        else
        {
            $end_date = date('Y-m-t',strtotime('today'));
        }

        $dates = $this->modelsManager->executeQuery(
            'SELECT A.*
            FROM account as A
            WHERE A.transaction_date BETWEEN :start_date: AND :end_date:
            GROUP BY A.transaction_date
            ORDER BY A.transaction_date ASC',
            [
                'start_date' => $start_date,
                'end_date'   => $end_date
            ]
        );

        foreach($dates as $date){
            $transactions = $this->modelsManager->executeQuery(
                'SELECT T.* , A.*
                FROM transaction as T
                JOIN account as A
                ON T.uuid = A.uuid
                WHERE A.transaction_date = :transaction_date:
                GROUP BY T.uuid
                ORDER BY T.agreement_number ASC',
                [
                    'transaction_date' => $date->transaction_date,
                ]
            );
            unset($pawn_transaction);
            unset($interest_transaction);
            unset($withdraw_transaction);
            unset($sale_transaction);
            unset($deposit_transaction);
            unset($selling_transaction);
            foreach($transactions as $transaction){ 
                if($transaction->T->status == 'ฝาก' || $transaction->T->status == 'ซื้อเข้า' || $transaction->T->status == 'เพิ่มเงิน' || $transaction->T->status == 'เพิ่มสินค้า'){
                    $pawn_transaction[] = array(
                        'transaction_status' => $transaction->T->status,
                        'agreement_number'  => $transaction->T->agreement->agreement_number,
                        'status'            => $transaction->T->status,
                        'value'             => $transaction->A->value,
                    );
                }else if ($transaction->T->status == 'ต่อดอกชิ้นเดียว' || $transaction->T->status == 'ต่อดอกทั้งหมด' ){
                    $interest_transaction[] = array(
                        'transaction_status' => $transaction->T->status,
                        'agreement_number'  => $transaction->T->agreement->agreement_number,
                        'status'            => $transaction->T->status,
                        'value'             => $transaction->A->value,
                    );
                }else if ($transaction->T->status == 'ไถ่คืนชิ้นเดียว' || $transaction->T->status == 'ไถ่คืนทั้งหมด' || $transaction->T->status == 'ลดต้น'){
                    if($transaction->T->status == 'ไถ่คืนชิ้นเดียว' || $transaction->T->status == 'ไถ่คืนทั้งหมด'){
                        $transaction_status = 'ไถ่คืน';
                    } else {
                        $transaction_status = $transaction->T->status;
                    }
                    $withdraw_transaction[] = array(
                        'transaction_status' => $transaction_status,
                        'agreement_number'  => $transaction->T->agreement->agreement_number,
                        'status'            => $transaction->T->status,
                        'principal'         => $transaction->A->principal,
                        'value'             => $transaction->A->value,
                    );
                }else if ($transaction->T->status == 'ขายแล้ว'){
                    $sale_transaction[] = array(
                        'agreement_number'  => $transaction->T->agreement->agreement_number,
                        'status'            => $transaction->T->status,
                        'product_name'      => $transaction->T->product->name,
                        'product_value'     => $transaction->T->product->value,
                        'value'             => $transaction->A->value,
                    );
                }else if ($transaction->T->status == 'มัดจำ'){
                    $deposit_transaction[] = array(
                        'agreement_number'  => $transaction->T->agreement->agreement_number,
                        'status'            => $transaction->T->status,
                        'product_name'      => $transaction->T->product->name,
                        'value'             => $transaction->A->value,
                    );
                }else if ($transaction->T->status == 'ตั้งขาย' || $transaction->T->status == 'ตั้งขายกรณีพิเศษ'){
                    $transaction_status = 'ตั้งขาย';
                    $selling_transaction[] = array(
                        'agreement_number'  => $transaction->T->agreement->agreement_number,
                        'status'            => $transaction_status,
                        'product_name'      => $transaction->T->product->name,
                        'product_value'     => $transaction->T->product->value,
                        'value'             => $transaction->A->value,
                    );
                }
            }

            $date_array[] = array(
                'date'  => $this->convertToBuddhistEra($date->transaction_date),
                'pawn_transaction'     => $pawn_transaction,
                'interest_transaction' => $interest_transaction,
                'withdraw_transaction' => $withdraw_transaction,
                'sale_transaction'     => $sale_transaction,
                'deposit_transaction'  => $deposit_transaction,
                'selling_transaction'  => $selling_transaction,
            );
        }

        $date_json = json_encode($date_array);

        echo $date_json;

        $this->view->setVars(
            [
                'date_json'     => $date_json,
                "start_date"    => $start_date,
                "end_date"      => $end_date
            ]
            );
    }

    function dailyAction(){
        $this->view->setMainView('desktop-layout');
        $this->tag->setTitle("| ประเสริฐรัตน์บริการ");
        $this->tag->prependTitle("บัญชีรายรับรายจ่ายประจำวัน ");
        $this->assets->addJs("js/account/daily.js");

        $th_month_array = array(
            "1"=>"มกราคม", 
            "2"=>"กุมภาพันธ์", 
            "3"=>"มีนาคม",
            "4"=>"เมษายน",
            "5"=>"พกฤษภาคม",
            "6"=>"มิถุนายน",
            "7"=>"กรกฏาคม",
            "8"=>"สิงหาคม",
            "9"=>"กันยายน",
            "10"=>"ตุลาคม",
            "11"=>"พฤษจิกายน",
            "12"=>"ธันวาคม"
        );

        $value_start_date = date("n");
        $start_month = $th_month_array[date("n")];

        $value_end_date = date("n");
        $end_month = $th_month_array[date("n")];

        $years = $this->modelsManager->executeQuery(
            "SELECT year(A.transaction_date) AS year 
                FROM account AS A
                GROUP BY year(A.transaction_date)"
        );

        foreach($years as $year){
            $data[] = array(
                'year' => $year->year
            );
        }

        $last_year = date("Y");
        $data_json = json_encode($data);

        $this->view->setVars(
            [
                "value_start_month" => $value_start_date,
                "start_month"       => $start_month,
                "value_end_month"   => $value_end_date,
                "end_month"         => $end_month,
                "last_year"         => $last_year,
                "year_json"         => $data_json,
            ]
            );
    }

    function server_processingAction($year,$month){
        $this->view->disable();

        $th_month_array = array(
            "1"=>"มกราคม", 
            "2"=>"กุมภาพันธ์", 
            "3"=>"มีนาคม",
            "4"=>"เมษายน",
            "5"=>"พกฤษภาคม",
            "6"=>"มิถุนายน",
            "7"=>"กรกฏาคม",
            "8"=>"สิงหาคม",
            "9"=>"กันยายน",
            "10"=>"ตุลาคม",
            "11"=>"พฤษจิกายน",
            "12"=>"ธันวาคม"
        );

        $th_day_array = array(
            "1"=>"จันทร์", 
            "2"=>"อังคาร", 
            "3"=>"พุธ",
            "4"=>"พฤหัสบดี",
            "5"=>"ศุกร์",
            "6"=>"เสาร์",
            "7"=>"อาทิตย์",
        );

        //  หาเดือนที่มีข้อมูล
        $months = $this->modelsManager->executeQuery(
            "SELECT year(A.transaction_date) AS year , month(A.transaction_date) AS month
                FROM account AS A
                WHERE year(A.transaction_date) = '$year' AND month(A.transaction_date) = '$month'
                GROUP BY month(A.transaction_date)"
        );

        // reset array ของแต่ละเดือน
        unset($sum_pawn_value_array);
        unset($sum_withdraw_value_array);
        unset($sum_withdraw_interst_value_array);
        unset($sum_interest_value_array);
        unset($sum_sale_value_array);
        unset($sum_profit_value_array);
        unset($sum_deposit_value_array);

        unset($date_pawn_value_array);
        foreach ($months as $month){

                // แปลงเป็นเดือนแบบภาษาไทย
                $month_th = $th_month_array[$month->month];
                
                // หาวันที่ในเดือนนั้นทั้งหมด
                $dates = $this->modelsManager->executeQuery(
                    "SELECT date(A.transaction_date) as date
                        FROM account as A
                        WHERE year(A.transaction_date) = '$year' AND month(A.transaction_date) = '$month->month'
                        GROUP BY date(A.transaction_date)"
                    );
                    
                // reset array ของแต่ละวัน
                unset($data_pawn_value_array);
                unset($data_withdraw_value_array);
                unset($data_withdraw_interst_value_array);
                unset($data_interest_value_array);
                unset($data_sale_value_array);
                unset($data_profit_value_array);
                unset($data_deposit_value_array);
                foreach($dates as $date){

                    // หา sum ในแต่ละวัน
                        $date_sql = 
                        "SELECT 
                            (SELECT SUM(A.value)        FROM account as A  JOIN (SELECT TS.* FROM transaction AS TS  GROUP BY TS.uuid) AS T ON A.uuid = T.uuid WHERE (T.status = 'ฝาก' OR T.status = 'ซื้อเข้า') AND date(A.transaction_date) = '$date->date') AS sum_pawn_value ,
                            (SELECT SUM(A.value)        FROM account as A  JOIN (SELECT TS.* FROM transaction AS TS  GROUP BY TS.uuid) AS T ON A.uuid = T.uuid WHERE (T.status = 'เพิ่มเงิน') AND date(A.transaction_date) = '$date->date') AS sum_increase_value ,
                            (SELECT SUM(A.value)        FROM account as A  JOIN (SELECT TS.* FROM transaction AS TS  GROUP BY TS.uuid) AS T ON A.uuid = T.uuid WHERE (T.status = 'เพิ่มสินค้า') AND date(A.transaction_date) = '$date->date') AS sum_insert_value ,
                            (SELECT SUM(A.principal)    FROM account as A  JOIN (SELECT TS.* FROM transaction AS TS  GROUP BY TS.uuid) AS T ON A.uuid = T.uuid WHERE (T.status LIKE '%ไถ่คืน%') AND date(A.transaction_date) = '$date->date') AS sum_withdraw_value ,
                            (SELECT SUM(A.value)        FROM account as A  JOIN (SELECT TS.* FROM transaction AS TS  GROUP BY TS.uuid) AS T ON A.uuid = T.uuid WHERE (T.status LIKE '%ไถ่คืน%') AND date(A.transaction_date) = '$date->date') AS sum_withdraw_interest_value ,
                            (SELECT SUM(A.principal)    FROM account as A  JOIN (SELECT TS.* FROM transaction AS TS  GROUP BY TS.uuid) AS T ON A.uuid = T.uuid WHERE (T.status = 'ลดต้น') AND date(A.transaction_date) = '$date->date') AS sum_decrease_value ,
                            (SELECT SUM(A.value)        FROM account as A  JOIN (SELECT TS.* FROM transaction AS TS  GROUP BY TS.uuid) AS T ON A.uuid = T.uuid WHERE (T.status = 'ลดต้น') AND date(A.transaction_date) = '$date->date') AS sum_decrease_interest_value ,
                            (SELECT SUM(A.value)        FROM account as A  JOIN (SELECT TS.* FROM transaction AS TS  GROUP BY TS.uuid) AS T ON A.uuid = T.uuid WHERE (T.status LIKE '%ต่อดอก%') AND date(A.transaction_date) = '$date->date') AS sum_interest_value ,
                            (SELECT SUM(A.value)        FROM account as A  JOIN (SELECT TS.* FROM transaction AS TS  GROUP BY TS.uuid) AS T ON A.uuid = T.uuid WHERE (T.status = 'ขายแล้ว') AND date(A.transaction_date) = '$date->date') AS sum_sale_value ,
                            (
                                SELECT SUM(A.value) - SUM(P.value)
                                FROM account as A 
                                JOIN (SELECT TS.* FROM transaction AS TS  GROUP BY TS.uuid) AS T
                                ON A.uuid = T.uuid
                                JOIN product as P
                                ON T.product_id = P.product_id
                                WHERE (T.status = 'ขายแล้ว') AND date(A.transaction_date) = '$date->date'
                            ) AS sum_profit_value ,
                            (SELECT SUM(A.value)        FROM account as A JOIN (SELECT TS.* FROM transaction AS TS  GROUP BY TS.uuid) AS T ON A.uuid = T.uuid WHERE (T.status = 'มัดจำ') AND date(A.transaction_date) = '$date->date') AS sum_deposit_value ,
                            (SELECT SUM(A.value)        FROM account as A JOIN (SELECT TS.* FROM transaction AS TS  GROUP BY TS.uuid) AS T ON A.uuid = T.uuid WHERE (T.status = 'ตั้งขาย' OR T.status = 'ตั้งขายกรณีพิเศษ') AND date(A.transaction_date) = '$date->date') AS sum_selling_value
                         FROM account as A
                        ";
                        $connection = $this->db;
                        $data       = $connection->query($date_sql);
                        $date_result    = $data->fetch();

                        $sum_array[] = array
                        (
                            'date' => $this->convertToBuddhistEra($date->date),
                            'sum_pawn_value' => number_format((int)$date_result['sum_pawn_value'] + (int)$date_result['sum_increase_value'] + (int)$date_result['sum_insert_value']),
                            'sum_withdraw_value' =>  number_format((int)$date_result['sum_withdraw_value'] + (int)$date_result['sum_decrease_value']),
                            'sum_sale_value' => number_format((int)$date_result['sum_sale_value']),
                            'sum_interest_value' => number_format((int)$date_result['sum_interest_value']),
                            'sum_withdraw_interest_value' => number_format((int)$date_result['sum_withdraw_interest_value'] + (int)$date_result['sum_decrease_interest_value']),
                            'sum_profit_value' => number_format((int)$date_result['sum_profit_value']),
                            'sum_selling_value' => number_format((int)$date_result['sum_selling_value'])
                        );
                }
        }
        $data_json = json_encode($sum_array);
        echo $data_json;
    }
}
