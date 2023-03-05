<?php
use Phalcon\Crypt;
use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;
use Phalcon\Mvc\View;

class SaleController extends ControllerBase
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
        $this->tag->prependTitle("รายการตั้งขาย ");
        $this->assets->addJs("js/sale/index.js");
    }

    public function server_processingAction()
    {
        $this->view->disable();
        $data = array();
        $products = $this->modelsManager->executeQuery(
            "SELECT P.* , T.* , A.*
                FROM product AS P
                JOIN transaction AS T
                ON P.product_id = T.product_id 
                JOIN account AS A 
                ON T.uuid = A.uuid 
                WHERE P.related_product IS NULL AND T.active = 'T' AND (T.status = 'ตั้งขาย' OR T.status = 'ตั้งขายกรณีพิเศษ') "
        );

        foreach ($products as $product) {

            if($this->session->get("role") == 'administrator'){
                $link = '<a href="../payment/search/'.$product->P->agreement_number.'" target="_blank">'.$product->P->agreement_number.'</a>';
            }else{
                $link = $product->P->agreement_number;
            }

            if($product->T->status == 'ตั้งขาย'){ 
                $data[] = array
                (
                    'link' => $link,
                    'agreement_number' => $product->P->agreement_number,
                    'account_id'   => $product->A->account_id,
                    'product_id'   => $product->P->product_id,
                    'product_name' => $product->P->name,
                    'product_brand' => $product->P->brand,
                    'product_detail' => $product->P->detail,
                    'product_value' => number_format($product->P->value),
                    'sale_value'    => number_format($product->A->value),
                    'transaction_date' => $this->convertToBuddhistEra($product->A->transaction_date).' '.$product->A->transaction_time,
                    'note'          => $product->T->note,
                    'tag'           => 'ตั้งขาย',
                );
            }elseif($product->T->status == 'ตั้งขายกรณีพิเศษ'){
                $data[] = array
                (
                    'link' => $link,
                    'agreement_number' => $product->P->agreement_number,
                    'account_id'   => $product->A->account_id,
                    'product_id'   => $product->P->product_id,
                    'product_name' => $product->P->name,
                    'product_brand' => $product->P->brand,
                    'product_detail' => $product->P->detail,
                    'product_value' => number_format($product->P->value),
                    'sale_value'    => number_format($product->A->value),
                    'transaction_date' => $this->convertToBuddhistEra($product->A->transaction_date).' '.$product->A->transaction_time,
                    'note'          => $product->T->note,
                    'tag'           => 'ตั้งขายกรณีพิเศษ',
                );
            }
        }

        $data_json = json_encode($data);
        echo $data_json;   
    }

    // * OK
    public function editSaleTransactionAction()
    {
        $this->view->disable();

        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($account_id = $this->request->get('account_id')) {

                $sale_value = $this->convertToNumber($this->request->get('sale_value'));
                $note = $this->request->get('note');

                $account = Account::findFirst($account_id);
                foreach($account->transactionDetail as $transaction){
                    $transaction->note = $note;
                    if($transaction->save() === false){
                        $transaction_->rollback(
                            'ขออภัยเกิดผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                        );
                    }
                }
                $account->value = $this->convertToNumber($sale_value);
                if ($account->save() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลประวัติการทำรายการ ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }
                $transaction_ = $manager->commit();

            }

        } catch (TxFailed $error) {
            echo $error->getMessage();
        }
    }

    // * OK
    public function deleteSaleTransactionAction()
    {
        $this->view->disable();

        try {
            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($account_id = $this->request->get('account_id')) {
                
                $account = Account::findFirst($account_id);
                foreach($account->transactionDetail as $transaction){
                    $update_transaction = Transaction::findFirstByuuid($transaction->related_uuid);
                    $update_transaction->active = 'T';
                    if($update_transaction->save() === false){
                         $transaction_->rollback(
                             'ขออภัยเกิดผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                         );
                    }
                }
                if ($account->delete() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลประวัติการทำรายการ ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }
                
                $transaction_ = $manager->commit();
            }

        } catch (TxFailed $error) {
            echo $error->getMessage();
        }
    }

    // * OK
    public function separateSaleAction()
    {
        $this->view->disable();

        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($product_id = $this->request->get('product_id')) {

                $uuid = $this->getUuid();

                $transaction_date =  $this->request->get('transaction_date');
                $transaction_time =  $this->request->get('transaction_time');
                $product_name = $this->request->get('product_name');
                $product_brand = $this->request->get('product_brand');
                $product_detail = $this->request->get('product_detail');
                $product_value = $this->convertToNumber($this->request->get('product_value'));
                $sale_value = $this->convertToNumber($this->request->get('sale_value'));
                $note = $this->request->get('note');
            
                // หา max_start_date และ max_end_date
                $transaction = $this->modelsManager->executeQuery(
                    "SELECT MAX(T.start_date) AS max_start_date , MAX(T.end_date) AS max_end_date , T.agreement_number
                        FROM transaction AS T
                        WHERE T.product_id = '$product_id'
                        ORDER BY T.transaction_id DESC "
                )->getFirst();

                // นับจำนวนสินค้าที่แยกขาย
                $product = Product::findFirst($product_id);
                $product->related_product = $product->related_product + 1;
                if ($product->save() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลสินค้า ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                // แยกขาย transaction
                $new_transaction = new Transaction();
                $new_transaction->agreement_number = $transaction->agreement_number;
                $new_transaction->product_id = $product_id;
                $new_transaction->start_date = $transaction->max_start_date;
                $new_transaction->end_date = $transaction->max_end_date;
                $new_transaction->status = "แยกขาย";
                $new_transaction->uuid = $uuid;
                if ($new_transaction->save() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                // บันทึกประวัติการทำงาน
                $account = new Account();
                $account->uuid = $uuid;
                $account->value = $product_value;
                $account->transaction_date = $this->convertToCommonEraSQL($transaction_date);
                $account->transaction_time = $transaction_time;
                if ($account->save() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลประวัติการทำรายการ ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                $uuid_ = $this->getUuid();

                // สินค้าแยกขาย
                $new_product = new Product();
                $new_product->agreement_number = $transaction->agreement_number;
                $new_product->name = $product_name;
                $new_product->brand = $product_brand;
                $new_product->detail = $product_detail;
                $new_product->value = $product_value;
                $new_product->interest = $this->calculateInterest($product_value);
                if ($new_product->save() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลสินค้า ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                $new_transaction = new Transaction();
                $new_transaction->agreement_number = $transaction->agreement_number;
                $new_transaction->product_id = $new_product->product_id; 
                $new_transaction->start_date = $transaction->max_start_date;
                $new_transaction->end_date = $transaction->max_end_date;
                $new_transaction->status = 'ตั้งขายกรณีพิเศษ';
                $new_transaction->note = $note;
                $new_transaction->uuid = $uuid_;
                $new_transaction->related_uuid =  $uuid;
                if ($new_transaction->save() === false) {
                    $new_transaction_->rollback(
                        'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                $new_account = new Account();
                $new_account->value = $sale_value;
                $new_account->transaction_date = $this->getCurrentDate();
                $new_account->transaction_time = $this->getCurrentTime();
                $new_account->uuid = $uuid_;
                if ($new_account->save() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลประวัติการทำรายการ ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                $transaction_ = $manager->commit();
            }
        } catch (TxFailed $error) {
            echo $error->getMessage();
        }
    }

    // * OK
    public function editSeparateSaleTransactionAction()
    {
        $this->view->disable();

        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($account_id = $this->request->get('account_id')) {

                $sale_value = $this->convertToNumber(($this->request->get('sale_value')));
                $note = $this->request->get('note');

                $account = Account::findFirst($account_id);
                $account->value = $sale_value;
                foreach ($account->transactionDetail as $transactionDetail) {
                    $transactionDetail->note = $note;
                    if ($transactionDetail->save() == false) {
                        $transaction_->rollback(
                            'ขออภัย การทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                        );
                    }
                }
                if ($account->save() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลประวัติการทำรายการ ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }
                $transaction_ = $manager->commit();

            }

        } catch (TxFailed $error) {
            echo $error->getMessage();
        }
    }

    // * OK
    public function deleteSeparateSaleTransactionAction()
    {
        $this->view->disable();

        try {
            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($account_id = $this->request->get('account_id')) {
                
                $account = Account::findFirst($account_id);
                foreach($account->transactionDetail as $transactionDetail){
                    $separate_account = Transaction::findFirstByUuid($transactionDetail->related_uuid);
                    $product = $transactionDetail->product;
                }
                $main_product = $separate_account->product;
                
                if ($product->delete() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลประวัติการทำรายการ ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                } else {
                    if ($separate_account->delete() === false) {
                        $transaction_->rollback(
                            'ขออภัย ข้อมูลประวัติการทำรายการ ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                        );
                    }
                    if ($account->delete() === false) {
                        $transaction_->rollback(
                            'ขออภัย ข้อมูลประวัติการทำรายการ ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                        );
                    }
                    if($main_product->related_product == 1){
                        $main_product->related_product = NULL;
                    }else{
                        $main_product->related_product = $main_product->related_product - 1;
                    }
                    if ($main_product->save() === false) {
                        $transaction_->rollback(
                            'ขออภัย ข้อมูลสินค้า ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                        );
                    }
                }

                $transaction_ = $manager->commit();
            }

        } catch (TxFailed $error) {
            echo $error->getMessage();
        }
    }

    //  * OK
    public function createSaleReceiptAction()
    {
        $this->view->setRenderLevel(
            View::LEVEL_ACTION_VIEW
        );

        $this->assets->addCss("css/paper.css");
        $this->assets->addCss('bootstrap/css/bootstrap.css'); 
        $this->assets->addCss('css/style/style.css');
        $this->assets->addCss('fontawesome/css/all.css');

        $this->assets->addJs('jquery/jquery-3.3.1.js');
        $this->assets->addCss('bootstrap-datepicker/css/datepicker.css');
        $this->assets->addJs('bootstrap-datepicker/js/datepicker.js');
        $this->assets->addJs('bootstrap-datepicker/js/i18n/datepicker.th-TH.js');

        if($sale_list_json = $this->request->get('sale_list_json')){   

            $sale_list = json_decode($sale_list_json);

            foreach($sale_list as $product_id){

                $product = $this->modelsManager->executeQuery(
                    "SELECT T.* , A.* , P.*
                        FROM transaction as T
                        JOIN account as A
                        ON T.uuid = A.uuid
                        JOIN product as P
                        ON T.product_id = P.product_id
                        WHERE T.product_id = '$product_id' AND T.status LIKE '%ตั้งขาย%' AND T.active = 'T' "
                )->getFirst();
                
                $products[] = array(
                    'agreement_number'  => $product->P->agreement_number,
                    'product_id'        => $product_id,
                    'product_name'      => $product->P->name,
                    'product_brand'     => $product->P->brand,
                    'product_detail'    => $product->P->detail,
                    'product_value'     => $product->P->value,
                    'sale_value'        => $product->A->value,
                );
            }
            $product_json =  json_encode($products);

            $owner = Owner::findFirst('1');
    
            $this->view->setVars(
                [
                    "product_json"   => $product_json,
                    "storename"      =>  $owner->storename,
                    "firstname"      =>  $owner->firstname,
                    "lastname"       =>  $owner->lastname,
                    "tel"            =>  $owner->tel,
                    "m_tel"          =>  $owner->m_tel,
                    "house_no"       =>  $owner->house_no,
                    "village_no"     =>  $owner->village_no,
                    "lane"           =>  $owner->lane,
                    "road"           =>  $owner->road,
                    "sub_district"   =>  $owner->sub_district,
                    "district"       =>  $owner->district,
                    "province"       =>  $owner->province,
                    "zip_code"       =>  $owner->zip_code,
                    "line"           =>  $owner->line
                ]
            );
        }   
    }

    // * OK
    public function printSaleReceiptAction()
    {
        $this->view->setRenderLevel(
            View::LEVEL_ACTION_VIEW
        );
        
        $this->assets->addCss("css/paper.css");
        $this->assets->addCss('bootstrap/css/bootstrap-custom.css'); 
        $this->assets->addCss('css/style/main.css');

        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            if($product_id_array = $this->request->getPost('product_id')){

                $transaction_date = $this->request->getPost('transaction_date');
                $sale_value = $this->request->getPost('sale_value');
                $note = $this->request->getPost('note');
                $sum_sale_value = 0;

                for ($i = 0 ; $i < count($product_id_array) ; $i++){

                    $uuid = $this->getUuid();

                    $product_id = $product_id_array[$i];
                    
                    $transaction = $this->modelsManager->executeQuery(
                        "SELECT T.*
                            FROM transaction AS T 
                            WHERE T.status LIKE '%ตั้งขาย%' AND T.product_id = '$product_id' AND T.active = 'T' 
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
                    $new_transaction->status = 'ขายแล้ว';
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
                    $account->value = $sale_value[$i];
                    $account->transaction_date = $this->convertToCommonEraSQL($transaction_date);
                    if ($account->save() === false) {
                        $transaction_->rollback(
                            'ขออภัย ข้อมูลประวัติการทำรายการ ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                        );
                    }

                    $product = Product::findFirst($product_id);
                    $products[] = array(
                        'agreement_number'  => $product->agreement_number,
                        'product_name'      => $product->name,
                        'product_brand'     => $product->brand,
                        'product_detail'    => $product->detail,
                        'product_value'     => $product->value,
                        'sale_value'        => $sale_value[$i],
                    );

                    $sum_sale_value = $sum_sale_value + $sale_value[$i];
                }

                $product_json =  json_encode($products);

                $owner = Owner::findFirst('1');
        
                $this->view->setVars(
                    [
                        "product_json"   =>  $product_json,
                        "transaction_date" => $transaction_date,
                        "sum_sale_value" =>  $sum_sale_value,
                        "note"           =>  $note,
                        "storename"      =>  $owner->storename,
                        "firstname"      =>  $owner->firstname,
                        "lastname"       =>  $owner->lastname,
                        "tel"            =>  $owner->tel,
                        "m_tel"          =>  $owner->m_tel,
                        "house_no"       =>  $owner->house_no,
                        "village_no"     =>  $owner->village_no,
                        "lane"           =>  $owner->lane,
                        "road"           =>  $owner->road,
                        "sub_district"   =>  $owner->sub_district,
                        "district"       =>  $owner->district,
                        "province"       =>  $owner->province,
                        "zip_code"       =>  $owner->zip_code,
                        "line"           =>  $owner->line
                    ]
                );
                
                $transaction_ = $manager->commit();
            }
        } catch (TxFailed $error) {
            echo $error->getMessage();
        }
    }

    // * OK
    public function createDepositReceiptAction()
    {
        $this->view->setRenderLevel(
            View::LEVEL_ACTION_VIEW
        );

        $this->assets->addCss("css/paper.css");
        $this->assets->addCss('bootstrap/css/bootstrap.css'); 
        $this->assets->addCss('css/style/style.css');
        $this->assets->addCss('fontawesome/css/all.css');

        $this->assets->addJs('jquery/jquery-3.3.1.js');
        $this->assets->addCss('bootstrap-datepicker/css/datepicker.css');
        $this->assets->addJs('bootstrap-datepicker/js/datepicker.js');
        $this->assets->addJs('bootstrap-datepicker/js/i18n/datepicker.th-TH.js');

        if($deposit_list_json = $this->request->get('deposit_list_json')){   
            $deposit_list = json_decode($deposit_list_json);
            foreach($deposit_list as $product_id){

                $product = $this->modelsManager->executeQuery(
                    "SELECT T.* , A.* , P.*
                        FROM transaction as T
                        JOIN account as A
                        ON T.uuid = A.uuid
                        JOIN product as P
                        ON T.product_id = P.product_id
                        WHERE T.product_id = '$product_id' AND T.status LIKE '%ตั้งขาย%' AND T.active = 'T' "
                )->getFirst();
                
                $products[] = array(
                    'agreement_number'  => $product->P->agreement_number,
                    'product_id'        => $product_id,
                    'product_name'      => $product->P->name,
                    'product_brand'     => $product->P->brand,
                    'product_detail'    => $product->P->detail,
                    'product_value'     => $product->P->value,
                    'sale_value'        => $product->A->value,
                );
            }
            $product_json =  json_encode($products);

            $owner = Owner::findFirst('1');
    
            $this->view->setVars(
                [
                    "product_json"   => $product_json,
                    "storename"      =>  $owner->storename,
                    "firstname"      =>  $owner->firstname,
                    "lastname"       =>  $owner->lastname,
                    "tel"            =>  $owner->tel,
                    "m_tel"          =>  $owner->m_tel,
                    "house_no"       =>  $owner->house_no,
                    "village_no"     =>  $owner->village_no,
                    "lane"           =>  $owner->lane,
                    "road"           =>  $owner->road,
                    "sub_district"   =>  $owner->sub_district,
                    "district"       =>  $owner->district,
                    "province"       =>  $owner->province,
                    "zip_code"       =>  $owner->zip_code,
                    "line"           =>  $owner->line
                ]
            );
        }   
    }

    // * OK
    public function printDepositReceiptAction()
    {
        $this->view->setRenderLevel(
            View::LEVEL_ACTION_VIEW
        );
        
        $this->assets->addCss("css/paper.css");
        $this->assets->addCss('bootstrap/css/bootstrap-custom.css'); 
        $this->assets->addCss('css/style/main.css');

        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            if($product_id_array = $this->request->getPost('product_id')){

                $transaction_date = $this->request->getPost('transaction_date');
                $sale_value = $this->request->getPost('sale_value');
                $deposit_value = $this->request->getPost('deposit_value');
                $note = $this->request->getPost('note');
                $sum_sale_value = 0;
                $sum_deposit_value = 0;

                for ($i = 0 ; $i < count($product_id_array) ; $i++){

                    $uuid = $this->getUuid();

                    $product_id = $product_id_array[$i];
                    
                    $transaction = $this->modelsManager->executeQuery(
                        "SELECT T.*
                            FROM transaction AS T 
                            WHERE T.status LIKE '%ตั้งขาย%' AND T.product_id = '$product_id' AND T.active = 'T' 
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
                    $new_transaction->status = 'มัดจำ';
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
                    $account->principal = $sale_value[$i];
                    $account->value = $deposit_value[$i];
                    $account->transaction_date = $this->convertToCommonEraSQL($transaction_date);
                    if ($account->save() === false) {
                        $transaction_->rollback(
                            'ขออภัย ข้อมูลประวัติการทำรายการ ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                        );
                    }

                    $product = Product::findFirst($product_id);
                    $products[] = array(
                        'agreement_number'  => $product->agreement_number,
                        'product_name'      => $product->name,
                        'product_brand'     => $product->brand,
                        'product_detail'    => $product->detail,
                        'product_value'     => $product->value,
                        'sale_value'        => $sale_value[$i],
                        'deposit_value'     => $deposit_value[$i],
                    );

                    $sum_sale_value = $sum_sale_value + $sale_value[$i];
                    $sum_deposit_value = $sum_deposit_value + $deposit_value[$i];
                }

                $product_json =  json_encode($products);

                $owner = Owner::findFirst('1');
        
                $this->view->setVars(
                    [
                        "product_json"   =>  $product_json,
                        "transaction_date" => $transaction_date,
                        "sum_sale_value" =>  $sum_sale_value,
                        "sum_deposit_value" => $sum_deposit_value,
                        "note"           =>  $note,
                        "storename"      =>  $owner->storename,
                        "firstname"      =>  $owner->firstname,
                        "lastname"       =>  $owner->lastname,
                        "tel"            =>  $owner->tel,
                        "m_tel"          =>  $owner->m_tel,
                        "house_no"       =>  $owner->house_no,
                        "village_no"     =>  $owner->village_no,
                        "lane"           =>  $owner->lane,
                        "road"           =>  $owner->road,
                        "sub_district"   =>  $owner->sub_district,
                        "district"       =>  $owner->district,
                        "province"       =>  $owner->province,
                        "zip_code"       =>  $owner->zip_code,
                        "line"           =>  $owner->line
                    ]
                );
                
                $transaction_ = $manager->commit();
            }
        } catch (TxFailed $error) {
            echo $error->getMessage();
        }
    }

}
