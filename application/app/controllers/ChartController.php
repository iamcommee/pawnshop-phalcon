<?php
use Phalcon\Crypt;
use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;
use Phalcon\Mvc\View;

class ChartController extends ControllerBase
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

    public function indexAction()
    {
        $this->view->setMainView('desktop-layout');
        $this->tag->setTitle("| ประเสริฐรัตน์บริการ");
        $this->tag->prependTitle("กราฟ ");
        $this->assets->addJs("js/chart/index.js");

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

    public function server_processingAction($year,$start_month,$end_month)
    {
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
                WHERE year(A.transaction_date) = '$year' AND ( month(A.transaction_date) BETWEEN '$start_month' AND '$end_month' )
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
                $pawn_value_counter = 0;
                $withdraw_value_counter = 0;
                $sale_value_counter = 0;
                $interest_value_counter = 0;
                $withdraw_interest_value_counter = 0;
                $profit_value_counter = 0;
                $deposit_value_counter = 0;
                $selling_value_counter = 0;
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

                        $sum_pawn_value = (int)$date_result['sum_pawn_value'] + (int)$date_result['sum_increase_value'] + (int)$date_result['sum_insert_value'];
                        if($sum_pawn_value != 0){
                            $pawn_value_counter++;
                        }
                        $data_pawn_value_array[] = array(
                            $th_day_array[date("N", strtotime($date->date))].' ที่ '.date('d/m/Y',strtotime("+543 years", strtotime($date->date))),
                            $sum_pawn_value
                        );

                        $sum_withdraw_value = (int)$date_result['sum_withdraw_value'] + (int)$date_result['sum_decrease_value'];
                        if($sum_withdraw_value != 0){
                            $withdraw_value_counter++;
                        }
                        $data_withdraw_value_array[] = array(
                            $th_day_array[date("N", strtotime($date->date))].' ที่ '.date('d/m/Y',strtotime("+543 years", strtotime($date->date))),
                            $sum_withdraw_value
                        );

                        $sum_withdraw_interest_value = (int)$date_result['sum_withdraw_interest_value'] + (int)$date_result['sum_decrease_interest_value'];
                        if($sum_withdraw_interest_value != 0){
                            $withdraw_interest_value_counter++;
                        }
                        $data_withdraw_interst_value_array[] = array(
                            $th_day_array[date("N", strtotime($date->date))].' ที่ '.date('d/m/Y',strtotime("+543 years", strtotime($date->date))),
                            $sum_withdraw_interest_value
                        );
                        
                        $sum_interest_value = (int)$date_result['sum_interest_value'];
                        if($sum_interest_value != 0){
                            $interest_value_counter++;
                        }
                        $data_interest_value_array[] = array(
                            $th_day_array[date("N", strtotime($date->date))].' ที่ '.date('d/m/Y',strtotime("+543 years", strtotime($date->date))),
                            $sum_interest_value
                        );

                        $sum_sale_value = (int)$date_result['sum_sale_value'];
                        if($sum_sale_value != 0){
                            $sale_value_counter++;
                        }
                        $data_sale_value_array[] = array(
                            $th_day_array[date("N", strtotime($date->date))].' ที่ '.date('d/m/Y',strtotime("+543 years", strtotime($date->date))),
                            $sum_sale_value
                        );

                        $sum_profit_value = (int)$date_result['sum_profit_value'];
                        if($sum_profit_value != 0){
                            $profit_value_counter++;
                        }
                        $data_profit_value_array[] = array(
                            $th_day_array[date("N", strtotime($date->date))].' ที่ '.date('d/m/Y',strtotime("+543 years", strtotime($date->date))),
                            $sum_profit_value
                        );

                        $sum_deposit_value = (int)$date_result['sum_deposit_value'];
                        if($sum_deposit_value != 0){
                            $deposit_value_counter++;
                        }
                        $data_deposit_value_array[] = array(
                            $th_day_array[date("N", strtotime($date->date))].' ที่ '.date('d/m/Y',strtotime("+543 years", strtotime($date->date))),
                            $sum_deposit_value
                        );  

                        $sum_selling_value = (int)$date_result['sum_selling_value'];
                        if($sum_selling_value != 0){
                            $selling_value_counter++;
                        }
                        $data_selling_value_array[] = array(
                            $th_day_array[date("N", strtotime($date->date))].' ที่ '.date('d/m/Y',strtotime("+543 years", strtotime($date->date))),
                           $sum_selling_value
                        );  
                }

            // หา sum ในแต่ละเดือน
            $month_sql =
            "SELECT 
                (SELECT SUM(A.value)        FROM account as A JOIN (SELECT TS.* FROM transaction AS TS  GROUP BY TS.uuid) AS T ON A.uuid = T.uuid WHERE (T.status = 'ฝาก' OR T.status = 'ซื้อเข้า') AND year(A.transaction_date) = '$year' AND month(A.transaction_date) = '$month->month') AS sum_pawn_value ,
                (SELECT SUM(A.value)        FROM account as A JOIN (SELECT TS.* FROM transaction AS TS  GROUP BY TS.uuid) AS T ON A.uuid = T.uuid WHERE (T.status = 'เพิ่มเงิน') AND year(A.transaction_date) = '$year' AND month(A.transaction_date) = '$month->month') AS sum_increase_value ,
                (SELECT SUM(A.value)        FROM account as A JOIN (SELECT TS.* FROM transaction AS TS  GROUP BY TS.uuid) AS T ON A.uuid = T.uuid WHERE (T.status = 'เพิ่มสินค้า') AND year(A.transaction_date) = '$year' AND month(A.transaction_date) = '$month->month') AS sum_insert_value ,
                (SELECT SUM(A.principal)    FROM account as A JOIN (SELECT TS.* FROM transaction AS TS  GROUP BY TS.uuid) AS T ON A.uuid = T.uuid WHERE (T.status LIKE '%ไถ่คืน%') AND year(A.transaction_date) = '$year' AND month(A.transaction_date) = '$month->month') AS sum_withdraw_value ,
                (SELECT SUM(A.value)        FROM account as A JOIN (SELECT TS.* FROM transaction AS TS  GROUP BY TS.uuid) AS T ON A.uuid = T.uuid WHERE (T.status LIKE '%ไถ่คืน%') AND year(A.transaction_date) = '$year' AND month(A.transaction_date) = '$month->month') AS sum_withdraw_interest_value ,
                (SELECT SUM(A.principal)    FROM account as A JOIN (SELECT TS.* FROM transaction AS TS  GROUP BY TS.uuid) AS T ON A.uuid = T.uuid WHERE (T.status = 'ลดต้น') AND year(A.transaction_date) = '$year' AND month(A.transaction_date) = '$month->month') AS sum_decrease_value ,
                (SELECT SUM(A.value)        FROM account as A JOIN (SELECT TS.* FROM transaction AS TS  GROUP BY TS.uuid) AS T ON A.uuid = T.uuid WHERE (T.status = 'ลดต้น') AND year(A.transaction_date) = '$year' AND month(A.transaction_date) = '$month->month') AS sum_decrease_interest_value ,
                (SELECT SUM(A.value)        FROM account as A JOIN (SELECT TS.* FROM transaction AS TS  GROUP BY TS.uuid) AS T ON A.uuid = T.uuid WHERE (T.status LIKE '%ต่อดอก%') AND year(A.transaction_date) = '$year' AND month(A.transaction_date) = '$month->month') AS sum_interest_value ,
                (SELECT SUM(A.value)        FROM account as A JOIN (SELECT TS.* FROM transaction AS TS  GROUP BY TS.uuid) AS T ON A.uuid = T.uuid WHERE (T.status = 'ขายแล้ว') AND year(A.transaction_date) = '$year' AND month(A.transaction_date) = '$month->month') AS sum_sale_value ,
                (
                    SELECT SUM(A.value) - SUM(P.value)
                    FROM account as A 
                    JOIN (SELECT TS.* FROM transaction AS TS  GROUP BY TS.uuid) AS T
                    ON A.uuid = T.uuid
                    JOIN product as P
                    ON T.product_id = P.product_id
                    WHERE (T.status = 'ขายแล้ว') AND year(A.transaction_date) = '$year' AND month(A.transaction_date) = '$month->month'
                ) AS sum_profit_value ,
                (SELECT SUM(A.value)        FROM account as A JOIN (SELECT TS.* FROM transaction AS TS  GROUP BY TS.uuid) AS T ON A.uuid = T.uuid WHERE (T.status = 'มัดจำ') AND year(A.transaction_date) = '$year' AND month(A.transaction_date) = '$month->month') AS sum_deposit_value ,
                (SELECT SUM(A.value)        FROM account as A JOIN (SELECT TS.* FROM transaction AS TS  GROUP BY TS.uuid) AS T ON A.uuid = T.uuid WHERE (T.status = 'ตั้งขาย' OR T.status = 'ตั้งขายกรณีพิเศษ') AND year(A.transaction_date) = '$year' AND month(A.transaction_date) = '$month->month') AS sum_selling_value
                FROM account as A
            ";

            $connection = $this->db;
            $data       = $connection->query($month_sql);
            $month_result    = $data->fetch();

            $sum_pawn_value_array[] = array(
                'name' => $month_th,
                'y'    => (int)$month_result['sum_pawn_value'] + (int)$month_result['sum_increase_value'] + (int)$month_result['sum_insert_value'],
                'drilldown' => $month->month.'-pawn-dates'
            );

            $sum_withdraw_value_array[] = array(
                'name' => $month_th,
                'y'    => (int)$month_result['sum_withdraw_value'] + (int)$month_result['sum_decrease_value'],
                'drilldown' => $month->month.'-withdraw-dates'
            );

            $sum_withdraw_interest_value_array[] = array(
                'name' => $month_th,
                'y'    => (int)$month_result['sum_withdraw_interest_value'] + (int)$month_result['sum_decrease_interest_value'],
                'drilldown' => $month->month.'-withdraw-interest-dates'
            );

            $sum_interest_value_array[] = array(
                'name' => $month_th,
                'y'    => (int)$month_result['sum_interest_value'],
                'drilldown' => $month->month.'-interest-dates'
            );

            $sum_sale_value_array[] = array(
                'name' => $month_th,
                'y'    => (int)$month_result['sum_sale_value'],
                'drilldown' => $month->month.'-sale-dates'
            );

            $sum_profit_value_array[] = array(
                'name' => $month_th,
                'y'    => (int)$month_result['sum_profit_value'],
                'drilldown' => $month->month.'-profit-dates'
            );

            $sum_deposit_value_array[] = array(
                'name' => $month_th,
                'y'    => (int)$month_result['sum_deposit_value'],
                'drilldown' => $month->month.'-deposit-dates'
            );

            $sum_selling_value_array[] = array(
                'name' => $month_th,
                'y'    => (int)$month_result['sum_selling_value'],
                'drilldown' => $month->month.'-selling-dates'
            );

            
            // เฉลี่ยนในแต่ละเดือน
            $sum_pawn_value_avg_array[] = array(
                'name' => $month_th,
                'y'    => round(((int)$month_result['sum_pawn_value'] + (int)$month_result['sum_increase_value'] + (int)$month_result['sum_insert_value'])/$pawn_value_counter),
            );

            $sum_withdraw_value_avg_array[] = array(
                'name' => $month_th,
                'y'    => round(((int)$month_result['sum_withdraw_value'] + (int)$month_result['sum_decrease_value'])/$withdraw_value_counter),
            );

            $sum_withdraw_interest_value_avg_array[] = array(
                'name' => $month_th,
                'y'    => round(((int)$month_result['sum_withdraw_interest_value'] + (int)$month_result['sum_decrease_interest_value'])/$withdraw_interest_value_counter),
            );

            $sum_interest_value_avg_array[] = array(
                'name' => $month_th,
                'y'    => round(((int)$month_result['sum_interest_value'])/$interest_value_counter),
            );

            $sum_sale_value_avg_array[] = array(
                'name' => $month_th,
                'y'    => round(((int)$month_result['sum_sale_value'])/$sale_value_counter),
            );

            $sum_profit_value_avg_array[] = array(
                'name' => $month_th,
                'y'    => round(((int)$month_result['sum_profit_value'])/$profit_value_counter),
            );

            $sum_deposit_value_avg_array[] = array(
                'name' => $month_th,
                'y'    => round(((int)$month_result['sum_deposit_value'])/$deposit_value_counter),
            );

            $sum_selling_value_avg_array[] = array(
                'name' => $month_th,
                'y'    => round(((int)$month_result['sum_selling_value'])/$selling_value_counter),
            );

            // drilldown
            $data_date_array[] = array(
                "name" => "ยอดฝาก",
                "id" => $month->month.'-pawn-dates',
                "data" => $data_pawn_value_array,
            );

            $data_date_array[] = array(
                "name" => "ยอดไถ่",
                "id" => $month->month.'-withdraw-dates',
                "data" => $data_withdraw_value_array,
            );

            $data_date_array[] = array(
                "name" => "ดอกจากการไถ่",
                "id" => $month->month.'-withdraw-interest-dates',
                "data" => $data_withdraw_interst_value_array,
            );

            $data_date_array[] = array(
                "name" => "ดอกจากการต่อ",
                "id" => $month->month.'-interest-dates',
                "data" => $data_interest_value_array
            );

            $data_date_array[] = array(
                "name" => "ยอดขาย",
                "id" => $month->month.'-sale-dates',
                "data" => $data_sale_value_array,
            );
            $data_date_array[] = array(
                "name" => "กำไร",
                "id" =>  $month->month.'-profit-dates',
                "data" => $data_profit_value_array,
            );
            $data_date_array[] = array(
                "name" => "มัดจำ",
                "id" => $month->month.'-deposit-dates',
                "data" => $data_deposit_value_array,
            );

            $data_date_array[] = array(
                "name" => "ตั้งขาย",
                "id" => $month->month.'-deposit-dates',
                "data" => $data_selling_value_array,
            );

            // json ที่ส่งค่าไป view
            $data = array(
                'month' => array ( 
                    0 => array ( 
                        'name' => 'ยอดฝาก' ,
                        'data' => $sum_pawn_value_array
                    ),
                    1 => array (
                        'name' => 'ยอดไถ่คืน' ,
                        'data' => $sum_withdraw_value_array
                    ),
                    2 => array (
                        'name' => 'ดอกจากการต่อดอก' ,
                        'data' => $sum_interest_value_array
                    ),
                    3 => array (
                        'name' => 'ดอกจากการไถ่' ,
                        'data' => $sum_withdraw_interest_value_array
                    ),
                    4 => array (
                        'name' => 'ยอดขาย' ,
                        'data' => $sum_sale_value_array
                    ),
                    5 => array (
                        'name' => 'กำไร' ,
                        'data' => $sum_profit_value_array
                    ),
                    6 => array (
                        'name' => 'มัดจำ' ,
                        'data' => $sum_deposit_value_array
                    ),
                    7 => array (
                        'name' => 'ตั้งขาย' ,
                        'data' => $sum_selling_value_array
                    ),
                    8 => array ( 
                        'name' => 'ยอดฝาก(เฉลี่ย)' ,
                        'data' => $sum_pawn_value_avg_array
                    ),
                    9 => array (
                        'name' => 'ยอดไถ่คืน(เฉลี่ย)' ,
                        'data' => $sum_withdraw_value_avg_array
                    ),
                    10 => array (
                        'name' => 'ดอกจากการต่อดอก(เฉลี่ย)' ,
                        'data' => $sum_interest_value_avg_array
                    ),
                    11 => array (
                        'name' => 'ดอกจากการไถ่(เฉลี่ย)' ,
                        'data' => $sum_withdraw_interest_value_avg_array
                    ),
                    12 => array (
                        'name' => 'ยอดขาย(เฉลี่ย)' ,
                        'data' => $sum_sale_value_avg_array
                    ),
                    13 => array (
                        'name' => 'กำไร(เฉลี่ย)' ,
                        'data' => $sum_profit_value_avg_array
                    ),
                    14 => array (
                        'name' => 'มัดจำ(เฉลี่ย)' ,
                        'data' => $sum_deposit_value_avg_array
                    ),
                    15 => array (
                        'name' => 'ตั้งขาย(เฉลี่ย)' ,
                        'data' => $sum_selling_value_avg_array
                    )
                ),
                // drilldown data
                'date' => $data_date_array
            );
        }
        echo json_encode($data);
    }

}
