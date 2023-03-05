<?php
use Phalcon\Crypt;
use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;
use Phalcon\Mvc\View;

class PaymentController extends ControllerBase
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
        $this->view->setRenderLevel(
            View::LEVEL_ACTION_VIEW
        );
          
        $this->assets->addJs('jquery/jquery-3.3.1.js');
        $this->assets->addCss('jquery-ui/jquery-ui.css'); 
        $this->assets->addJs('jquery-ui/jquery-ui.js');
        $this->assets->addCss('bootstrap/css/bootstrap.css');  
        $this->assets->addCss('css/style/style.css');  
        $this->assets->addJs("js/payment/index.js");
        $this->tag->setTitle("| ประเสริฐรัตน์บริการ");
        $this->tag->prependTitle("ต่อดอก/ไถ่คืน ");
    }

    public function processAction()
    {
        $number = $this->request->getPost('number');
        if (strlen($number) <= 10) {
            header("Location: search/$number ");
        } else {
            $customer = Customer::findFirstByidcard($number);
            if ($customer) {
                header("Location: ../customer/search/$number ");
            } else {
                $this->flashSession->error("ไม่พบ $number ในระบบ");
                return $this->response->redirect("payment/");
            }
        }
    }

    // * OK
    public function searchAction($agreement_number)
    {
        // $this->view->disable();
        $this->view->setMainView('desktop-layout');
        $this->tag->setTitle("| ประเสริฐรัตน์บริการ");
        $this->tag->prependTitle("รายละเอียดสัญญา : $agreement_number ");
        $this->assets->addJs("js/payment/search.js");

        $agreement = Agreement::findFirstByagreement_number($agreement_number);
        if ($agreement) {

            $agreement = Agreement::findFirstByagreement_number($agreement_number);
            $agreement_value = $agreement->value; // * เงินทั้งหมดในสัญญา
            $agreement_interest = $agreement->interest; // * ดอกเบี้ยทั้งหมดในสัญญา

            $customer = $agreement->customer;
            $customer_array[] = array(
                'idcard' => $customer->idcard,
                'firstname' => $customer->firstname,
                'lastname' => $customer->lastname,
                'image' => $customer->image,
            );
            $customer_json = json_encode($customer_array);

            $products = Product::findByagreement_number($agreement_number);
            $i = 1;
            $j = 1;
            $total_product_value = 0;

            // * ค้นหาวันที่ล่าสุด
            $last_ = $this->modelsManager->executeQuery(
                "SELECT MAX(T.start_date) AS last_start_date , MAX(T.end_date) AS last_end_date
                    FROM transaction AS T
                    WHERE T.agreement_number = '$agreement_number'
                ")->getFirst();

            if($this->getMonth($last_->last_end_date) == '01' && $this->getMonth($this->getNextMonth($last_->last_end_date)) == '03'){
                $m = '02';
                $y = $this->getYear($last_->last_end_date);
                $d = $this->getNumberDays("{$y}-02");
                $last_end_date = $last_->last_end_date;
                $next_end_date = $d.'/'.$m.'/'.$y;
            } else if($this->getMonth($last_->last_end_date) == '03' && $this->getMonth($this->getNextMonth($last_->last_end_date)) == '05'){
                $d = '30';
                $m = '04';
                $y = $this->getYear($last_->last_end_date);

                $last_end_date = $last_->last_end_date;
                $next_end_date = $d.'/'.$m.'/'.$y;
            } else if($this->getMonth($last_->last_end_date) == '05' && $this->getMonth($this->getNextMonth($last_->last_end_date)) == '07'){
                $d = '30';
                $m = '06';
                $y = $this->getYear($last_->last_end_date);

                $last_end_date = $last_->last_end_date;
                $next_end_date = $d.'/'.$m.'/'.$y;
            } else if($this->getMonth($last_->last_end_date) == '08' && $this->getMonth($this->getNextMonth($last_->last_end_date)) == '10'){
                $d = '30';
                $m = '09';
                $y = $this->getYear($last_->last_end_date);

                $last_end_date = $last_->last_end_date;
                $next_end_date = $d.'/'.$m.'/'.$y;
            } else if($this->getMonth($last_->last_end_date) == '10' && $this->getMonth($this->getNextMonth($last_->last_end_date)) == '12'){
                $d = '30';
                $m = '11';
                $y = $this->getYear($last_->last_end_date);

                $last_end_date = $last_->last_end_date;
                $next_end_date = $d.'/'.$m.'/'.$y;
            } else if ($this->getMonth($last_->last_end_date) == '02'){
                $year = $this->getYear($last_->last_end_date);

                $prev_ = $this->modelsManager->executeQuery(
                    "SELECT T.start_date AS start_date
                    FROM transaction AS T
                    WHERE T.agreement_number = '$agreement_number' AND MONTH(T.start_date) = '1' AND YEAR(T.start_date) = '$year'
                ")->getFirst();

                $d = $this->getDate($prev_->start_date);
                $m = $this->getMonth($this->getNextMonth($last_->last_end_date));
                $y = $this->getYear($last_->last_end_date);

                $last_end_date = $last_->last_end_date;
                $next_end_date = $d.'/'.$m.'/'.$y;
            } else if ($this->getMonth($last_->last_end_date) == '04'){
                $year = $this->getYear($last_->last_end_date);

                $prev_ = $this->modelsManager->executeQuery(
                    "SELECT T.start_date AS start_date
                    FROM transaction AS T
                    WHERE T.agreement_number = '$agreement_number' AND MONTH(T.start_date) = '3' AND YEAR(T.start_date) = '$year'
                ")->getFirst();

                $d = $this->getDate($prev_->start_date);
                $m = $this->getMonth($this->getNextMonth($last_->last_end_date));
                $y = $this->getYear($last_->last_end_date);

                $last_end_date = $last_->last_end_date;
                $next_end_date = $d.'/'.$m.'/'.$y;
            } else if ($this->getMonth($last_->last_end_date) == '06'){
                $year = $this->getYear($last_->last_end_date);

                $prev_ = $this->modelsManager->executeQuery(
                    "SELECT T.start_date AS start_date
                    FROM transaction AS T
                    WHERE T.agreement_number = '$agreement_number' AND MONTH(T.start_date) = '5' AND YEAR(T.start_date) = '$year'
                ")->getFirst();

                $d = $this->getDate($prev_->start_date);
                $m = $this->getMonth($this->getNextMonth($last_->last_end_date));
                $y = $this->getYear($last_->last_end_date);

                $last_end_date = $last_->last_end_date;
                $next_end_date = $d.'/'.$m.'/'.$y;
            } else if ($this->getMonth($last_->last_end_date) == '09'){
                $year = $this->getYear($last_->last_end_date);

                $prev_ = $this->modelsManager->executeQuery(
                    "SELECT T.start_date AS start_date
                    FROM transaction AS T
                    WHERE T.agreement_number = '$agreement_number' AND MONTH(T.start_date) = '8' AND YEAR(T.start_date) = '$year'
                ")->getFirst();

                $d = $this->getDate($prev_->start_date);
                $m = $this->getMonth($this->getNextMonth($last_->last_end_date));
                $y = $this->getYear($last_->last_end_date);

                $last_end_date = $last_->last_end_date;
                $next_end_date = $d.'/'.$m.'/'.$y;
            } else if ($this->getMonth($last_->last_end_date) == '11'){
                $year = $this->getYear($last_->last_end_date);

                $prev_ = $this->modelsManager->executeQuery(
                    "SELECT T.start_date AS start_date
                    FROM transaction AS T
                    WHERE T.agreement_number = '$agreement_number' AND MONTH(T.start_date) = '10' AND YEAR(T.start_date) = '$year'
                ")->getFirst();

                $d = $this->getDate($prev_->start_date);
                $m = $this->getMonth($this->getNextMonth($last_->last_end_date));
                $y = $this->getYear($last_->last_end_date);

                $last_end_date = $last_->last_end_date;
                $next_end_date = $d.'/'.$m.'/'.$y;
            } else{
                $last_end_date = $last_->last_end_date;
                $next_end_date = $this->getNextMonth($last_->last_end_date);
            }
            
            // * เลือกสินค้าในสัญญา
            foreach ($products as $product){
                $transactions = $this->modelsManager->executeQuery(
                    'SELECT T.* , A.*
                    FROM transaction AS T
                    LEFT JOIN account AS A
                    ON T.uuid = A.uuid
                    WHERE T.product_id = :product_id: AND T.active != "S" ',
                    [
                        'product_id' => $product->product_id,
                    ]
                );
                unset($transaction_array);
                // * เลือกข้อมูล transaction ของแต่ละสินค้าเพื่อเก็บในอาร์เรย์
                foreach ($transactions as $transaction){
                    $transaction_array[] = array(
                        'transaction_number' => $i,
                        'account_id' => $transaction->A->account_id,
                        'transaction_id' => $transaction->T->transaction_id,
                        'uuid' => $transaction->T->uuid,
                        'agreement_number' => $transaction->T->agreement_number,
                        'product_id' => $transaction->T->product_id,
                        'status' => $transaction->T->status,
                        'start_date' => $this->convertToBuddhistEra($transaction->T->start_date),
                        'common_start_date' => $this->convertToCommonEra($transaction->T->start_date),
                        'end_date' => $this->convertToBuddhistEra($transaction->T->end_date),
                        'common_end_date'  => $this->convertToCommonEra($transaction->T->end_date),
                        'transaction_date' => $this->convertToBuddhistEra($transaction->A->transaction_date),
                        'common_transaction_date' => $this->convertToCommonEra($transaction->A->transaction_date),
                        'transaction_time' => $transaction->A->transaction_time,
                        'note' => $transaction->T->note,
                        'principal' => $transaction->A->principal,
                        'value' => $transaction->A->value,
                        'related_uuid' => $transaction->T->related_uuid,
                    );
                    $i++;
                }
                $product_array[] = array(
                    'product_number' => $j,
                    'product_id' => $product->product_id,
                    'name' => $product->name,
                    'brand' => $product->brand,
                    'detail' => $product->detail,
                    'value' => $product->value,
                    'interest' => $product->interest,
                    'transactions' => $transaction_array,
                );
                $j++;
                $total_product_value = $total_product_value + $product->value;
            }

            $product_json = json_encode($product_array);

            echo $product_json;

            $this->view->setVars(
                [
                    "agreement_number" => $agreement_number,
                    "last_start_date"   => $this->convertToCommonEra($last_->last_start_date),
                    "last_end_date"     => $this->convertToCommonEra($last_end_date),
                    "next_start_date"   => $this->getNextMonth($last_->last_start_date),
                    "next_end_date"     => $next_end_date,
                    "agreement_interests" => $agreement_interest,
                    "agreement_withdraws" => $agreement_value + $agreement_interest,
                    "customer_json" => $customer_json,
                    "product_json" => $product_json,
                ]
            );

        } else {
            $this->flashSession->error("ไม่พบ $agreement_number ในระบบ");
            return $this->response->redirect("payment/");
        }
    }

    // * OK
    public function editPawnTransactionAction()
    {
        $this->view->disable();

        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($agreement_number = $this->request->getPost('agreement_number')) {

                // * Post variable
                $account_id     = $this->request->getPost('account_id');
                $status         = $this->request->getPost('status');
                $value          = $this->request->getPost('value');

                // * ค้นหารายละเอียดต่างๆ
                $transaction = $this->modelsManager->executeQuery(
                    'SELECT T.transaction_id , T.agreement_number , T.product_id
                    FROM account AS A
                    JOIN transaction AS T
                    ON A.uuid = T.uuid
                    WHERE A.account_id = :account_id:',
                    [
                        'account_id' => $account_id,
                    ]
                )->getFirst();

                // * แก้ไขราคาสินค้า
                $product = Product::findFirst($transaction->product_id);
                $product->value = $value; // ราคาสินค้าใหม่
                $product->interest = $this->calculateInterest($product->value);
                if ($product->save() == false) {
                    $transaction_->rollback(
                        'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                // * แก้ไขราคาในสัญญา
                $product_value = 0;
                $products = Product::findByagreement_number($transaction->agreement_number);
                foreach ($products as $product) {
                    $product_value = $product_value + $product->value;
                }
                $agreement = Agreement::findFirstByagreement_number($transaction->agreement_number);
                $agreement->value = $product_value;
                $agreement->interest = $this->calculateInterest($product_value);
                if ($agreement->save() == false) {
                    $transaction->rollback(
                        'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                // * แก้ไขสถานะ
                $update_transaction = Transaction::findFirst($transaction->transaction_id);
                $update_transaction->status = $status;
                if ($update_transaction->save() === false) {
                    $transaction_->rollback(
                        'ขออภัย การทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                $transaction_ = $manager->commit();
                return $this->response->redirect('payment/search/'.$agreement_number);
            }

        } catch (TxFailed $error) {
            echo $error->getMessage();
        }
    }

    // * OK
    public function editTransactionAction()
    {
        $this->view->disable();

        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($agreement_number = $this->request->getPost('agreement_number')) {

                // * Post variable
                $transaction_id     = $this->request->getPOST('transaction_id');
                $status = $this->request->getPOST('status');
                $start_date         = $this->request->getPost('start_date');
                $end_date         = $this->request->getPost('end_date');
                $note               = $this->request->getPost('note');

                $update_transaction = Transaction::findFirst($transaction_id);
                $update_transaction->status = $status;
                $update_transaction->start_date = $this->convertToCommonEraSQL($start_date);
                $update_transaction->end_date = $this->convertToCommonEraSQL($end_date);
                $update_transaction->note = $note;
                if ($update_transaction->save() === false) {
                    $transaction_->rollback(
                        'ขออภัยเกิดผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                $transaction_ = $manager->commit();

                return $this->response->redirect('payment/search/'.$agreement_number);
            }
        } catch (TxFailed $error) {
            echo $error->getMessage();
        }
    }

    // * OK
    public function deleteAgreementAction()
    {
        $this->view->disable();

        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($agreement_number = $this->request->getPost('agreement_number')) {

                $agreement = Agreement::findFirstByagreement_number($agreement_number);
                foreach($agreement->product as $product){
                    foreach($product->transaction as $transaction){
                        if($transaction->uuid){
                            if ($transaction->account->delete() === false) {
                                $transaction_->rollback(
                                    'ขออภัย ข้อมูลสัญญา ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }
                        }
                    }
                }
                if ($agreement->delete() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลสัญญา ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }
            }
            $transaction_ = $manager->commit();
            $this->flashSession->success("ลบเลขที่สัญญา " . $agreement_number . " เรียบร้อย ");
            return $this->response->redirect('payment/');
        } catch (TxFailed $error) {
            echo $error->getMessage();
        }
    }

    // * OK
    public function insertProductAction()
    {
        $this->view->disable();

        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($product_name = $this->request->getPost('product_name')) {

                $agreement_number       = $this->request->getPost('agreement_number');
                $product_name           = $this->request->getPost('product_name');
                $product_brand          = $this->request->getPost('product_brand');
                $product_detail         = $this->request->getPost('product_detail');
                $product_value          = $this->request->getPost('product_value');
                $start_date             = $this->request->getPost('start_date');
                $end_date               = $this->request->getPost('end_date');

                $uuid =  $this->getUuid();

                $product = new Product();
                $product->agreement_number = $agreement_number;
                $product->name = $product_name;
                $product->brand = $product_brand;
                $product->detail = $product_detail;
                $product->value = $product_value;
                $product->interest = $this->calculateInterest($product_value);
                if ( $product->save() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลสินค้า ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                $total_product_value = 0;
                $products = Product::findByagreement_number($agreement_number);
                foreach($products as $product){
                    $total_product_value = $total_product_value + $product->value;
                }
                $agreement = Agreement::findFirstByAgreementNumber($agreement_number);
                $agreement->value = $total_product_value;
                $agreement->interest = $this->calculateInterest($agreement->value);
                if ($agreement->save() == false) {
                    $transaction_->rollback(
                        'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                $account = new Account();
                $account->uuid = $uuid;
                $account->value = $product_value;
                $account->transaction_date =  $this->getCurrentDate();
                $account->transaction_time = $this->getCurrentTime();
                if ( $account->save() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลประวัติการทำรายการ ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                $transaction = new Transaction();
                $transaction->agreement_number = $agreement_number; 
                $transaction->product_id = $product->product_id; 
                $transaction->start_date = $this->convertToCommonEraSQL($start_date);
                $transaction->end_date = $this->convertToCommonEraSQL($end_date);
                $transaction->status = 'เพิ่มสินค้า';
                $transaction->uuid = $uuid;
                if ( $transaction->save() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                $transaction = new Transaction();
                $transaction->agreement_number = $agreement_number; 
                $transaction->product_id = $product->product_id; 
                $transaction->start_date = $this->convertToCommonEraSQL($start_date);
                $transaction->end_date = $this->convertToCommonEraSQL($end_date);
                $transaction->status = 'ว่าง';
                if ( $transaction->save() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                $transaction_ = $manager->commit();
                return $this->response->redirect('payment/search/'.$agreement_number);
            }
        } catch (TxFailed $error) {
            echo $error->getMessage();
        }
    }

    // * OK
    public function separateProductAction()
    {
        $this->view->disable();

        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($agreement_number = $this->request->getPost('agreement_number')) {

                $uuid = $this->getUuid();

                $product_id = $this->request->getPost('product_id');
                $transaction_date =  $this->request->getPost('transaction_date');
                $transaction_time =  $this->request->getPost('transaction_time');
                $product_name = $this->request->getPost('product_name');
                $product_brand = $this->request->getPost('product_brand');
                $product_detail = $this->request->getPost('product_detail');
                $product_value = $this->request->getPost('product_value');
                $note = $this->request->getPost('note');
            
                // * ค้นหาสถานะว่างของแต่ละสินค้า
                $transaction = $this->modelsManager->executeQuery(
                    "SELECT T.transaction_id
                        FROM transaction AS T 
                        WHERE (T.status = 'ว่าง' AND T.active = 'T') AND T.product_id = '$product_id'
                            ")->getFirst();

                    // * ถ้ามีสถานะ ว่าง
                    if ($transaction) {

                        // หา max_start_date และ max_end_date
                        $max_date_transaction = $this->modelsManager->executeQuery(
                            "SELECT MAX(T.start_date) AS max_start_date , MAX(T.end_date) AS max_end_date , T.agreement_number
                                FROM transaction AS T
                                WHERE T.product_id = '$product_id'
                                ORDER BY T.transaction_id DESC "
                        )->getFirst();

                        $update_transaction = Transaction::findFirst($transaction->transaction_id);
                        $update_transaction->related_uuid = $uuid;
                        $update_transaction->active = 'S';
                        if ($update_transaction->save() == false) {
                            $transaction_->rollback(
                            'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                        );
                        }

                        $new_transaction = new Transaction();
                        $new_transaction->agreement_number = $agreement_number;
                        $new_transaction->product_id = $product_id; 
                        $new_transaction->start_date = $max_date_transaction->max_start_date;
                        $new_transaction->end_date = $max_date_transaction->max_end_date;
                        $new_transaction->status = 'แยกขาย';
                        $new_transaction->uuid =  $uuid;
                        $new_transaction->note = $note;
                        if ($new_transaction->save() === false) {
                            $new_transaction_->rollback(
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

                        // นับจำนวนสินค้าที่แยกขาย
                        $product = Product::findFirst($product_id);
                        $product->related_product = $product->related_product + 1;
                        if ($product->save() === false) {
                            $transaction_->rollback(
                                'ขออภัย ข้อมูลสินค้า ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                        }

                        $uuid_ = $this->getUuid();

                        // สินค้าแยกขาย
                        $new_product = new Product();
                        $new_product->agreement_number = $agreement_number;
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
                        $new_transaction->agreement_number = $agreement_number;
                        $new_transaction->product_id = $new_product->product_id; 
                        $new_transaction->start_date = $max_date_transaction->max_start_date;
                        $new_transaction->end_date = $max_date_transaction->max_end_date;
                        $new_transaction->status = 'ว่าง';
                        $new_transaction->related_uuid =  $uuid;
                        $new_transaction->note = $note;
                        if ($new_transaction->save() === false) {
                            $new_transaction_->rollback(
                                'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                        }

                    } else {

                        // หา max_start_date และ max_end_date
                        $max_date_transaction = $this->modelsManager->executeQuery(
                        "SELECT MAX(T.start_date) AS max_start_date , MAX(T.end_date) AS max_end_date , T.agreement_number
                                FROM transaction AS T
                                WHERE T.product_id = '$product_id'
                                ORDER BY T.transaction_id DESC "
                        )->getFirst();

                        $new_transaction = new Transaction();
                        $new_transaction->agreement_number = $agreement_number;
                        $new_transaction->product_id = $product_id; 
                        $new_transaction->start_date = $max_date_transaction->max_start_date;
                        $new_transaction->end_date = $max_date_transaction->max_end_date;
                        $new_transaction->status = 'แยกขาย';
                        $new_transaction->uuid =  $uuid;
                        $new_transaction->note = $note;
                        if ($new_transaction->save() === false) {
                            $new_transaction_->rollback(
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

                        // นับจำนวนสินค้าที่แยกขาย
                        $product = Product::findFirst($product_id);
                        $product->related_product = $product->related_product + 1;
                        if ($product->save() === false) {
                            $transaction_->rollback(
                                'ขออภัย ข้อมูลสินค้า ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                        }

                        $uuid_ = $this->getUuid();

                        // สินค้าแยกขาย
                        $new_product = new Product();
                        $new_product->agreement_number = $agreement_number;
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
                        $new_transaction->agreement_number = $agreement_number;
                        $new_transaction->product_id = $new_product->product_id; 
                        $new_transaction->start_date = $max_date_transaction->max_start_date;
                        $new_transaction->end_date = $max_date_transaction->max_end_date;
                        $new_transaction->status = 'ว่าง';
                        $new_transaction->related_uuid =  $uuid;
                        $new_transaction->note = $note;
                        if ($new_transaction->save() === false) {
                            $new_transaction_->rollback(
                                'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                        }

                    }

                $transaction_ = $manager->commit();
                return $this->response->redirect('payment/search/'.$agreement_number);
            }
        } catch (TxFailed $error) {
            echo $error->getMessage();
        }

    }

    // * OK
    public function deleteSeparateProductTransactionAction()
    {
        $this->view->disable();

        try {
            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($transaction_id = $this->request->get('transaction_id')) {
                
                $transaction = Transaction::findFirst($transaction_id);
                $uuid = $transaction->related_uuid;
                if ($transaction->delete() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                $separate_transaction = Transaction::findFirstByUuid($uuid);
                if ($separate_transaction->delete() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                $update_transaction = Transaction::findFirstByRelatedUuid($uuid);
                if ($update_transaction) {
                    $update_transaction->related_uuid = null;
                    $update_transaction->active = 'T';
                    if ($update_transaction->save() == false) {
                        $transaction_->rollback(
                        'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                    }
                }   

                $account = Account::findFirstByUuid($uuid);
                if ($account->delete() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลประวัติการทำรายการ ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                $product = $transaction->Product;
                $main_product = $separate_transaction->Product;
                
                if ($product->delete() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลประวัติการทำรายการ ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                } else {
                    if ($separate_transaction->delete() === false) {
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

    // * OK
    public function separateSaleAction()
    {
        $this->view->disable();

        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($agreement_number = $this->request->getPost('agreement_number')) {

                $uuid = $this->getUuid();

                $product_id = $this->request->getPost('product_id');
                $transaction_date =  $this->request->getPost('transaction_date');
                $transaction_time =  $this->request->getPost('transaction_time');
                $product_name = $this->request->getPost('product_name');
                $product_brand = $this->request->getPost('product_brand');
                $product_detail = $this->request->getPost('product_detail');
                $product_value = $this->request->getPost('product_value');
                $sale_value = $this->request->getPost('sale_value');
                $note = $this->request->getPost('note');
            
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
                return $this->response->redirect('payment/search/'.$agreement_number);
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

            if ($agreement_number = $this->request->getPost('agreement_number')) {

                $account_id = $this->request->getPost('account_id');
                $sale_value = $this->request->getPost('sale_value');
                $note = $this->request->getPost('note');

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
                return $this->response->redirect('payment/search/'.$agreement_number);
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

    // * OK
    public function printReceiptAction($status,$uuid)
    {
        $this->view->setRenderLevel(
            View::LEVEL_ACTION_VIEW
        );
        $this->assets->addCss("css/paper_tm80.css");
        $this->assets->addCss('fontawesome/css/all.css');

        if($status == 'เพิ่มเงิน' || $status == 'ลดต้น'){
            $account = Account::findFirstByUuid($uuid);
            foreach($account->transactionDetail as $transaction){
                $products = Product::findByagreement_number($transaction->agreement_number);
                foreach($products as $product){
                    $product_array[] = array(
                        'product_name'  => $product->name,
                        'product_brand' => $product->brand,
                        'product_detail' => $product->detail,
                        'product_value'  => $product->value
                );
                }
                $note = $transaction->note;
                $last_ = $this->modelsManager->executeQuery(
                    "SELECT MAX(T.end_date) as end_date
                        FROM transaction AS T 
                        WHERE T.status = 'ว่าง' AND T.agreement_number = '$product->agreement_number'
                        ")->getFirst();
                $last_end_date = $this->convertToBuddhistEra($last_->end_date);
            }
            $product_json = json_encode($product_array);
        }else{
            $account = Account::findFirstByUuid($uuid);
            foreach($account->transactionDetail as $transaction){
                $product = $transaction->product;
                $product_array[] = array(
                        'product_name'  => $product->name,
                        'product_brand' => $product->brand,
                        'product_detail' => $product->detail,
                        'product_value'  => $product->value
                );
                $note = $transaction->note;
            }
            $product_json = json_encode($product_array);
            $last_ = $this->modelsManager->executeQuery(
                "SELECT MAX(T.end_date) as end_date
                    FROM transaction AS T 
                    WHERE T.status = 'ว่าง' AND T.agreement_number = '$product->agreement_number'
                    ")->getFirst();
            $last_end_date = $this->convertToBuddhistEra($last_->end_date);
        }

        $owner = Owner::findFirst('1');
        $this->view->setVars(
            [
                'transaction_date'  => $this->convertToBuddhistEraWithTime($account->created_date),
                'agreement_number'  => $transaction->agreement_number,
                'status'            => $status,
                'end_date'          => $last_end_date,
                'note'              => $note,
                'product_json'   =>  $product_json,
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

    // * OK
    public function printLastReceiptAction($agreement_number)
    {
        $this->view->setRenderLevel(
            View::LEVEL_ACTION_VIEW
        );
        $this->assets->addCss("css/paper_tm80.css");
        $this->assets->addCss('fontawesome/css/all.css');

        $transactions = $this->modelsManager->executeQuery(
            "SELECT T.* 
                FROM transaction AS T 
                WHERE T.status = 'ว่าง' AND T.agreement_number = '$agreement_number'");

        foreach($transactions as $transaction){
            $product = $transaction->product;
            $product_array[] = array(
                'product_name'  => $product->name,
                'product_brand' => $product->brand,
                'product_detail' => $product->detail,
                'product_value'  => $product->value
            );
        }

        $last_ = $this->modelsManager->executeQuery(
            "SELECT MAX(T.end_date) as end_date
                FROM transaction AS T 
                WHERE T.status = 'ว่าง' AND T.agreement_number = '$agreement_number'
                ")->getFirst();

        $product_json = json_encode($product_array);
        $last_end_date = $last_->end_date;
        $owner = Owner::findFirst('1');
        $this->view->setVars(
            [
                'agreement_number'  => $agreement_number,
                'end_date'          => $this->convertToBuddhistEra($last_end_date),
                'product_json'   =>  $product_json,
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

    // * OK
    public function printAgreementAction($agreement_number)
    {
        // $this->view->disable();
        $this->view->setRenderLevel(
            View::LEVEL_ACTION_VIEW
        );
        $this->assets->addCss("css/paper.css");

        $i = 0;
        $count_products = 1;
        $total_value = 0;
        $agreement = Agreement::findFirstByAgreementNumber($agreement_number);
        $customer    = $agreement->customer;
        $customer_array[] = array(
            'idcard' => $customer->idcard,
            'firstname' => $customer->firstname,
            'lastname' => $customer->lastname,
            'image'    => $customer->image,
        );

        $products = Product::findByAgreementNumber($agreement_number);

        foreach($products as $product){
            $transaction = $this->modelsManager->executeQuery(
                'SELECT T.status , MIN(T.start_date) AS min_start_date , MIN(T.end_date) AS min_end_date
                    FROM transaction AS T 
                    JOIN account AS A 
                    ON T.uuid = A.uuid 
                    WHERE T.transaction_id = (
                        SELECT MAX(T.transaction_id) 
                        FROM transaction AS T
                        JOIN account AS A
                        ON T.uuid = A.uuid
                        WHERE T.product_id = "'.$product->product_id.'")
                        ')->getFirst();
    
            $min_start_date = $transaction->min_start_date;
            $min_end_date = $transaction->min_end_date;

            if ($transaction->status != 'แยกขาย' && $transaction->status != 'แยกสินค้า') {
                $product_array[] = array(
                'count' =>  $i = $i + 1,
                'name'  => $product->name,
                'brand' => $product->brand,
                'detail' => $product->detail,
                'value'  => $product->value
                );
                $total_value = $total_value + $product->value;
                $count_products++;
            }
        }
        
            $agreement_array[] = array(
                "count_products"     => $count_products,
                "status"             => 'ฝาก',
                "agreement_number"   => $agreement_number,
                "create_date"        => $this->convertToBuddhistEraWithTime($agreement->create_date),
                "start_date"         => $this->convertToBuddhistEra($min_start_date),
                "end_date"           => $this->convertToBuddhistEra($min_end_date),
                "customers"          => $customer_array,
                "products"           => $product_array,
                "total_value"        => $total_value
            );
            
            
            $owner = Owner::findFirst('1');
            
            $agreement_json = json_encode($agreement_array);

            $this->view->setVars(
                [
                    "agreement_number" => $agreement_number,
                    "agreement_json" =>  $agreement_json,
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

    // * OK
    /* ต่อดอกทั้งหมด */
    public function payInterestProductsAction()
    {
        $this->view->disable();

        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($agreement_number = $this->request->getPost('agreement_number')) {

                // * Post variable
                $transaction_date       = $this->request->getPost('transaction_date');
                $transaction_time       = $this->request->getPost('transaction_time');
                $start_date             = $this->request->getPost('start_date');
                $end_date               = $this->request->getPost('end_date');
                $note                   = $this->request->getPost('note');
                $agreement_interests    = $this->request->getPost('agreement_interests');
                $count_payment          = $this->request->getPost('count_payment');

                $condition = FALSE;
                $products = Product::findByagreement_number($agreement_number);
                
                for ($i = 1; $i <= $count_payment; $i++) {

                    $uuid = $this->getUuid();

                    foreach ($products as $product) {

                        $start_date;
                        $end_date;

                        // * ค้นหาสถานะว่างของแต่ละสินค้า
                        $transaction = $this->modelsManager->executeQuery(
                        "SELECT T.*
                            FROM transaction AS T 
                            WHERE T.status = 'ว่าง' AND T.active != 'S' AND T.product_id = '$product->product_id'
                                "
                        )->getFirst();
                                                
                            // * ถ้ามีสถานะ ว่าง
                            if ($transaction) {
                                $update_transaction = Transaction::findFirst($transaction->transaction_id);
                                $update_transaction->status = 'ต่อดอกทั้งหมด';
                                $update_transaction->uuid = $uuid;
                                $update_transaction->value = $agreement_interests;
                                if ($update_transaction->save() == false) {
                                    $transaction_->rollback(
                                    'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                                }

                                $new_transaction = new Transaction();
                                $new_transaction->agreement_number = $agreement_number;
                                $new_transaction->product_id = $product->product_id;
                                $new_transaction->start_date = $this->convertToCommonEraSQL($start_date);
                                $new_transaction->end_date = $this->convertToCommonEraSQL($end_date);
                                $new_transaction->status = 'ว่าง';
                                // $new_transaction->related_uuid = $uuid;
                                if ($new_transaction->save() === false) {
                                    $transaction_->rollback(
                                    'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                                }
                            
                                $agreement = Agreement::findFirstByAgreementNumber($agreement_number);
                                $agreement->interest = $agreement_interests;
                                if ($agreement->save() == false) {
                                    $transaction_->rollback(
                                    'ขออภัย ข้อมูลสัญญา ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                                }
                           
                                $condition = TRUE;
                                
                            } else {
                                // TODO ถ้าไม่มีสถานะว่างให้เช็คว่าเป็นสถานะอะไร เช่น หลุด ...
                                $transaction = $this->modelsManager->executeQuery(
                                "SELECT T.*
                                    FROM transaction AS T 
                                    WHERE T.product_id = '$product->product_id'
                                    ORDER BY T.transaction_id DESC
                                        "
                            )->getFirst();

                                if ($transaction->status == 'หลุด' || $transaction->status == 'ตั้งขาย' || $transaction->status == 'ตั้งขายกรณีพิเศษ') {
                                    $transaction->active = 'F';
                                    if ($transaction->save() === false) {
                                        $transaction_->rollback(
                                        'ขออภัยเกิดผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                    );
                                    }

                                    // * สร้างสถานะว่างใหม่
                                    $new_transaction = new Transaction();
                                    $new_transaction->agreement_number = $agreement_number;
                                    $new_transaction->product_id = $product->product_id;
                                    $new_transaction->start_date = $this->convertToCommonEraSQL($transaction->start_date);
                                    $new_transaction->end_date = $this->convertToCommonEraSQL($transaction->end_date);
                                    $new_transaction->status = 'ว่าง';
                                    if ($new_transaction->save() === false) {
                                        $transaction_->rollback(
                                        'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                    );
                                    }
    
                                    // * อัพเดทสถานะว่างที่สร้างใหม่เป็น ต่อดอกทั้งหมด
                                    $update_transaction = Transaction::findFirst($new_transaction->transaction_id);
                                    $update_transaction->status = 'ต่อดอกทั้งหมด';
                                    $update_transaction->uuid = $uuid;
                                    // $update_transaction->related_uuid = $transaction->uuid;
                                    $update_transaction->value = $agreement_interests;
                                    if ($update_transaction->save() == false) {
                                        $transaction_->rollback(
                                        'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                    );
                                    }
    
                                    // * สร้างสถานะว่างเพื่อต่อดอกงวดหน้า
                                    $new_transaction = new Transaction();
                                    $new_transaction->agreement_number = $agreement_number;
                                    $new_transaction->product_id = $product->product_id;
                                    $new_transaction->start_date = $this->convertToCommonEraSQL($start_date);
                                    $new_transaction->end_date = $this->convertToCommonEraSQL($end_date);
                                    $new_transaction->status = 'ว่าง';
                                    // $new_transaction->related_uuid = $uuid;
                                    if ($new_transaction->save() === false) {
                                        $transaction_->rollback(
                                        'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                    );
                                    }

                                    // * อัพเดทยอดเงินในสัญญา
                                    $agreement = Agreement::findFirstByAgreementNumber($agreement_number);
                                    $agreement->interest = $agreement_interests;
                                    if ($agreement->save() == false) {
                                        $transaction_->rollback(
                                        'ขออภัย ข้อมูลสัญญา ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                    );
                                    }

                                    $condition = true;

                                } elseif ($transaction->status == 'มัดจำ') {
                                    $deposit_transactions = $this->modelsManager->executeQuery(
                                    "SELECT T.*
                                        FROM transaction AS T
                                        WHERE T.product_id = '$product->product_id' AND T.status = 'มัดจำ' 
                                    "
                                );

                                    foreach ($deposit_transactions as $deposit_transaction) {
                                        $deposit_transaction->active = 'F';
                                        if ($deposit_transaction->save() === false) {
                                            $transaction_->rollback(
                                            'ขออภัยเกิดผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                        );
                                        }
                                    }

                                    // * สร้างสถานะว่างใหม่
                                    $new_transaction = new Transaction();
                                    $new_transaction->agreement_number = $agreement_number;
                                    $new_transaction->product_id = $product->product_id;
                                    $new_transaction->start_date = $this->convertToCommonEraSQL($transaction->start_date);
                                    $new_transaction->end_date = $this->convertToCommonEraSQL($transaction->end_date);
                                    $new_transaction->status = 'ว่าง';
                                    if ($new_transaction->save() === false) {
                                        $transaction_->rollback(
                                        'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                    );
                                    }
    
                                    // * อัพเดทสถานะว่างที่สร้างใหม่เป็น ต่อดอกทั้งหมด
                                    $update_transaction = Transaction::findFirst($new_transaction->transaction_id);
                                    $update_transaction->status = 'ต่อดอกทั้งหมด';
                                    $update_transaction->uuid = $uuid;
                                    // $update_transaction->related_uuid = $transaction->uuid;
                                    $update_transaction->value = $agreement_interests;
                                    if ($update_transaction->save() == false) {
                                        $transaction_->rollback(
                                        'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                    );
                                    }
    
                                    // * สร้างสถานะว่างเพื่อต่อดอกงวดหน้า
                                    $new_transaction = new Transaction();
                                    $new_transaction->agreement_number = $agreement_number;
                                    $new_transaction->product_id = $product->product_id;
                                    $new_transaction->start_date = $this->convertToCommonEraSQL($start_date);
                                    $new_transaction->end_date = $this->convertToCommonEraSQL($end_date);
                                    $new_transaction->status = 'ว่าง';
                                    // $new_transaction->related_uuid = $uuid;
                                    if ($new_transaction->save() === false) {
                                        $transaction_->rollback(
                                        'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                    );
                                    }

                                    // * อัพเดทยอดเงินในสัญญา
                                    $agreement = Agreement::findFirstByAgreementNumber($agreement_number);
                                    $agreement->interest = $agreement_interests;
                                    if ($agreement->save() == false) {
                                        $transaction_->rollback(
                                        'ขออภัย ข้อมูลสัญญา ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                    );
                                    }

                                    $condition = TRUE;
                                
                                } else { // TODO ถ้าเป็นสถานะนอกเหนือจากนั้น
                                }
                            }
                            // $condition = FALSE;
                        }

                        if ($condition == TRUE) {
                            $account = new Account();
                            $account->uuid = $uuid;
                            $account->value = $agreement_interests;
                            $account->transaction_date = $this->convertToCommonEraSQL($transaction_date);
                            $account->transaction_time =  $this->convertToTimeSQL($transaction_time);
                            if ($account->save() === false) {
                                $transaction_->rollback(
                                'ขออภัย ข้อมูลประวัติการทำรายการ ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                            }
                            $start_date = $this->getNextMonth($start_date);
                            $end_date = $this->getNextMonth($end_date);
                            $transaction_ = $manager->commit();
                        }
                    }

                $this->flashSession->success("เลขที่สัญญา " . $agreement_number . " ทำรายการต่อดอกทั้งหมด ");
                return $this->response->redirect('payment/print-receipt/ต่อดอกทั้งหมด/'.$uuid.'');
            }
        } catch (TxFailed $error) {
            echo $error->getMessage();
        }
    }


    // * OK
    /* ต่อดอกชิ้นเดียว */
    public function payInterestProductAction()
    {
        $this->view->disable();

        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($agreement_number = $this->request->getPost('agreement_number')) {

                // * Post variable
                $product_id             = $this->request->getPost('product_id');
                $transaction_date       = $this->request->getPost('transaction_date');
                $transaction_time       = $this->request->getPost('transaction_time');
                $start_date             = $this->request->getPost('start_date');
                $end_date               = $this->request->getPost('end_date');
                $note                   = $this->request->getPost('note');
                $agreement_interest     = $this->request->getPost('agreement_interest');
                $count_payment          = $this->request->getPost('count_payment');

                for ($i = 1; $i <= $count_payment; $i++) {

                    $uuid = $this->getUuid();
                    $start_date;
                    $end_date;

                    // * ค้นหาสถานะว่างของแต่ละสินค้า
                    $transaction = $this->modelsManager->executeQuery(
                    "SELECT T.transaction_id
                        FROM transaction AS T 
                        WHERE T.status = 'ว่าง' AND T.active != 'S' AND T.product_id = '$product_id'
                            ")->getFirst();

                    // * ถ้ามีสถานะ ว่าง
                    if ($transaction) {

                        $account = new Account();
                        $account->uuid = $uuid;
                        $account->value = $agreement_interest;
                        $account->transaction_date = $this->convertToCommonEraSQL($transaction_date);
                        $account->transaction_time =  $this->convertToTimeSQL($transaction_time);
                        if ($account->save() === false) {
                            $transaction_->rollback(
                                'ขออภัย ข้อมูลประวัติการทำรายการ ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                        }

                        $update_transaction = Transaction::findFirst($transaction->transaction_id);
                        $update_transaction->status = 'ต่อดอกชิ้นเดียว';
                        $update_transaction->uuid = $uuid;
                        $update_transaction->value = $agreement_interest;
                        if ($update_transaction->save() == false) {
                            $transaction_->rollback(
                                'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                        }

                        $new_transaction = new Transaction();
                        $new_transaction->agreement_number = $agreement_number; 
                        $new_transaction->product_id = $product_id; 
                        $new_transaction->start_date = $this->convertToCommonEraSQL($start_date);
                        $new_transaction->end_date = $this->convertToCommonEraSQL($end_date);
                        $new_transaction->status = 'ว่าง';
                        // $new_transaction->related_uuid = $uuid;
                        if ( $new_transaction->save() === false) {
                            $transaction_->rollback(
                                'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                        }
                        
                        // อัพเดท interest
                        $product = Product::findFirst($product_id);
                        $product->interest = $agreement_interest;
                        if ($product->save() == false) {
                            $transaction_->rollback(
                                'ขออภัย ข้อมูลสัญญา ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                        }
                        $transaction_ = $manager->commit();

                        $start_date = $this->getNextMonth($start_date);
                        $end_date = $this->getNextMonth($end_date);

                    } else {
                        // TODO ถ้าไม่มีสถานะว่างให้เช็คว่าเป็นสถานะอะไร เช่น หลุด ...
                        $transaction = $this->modelsManager->executeQuery(
                            "SELECT T.*
                                FROM transaction AS T 
                                WHERE T.product_id = '$product_id'
                                ORDER BY T.transaction_id DESC
                                    "
                        )->getFirst();

                        if ($transaction->status == 'หลุด' || $transaction->status == 'ตั้งขาย' || $transaction->status == 'ตั้งขายกรณีพิเศษ') {
                            $transaction->active = 'F';
                            if ($transaction->save() === false) {
                                $transaction_->rollback(
                                    'ขออภัยเกิดผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }

                            // * สร้างสถานะว่างใหม่
                            $new_transaction = new Transaction();
                            $new_transaction->agreement_number = $agreement_number;
                            $new_transaction->product_id = $product_id;
                            $new_transaction->start_date = $this->convertToCommonEraSQL($transaction->start_date);
                            $new_transaction->end_date = $this->convertToCommonEraSQL($transaction->end_date);
                            $new_transaction->status = 'ว่าง';
                            if ($new_transaction->save() === false) {
                                $transaction_->rollback(
                                    'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }

                            $account = new Account();
                            $account->uuid = $uuid;
                            $account->value = $agreement_interest;
                            $account->transaction_date = $this->convertToCommonEraSQL($transaction_date);
                            $account->transaction_time =  $this->convertToTimeSQL($transaction_time);
                            if ($account->save() === false) {
                                $transaction_->rollback(
                                    'ขออภัย ข้อมูลประวัติการทำรายการ ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }

                            // * อัพเดทสถานะ ว่าง ที่สร้างใหม่ -> ต่อดอกชิ้นเดียว
                            $update_transaction = Transaction::findFirst($new_transaction->transaction_id);
                            $update_transaction->status = 'ต่อดอกชิ้นเดียว';
                            $update_transaction->uuid = $uuid;
                            // $update_transaction->related_uuid = $transaction->uuid;
                            if ($update_transaction->save() == false) {
                                $transaction_->rollback(
                                    'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }

                            // * สร้างสถานะว่างเพื่อต่อดอกงวดหน้า
                            $new_transaction = new Transaction();
                            $new_transaction->agreement_number = $agreement_number;
                            $new_transaction->product_id = $product_id;
                            $new_transaction->start_date = $this->convertToCommonEraSQL($start_date);
                            $new_transaction->end_date = $this->convertToCommonEraSQL($end_date);
                            $new_transaction->status = 'ว่าง';
                            // $new_transaction->related_uuid = $uuid;
                            if ($new_transaction->save() === false) {
                                $transaction_->rollback(
                                    'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }
                            
                            // อัพเดท interest
                            $product = Product::findFirst($product_id);
                            $product->interest = $agreement_interest;
                            if ($product->save() == false) {
                                $transaction_->rollback(
                                    'ขออภัย ข้อมูลสัญญา ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }
                            $transaction_ = $manager->commit();

                            $start_date = $this->getNextMonth($start_date);
                            $end_date = $this->getNextMonth($end_date);

                        } elseif ($transaction->status == 'มัดจำ') {
                            $deposit_transactions = $this->modelsManager->executeQuery(
                                "SELECT T.*
                                    FROM transaction AS T
                                    WHERE T.product_id = '$product_id' AND T.status = 'มัดจำ' 
                                "
                            );

                            foreach ($deposit_transactions as $deposit_transaction) {
                                $deposit_transaction->active = 'F';
                                if ($deposit_transaction->save() === false) {
                                    $transaction_->rollback(
                                        'ขออภัยเกิดผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                    );
                                }
                            }

                            // * สร้างสถานะว่างใหม่
                            $new_transaction = new Transaction();
                            $new_transaction->agreement_number = $agreement_number;
                            $new_transaction->product_id = $product_id;
                            $new_transaction->start_date = $this->convertToCommonEraSQL($transaction->start_date);
                            $new_transaction->end_date = $this->convertToCommonEraSQL($transaction->end_date);
                            $new_transaction->status = 'ว่าง';
                            if ($new_transaction->save() === false) {
                                $transaction_->rollback(
                                    'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }

                            $account = new Account();
                            $account->uuid = $uuid;
                            $account->value = $agreement_interest;
                            $account->transaction_date = $this->convertToCommonEraSQL($transaction_date);
                            $account->transaction_time =  $this->convertToTimeSQL($transaction_time);
                            if ($account->save() === false) {
                                $transaction_->rollback(
                                    'ขออภัย ข้อมูลประวัติการทำรายการ ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }

                            // * อัพเดทสถานะ ว่าง ที่สร้างใหม่ -> ต่อดอกชิ้นเดียว
                            $update_transaction = Transaction::findFirst($new_transaction->transaction_id);
                            $update_transaction->status = 'ต่อดอกชิ้นเดียว';
                            $update_transaction->uuid = $uuid;
                            // $update_transaction->related_uuid = $transaction->uuid;
                            if ($update_transaction->save() == false) {
                                $transaction_->rollback(
                                    'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }

                            // * สร้างสถานะว่างเพื่อต่อดอกงวดหน้า
                            $new_transaction = new Transaction();
                            $new_transaction->agreement_number = $agreement_number;
                            $new_transaction->product_id = $product_id;
                            $new_transaction->start_date = $this->convertToCommonEraSQL($start_date);
                            $new_transaction->end_date = $this->convertToCommonEraSQL($end_date);
                            $new_transaction->status = 'ว่าง';
                            // $new_transaction->related_uuid = $uuid;
                            if ($new_transaction->save() === false) {
                                $transaction_->rollback(
                                    'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }
                            
                            // อัพเดท interest
                            $product = Product::findFirst($product_id);
                            $product->interest = $agreement_interest;
                            if ($product->save() == false) {
                                $transaction_->rollback(
                                    'ขออภัย ข้อมูลสัญญา ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }
                            $transaction_ = $manager->commit();

                            $start_date = $this->getNextMonth($start_date);
                            $end_date = $this->getNextMonth($end_date);

                        } else { // TODO ถ้าเป็นสถานะนอกเหนือจากนั้น
                        }

                    }
                }
                $this->flashSession->success("เลขที่สัญญา " . $agreement_number . " ทำรายการต่อดอกชิ้นเดียว");
                return $this->response->redirect('payment/print-receipt/ต่อดอกชิ้นเดียว/'.$uuid.'');
            }
        } catch (TxFailed $error) {
            echo $error->getMessage();
        }
    }

    // * OK
    public function editInterestTransactionAction()
    {
        $this->view->disable();

        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($agreement_number = $this->request->getPost('agreement_number')) {

                // * Post variable
                $account_id         = $this->request->getPOST('account_id');
                $start_date         = $this->request->getPost('start_date');
                $end_date           = $this->request->getPost('end_date');
                $transaction_date   = $this->request->getPost('transaction_date');
                $transaction_time   = $this->request->getPost('transaction_time');
                $interest           = $this->request->getPost('interest');
                $note               = $this->request->getPost('note');

                $account = Account::findFirst($account_id);
                $account->transaction_date = $this->convertToCommonEraSQL($transaction_date);
                $account->transaction_time = $this->convertToTimeSQL($transaction_time);
                $account->value = $interest;

                // * แก้ไขรายละเอียดธุรกรรม
                foreach ($account->transactionDetail as $transactionDetail) {
                    $transactionDetail->start_date = $this->convertToCommonEraSQL($start_date);
                    $transactionDetail->end_date = $this->convertToCommonEraSQL($end_date);
                    $transactionDetail->note = $note;
                    if ($transactionDetail->save() == false) {
                        $transaction_->rollback(
                            'ขออภัย การทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                        );
                    }
                }

                if ($account->save() == false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลประวัติการทำรายการ ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }
                $transaction_ = $manager->commit();

                return $this->response->redirect('payment/search/'.$agreement_number);
            }
        } catch (TxFailed $error) {
            echo $error->getMessage();
        }
    }

    // * OK
    public function deleteInterestsTransactionAction()
    {
        $this->view->disable();

        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($account_id = $this->request->get('account_id')) {

                $account = Account::findFirst($account_id);
                // * คืนสถานะว่าง
                foreach ($account->transactionDetail as $transactionDetail) {

                    // * ค้นหา transaction ก่อนหน้า
                    $prev_transaction = $this->modelsManager->executeQuery(
                        "SELECT T.*
                            FROM transaction AS T
                            WHERE T.transaction_id != ( 
                                SELECT T.transaction_id
                                    FROM transaction AS T
                                    WHERE T.transaction_id = '$transactionDetail->transaction_id' )
                            AND T.product_id = '$transactionDetail->product_id'
                            AND T.status != 'ว่าง'
                            ORDER BY T.transaction_id DESC
                        ")->getFirst();

                    // * ถ้าสถานะก่อนหน้าเป็น หลุด , ตั้งขาย , ... ให้คืน active -> T และลบสถานะที่เกี่ยวข้องทั้งหมด
                    if($prev_transaction->status == 'หลุด' || $prev_transaction->status == 'ตั้งขาย' || $prev_transaction->status == 'ตั้งขายกรณีพิเศษ'){
                        $prev_transaction->active = 'T';
                        if ($prev_transaction->save() == false){
                            $transaction_->rollback(
                                'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                        }

                        // * ลบสถานะว่างล่าสุด
                        $last_transaction = $this->modelsManager->executeQuery(
                            "SELECT T.*
                                FROM transaction as T
                                WHERE T.status = 'ว่าง' AND T.product_id = '$transactionDetail->product_id'
                        ")->getFirst();

                        if ($last_transaction->delete() == false) {
                            $transaction_->rollback(
                                'ขออภัย การทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                        }

                        // * ลบสถานะต่อดอกทั้งหมด
                        if ($transactionDetail->delete() == false) {
                            $transaction_->rollback(
                                'ขออภัย การทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                        }

                    // * ถ้าสถานะก่อนหน้าเป็นมัดจำ ให้คืน active -> T และลบสถานะที่เกี่ยวข้องทั้งหมด
                    }else if($prev_transaction->status == 'มัดจำ'){

                        $deposit_transactions = $this->modelsManager->executeQuery(
                            "SELECT T.*
                                FROM transaction AS T
                                WHERE T.product_id = '$transactionDetail->product_id' AND T.status = 'มัดจำ' 
                            ");

                        foreach($deposit_transactions as $deposit_transaction){
                            $deposit_transaction->active = 'T';
                            if ( $deposit_transaction->save() === false) {
                                $transaction_->rollback(
                                    'ขออภัยเกิดผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }
                        }

                        // * ลบสถานะว่างล่าสุด
                        $last_transaction = $this->modelsManager->executeQuery(
                            "SELECT T.*
                                FROM transaction as T
                                WHERE T.status = 'ว่าง' AND T.product_id = '$transactionDetail->product_id'
                        ")->getFirst();

                        if ($last_transaction->delete() == false) {
                            $transaction_->rollback(
                                'ขออภัย การทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                        }

                        // * ลบสถานะต่อดอกทั้งหมด
                        if ($transactionDetail->delete() == false) {
                            $transaction_->rollback(
                                'ขออภัย การทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                        }

                    // * ถ้าสถานะก่อนหน้าเป็น ต่อดอก , ไถ่คืน ให้คืนสถานะว่าง
                    }else{ 

                        // * ลบสถานะว่างล่าสุด
                        $last_transaction = $this->modelsManager->executeQuery(
                            "SELECT T.*
                                FROM transaction as T
                                WHERE T.status = 'ว่าง' AND T.product_id = '$transactionDetail->product_id'
                        ")->getFirst();

                        if ($last_transaction->delete() == false) {
                            $transaction_->rollback(
                                'ขออภัย การทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                        }

                        $transactionDetail->status = 'ว่าง';
                        $transactionDetail->uuid = null;
                        if ($transactionDetail->save() == false) {
                            $transaction_->rollback(
                                'ขออภัย การทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                        }
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
    /* ไถ่คืนทั้งหมด */
    public function withdrawProductsAction()
    {
        $this->view->disable();

        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($agreement_number = $this->request->getPost('agreement_number')) {

                // * Post variable
                $transaction_date       = $this->request->getPost('transaction_date');
                $transaction_time       = $this->request->getPost('transaction_time');
                $start_date             = $this->request->getPost('start_date');
                $end_date               = $this->request->getPost('end_date');
                $note                   = $this->request->getPost('note');
                $agreement_withdraws    = $this->request->getPost('agreement_withdraws');

                $uuid = $this->getUuid();
                $condition = FALSE;
                $products = Product::findByagreement_number($agreement_number);
                $total_product_value = 0;
                foreach ($products as $product) {

                    // * ค้นหาสถานะว่างของแต่ละสินค้า
                    $transaction = $this->modelsManager->executeQuery(
                        "SELECT T.transaction_id
                            FROM transaction AS T 
                            WHERE T.status = 'ว่าง' AND T.active != 'S' AND T.product_id = '$product->product_id'
                                ")->getFirst();
                                            
                        // * ถ้ามีสถานะ ว่าง
                        if ($transaction) {

                            $update_transaction = Transaction::findFirst($transaction->transaction_id);
                            $update_transaction->status = 'ไถ่คืนทั้งหมด';
                            $update_transaction->uuid = $uuid;
                            if ($update_transaction->save() == false) {
                                $transaction_->rollback(
                                    'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }

                            $total_product_value = $total_product_value + $product->value;

                            $condition = TRUE;

                    } else { 
                        // TODO ถ้าไม่มีสถานะว่างให้เช็คว่าเป็นสถานะอะไร เช่น หลุด ...
                        $transaction = $this->modelsManager->executeQuery(
                            "SELECT T.*
                                FROM transaction AS T 
                                WHERE T.product_id = '$product->product_id'
                                ORDER BY T.transaction_id DESC
                                    ")->getFirst(); 

                        if($transaction->status == 'หลุด' || $transaction->status == 'ตั้งขาย' || $transaction->status == 'ตั้งขายกรณีพิเศษ'){

                            $transaction->active = 'F';
                            if ( $transaction->save() === false) {
                                $transaction_->rollback(
                                    'ขออภัยเกิดผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }

                            // * สร้างสถานะว่างใหม่
                            $new_transaction = new Transaction();
                            $new_transaction->agreement_number = $agreement_number; 
                            $new_transaction->product_id = $product->product_id; 
                            $new_transaction->start_date = $this->convertToCommonEraSQL($transaction->start_date);
                            $new_transaction->end_date = $this->convertToCommonEraSQL($transaction->end_date);
                            $new_transaction->status = 'ว่าง';
                            if ( $new_transaction->save() === false) {
                                $transaction_->rollback(
                                    'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }

                            // * อัพเดทสถานะ ว่าง ที่สร้างใหม่ -> ไถ่คืนทั้งหมด
                            $update_transaction = Transaction::findFirst($new_transaction->transaction_id);
                            $update_transaction->status = 'ไถ่คืนทั้งหมด';
                            $update_transaction->uuid = $uuid;
                            // $update_transaction->related_uuid = $transaction->uuid;
                            if ($update_transaction->save() == false) {
                                $transaction_->rollback(
                                    'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }
                            
                            $total_product_value = $total_product_value + $product->value;

                            $condition = TRUE;

                        } elseif ($transaction->status == 'มัดจำ') {
                                
                            $deposit_transactions = $this->modelsManager->executeQuery(
                                "SELECT T.*
                                    FROM transaction AS T
                                    WHERE T.product_id = '$product->product_id' AND T.status = 'มัดจำ' 
                                ");

                            foreach($deposit_transactions as $deposit_transaction){
                                $deposit_transaction->active = 'F';
                                if ( $deposit_transaction->save() === false) {
                                    $transaction_->rollback(
                                        'ขออภัยเกิดผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                    );
                                }
                            }

                            // * สร้างสถานะว่างใหม่
                            $new_transaction = new Transaction();
                            $new_transaction->agreement_number = $agreement_number; 
                            $new_transaction->product_id = $product->product_id; 
                            $new_transaction->start_date = $this->convertToCommonEraSQL($transaction->start_date);
                            $new_transaction->end_date = $this->convertToCommonEraSQL($transaction->end_date);
                            $new_transaction->status = 'ว่าง';
                            if ( $new_transaction->save() === false) {
                                $transaction_->rollback(
                                    'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }

                            // * อัพเดทสถานะ ว่าง ที่สร้างใหม่ -> ไถ่คืนทั้งหมด
                            $update_transaction = Transaction::findFirst($new_transaction->transaction_id);
                            $update_transaction->status = 'ไถ่คืนทั้งหมด';
                            $update_transaction->uuid = $uuid;
                            // $update_transaction->related_uuid = $transaction->uuid;
                            if ($update_transaction->save() == false) {
                                $transaction_->rollback(
                                    'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }
                            
                            $total_product_value = $total_product_value + $product->value;

                            $condition = TRUE;
        
                        } else { // TODO ถ้าเป็นสถานะนอกเหนือจากนั้น

                        }
                    }
                }

                if($condition == TRUE){

                    $account = new Account();
                    $account->uuid = $uuid;
                    $account->principal = $total_product_value;
                    $account->value = $agreement_withdraws - $total_product_value;
                    $account->transaction_date = $this->convertToCommonEraSQL($transaction_date);
                    $account->transaction_time =  $this->convertToTimeSQL($transaction_time);
                    if ($account->save() === false) {
                        $transaction_->rollback(
                            'ขออภัย ข้อมูลประวัติการทำรายการ ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                        );
                    }
                    
                    $transaction_ = $manager->commit();
                }

                $this->flashSession->error("เลขที่สัญญา " . $agreement_number . " ทำรายการไถ่คืนทั้งหมด");
                return $this->response->redirect('payment/search/'.$agreement_number);
            }
        } catch (TxFailed $error) {
            echo $error->getMessage();
        }
    }

    // * OK
    /* ไถ่คืนชิ้นเดียว */
    public function withdrawProductAction()
    {
        $this->view->disable();

        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($agreement_number = $this->request->getPost('agreement_number')) {

                // * Post variable
                $product_id         = $this->request->getPost('product_id');
                $transaction_date   = $this->request->getPost('transaction_date');
                $transaction_time   = $this->request->getPost('transaction_time');
                $start_date         = $this->request->getPost('start_date');
                $end_date           = $this->request->getPost('end_date');
                $note               = $this->request->getPost('note');
                $agreement_withdraw = $this->request->getPost('agreement_withdraw');

                $uuid = $this->getUuid();

                // * ค้นหาสถานะว่างของแต่ละสินค้า
                $transaction = $this->modelsManager->executeQuery(
                    "SELECT T.transaction_id
                        FROM transaction AS T 
                        WHERE T.status = 'ว่าง' AND T.active != 'S' AND T.product_id = '$product_id'
                            ")->getFirst();
    
                    // * ถ้ามีสถานะ ว่าง
                    if ($transaction) {

                        // * คำนวณยอดเงินในสัญญา
                        // TODO สามารถใช้ belongTO แทนการใช้ ORM ได้
                        $product = Product::findFirst($product_id);
                        $agreement = Agreement::findFirstByAgreementNumber($product->agreement_number);
                        $agreement->value = $agreement->value - $product->value;
                        $agreement->interest = $this->calculateInterest($agreement->value);
                        if ($agreement->save() == false) {
                            $transaction_->rollback(
                                'ขออภัย ข้อมูลสัญญา ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                        }

                        // * อัพเดทสถานะ ว่าง ที่สร้างใหม่ -> ไถ่คืนชิ้นเดียว
                        $update_transaction = Transaction::findFirst($transaction->transaction_id);
                        $update_transaction->status = 'ไถ่คืนชิ้นเดียว';
                        $update_transaction->uuid = $uuid;
                        $update_transaction->transaction_date = $this->convertToCommonEraSQL($transaction_date);
                        $update_transaction->transaction_time =  $this->convertToTimeSQL($transaction_time);
                        if ($update_transaction->save() == false) {
                            $transaction_->rollback(
                                'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                        }

                        $account = new Account();
                        $account->uuid = $uuid;
                        $account->principal = $product->value;
                        $account->value = $agreement_withdraw - $product->value;
                        $account->transaction_date = $this->convertToCommonEraSQL($transaction_date);
                        $account->transaction_time =  $this->convertToTimeSQL($transaction_time);
                        if ($account->save() === false) {
                            $transaction_->rollback(
                                'ขออภัย ข้อมูลประวัติการทำรายการ ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                        }

                        $transaction_ = $manager->commit();
                    } else {
                        // TODO ถ้าไม่มีสถานะว่างให้เช็คว่าเป็นสถานะอะไร เช่น หลุด ...
                        $transaction = $this->modelsManager->executeQuery(
                            "SELECT T.*
                                FROM transaction AS T 
                                WHERE T.product_id = '$product_id'
                                ORDER BY T.transaction_id DESC
                                    ")->getFirst(); 

                        if($transaction->status == 'หลุด' || $transaction->status == 'ตั้งขาย' || $transaction->status == 'ตั้งขายกรณีพิเศษ'){

                            $transaction->active = 'F';
                            if ( $transaction->save() === false) {
                                $transaction_->rollback(
                                    'ขออภัยเกิดผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }

                            // * สร้างสถานะว่างใหม่
                            $new_transaction = new Transaction();
                            $new_transaction->agreement_number = $agreement_number; 
                            $new_transaction->product_id = $product_id; 
                            $new_transaction->start_date = $this->convertToCommonEraSQL($transaction->start_date);
                            $new_transaction->end_date = $this->convertToCommonEraSQL($transaction->end_date);
                            $new_transaction->status = 'ว่าง';
                            if ( $new_transaction->save() === false) {
                                $transaction_->rollback(
                                    'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }

                            // * คำนวณยอดเงินในสัญญา
                            // TODO สามารถใช้ belongTO แทนการใช้ ORM ได้
                            $product = Product::findFirst($product_id);
                            $agreement = Agreement::findFirstByAgreementNumber($product->agreement_number);
                            $agreement->value = $agreement->value - $product->value;
                            $agreement->interest = $this->calculateInterest($agreement->value);
                            if ($agreement->save() == false) {
                                $transaction_->rollback(
                                    'ขออภัย ข้อมูลสัญญา ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }

                            $account = new Account();
                            $account->uuid = $uuid;
                            $account->principal = $product->value;
                            $account->value = $agreement_withdraw - $product->value;
                            $account->transaction_date = $this->convertToCommonEraSQL($transaction_date);
                            $account->transaction_time =  $this->convertToTimeSQL($transaction_time);
                            if ($account->save() === false) {
                                $transaction_->rollback(
                                    'ขออภัย ข้อมูลประวัติการทำรายการ ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }

                            // * อัพเดทสถานะ ว่าง ที่สร้างใหม่ -> ไถ่คืนชิ้นเดียว
                            $update_transaction = Transaction::findFirst($new_transaction->transaction_id);
                            $update_transaction->status = 'ไถ่คืนชิ้นเดียว';
                            $update_transaction->uuid = $uuid;
                            // $update_transaction->related_uuid = $transaction->uuid;
                            if ($update_transaction->save() == false) {
                                $transaction_->rollback(
                                    'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }
                            
                            $transaction_ = $manager->commit();

                        } elseif ($transaction->status == 'มัดจำ') {
                                
                            $deposit_transactions = $this->modelsManager->executeQuery(
                                "SELECT T.*
                                    FROM transaction AS T
                                    WHERE T.product_id = '$product_id' AND T.status = 'มัดจำ' 
                                ");
    
                            foreach($deposit_transactions as $deposit_transaction){
                                $deposit_transaction->active = 'F';
                                if ( $deposit_transaction->save() === false) {
                                    $transaction_->rollback(
                                        'ขออภัยเกิดผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                    );
                                }
                            }

                            // * สร้างสถานะว่างใหม่
                            $new_transaction = new Transaction();
                            $new_transaction->agreement_number = $agreement_number; 
                            $new_transaction->product_id = $product_id; 
                            $new_transaction->start_date = $this->convertToCommonEraSQL($transaction->start_date);
                            $new_transaction->end_date = $this->convertToCommonEraSQL($transaction->end_date);
                            $new_transaction->status = 'ว่าง';
                            if ( $new_transaction->save() === false) {
                                $transaction_->rollback(
                                    'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }

                            // * คำนวณยอดเงินในสัญญา
                            // TODO สามารถใช้ belongTO แทนการใช้ ORM ได้
                            $product = Product::findFirst($product_id);
                            $agreement = Agreement::findFirstByAgreementNumber($product->agreement_number);
                            $agreement->value = $agreement->value - $product->value;
                            $agreement->interest = $this->calculateInterest($agreement->value);
                            if ($agreement->save() == false) {
                                $transaction_->rollback(
                                    'ขออภัย ข้อมูลสัญญา ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }

                            $account = new Account();
                            $account->uuid = $uuid;
                            $account->principal = $product->value;
                            $account->value = $agreement_withdraw - $product->value;
                            $account->transaction_date = $this->convertToCommonEraSQL($transaction_date);
                            $account->transaction_time =  $this->convertToTimeSQL($transaction_time);
                            if ($account->save() === false) {
                                $transaction_->rollback(
                                    'ขออภัย ข้อมูลประวัติการทำรายการ ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }

                            // * อัพเดทสถานะ ว่าง ที่สร้างใหม่ -> ไถ่คืนชิ้นเดียว
                            $update_transaction = Transaction::findFirst($new_transaction->transaction_id);
                            $update_transaction->status = 'ไถ่คืนชิ้นเดียว';
                            $update_transaction->uuid = $uuid;
                            // $update_transaction->related_uuid = $transaction->uuid;
                            if ($update_transaction->save() == false) {
                                $transaction_->rollback(
                                    'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }

                            $transaction_ = $manager->commit();

                        } else { // TODO ถ้าเป็นสถานะนอกเหนือจากนั้น

                        }
                    }
                $this->flashSession->error("เลขที่สัญญา " . $agreement_number . " ทำรายการไถ่คืนชิ้นเดียว");
                return $this->response->redirect('payment/search/'.$product->agreement_number);
            }
        } catch (TxFailed $error) {
            echo $error->getMessage();
        }
    }

    // * OK
    public function editWithdrawTransactionAction()
    {
        $this->view->disable();

        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($agreement_number = $this->request->getPOST('agreement_number')) {

                // * Post variable
                $account_id         = $this->request->getPOST('account_id');
                $transaction_date   = $this->request->getPost('transaction_date');
                $transaction_time   = $this->request->getPost('transaction_time');
                $interest           = $this->request->getPost('interest');
                $note               = $this->request->getPost('note');

                $account = Account::findFirst($account_id);
                $account->transaction_date = $this->convertToCommonEraSQL($transaction_date);
                $account->transaction_time = $this->convertToTimeSQL($transaction_time);
                $account->value = $interest;

                // * แก้ไขรายละเอียดธุรกรรม
                foreach ($account->transactionDetail as $transactionDetail) {
                    $transactionDetail->note = $note;
                    if ($transactionDetail->save() == false) {
                        $transaction_->rollback(
                            'ขออภัย การทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                        );
                    }
                }

                if ($account->save() == false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลประวัติการทำรายการ ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }
                $transaction_ = $manager->commit();

                return $this->response->redirect('payment/search/'.$agreement_number);
            }
        } catch (TxFailed $error) {
            echo $error->getMessage();
        }
    }

    // * OK
    public function deleteWithdrawsTransactionAction()
    {
        $this->view->disable();

        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($account_id = $this->request->get('account_id')) {

                $account = Account::findFirst($account_id);
                foreach ($account->transactionDetail as $transactionDetail) {
                    // * ค้นหา transaction ก่อนหน้า
                    $prev_transaction = $this->modelsManager->executeQuery(
                        "SELECT T.*
                            FROM transaction AS T
                            WHERE T.transaction_id != ( 
                                SELECT T.transaction_id
                                    FROM transaction AS T
                                    WHERE T.transaction_id = '$transactionDetail->transaction_id' )
                            AND T.product_id = '$transactionDetail->product_id'
                            ORDER BY T.transaction_id DESC
                        ")->getFirst();

                    // * ถ้าสถานะก่อนหน้าเป็น หลุด , ตั้งขาย , ... ให้คืน active -> T และลบสถานะที่เกี่ยวข้องทั้งหมด
                    if($prev_transaction->status == 'หลุด' || $prev_transaction->status == 'ตั้งขาย' || $prev_transaction->status == 'ตั้งขายกรณีพิเศษ'){
                        $prev_transaction->active = 'T';
                        if ($prev_transaction->save() == false){
                            $transaction_->rollback(
                                'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                        }
                        // * ลบสถานะไถ่คืนทั้งหมด
                        if ($transactionDetail->delete() == false) {
                            $transaction_->rollback(
                                'ขออภัย การทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                        }
                    }else if($prev_transaction->status == 'มัดจำ'){

                        $deposit_transactions = $this->modelsManager->executeQuery(
                            "SELECT T.*
                                FROM transaction AS T
                                WHERE T.product_id = '$transactionDetail->product_id' AND T.status = 'มัดจำ' 
                            ");

                        foreach($deposit_transactions as $deposit_transaction){
                            $deposit_transaction->active = 'T';
                            if ( $deposit_transaction->save() === false) {
                                $transaction_->rollback(
                                    'ขออภัยเกิดผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }
                        }

                        // * ลบสถานะไถ่คืนทั้งหมด
                        if ($transactionDetail->delete() == false) {
                            $transaction_->rollback(
                                'ขออภัย การทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                        }

                    // * ถ้าสถานะก่อนหน้าเป็น ต่อดอก , ไถ่คืน ให้คืนสถานะว่าง
                    }else{ 
                        $transactionDetail->status = 'ว่าง';
                        $transactionDetail->uuid = null;
                        if ($transactionDetail->save() == false) {
                            $transaction_->rollback(
                                'ขออภัย การทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                        }
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
    public function deleteWithdrawTransactionAction()
    {
        $this->view->disable();

        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($account_id = $this->request->get('account_id')) {

                $account = Account::findFirst($account_id);
                foreach ($account->transactionDetail as $transactionDetail) {
                    // * ค้นหา transaction ก่อนหน้า
                    $prev_transaction = $this->modelsManager->executeQuery(
                        "SELECT T.*
                            FROM transaction AS T
                            WHERE T.transaction_id != ( 
                                SELECT T.transaction_id
                                    FROM transaction AS T
                                    WHERE T.transaction_id = '$transactionDetail->transaction_id' )
                            AND T.product_id = '$transactionDetail->product_id'
                            ORDER BY T.transaction_id DESC
                        ")->getFirst();

                    // * ถ้าสถานะก่อนหน้าเป็น หลุด , ตั้งขาย , ... ให้คืน active -> T และลบสถานะที่เกี่ยวข้องทั้งหมด
                    if($prev_transaction->status == 'หลุด' || $prev_transaction->status == 'ตั้งขาย' || $prev_transaction->status == 'ตั้งขายกรณีพิเศษ'){
                        $prev_transaction->active = 'T';
                        if ($prev_transaction->save() == false){
                            $transaction_->rollback(
                                'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                        }
                        // * ลบสถานะไถ่คืนทั้งหมด
                        if ($transactionDetail->delete() == false) {
                            $transaction_->rollback(
                                'ขออภัย การทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                        }

                        // * คำนวณยอดเงินในสัญญา
                        $agreement = Agreement::findFirstByAgreementNumber($transactionDetail->agreement_number);
                        $agreement->value = $agreement->value + $account->principal;
                        $agreement->interest = $this->calculateInterest($agreement->value);
                        if ($agreement->save() == false) {
                            $transaction_->rollback(
                                'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                        }
                    }else if($prev_transaction->status == 'มัดจำ'){

                        $deposit_transactions = $this->modelsManager->executeQuery(
                            "SELECT T.*
                                FROM transaction AS T
                                WHERE T.product_id = '$transactionDetail->product_id' AND T.status = 'มัดจำ' 
                            ");

                        foreach($deposit_transactions as $deposit_transaction){
                            $deposit_transaction->active = 'T';
                            if ( $deposit_transaction->save() === false) {
                                $transaction_->rollback(
                                    'ขออภัยเกิดผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                                );
                            }
                        }

                        // * ลบสถานะไถ่คืนทั้งหมด
                        if ($transactionDetail->delete() == false) {
                            $transaction_->rollback(
                                'ขออภัย การทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                        }

                        // * คำนวณยอดเงินในสัญญา
                        $agreement = Agreement::findFirstByAgreementNumber($transactionDetail->agreement_number);
                        $agreement->value = $agreement->value + $account->principal;
                        $agreement->interest = $this->calculateInterest($agreement->value);
                        if ($agreement->save() == false) {
                            $transaction_->rollback(
                                'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                        }

                    // * ถ้าสถานะก่อนหน้าเป็น ต่อดอก , ไถ่คืน ให้คืนสถานะว่าง
                    }else{ 
                        $transactionDetail->status = 'ว่าง';
                        $transactionDetail->uuid = null;
                        if ($transactionDetail->save() == false) {
                            $transaction_->rollback(
                                'ขออภัย การทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                        }

                        // * คำนวณยอดเงินในสัญญา
                        $agreement = Agreement::findFirstByAgreementNumber($transactionDetail->agreement_number);
                        $agreement->value = $agreement->value + $account->principal;
                        $agreement->interest = $this->calculateInterest($agreement->value);
                        if ($agreement->save() == false) {
                            $transaction_->rollback(
                                'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                        }
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
    public function increaseValueAction()
    {
        $this->view->disable();

        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($agreement_number = $this->request->getPost('agreement_number')) {

                // * Post variable
                $product_id             = $this->request->getPost('product_id');
                $transaction_date       = $this->request->getPost('transaction_date');
                $transaction_time       = $this->request->getPost('transaction_time');
                $start_date             = $this->request->getPost('start_date');
                $end_date               = $this->request->getPost('end_date');
                $note                   = $this->request->getPost('note');
                $increase_value = $this->request->getPost('increase_value');

                $uuid = $this->getUuid();

                // * คำนวณยอดของสินค้า
                $product = Product::findFirst($product_id);
                $product->value = $product->value + $increase_value;
                $product->interest = $this->calculateInterest($product->value);
                if ($product->save() == false) {
                    $transaction_->rollback(
                        'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                // * คำนวณยอดในสัญญา
                $agreement = Agreement::findFirstByAgreementNumber($product->agreement_number);
                $agreement->value = $agreement->value + $increase_value;
                $agreement->interest = $this->calculateInterest($agreement->value);
                if ($agreement->save() == false) {
                    $transaction_->rollback(
                        'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                // * ค้นหาสถานะว่าง -> เพิ่มเงิน
                $update_transaction = $this->modelsManager->executeQuery(
                    "SELECT T.*
                        FROM transaction AS T 
                        WHERE ( T.status = 'ว่าง' OR T.status = 'หลุด' ) AND T.product_id = '$product_id'
                            ")->getFirst();

                $update_transaction->status = 'เพิ่มเงิน';
                $update_transaction->uuid = $uuid;
                $update_transaction->note = $note;
                if ($update_transaction->save() == false) {
                    $transaction_->rollback(
                        'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                $account = new Account();
                $account->uuid = $uuid;
                $account->value = $increase_value;
                $account->transaction_date = $this->convertToCommonEraSQL($transaction_date);
                $account->transaction_time =  $this->convertToTimeSQL($transaction_time);
                if ( $account->save() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลประวัติการทำรายการ ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                $new_transaction = new Transaction();
                $new_transaction->agreement_number = $agreement_number;
                $new_transaction->product_id = $product_id;
                $new_transaction->start_date = $this->convertToCommonEraSQL($start_date);
                $new_transaction->end_date = $this->convertToCommonEraSQL($end_date);
                $new_transaction->status = 'ว่าง';
                if ($new_transaction->save() == false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }
                $transaction_ = $manager->commit();

                $this->flashSession->success("เลขที่สัญญา " . $agreement_number . " ทำรายการเพิ่มเงิน");
                return $this->response->redirect('payment/print-receipt/เพิ่มเงิน/'.$uuid.'');
            }
        } catch (TxFailed $error) {
            echo $error->getMessage();
        }
    }

    // * OK
    public function editIncreaseTransactionAction()
    {
        $this->view->disable();
        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($agreement_number = $this->request->getPost('agreement_number')) {

                // * Post variable
                $account_id = $this->request->getPost('account_id');
                $transaction_date = $this->request->getPost('transaction_date');
                $transaction_time = $this->request->getPost('transaction_time');
                $increase_value = $this->request->getPost('increase_value');
                $note = $this->request->getPost('note');

                // * ค้นหารายละเอียดต่างๆ
                $transaction = $this->modelsManager->executeQuery(
                    'SELECT T.agreement_number , T.product_id , A.value
                    FROM account AS A
                    JOIN transaction AS T
                    ON A.uuid = T.uuid
                    WHERE A.account_id = :account_id:',
                    [
                        'account_id' => $account_id,
                    ]
                )->getFirst();

                // * แก้ไขราคาสินค้า
                $product = Product::findFirst($transaction->product_id);
                $product->value = ($product->value - $transaction->value) + $increase_value; // ( ยอดเงินเดิม - ยอดเงินเพิ่มเดิม) + ยอดเงินเพิ่มใหม่
                $product->interest = $this->calculateInterest($product->value);
                if ($product->save() == false) {
                    $transaction_->rollback(
                        'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                // * แก้ไขราคาในสัญญา
                $product_value = 0;
                $products = Product::findByAgreementNumber($transaction->agreement_number);
                foreach ($products as $product) {
                    $product_value = $product_value + $product->value;
                }
                $agreement = Agreement::findFirstByAgreementNumber($transaction->agreement_number);
                $agreement->value = $product_value;
                $agreement->interest = $this->calculateInterest($product_value);
                if ($agreement->save() == false) {
                    $transaction->rollback(
                        'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                $account = Account::findFirst($account_id);
                $account->transaction_date = $this->convertToCommonEraSQL($transaction_date);
                $account->transaction_time = $this->convertToTimeSQL($transaction_time);
                $account->value = $increase_value;

                // * แก้ไขรายละเอียดธุรกรรม
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
                return $this->response->redirect('payment/search/'.$agreement_number);
            }
        } catch (TxFailed $error) {
            echo $error->getMessage();
        }
    }

    // * OK
    public function deleteIncreaseTransactionAction()
    {

        $this->view->disable();

        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($account_id = $this->request->get('account_id')) {

                $transaction = $this->modelsManager->executeQuery(
                    'SELECT T.agreement_number , T.product_id , A.value
                    FROM account AS A
                    JOIN transaction AS T
                    ON A.uuid = T.uuid
                    WHERE A.account_id = :account_id:',
                    [
                        'account_id' => $account_id,
                    ]
                )->getFirst();

                $account = Account::findFirst($account_id);

                $product = Product::findFirst($transaction->product_id);
                $product->value = ($product->value - $account->value); // ( ยอดเงินเดิม - ยอดเงินเพิ่มเดิม)
                $product->interest = $this->calculateInterest($product->value);
                if ($product->save() == false) {
                    $transaction_->rollback(
                        'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                $product_value = 0;
                $products = Product::findByAgreementNumber($transaction->agreement_number);
                foreach ($products as $product) {
                    $product_value = $product_value + $product->value;
                }
                $agreement = Agreement::findFirstByAgreementNumber($transaction->agreement_number);
                $agreement->value = $product_value;
                $agreement->interest = $this->calculateInterest($product_value);
                if ($agreement->save() == false) {
                    $transaction->rollback(
                        'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                if ($account->delete() == false) {
                    $transaction_->rollback(
                        'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                $transaction_ = $manager->commit();
            }

        } catch (TxFailed $error) {
            echo $error->getMessage();
        }
    }

    // * OK
    public function decreaseValueAction()
    {

        $this->view->disable();

        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($agreement_number = $this->request->getPost('agreement_number')) {

                // * Post variable
                $product_id             = $this->request->getPost('product_id');
                $transaction_date       = $this->request->getPost('transaction_date');
                $transaction_time       = $this->request->getPost('transaction_time');
                $start_date             = $this->request->getPost('start_date');
                $end_date               = $this->request->getPost('end_date');
                $note                   = $this->request->getPost('note');
                $principal              = $this->request->getPost('principal');
                $decrease_value         = $this->request->getPost('decrease_value');

                $uuid = $this->getUuid();

                // * คำนวณยอดของสินค้า
                $product = Product::findFirst($product_id);
                $product->value = $product->value - $principal;
                $product->interest = $this->calculateInterest($product->value);
                if ($product->save() == false) {
                    $transaction_->rollback(
                        'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                // * คำนวณยอดในสัญญา
                $agreement = Agreement::findFirstByAgreementNumber($product->agreement_number);
                $agreement->value = $agreement->value - $principal;
                $agreement->interest = $this->calculateInterest($agreement->value);
                if ($agreement->save() == false) {
                    $transaction_->rollback(
                        'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                // * ค้นหาสถานะว่าง -> ลดต้น
                $update_transaction = $this->modelsManager->executeQuery(
                    "SELECT T.*
                        FROM transaction AS T 
                        WHERE ( T.status = 'ว่าง' OR T.status = 'หลุด' ) AND T.product_id = '$product_id'
                            ")->getFirst();

                $update_transaction->status = 'ลดต้น';
                $update_transaction->uuid = $uuid;
                $update_transaction->note = $note;
                if ($update_transaction->save() == false) {
                    $transaction_->rollback(
                        'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                $account = new Account();
                $account->uuid = $uuid;
                $account->principal = $principal;
                $account->value = $decrease_value;
                $account->transaction_date = $this->convertToCommonEraSQL($transaction_date);
                $account->transaction_time =  $this->convertToTimeSQL($transaction_time);
                if ( $account->save() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลประวัติการทำรายการ ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                $new_transaction = new Transaction();
                $new_transaction->agreement_number = $agreement_number;
                $new_transaction->product_id = $product_id;
                $new_transaction->start_date = $this->convertToCommonEraSQL($start_date);
                $new_transaction->end_date = $this->convertToCommonEraSQL($end_date);
                $new_transaction->status = 'ว่าง';
                if ($new_transaction->save() == false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }
                $transaction_ = $manager->commit();

                $this->flashSession->success("เลขที่สัญญา " . $agreement_number . " ทำรายการลดต้น");
                return $this->response->redirect('payment/print-receipt/ลดต้น/'.$uuid.'');
            }
        } catch (TxFailed $error) {
            echo $error->getMessage();
        }
    }

    // * OK
    public function editDecreaseTransactionAction()
    {
        $this->view->disable();
        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($agreement_number = $this->request->getPost('agreement_number')) {

                $account_id = $this->request->getPost('account_id');
                $transaction_date = $this->request->getPost('transaction_date');
                $transaction_time = $this->request->getPost('transaction_time');
                $principle_value = $this->request->getPost('principle_value');
                $interest_value = $this->request->getPost('interest_value');
                $note = $this->request->getPost('note');

                // * ค้นหารายละเอียดต่างๆ
                $transaction = $this->modelsManager->executeQuery(
                    'SELECT T.agreement_number , T.product_id , A.value , A.principal
                    FROM account AS A
                    JOIN transaction AS T
                    ON A.uuid = T.uuid
                    WHERE A.account_id = :account_id:',
                    [
                        'account_id' => $account_id,
                    ]
                )->getFirst();

                $product = Product::findFirst($transaction->product_id);
                $product->value = ($product->value + $transaction->principal) - $principle_value; // ( ยอดเงินเดิม + ยอดเงินลดต้นเดิม) - ยอดเงินลดต้นใหม่
                $product->interest = $this->calculateInterest($product->value);
                if ($product->save() == false) {
                    $transaction_->rollback(
                        'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                $product_value = 0;
                $products = Product::findByAgreementNumber($transaction->agreement_number);
                foreach ($products as $product) {
                    $product_value = $product_value + $product->value;
                }
                $agreement = Agreement::findFirstByAgreementNumber($transaction->agreement_number);
                $agreement->value = $product_value;
                $agreement->interest = $this->calculateInterest($product_value);
                if ($agreement->save() == false) {
                    $transaction->rollback(
                        'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                $account = Account::findFirst($account_id);
                $account->transaction_date = $this->convertToCommonEraSQL($transaction_date);
                $account->transaction_time = $this->convertToTimeSQL($transaction_time);
                $account->principal = $principle_value;
                $account->value = $interest_value;

                // * แก้ไขรายละเอียดธุรกรรม
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
                return $this->response->redirect('payment/search/'.$agreement_number);
            }
        } catch (TxFailed $error) {
            echo $error->getMessage();
        }
    }

    // * OK
    public function deleteDecreaseTransactionAction()
    {

        $this->view->disable();

        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($account_id = $this->request->get('account_id')) {

                $transaction = $this->modelsManager->executeQuery(
                    'SELECT T.agreement_number , T.product_id , A.principal
                    FROM account AS A
                    JOIN transaction AS T
                    ON A.uuid = T.uuid
                    WHERE A.account_id = :account_id:',
                    [
                        'account_id' => $account_id,
                    ]
                )->getFirst();

                $account = Account::findFirst($account_id);

                $product = Product::findFirst($transaction->product_id);
                $product->value = ($product->value + $account->principal); // ( ยอดเงินเดิม + ยอดเงินที่ลดต้น)
                $product->interest = $this->calculateInterest($product->value);
                if ($product->save() == false) {
                    $transaction_->rollback(
                        'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                $product_value = 0;
                $products = Product::findByAgreementNumber($transaction->agreement_number);
                foreach ($products as $product) {
                    $product_value = $product_value + $product->value;
                }
                $agreement = Agreement::findFirstByAgreementNumber($transaction->agreement_number);
                $agreement->value = $product_value;
                $agreement->interest = $this->calculateInterest($product_value);
                if ($agreement->save() == false) {
                    $transaction->rollback(
                        'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                if ($account->delete() == false) {
                    $transaction_->rollback(
                        'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }
                $transaction_ = $manager->commit();
            }

        } catch (TxFailed $error) {
            echo $error->getMessage();
        }
    }

    // * OK
    public function editSaleTransactionAction()
    {
        $this->view->disable();

        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($agreement_number = $this->request->getPOST('agreement_number')) {

                $account_id = $this->request->getPOST('account_id');
                $status = $this->request->getPOST('status');
                $transaction_date = $this->request->getPost('transaction_date');
                $transaction_time = $this->request->getPost('transaction_time');
                $sale_value = $this->request->getPost('sale_value');
                $note = $this->request->getPost('note');

                $account = Account::findFirst($account_id);
                foreach($account->transactionDetail as $transaction){
                    $transaction->status = $status;
                    $transaction->note = $note;
                    if($transaction->save() === false){
                        $transaction_->rollback(
                            'ขออภัยเกิดผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                        );
                    }
                }
                $account->transaction_date = $this->convertToCommonEraSQL($transaction_date);
                $account->transaction_time = $transaction_time;
                $account->value = $sale_value;
                if ($account->save() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลประวัติการทำรายการ ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }
                $transaction_ = $manager->commit();
                return $this->response->redirect('payment/search/'.$agreement_number);
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
                    if ($update_transaction) {
                        $update_transaction->active = 'T';
                        if ($update_transaction->save() === false) {
                            $transaction_->rollback(
                             'ขออภัยเกิดผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                         );
                        }
                    } else {
                        $update_transaction = $this->modelsManager->executeQuery(
                            'SELECT T.*
                                FROM transaction AS T
                                WHERE T.agreement_number = :agreement_number: 
                                    AND T.status = :status: 
                                    AND T.related_uuid IS NULL
                                    AND T.active = :active:',
                                [
                                    'agreement_number'  => $transaction->agreement_number,
                                    'status' => 'หลุด',
                                    'active'    => 'F'
                                ]
                            )->getFirst();
                        $update_transaction->active = 'T';
                        if ($update_transaction->save() === false) {
                            $transaction_->rollback(
                             'ขออภัยเกิดผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                         );
                        }
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
    public function editSoldOutTransactionAction()
    {
        $this->view->disable();

        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($agreement_number = $this->request->getPOST('agreement_number')) {

                $account_id = $this->request->getPOST('account_id');
                $transaction_date = $this->request->getPost('transaction_date');
                $sold_value = $this->request->getPost('sold_value');
                $note = $this->request->getPost('note');

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
                $account->value = $sold_value;
                if ($account->save() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลประวัติการทำรายการ ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }
                $transaction_ = $manager->commit();
                return $this->response->redirect('payment/search/'.$agreement_number);
            }

        } catch (TxFailed $error) {
            echo $error->getMessage();
        }
    }

   // * OK
   public function deleteSoldOutTransactionAction()
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
    public function editDepositTransactionAction()
    {
        $this->view->disable();
            try {

            $manager = new TxManager();
            $transaction_ = $manager->get();
            if ($agreement_number = $this->request->getPost('agreement_number')) 
            {
                    $account_id = $this->request->getPost('account_id');
                    $transaction_date =  $this->request->getPost('transaction_date');
                    $deposit_value = $this->request->getPost('deposit_value');
                    $note = $this->request->getPost('note');

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
                return $this->response->redirect('payment/search/'.$agreement_number);

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
}
