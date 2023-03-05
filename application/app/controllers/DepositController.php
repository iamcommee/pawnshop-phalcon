<?php
use Phalcon\Crypt;
use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;
use Phalcon\Mvc\View;

class DepositController extends ControllerBase
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
        $this->tag->prependTitle("รายการมัดจำ ");
        $this->assets->addJs("js/deposit/index.js");
    }

    // * OK
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
                WHERE T.status LIKE '%มัดจำ%' AND T.active = 'T' 
                GROUP BY P.product_id "
        );

        foreach ($products as $product) {

                if($this->session->get("role") == 'administrator'){
                    $link = '<a href="../payment/search/'.$product->P->agreement_number.'" target="_blank">'.$product->P->agreement_number.'</a>';
                }else{
                    $link = $product->P->agreement_number;
                }
                
                $deposit_transactions = $this->modelsManager->executeQuery(
                    "SELECT T.* , A.*
                        FROM transaction AS T
                        JOIN account AS A
                        ON T.uuid = A.uuid
                        WHERE T.product_id = :product_id: AND T.status = :status:",
                        [
                            'product_id'=> $product->P->product_id,
                            'status'    => 'มัดจำ',
                        ]
                );
    
                $sum_deposit_value = 0;
                unset($deposit_transaction_array);
    
                foreach ($deposit_transactions as $deposit_transaction){
                    $deposit_transaction_array[] = array
                    (
                        'account_id'             => $deposit_transaction->A->account_id,
                        'deposit_value'    => number_format($deposit_transaction->A->value),
                        'transaction_date' => $this->convertToBuddhistEra($deposit_transaction->A->transaction_date),
                        'note'             => $deposit_transaction->T->note,
                    );
                    $sum_deposit_value = $sum_deposit_value + $deposit_transaction->A->value;
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
                    'sale_value'    => number_format($deposit_transaction->A->principal),
                    'sum_deposit_value' => (string)number_format($sum_deposit_value),
                    'transaction_date'   => $this->convertToBuddhistEra($deposit_transaction->A->transaction_date),
                    'deposit_transactions'   => $deposit_transaction_array,
                );
        
        }

        $data_json = json_encode($data);
        echo $data_json;   
    }

    // * OK
    public function getDepositInfoAction($account_id)
    {
        $this->view->disable();
        $transaction = $this->modelsManager->executeQuery(
            'SELECT T.* , A.*
                FROM transaction AS T
                JOIN account AS A
                ON T.uuid = A.uuid
                WHERE A.account_id = :account_id:',
                [
                    'account_id'=> $account_id
                ]
            )->getFirst();

        $deposit_array = array(
            'account_id'       => $transaction->A->account_id,
            'transaction_date' => $this->convertToCommonEra($transaction->A->transaction_date),
            'transaction_time' => $transaction->A->transaction_time,
            'deposit_value'    => number_format($transaction->A->value),
            'note'             => $transaction->T->note,
        );
        $deposit_json = json_encode($deposit_array);
        echo $deposit_json;
    }

    // * OK
    public function editDepositTransactionAction()
    {
        $this->view->disable();
        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();
            if ($account_id = $this->request->get('account_id'))
            {

                $transaction_date =  $this->request->get('transaction_date');
                $deposit_value = $this->convertToNumber($this->request->get('deposit_value'));
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
                $account->transaction_date = $this->convertToCommonEraSQL($transaction_date);
                $account->value = $deposit_value;
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
    public function deleteDepositTransactionAction()
    {
        $this->view->disable();
        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();
            if ($account_id = $this->request->get('account_id')){

                $account = Account::findFirst($account_id);
                foreach($account->transactionDetail as $transaction){
                    $update_transaction = Transaction::findFirstByuuid($transaction->related_uuid);
                    if($update_transaction->status == 'มัดจำ'){

                    }else{
                        $update_transaction->active = 'T';
                    }
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
                    "SELECT T.* , A.* , P.* , 
                        (   SELECT MAX(A.principal)
                            FROM transaction AS T
                            JOIN account AS A
                            ON T.uuid = A.uuid
                            WHERE T.status LIKE '%มัดจำ%' AND T.product_id = '$product_id'
                            ) AS principle_value
                        FROM transaction AS T
                        JOIN account AS A
                        ON T.uuid = A.uuid
                        JOIN Product AS P
                        ON T.product_id = P.product_id
                        WHERE T.status LIKE :status: AND P.product_id = :product_id:",
                        [
                            'status'    =>  '%มัดจำ%',
                            'product_id'    => $product_id
                        ]
                )->getFirst();
                
                $products[] = array(
                    'agreement_number'  => $product->P->agreement_number,
                    'product_id'        => $product_id,
                    'product_name'      => $product->P->name,
                    'product_brand'     => $product->P->brand,
                    'product_detail'    => $product->P->detail,
                    'product_value'     => $product->P->value,
                    'sale_value'        => $product->principle_value,
                    'principle_value'   => $product->principle_value,
                );
            }
            $product_json =  json_encode($products);

            $owner = Owner::findFirst('1');
    
            $this->view->setVars(
                [
                    "product_json" => $product_json,
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
                            WHERE T.status LIKE '%มัดจำ%' AND T.product_id = '$product_id' AND T.active = 'T' 
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
                    "SELECT T.* , A.* , P.* , SUM(A.value) AS sum_deposit_value ,
                    (   SELECT MAX(A.principal)
                        FROM transaction AS T
                        JOIN account AS A
                        ON T.uuid = A.uuid
                        WHERE T.status LIKE '%มัดจำ%' AND T.product_id = '$product_id'
                        ) AS principle_value
                        FROM transaction AS T
                        JOIN account AS A
                        ON T.uuid = A.uuid
                        JOIN Product AS P
                        ON T.product_id = P.product_id
                        WHERE T.status LIKE :status: AND P.product_id = :product_id:",
                        [
                            'status'    =>  '%มัดจำ%',
                            'product_id'    => $product_id
                        ]
                )->getFirst();
                
                $products[] = array(
                    'agreement_number'  => $product->P->agreement_number,
                    'product_id'        => $product_id,
                    'product_name'      => $product->P->name,
                    'product_brand'     => $product->P->brand,
                    'product_detail'    => $product->P->detail,
                    'product_value'     => $product->P->value,
                    'sum_deposit_value'        => $product->sum_deposit_value,
                    'principle_value'   => $product->principle_value,
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
                $sum_deposit_value = $this->request->getPost('sum_deposit_value');
                $deposit_value = $this->request->getPost('deposit_value');
                $note = $this->request->getPost('note');
                $sum_sale_value = 0;
                $current_sum_deposit_value = 0;

                for ($i = 0 ; $i < count($product_id_array) ; $i++){

                    $uuid = $this->getUuid();

                    $product_id = $product_id_array[$i];
                    
                    $transaction = $this->modelsManager->executeQuery(
                        "SELECT T.*
                            FROM transaction AS T 
                            WHERE T.status LIKE '%มัดจำ%' AND T.product_id = '$product_id' AND T.active = 'T' 
                        ")->getFirst();
                    
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
                        'sum_deposit_value' => $sum_deposit_value[$i],
                        'deposit_value'     => $deposit_value[$i],
                        'current_sum_deposit_value' => $sum_deposit_value[$i] + $deposit_value[$i]
                    );

                    $sum_sale_value = $sum_sale_value + $sale_value[$i];
                    $current_sum_deposit_value = $current_sum_deposit_value + ($sum_deposit_value[$i] + $deposit_value[$i]);
                }

                $product_json =  json_encode($products);

                $owner = Owner::findFirst('1');
        
                $this->view->setVars(
                    [
                        "product_json"   =>  $product_json,
                        "transaction_date" => $transaction_date,
                        "sum_sale_value" =>  $sum_sale_value,
                        "sum_deposit_value" => $sum_deposit_value,
                        "current_sum_deposit_value" => $current_sum_deposit_value,
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
