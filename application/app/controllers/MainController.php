<?php
use Phalcon\Mvc\View;
use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;

class MainController extends ControllerBase
{
    public function onConstruct()
    {
        if ($this->session->has("user")) 
        {
            // nothing
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
        $this->tag->prependTitle("หน้าแรก ");
        $this->assets->addJs("js/main/index.js");
    }

    // * OK
    public function server_processingAction()
    {
        $this->view->disable(); 

        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();
        
            $current_date = $this->getCurrentDate();

            $owner = Owner::findFirst('1');
            $min_due_date = $owner->min_due_date + 1;
            $max_due_date = $owner->max_due_date + 1;

            $transactions = $this->modelsManager->executeQuery(
                "SELECT T.*
                    FROM transaction as T
                    WHERE T.status = 'ว่าง' 
                ");

            foreach ($transactions as $transaction) {

                $count_payments = $this->modelsManager->executeQuery(
                    "SELECT COUNT(T.transaction_id) as transaction_id
                        FROM transaction as T
                        WHERE T.agreement_number = '$transaction->agreement_number' AND T.status LIKE '%ต่อดอก%' "
                );

                foreach ($count_payments as $count_payment) {
                    // * ไม่เคยต่อดอก เลทได้ 3 วัน
                    if ($count_payment->transaction_id <= 1) {
                        $out_date = new DateTime($transaction->end_date);
                        $out_date->modify("+{$min_due_date} day");
                        $out_date = $out_date->format('Y-m-d');
                        if ($out_date <= $current_date) {
                            $uuid = $this->getUuid();
                            $account = new Account();
                            $account->uuid = $uuid;
                            $account->transaction_date = $this->getCurrentDate();
                            $account->transaction_time = $this->getCurrentTime();
                            if ($account->save() == false) {
                                $transaction_->rollback(
                                    'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }

                            $update_transaction = Transaction::findFirst($transaction->transaction_id);
                            $update_transaction->status = 'หลุด';
                            $update_transaction->uuid = $uuid;
                            if ($update_transaction->save() == false) {
                                $transaction_->rollback(
                                    'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }
                        }
                    }
                    // * เคยต่อดอก เลทได้ 7 วัน
                    else {
                        $out_date = new DateTime($transaction->end_date);
                        $out_date->modify("+{$max_due_date} day");
                        $out_date = $out_date->format('Y-m-d');
                        if ($out_date <= $current_date) {
                            $uuid = $this->getUuid();
                            $account = new Account();
                            $account->uuid = $uuid;
                            $account->transaction_date = $this->getCurrentDate();
                            $account->transaction_time = $this->getCurrentTime();
                            if ($account->save() == false) {
                                $transaction_->rollback(
                                    'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }

                            $update_transaction = Transaction::findFirst($transaction->transaction_id);
                            $update_transaction->status = 'หลุด';
                            $update_transaction->uuid = $uuid;
                            if ($update_transaction->save() == false) {
                                $transaction_->rollback(
                                    'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }
                        }
                    }
                    $transaction_ = $manager->commit();
                }
            }
        } catch (TxFailed $error) {
            echo $error->getMessage();
        }
    }

    // * OK
    public function getDashboardAction()
    {
        $this->view->disable();

        $pawn = $this->modelsManager->executeQuery(
            'SELECT SUM(A.value) AS sum_pawn_value , COUNT(A.account_id) AS count_pawn_transaction
                FROM transaction AS T
                JOIN account AS A
                ON T.uuid = A.uuid
                WHERE T.status = :status: AND A.transaction_date = :transaction_date:',
                [
                    'status'            => 'ฝาก',
                    'transaction_date'  => $this->getCurrentDate()
                ]
        )->getFirst();

        if($pawn->sum_pawn_value == NULL){
            $sum_pawn_value = 0;
        }else{
            $sum_pawn_value = $pawn->sum_pawn_value;
        }

        $interest = $this->modelsManager->executeQuery(
            'SELECT SUM(A.value) AS sum_interest_value , COUNT(A.account_id) AS count_interest_transaction
                FROM transaction AS T
                JOIN account AS A
                ON T.uuid = A.uuid
                WHERE T.status LIKE :status: AND A.transaction_date = :transaction_date:',
                [
                    'status'            => '%ต่อดอก%',
                    'transaction_date'  => $this->getCurrentDate()
                ]
        )->getFirst();

        if($interest->sum_interest_value == NULL){
            $sum_interest_value = 0;
        }else{
            $sum_interest_value = $interest->sum_interest_value;
        }

        $withdraw = $this->modelsManager->executeQuery(
            'SELECT SUM(A.principal) AS sum_principal_value , SUM(A.value) AS sum_withdraw_value , COUNT(A.account_id) AS count_withdraw_transaction
                FROM transaction AS T
                JOIN account AS A
                ON T.uuid = A.uuid
                WHERE T.status LIKE :status: AND A.transaction_date = :transaction_date:',
                [
                    'status'            => '%ไถ่คืน%',
                    'transaction_date'  => $this->getCurrentDate()
                ]
        )->getFirst();

        if($withdraw->sum_withdraw_value == NULL){
            $sum_withdraw_value = 0;
        }else{
            $sum_withdraw_value = $withdraw->sum_principal_value + $withdraw->sum_withdraw_value;
        }

        $sale = $this->modelsManager->executeQuery(
            'SELECT SUM(A.value) AS sum_sale_value , COUNT(A.account_id) AS count_sale_transaction
                FROM transaction AS T
                JOIN account AS A
                ON T.uuid = A.uuid
                WHERE T.status LIKE :status: AND A.transaction_date = :transaction_date:',
                [
                    'status'            => 'ขายแล้ว',
                    'transaction_date'  => $this->getCurrentDate()
                ]
        )->getFirst();

        if($sale->sum_sale_value == NULL){
            $sum_sale_value = 0;
        }else{
            $sum_sale_value = $sale->sum_sale_value;
        }

        $sum_array = array(
            'count_pawn_transaction' => $pawn->count_pawn_transaction,
            'sum_pawn_value'  => $sum_pawn_value,

            'count_interest_transaction' => $interest->count_interest_transaction,
            'sum_interest_value'  => $sum_interest_value,

            'count_withdraw_transaction'    => $withdraw->count_withdraw_transaction,
            'sum_withdraw_value'    => $sum_withdraw_value,

            'count_sale_transaction'    => $sale->count_sale_transaction,
            'sum_sale_value'    => $sum_sale_value
        );

        echo json_encode($sum_array);
    }

    // * OK
    public function getWaitingListAction()
    {
        $this->view->disable();
        $data = array();

        $current_date = $this->getCurrentDate();

        $products = $this->modelsManager->executeQuery(
            "SELECT P.* , T.* , A.*
                FROM product AS P
                JOIN transaction AS T
                ON P.product_id = T.product_id 
                JOIN account AS A
                ON T.uuid = A.uuid
                WHERE ( T.status = 'หลุด' OR T.status = 'ซื้อเข้า' ) AND T.active = 'T' AND A.transaction_date = '$current_date' "
        );

        foreach ($products as $product) {

                if($this->session->get("role") == 'administrator'){
                    $link = '<a href="../payment/search/'.$product->P->agreement_number.'" target="_blank">'.$product->P->agreement_number.'</a>';
                }else{
                    $link = $product->P->agreement_number;
                }

                $data[] = array
                (
                    'link' => $link,
                    'agreement_number' => $product->P->agreement_number,
                    'product_id'   => $product->P->product_id,
                    'product_name' => $product->P->name,
                    'product_brand' => $product->P->brand,
                    'product_detail' => $product->P->detail,
                    'product_value' => number_format($product->P->value),
                    'start_date' => $this->convertToBuddhistEra($product->T->start_date),
                    'end_date' => $this->convertToBuddhistEra($product->T->end_date),
                    'note'          => $product->T->note,
                );
                
        }

        $data_json = json_encode($data);
        echo $data_json;   
    }

    // * OK
    public function moveToSellAction()
    {
        $this->view->disable();

        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($product_id = $this->request->get('product_id')) {

                $uuid = $this->getUuid();

                $transaction_date       =  $this->request->get('transaction_date');
                $transaction_time       =  $this->request->get('transaction_time');
                $sale_value             = $this->convertToNumber($this->request->get('sale_value'));
                $note                   = $this->request->get('note');
                
                $transaction = $this->modelsManager->executeQuery(
                    "SELECT T.*
                        FROM transaction AS T 
                        WHERE ( T.status = 'หลุด' OR T.status = 'ซื้อเข้า' ) AND T.product_id = '$product_id' AND T.active = 'T' 
                    ")->getFirst();
                
                $transaction->active = 'F';
                if ($transaction->save() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                $new_transaction = new Transaction();
                $new_transaction->agreement_number = $transaction->agreement_number;
                $new_transaction->product_id = $product_id;
                $new_transaction->start_date = $transaction->start_date;
                $new_transaction->end_date = $transaction->end_date;
                $new_transaction->status = 'ตั้งขาย';
                $new_transaction->uuid = $uuid;
                $new_transaction->related_uuid = $transaction->uuid;
                $new_transaction->note = $note;
                if ($new_transaction->save() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                $account = new Account();
                $account->uuid = $uuid;
                $account->value = $sale_value;
                $account->transaction_date = $this->convertToCommonEraSQL($transaction_date);
                $account->transaction_time = $this->convertToTimeSQL($transaction_time);
                if ($account->save() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                $transaction_ = $manager->commit();
            }
        } catch (TxFailed $error) {
            echo $error->getMessage();
        }
    }

    public function restore_customersAction(){
        $this->view->disable();
        $connection = $this->db_restore;
        $customer_sql = "SELECT C.* FROM customer AS C";
        $query = $connection->query($customer_sql);
        $customers = $connection->fetchAll($customer_sql);
        foreach ($customers as $customer) {
            $new_customer = new Customer();
            $new_customer->customer_id = $customer['customer_id'];
            $new_customer->idcard = $customer['idcard'];
            $new_customer->firstname = $customer['firstname'];
            $new_customer->lastname = $customer['lastname'];
            $new_customer->image = $customer['image'];
            $new_customer->save();
        }
    }

    public function restore_dbAction(){
        $this->view->disable();
        $connection = $this->db_restore;
        $agreement_sql = "SELECT A.agreement_number,A.value,A.interest,A.create_date,C.idcard
        FROM agreement AS A
        JOIN customer AS C
        ON A.idcard = C.idcard
        GROUP BY A.agreement_number
        ORDER BY A.agreement_number ASC";
        $query = $connection->query($agreement_sql);
        $agreements = $connection->fetchAll($agreement_sql);
        foreach ($agreements as $agreement) {

            // * Agreement
            $new_agreement = new Agreement();
            $new_agreement->idcard = $agreement['idcard'];
            $new_agreement->agreement_number = $agreement['agreement_number'];
            $new_agreement->value = abs($agreement['value']);
            $new_agreement->interest = abs($agreement['interest']);
            if ($agreement['create_date'] == '') {
                $new_agreement->created_date = NULL;
            } else {
                $new_agreement->created_date = $agreement['create_date'];
            }
            $new_agreement->save();

            $product_sql = "SELECT P.* FROM product AS P WHERE P.agreement_number = ".$agreement['agreement_number']." ";
            $query = $connection->query($product_sql);
            $products = $connection->fetchAll($product_sql);

            foreach($products as $product){

                // * Product
                $new_product = new Product();
                $new_product->product_id = $product['product_id'];
                $new_product->agreement_number = $product['agreement_number'];
                $new_product->name = $product['name'];
                $new_product->brand = $product['brand'];
                $new_product->detail = $product['detail'];
                $new_product->value = $product['value'];
                $new_product->interest = $this->calculateInterest($product['value']);
                $new_product->related_product = $product['related_product'];
                $new_product->save();

                $transaction_sql = "SELECT T.agreement_number, T.product_id , T.start_date , T.end_date , T.note , T.uuid 
                , A.uuid , A.related_uuid , A.status , A.principal , A.value , A.transaction_date , A.transaction_time
                FROM transaction AS T 
                JOIN account AS A 
                ON T.uuid = A.uuid 
                WHERE T.product_id = ".$product['product_id']." ";
                $query = $connection->query($transaction_sql);
                $transactions = $connection->fetchAll($transaction_sql);   

                $i = 0;
                $len = count($transactions);
                foreach($transactions as $transaction){

                    // * Transasction

                    if($i == $len-1){ // * Last transaction

                        // * ถ้า transaction สุดท้าย = ต่อดอก , ต่อดอกทั้งหมด , เพิ่มเงิน , ลดต้น ให่เพิ่ม transaction ว่าง
                        if($transaction['status'] == 'ต่อดอก' || $transaction['status'] == 'ต่อดอกทั้งหมด'){

                            $new_transaction = new Transaction();
                            $new_transaction->agreement_number = $transaction['agreement_number'];
                            $new_transaction->product_id = $transaction['product_id'];
                            $new_transaction->start_date = $this->convertToCommonEraSQL($transaction['start_date']);
                            $new_transaction->end_date = $this->convertToCommonEraSQL($transaction['end_date']);
                            if($transaction['status'] == 'ต่อดอกทั้งหมด'){
                                $new_transaction->status = $transaction['status'];
                            }else{
                                $new_transaction->status = 'ต่อดอกชิ้นเดียว';
                            }
                            $new_transaction->note = $transaction['note'];
                            $new_transaction->uuid = $transaction['uuid'];
                            $new_transaction->related_uuid = $transaction['related_uuid'];
                            $new_transaction->save();

                            $find_uuid = Account::findFirstByUuid($transaction['uuid']);
                            if ($find_uuid == NULL) {
                                $new_account = new Account();
                                $new_account->uuid = $transaction['uuid'];
                                $new_account->principal = abs($transaction['principal']);
                                $new_account->value = abs($transaction['value']);
                                $new_account->transaction_date = $this->convertToCommonEraSQL(($transaction['transaction_date']));
                                $new_account->transaction_time = $this->convertToTimeSQL($transaction['transaction_time']);
                                $new_account->save();
                            }

                            $wait_transaction = new Transaction();
                            $wait_transaction->agreement_number = $transaction['agreement_number'];
                            $wait_transaction->product_id = $transaction['product_id'];
                            $wait_transaction->start_date = $this->convertToCommonEraSQL($this->getNextMonth($transaction['start_date']));
                            $wait_transaction->end_date = $this->convertToCommonEraSQL($this->getNextMonth($transaction['end_date']));
                            $wait_transaction->status = 'ว่าง';
                            $wait_transaction->save();

                        }elseif($transaction['status'] == 'เพิ่มเงิน'){

                            $new_transaction = new Transaction();
                            $new_transaction->agreement_number = $transaction['agreement_number'];
                            $new_transaction->product_id = $transaction['product_id'];
                            $new_transaction->start_date = $this->convertToCommonEraSQL($transaction['start_date']);
                            $new_transaction->end_date = $this->convertToCommonEraSQL($transaction['end_date']);
                            $new_transaction->status = $transaction['status'];
                            $new_transaction->note = $transaction['note'];
                            $new_transaction->uuid = $transaction['uuid'];
                            $new_transaction->related_uuid = $transaction['related_uuid'];
                            $new_transaction->save();

                            $find_uuid = Account::findFirstByUuid($transaction['uuid']);
                            if ($find_uuid == NULL) {
                                $new_account = new Account();
                                $new_account->uuid = $transaction['uuid'];
                                $new_account->principal = abs($transaction['principal']);
                                $new_account->value = abs($transaction['value']);
                                $new_account->transaction_date = $this->convertToCommonEraSQL(($transaction['transaction_date']));
                                $new_account->transaction_time = $this->convertToTimeSQL($transaction['transaction_time']);
                                $new_account->save();
                            }

                            $wait_transaction = new Transaction();
                            $wait_transaction->agreement_number = $transaction['agreement_number'];
                            $wait_transaction->product_id = $transaction['product_id'];
                            $wait_transaction->start_date = $this->convertToCommonEraSQL($transaction['start_date']);
                            $wait_transaction->end_date = $this->convertToCommonEraSQL($transaction['end_date']);
                            $wait_transaction->status = 'ว่าง';
                            $wait_transaction->save();

                        }elseif($transaction['status'] == 'ลดต้น'){

                            $new_transaction = new Transaction();
                            $new_transaction->agreement_number = $transaction['agreement_number'];
                            $new_transaction->product_id = $transaction['product_id'];
                            $new_transaction->start_date = $this->convertToCommonEraSQL($transaction['start_date']);
                            $new_transaction->end_date = $this->convertToCommonEraSQL($transaction['end_date']);
                            $new_transaction->status = $transaction['status'];
                            $new_transaction->note = $transaction['note'];
                            $new_transaction->uuid = $transaction['uuid'];
                            $new_transaction->related_uuid = $transaction['related_uuid'];
                            $new_transaction->save();

                            $find_uuid = Account::findFirstByUuid($transaction['uuid']);
                            if ($find_uuid == NULL) {
                                $new_account = new Account();
                                $new_account->uuid = $transaction['uuid'];
                                $new_account->principal = abs($transaction['principal']);
                                $new_account->value = abs($transaction['value']);
                                $new_account->transaction_date = $this->convertToCommonEraSQL(($transaction['transaction_date']));
                                $new_account->transaction_time = $this->convertToTimeSQL($transaction['transaction_time']);
                                $new_account->save();
                            }

                            $wait_transaction = new Transaction();
                            $wait_transaction->agreement_number = $transaction['agreement_number'];
                            $wait_transaction->product_id = $transaction['product_id'];
                            $wait_transaction->start_date = $this->convertToCommonEraSQL($transaction['start_date']);
                            $wait_transaction->end_date = $this->convertToCommonEraSQL($transaction['end_date']);
                            $wait_transaction->status = 'ว่าง';
                            $wait_transaction->save();

                        }elseif($transaction['status'] == 'ฝาก' || $transaction['status'] == 'ซื้อเข้า'){

                            $new_transaction = new Transaction();
                            $new_transaction->agreement_number = $transaction['agreement_number'];
                            $new_transaction->product_id = $transaction['product_id'];
                            $new_transaction->start_date = $this->convertToCommonEraSQL($transaction['start_date']);
                            $new_transaction->end_date = $this->convertToCommonEraSQL($transaction['end_date']);
                            $new_transaction->status = $transaction['status'];
                            $new_transaction->note = $transaction['note'];
                            $new_transaction->uuid = $transaction['uuid'];
                            $new_transaction->related_uuid = $transaction['related_uuid'];
                            $new_transaction->save();

                            $find_uuid = Account::findFirstByUuid($transaction['uuid']);
                            if ($find_uuid == NULL) {
                                $new_account = new Account();
                                $new_account->uuid = $transaction['uuid'];
                                $new_account->principal = abs($transaction['principal']);
                                $new_account->value = abs($transaction['value']);
                                $new_account->transaction_date = $this->convertToCommonEraSQL(($transaction['transaction_date']));
                                $new_account->transaction_time = $this->convertToTimeSQL($transaction['transaction_time']);
                                $new_account->save();
                            }

                            $wait_transaction = new Transaction();
                            $wait_transaction->agreement_number = $transaction['agreement_number'];
                            $wait_transaction->product_id = $transaction['product_id'];
                            $wait_transaction->start_date = $this->convertToCommonEraSQL($transaction['start_date']);
                            $wait_transaction->end_date = $this->convertToCommonEraSQL($transaction['end_date']);
                            $wait_transaction->status = 'ว่าง';
                            $wait_transaction->save();

                        }elseif($transaction['status'] == 'ตั้งขายกรณีพิเศษ'){
                            $new_transaction = new Transaction();
                            $new_transaction->agreement_number = $transaction['agreement_number'];
                            $new_transaction->product_id = $transaction['product_id'];
                            $new_transaction->start_date = $this->convertToCommonEraSQL($transaction['start_date']);
                            $new_transaction->end_date = $this->convertToCommonEraSQL($transaction['end_date']);
                            $new_transaction->status = $transaction['status'];
                            $new_transaction->note = $transaction['note'];
                            $new_transaction->uuid = $transaction['uuid'];
                            $new_transaction->related_uuid = $transaction['related_uuid'];
                            $new_transaction->save();

                            $find_uuid = Account::findFirstByUuid($transaction['uuid']);
                            if ($find_uuid == NULL) {
                                $new_account = new Account();
                                $new_account->uuid = $transaction['uuid'];
                                $new_account->principal = abs($transaction['principal']);
                                $new_account->value = abs($transaction['value']);
                                $new_account->transaction_date = $this->convertToCommonEraSQL(($transaction['transaction_date']));
                                $new_account->transaction_time = $this->convertToTimeSQL($transaction['transaction_time']);
                                $new_account->save();
                            }
                        }elseif($transaction['status'] == 'เพิ่มสินค้า'){

                            $new_transaction = new Transaction();
                            $new_transaction->agreement_number = $transaction['agreement_number'];
                            $new_transaction->product_id = $transaction['product_id'];
                            $new_transaction->start_date = $this->convertToCommonEraSQL($transaction['start_date']);
                            $new_transaction->end_date = $this->convertToCommonEraSQL($transaction['end_date']);
                            $new_transaction->status = 'ฝาก';
                            $new_transaction->note = $transaction['note'];
                            $new_transaction->uuid = $transaction['uuid'];
                            $new_transaction->related_uuid = $transaction['related_uuid'];
                            $new_transaction->save();

                            $find_uuid = Account::findFirstByUuid($transaction['uuid']);
                            if ($find_uuid == NULL) {
                                $new_account = new Account();
                                $new_account->uuid = $transaction['uuid'];
                                $new_account->principal = abs($transaction['principal']);
                                $new_account->value = abs($transaction['value']);
                                $new_account->transaction_date = $this->convertToCommonEraSQL(($transaction['transaction_date']));
                                $new_account->transaction_time = $this->convertToTimeSQL($transaction['transaction_time']);
                                $new_account->save();
                            }

                            $wait_transaction = new Transaction();
                            $wait_transaction->agreement_number = $transaction['agreement_number'];
                            $wait_transaction->product_id = $transaction['product_id'];
                            $wait_transaction->start_date = $this->convertToCommonEraSQL($transaction['start_date']);
                            $wait_transaction->end_date = $this->convertToCommonEraSQL($transaction['end_date']);
                            $wait_transaction->status = 'ว่าง';
                            $wait_transaction->save();

                        }elseif($transaction['status'] == 'ไถ่คืน' || $transaction['status'] == 'ไถ่คืนทั้งหมด'){

                            $new_transaction = new Transaction();
                            $new_transaction->agreement_number = $transaction['agreement_number'];
                            $new_transaction->product_id = $transaction['product_id'];
                            $new_transaction->start_date = $this->convertToCommonEraSQL($transaction['start_date']);
                            $new_transaction->end_date = $this->convertToCommonEraSQL($transaction['end_date']);
                            if($transaction['status'] == 'ไถ่คืน'){
                                $new_transaction->status = 'ไถ่คืนชิ้นเดียว';
                            }else{
                                $new_transaction->status = $transaction['status'];
                            }
                            $new_transaction->note = $transaction['note'];
                            $new_transaction->uuid = $transaction['uuid'];
                            $new_transaction->related_uuid = $transaction['related_uuid'];
                            $new_transaction->save();

                            $find_uuid = Account::findFirstByUuid($transaction['uuid']);
                            if ($find_uuid == NULL) {
                                $new_account = new Account();
                                $new_account->uuid = $transaction['uuid'];
                                $new_account->principal = abs($transaction['principal']);
                                $new_account->value = abs($transaction['value']);
                                $new_account->transaction_date = $this->convertToCommonEraSQL(($transaction['transaction_date']));
                                $new_account->transaction_time = $this->convertToTimeSQL($transaction['transaction_time']);
                                $new_account->save();
                            }

                        }elseif($transaction['status'] == 'หลุด'){

                            $new_transaction = new Transaction();
                            $new_transaction->agreement_number = $transaction['agreement_number'];
                            $new_transaction->product_id = $transaction['product_id'];
                            $new_transaction->start_date = $this->convertToCommonEraSQL($transaction['start_date']);
                            $new_transaction->end_date = $this->convertToCommonEraSQL($transaction['end_date']);
                            $new_transaction->status = $transaction['status'];
                            $new_transaction->note = $transaction['note'];
                            $new_transaction->uuid = $transaction['uuid'];
                            $new_transaction->related_uuid = $transaction['related_uuid'];
                            $new_transaction->save();

                            $find_uuid = Account::findFirstByUuid($transaction['uuid']);
                            if ($find_uuid == NULL) {
                                $new_account = new Account();
                                $new_account->uuid = $transaction['uuid'];
                                $new_account->principal = abs($transaction['principal']);
                                $new_account->value = abs($transaction['value']);
                                $new_account->transaction_date = $this->convertToCommonEraSQL(($transaction['transaction_date']));
                                $new_account->transaction_time = $this->convertToTimeSQL($transaction['transaction_time']);
                                $new_account->save();
                            }

                        }elseif($transaction['status'] == 'ตั้งขาย'){

                            $new_transaction = new Transaction();
                            $new_transaction->agreement_number = $transaction['agreement_number'];
                            $new_transaction->product_id = $transaction['product_id'];
                            $new_transaction->start_date = $this->convertToCommonEraSQL($transaction['start_date']);
                            $new_transaction->end_date = $this->convertToCommonEraSQL($transaction['end_date']);
                            $new_transaction->status = $transaction['status'];
                            $new_transaction->note = $transaction['note'];
                            $new_transaction->uuid = $transaction['uuid'];
                            $new_transaction->related_uuid = $transaction['related_uuid'];
                            $new_transaction->save();

                            $find_uuid = Account::findFirstByUuid($transaction['uuid']);
                            if ($find_uuid == NULL) {
                                $new_account = new Account();
                                $new_account->uuid = $transaction['uuid'];
                                $new_account->principal = abs($transaction['principal']);
                                $new_account->value = abs($transaction['value']);
                                $new_account->transaction_date = $this->convertToCommonEraSQL(($transaction['transaction_date']));
                                $new_account->transaction_time = $this->convertToTimeSQL($transaction['transaction_time']);
                                $new_account->save();
                            }

                        }else{

                            $new_transaction = new Transaction();
                            $new_transaction->agreement_number = $transaction['agreement_number'];
                            $new_transaction->product_id = $transaction['product_id'];
                            $new_transaction->start_date = $this->convertToCommonEraSQL($transaction['start_date']);
                            $new_transaction->end_date = $this->convertToCommonEraSQL($transaction['end_date']);
                            $new_transaction->status = $transaction['status'];
                            $new_transaction->note = $transaction['note'];
                            $new_transaction->uuid = $transaction['uuid'];
                            $new_transaction->related_uuid = $transaction['related_uuid'];
                            $new_transaction->save();

                            $find_uuid = Account::findFirstByUuid($transaction['uuid']);
                            if ($find_uuid == NULL) {
                                $new_account = new Account();
                                $new_account->uuid = $transaction['uuid'];
                                $new_account->principal = abs($transaction['principal']);
                                $new_account->value = abs($transaction['value']);
                                $new_account->transaction_date = $this->convertToCommonEraSQL(($transaction['transaction_date']));
                                $new_account->transaction_time = $this->convertToTimeSQL($transaction['transaction_time']);
                                $new_account->save();
                            }

                        }

                    }else{ // * Common transaction

                        // * ถ้า transaction สุดท้าย = ต่อดอก , ต่อดอกทั้งหมด , เพิ่มเงิน , ลดต้น ให่เพิ่ม transaction ว่าง
                        if($transaction['status'] == 'ต่อดอก' || $transaction['status'] == 'ต่อดอกทั้งหมด'){

                            $new_transaction = new Transaction();
                            $new_transaction->agreement_number = $transaction['agreement_number'];
                            $new_transaction->product_id = $transaction['product_id'];
                            $new_transaction->start_date = $this->convertToCommonEraSQL($transaction['start_date']);
                            $new_transaction->end_date = $this->convertToCommonEraSQL($transaction['end_date']);
                            if($transaction['status'] == 'ต่อดอกทั้งหมด'){
                                $new_transaction->status = $transaction['status'];
                            }else{
                                $new_transaction->status = 'ต่อดอกชิ้นเดียว';
                            }
                            $new_transaction->note = $transaction['note'];
                            $new_transaction->uuid = $transaction['uuid'];
                            $new_transaction->related_uuid = $transaction['related_uuid'];
                            $new_transaction->save();

                            $find_uuid = Account::findFirstByUuid($transaction['uuid']);
                            if ($find_uuid == NULL) {
                                $new_account = new Account();
                                $new_account->uuid = $transaction['uuid'];
                                $new_account->principal = abs($transaction['principal']);
                                $new_account->value = abs($transaction['value']);
                                $new_account->transaction_date = $this->convertToCommonEraSQL(($transaction['transaction_date']));
                                $new_account->transaction_time = $this->convertToTimeSQL($transaction['transaction_time']);
                                $new_account->save();
                            }

                        }elseif($transaction['status'] == 'เพิ่มเงิน'){

                            $new_transaction = new Transaction();
                            $new_transaction->agreement_number = $transaction['agreement_number'];
                            $new_transaction->product_id = $transaction['product_id'];
                            $new_transaction->start_date = $this->convertToCommonEraSQL($transaction['start_date']);
                            $new_transaction->end_date = $this->convertToCommonEraSQL($transaction['end_date']);
                            $new_transaction->status = $transaction['status'];
                            $new_transaction->note = $transaction['note'];
                            $new_transaction->uuid = $transaction['uuid'];
                            $new_transaction->related_uuid = $transaction['related_uuid'];
                            $new_transaction->save();

                            $find_uuid = Account::findFirstByUuid($transaction['uuid']);
                            if ($find_uuid == NULL) {
                                $new_account = new Account();
                                $new_account->uuid = $transaction['uuid'];
                                $new_account->principal = abs($transaction['principal']);
                                $new_account->value = abs($transaction['value']);
                                $new_account->transaction_date = $this->convertToCommonEraSQL(($transaction['transaction_date']));
                                $new_account->transaction_time = $this->convertToTimeSQL($transaction['transaction_time']);
                                $new_account->save();
                            }

                        }elseif($transaction['status'] == 'ลดต้น'){

                            $new_transaction = new Transaction();
                            $new_transaction->agreement_number = $transaction['agreement_number'];
                            $new_transaction->product_id = $transaction['product_id'];
                            $new_transaction->start_date = $this->convertToCommonEraSQL($transaction['start_date']);
                            $new_transaction->end_date = $this->convertToCommonEraSQL($transaction['end_date']);
                            $new_transaction->status = $transaction['status'];
                            $new_transaction->note = $transaction['note'];
                            $new_transaction->uuid = $transaction['uuid'];
                            $new_transaction->related_uuid = $transaction['related_uuid'];
                            $new_transaction->save();

                            $find_uuid = Account::findFirstByUuid($transaction['uuid']);
                            if ($find_uuid == NULL) {
                                $new_account = new Account();
                                $new_account->uuid = $transaction['uuid'];
                                $new_account->principal = abs($transaction['principal']);
                                $new_account->value = abs($transaction['value']);
                                $new_account->transaction_date = $this->convertToCommonEraSQL(($transaction['transaction_date']));
                                $new_account->transaction_time = $this->convertToTimeSQL($transaction['transaction_time']);
                                $new_account->save();
                            }

                        }elseif($transaction['status'] == 'ไถ่คืน' || $transaction['status'] == 'ไถ่คืนทั้งหมด'){

                            $new_transaction = new Transaction();
                            $new_transaction->agreement_number = $transaction['agreement_number'];
                            $new_transaction->product_id = $transaction['product_id'];
                            $new_transaction->start_date = $this->convertToCommonEraSQL($transaction['start_date']);
                            $new_transaction->end_date = $this->convertToCommonEraSQL($transaction['end_date']);
                            if($transaction['status'] == 'ไถ่คืน'){
                                $new_transaction->status = 'ไถ่คืนชิ้นเดียว';
                            }else{
                                $new_transaction->status = $transaction['status'];
                            }
                            $new_transaction->note = $transaction['note'];
                            $new_transaction->uuid = $transaction['uuid'];
                            $new_transaction->related_uuid = $transaction['related_uuid'];
                            $new_transaction->save();

                            $find_uuid = Account::findFirstByUuid($transaction['uuid']);
                            if ($find_uuid == NULL) {
                                $new_account = new Account();
                                $new_account->uuid = $transaction['uuid'];
                                $new_account->principal = abs($transaction['principal']);
                                $new_account->value = abs($transaction['value']);
                                $new_account->transaction_date = $this->convertToCommonEraSQL(($transaction['transaction_date']));
                                $new_account->transaction_time = $this->convertToTimeSQL($transaction['transaction_time']);
                                $new_account->save();
                            }

                        }elseif($transaction['status'] == 'หลุด'){

                            $new_transaction = new Transaction();
                            $new_transaction->agreement_number = $transaction['agreement_number'];
                            $new_transaction->product_id = $transaction['product_id'];
                            $new_transaction->start_date = $this->convertToCommonEraSQL($transaction['start_date']);
                            $new_transaction->end_date = $this->convertToCommonEraSQL($transaction['end_date']);
                            $new_transaction->status = $transaction['status'];
                            $new_transaction->note = $transaction['note'];
                            $new_transaction->uuid = $transaction['uuid'];
                            $new_transaction->related_uuid = $transaction['related_uuid'];
                            $new_transaction->active = 'F';
                            $new_transaction->save();

                            $find_uuid = Account::findFirstByUuid($transaction['uuid']);
                            if ($find_uuid == NULL) {
                                $new_account = new Account();
                                $new_account->uuid = $transaction['uuid'];
                                $new_account->principal = abs($transaction['principal']);
                                $new_account->value = abs($transaction['value']);
                                $new_account->transaction_date = $this->convertToCommonEraSQL(($transaction['transaction_date']));
                                $new_account->transaction_time = $this->convertToTimeSQL($transaction['transaction_time']);
                                $new_account->save();
                            }

                        }elseif($transaction['status'] == 'ตั้งขาย'){

                            $new_transaction = new Transaction();
                            $new_transaction->agreement_number = $transaction['agreement_number'];
                            $new_transaction->product_id = $transaction['product_id'];
                            $new_transaction->start_date = $this->convertToCommonEraSQL($transaction['start_date']);
                            $new_transaction->end_date = $this->convertToCommonEraSQL($transaction['end_date']);
                            $new_transaction->status = $transaction['status'];
                            $new_transaction->note = $transaction['note'];
                            $new_transaction->uuid = $transaction['uuid'];
                            $new_transaction->related_uuid = $transaction['related_uuid'];
                            $new_transaction->active = 'F';
                            $new_transaction->save();

                            $find_uuid = Account::findFirstByUuid($transaction['uuid']);
                            if ($find_uuid == NULL) {
                                $new_account = new Account();
                                $new_account->uuid = $transaction['uuid'];
                                $new_account->principal = abs($transaction['principal']);
                                $new_account->value = abs($transaction['value']);
                                $new_account->transaction_date = $this->convertToCommonEraSQL(($transaction['transaction_date']));
                                $new_account->transaction_time = $this->convertToTimeSQL($transaction['transaction_time']);
                                $new_account->save();
                            }

                        }else{

                            $new_transaction = new Transaction();
                            $new_transaction->agreement_number = $transaction['agreement_number'];
                            $new_transaction->product_id = $transaction['product_id'];
                            $new_transaction->start_date = $this->convertToCommonEraSQL($transaction['start_date']);
                            $new_transaction->end_date = $this->convertToCommonEraSQL($transaction['end_date']);
                            $new_transaction->status = $transaction['status'];
                            $new_transaction->note = $transaction['note'];
                            $new_transaction->uuid = $transaction['uuid'];
                            $new_transaction->related_uuid = $transaction['related_uuid'];
                            $new_transaction->save();

                            $find_uuid = Account::findFirstByUuid($transaction['uuid']);
                            if ($find_uuid == NULL) {
                                $new_account = new Account();
                                $new_account->uuid = $transaction['uuid'];
                                $new_account->principal = abs($transaction['principal']);
                                $new_account->value = abs($transaction['value']);
                                $new_account->transaction_date = $this->convertToCommonEraSQL(($transaction['transaction_date']));
                                $new_account->transaction_time = $this->convertToTimeSQL($transaction['transaction_time']);
                                $new_account->save();
                            }

                        }

                    }
                    $i++;
                }
            }
        }
    }
}
