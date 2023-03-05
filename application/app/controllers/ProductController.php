<?php
use Phalcon\Crypt;
use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;
use Phalcon\Mvc\View;

class ProductController extends ControllerBase
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
        $this->tag->prependTitle("รายชื่อสินค้า ");
        $this->assets->addJs("js/product/index.js");
    }

    public function server_processingAction($product_name)
    {
        $this->view->disable();
        $data = array();
        if($product_name != 'all'){
            $products = $this->modelsManager->executeQuery(
                "SELECT P.*
                    FROM Product AS P
                    WHERE P.related_product IS NULL AND P.name = BINARY('$product_name')
                    ORDER BY P.agreement_number ASC"
            );
        }else{
            $products = $this->modelsManager->executeQuery(
                "SELECT P.*
                    FROM Product AS P
                    WHERE P.related_product IS NULL
                    ORDER BY P.agreement_number ASC"
            );
        }

        foreach($products as $product){

            $transaction = $this->modelsManager->executeQuery(
                "SELECT T.* 
                    FROM Transaction AS T
                    WHERE T.product_id = '$product->product_id'
                    ORDER BY T.transaction_id DESC"
            )->getFirst();

            if($transaction){
                $status = $transaction->status;
            }

            if($this->session->get("role") == 'administrator'){
                $link = '<a href="../payment/search/'.$product->agreement_number.'" target="_blank">'.$product->agreement_number.'</a>';
            }else{
                $link = $product->agreement_number;
            }

           $data[] = array
           (
               'link' => $link,
               'product_id' => $product->product_id,
               'agreement_number'    => $product->agreement_number,
               'product_name' => $product->name,
               'product_brand'  => $product->brand,
               'product_detail'     => $product->detail,
               'status'             => $status,
               'product_value'      => number_format($product->value)
           );
        }

        $data_json = json_encode($data);
        echo $data_json;
    }

    public function getProductAction()
    {
        $this->view->disable();
        $value = $_GET['term'];
        $products = 
        $this->modelsManager->executeQuery(
            'SELECT P.*
            FROM Product AS P
            WHERE       P.name      LIKE :value:
            GROUP BY BINARY(P.name)
            LIMIT 50',
            [
            "value" => $value.'%'
            ]
        );
        foreach($products as $product)
        {
                $data[] = array(
                    'label'     => $product->name,
                    'value'     => $product->name,
                    'name'    => $product->name,
            );
        }
      echo json_encode($data);
    }

    // * OK
    public function editProductInformationAction()
    {
        $this->view->disable();
        try {
            $manager = new TxManager();
            $transaction_ = $manager->get();
            if ($product_id = $this->request->get('product_id'))
            {

                $product_name       = $this->request->get('product_name');
                $product_brand      = $this->request->get('product_brand');
                $product_detail     = $this->request->get('product_detail');
                $product_value      = $this->convertToNumber($this->request->get('product_value'));

                // * แก้ไขราคาสินค้า
                $product = Product::findFirst($product_id);
                $product->name = $product_name;
                $product->brand = $product_brand;
                $product->detail = $product_detail;
                $product->value = $product_value;
                $product->interest = $this->calculateInterest($product_value);
                if ($product->save() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลสินค้า ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }
 
                // * แก้ไขยอดรวมในสัญญา
                $total_product_value = 0;
                $products = Product::findByagreement_number($product->agreement_number);
                foreach($products as $product){
                    $total_product_value = $total_product_value + $product->value;
                }
                $agreement = Agreement::findFirstByAgreementNumber($product->agreement_number);
                $agreement->value = $total_product_value;
                $agreement->interest = $this->calculateInterest($agreement->value);
                if ($agreement->save() == false) {
                    $transaction_->rollback(
                        'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                // * แก้ไขยอด transaction
                $transaction = $this->modelsManager->executeQuery(
                    "SELECT T.uuid 
                        FROM transaction as T
                        WHERE T.product_id = '$product_id' 
                        AND (T.status = 'ฝาก' OR T.status = 'ซื้อเข้า' OR T.status = 'เพิ่มสินค้า') 
                        ")->getFirst();
                if($transaction){
                    $account = Account::findFirstByUuid($transaction->uuid);
                    $account->value = $product_value;
                    if ($account->save() == false) {
                        $transaction_->rollback(
                            'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                        );
                    }
                }
            }
            $transaction_ = $manager->commit();
        } catch (TxFailed $error) {
            echo $error->getMessage();
        }
    }

    // * OK
    public function deleteProductInformationAction()
    {
        $this->view->disable();
        try {
            $manager = new TxManager();
            $transaction_ = $manager->get();
            if ($product_id = $this->request->get('product_id'))
            {
                $transactions = Transaction::findByproduct_id($product_id);
                foreach($transactions as $transaction){
                    // * ลบข้อมูลใน transaction
                    if($transaction->uuid){
                        $account = Account::findFirstByUuid($transaction->uuid);
                        if ($account->delete() === false) {
                            $transaction_->rollback(
                                'ขออภัย ข้อมูลประวัติการทำรายการ ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                            );
                        }
                    }
                    // * ลบรายละเอียดใน transaction
                    if ($transaction->delete() === false) {
                        $transaction_->rollback(
                            'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                        );
                    }
                }
                
                $product = Product::findFirst($product_id);
                // * ลบสินค้า
                if ($product->delete() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลสินค้า ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                // * แก้ไขยอดรวมในสัญญา
                $total_product_value = 0;
                $products = Product::findByagreement_number($product->agreement_number);
                foreach($products as $product){
                    $total_product_value = $total_product_value + $product->value;
                }
                $agreement = Agreement::findFirstByAgreementNumber($product->agreement_number);
                $agreement->value = $total_product_value;
                $agreement->interest = $this->calculateInterest($agreement->value);
                if ($agreement->save() == false) {
                    $transaction_->rollback(
                        'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

            }
            $transaction_ = $manager->commit();
        } catch (TxFailed $error) {
        echo $error->getMessage();
        }
    } 


    function editMultiProductAction(){
        $this->view->disable();
        try{
            $manager = new TxManager();
            $transaction_ = $manager->get();
            if ($product_list_json = $this->request->getPost('product_list')) {
                $product_name = $this->request->getPost('product_name');
                $product_brand = $this->request->getPost('product_brand');
                $product_detail = $this->request->getPost('product_detail');
                foreach(json_decode($product_list_json) as $product_id){
                    $product = Product::findFirst($product_id);
                    if($product_name != ''){
                        $product->name = trim($product_name);
                    }

                    if($product_brand != '-'){
                        $product->brand = trim($product_brand);
                    }

                    if($product_detail != '-'){
                        $product->detail = trim($product_detail);
                    }

                    if($product->save() == false){
                        $transaction_->rollback(
                            'ขออภัยเกิดข้อผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                        );
                    }
                    $transaction_ = $manager->commit();
                    $this->response->redirect("product/");
                }
            }
        }catch (TXFailed $error){
            echo $error->getMessage();
        }

    }

}
