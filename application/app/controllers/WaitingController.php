<?php
use Phalcon\Crypt;
use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;
use Phalcon\Mvc\View;

class WaitingController extends ControllerBase
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
        $this->tag->prependTitle("รายการหลุด ");
        $this->assets->addJs("js/waiting/index.js");
    }

    // * OK
    public function server_processingAction()
    {
        $this->view->disable();
        $data = array();

        $products = $this->modelsManager->executeQuery(
            "SELECT P.* , T.* 
                FROM product AS P
                JOIN transaction AS T
                ON P.product_id = T.product_id 
                WHERE (T.status = 'หลุด' OR T.status LIKE '%ซื้อเข้า%') AND T.active = 'T' AND P.related_product IS NULL"
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
                $sale_value             = $this->request->get('sale_value');
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
}




