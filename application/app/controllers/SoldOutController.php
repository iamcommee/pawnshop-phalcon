<?php
use Phalcon\Crypt;
use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;
use Phalcon\Mvc\View;

class SoldOutController extends ControllerBase
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
        $this->tag->prependTitle("รายการสินค้าขายแล้ว ");
        $this->assets->addJs("js/sold_out/index.js");
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
                WHERE T.status LIKE '%ขายแล้ว%' AND T.active = 'T' "
        );

        foreach ($products as $product) {

                if($this->session->get("role") == 'administrator'){
                    $link = '<a href="../payment/search/'.$product->P->agreement_number.'" target="_blank">'.$product->P->agreement_number.'</a>';
                }else{
                    $link = $product->P->agreement_number;
                }

                $sale_transaction = Transaction::findFirstByUuid($product->T->related_uuid);
                
                if($sale_transaction){
                    $sale_value = $sale_transaction->account->value;
                }else{
                    $sale_value = 0;
                }

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
                    'sale_value'     => number_format($sale_value),
                    'sold_value'     => number_format($product->A->value),
                    'transaction_date' =>  $this->convertToBuddhistEra($product->A->transaction_date),
                    'common_transaction_date' => $this->convertToCommonEra($product->A->transaction_date),
                    'note'  => $product->T->note,
                );
            
        }

        $data_json = json_encode($data);
        echo $data_json;   
    }

    // * OK
    public function editSoldOutTransactionAction()
    {
        $this->view->disable();

        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            if ($account_id = $this->request->get('account_id')) {

                $transaction_date = $this->request->get('transaction_date');
                $sold_value = $this->convertToNumber($this->request->get('sold_value'));
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
                $account->value = $sold_value;
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
}
