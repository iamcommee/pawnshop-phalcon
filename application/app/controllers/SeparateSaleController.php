<?php
use Phalcon\Crypt;
use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;
use Phalcon\Mvc\View;

class SeparateSaleController extends ControllerBase
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
        $this->tag->prependTitle("รายการสินค้าแยกขาย ");
        $this->assets->addJs("js/separate_sale/index.js");
    }

    // * OK
    public function server_processingAction()
    {
        $this->view->disable();
        $data = array();
        $crypt = new Crypt();

        $products = $this->modelsManager->executeQuery(
            "SELECT P.* , T.* , A.*
                FROM product AS P
                JOIN transaction AS T
                ON P.product_id = T.product_id 
                JOIN account AS A 
                ON T.uuid = A.uuid 
                WHERE T.status = 'ตั้งขายกรณีพิเศษ' OR T.status = 'แยกขาย'
                GROUP BY P.product_id"
        );

        foreach ($products as $product) {

            if($this->session->get("role") == 'administrator'){
                $link = '<a href="../payment/search/'.$product->P->agreement_number.'" target="_blank">'.$product->P->agreement_number.'</a>';
            }else{
                $link = $product->P->agreement_number;
            }
        
            if($product->T->status == 'แยกขาย'){                         
                $data[] = array
                (
                    'link' => $link,
                    'agreement_number' => $product->P->agreement_number,
                    'product_id'   => $product->P->product_id,
                    'product_name' => $product->P->name,
                    'product_brand' => $product->P->brand,
                    'product_detail' => $product->P->detail,
                    'product_value' => number_format($product->P->value),
                    'sale_value'    => number_format($product->A->value),
                    'transaction_date' => $this->convertToBuddhistEra($product->A->transaction_date).' '.$product->A->transaction_time,
                    'note'          => $product->T->note,
                    'tag'            => 'สินค้าหลัก',
                );
            }
            else{
                $related_product = Transaction::findFirstByUuid($product->T->related_uuid);
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
                    'tag'            => 'แยกขายจาก '.$related_product->product->name,
                );
            }
            
        }
        $data_json = json_encode($data);
        echo $data_json;   
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

                $sale_value = $this->convertToNumber($this->request->get('sale_value'));
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
}
