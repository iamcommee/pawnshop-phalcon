<?php

use Phalcon\Mvc\View;
use Phalcon\Security\Random;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;

class AgreementController extends ControllerBase
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
        $this->tag->prependTitle("สัญญาขายฝาก ");
        $this->assets->addJs("js/agreement/index.js");
    }

    public function getAgreementNumberAction()
    {
        $this->view->disable();
        $agreement_number = Agreement::maximum(
            [
                'column' => 'agreement_number'
            ]
            );
        if($agreement_number == NULL)
        {
            echo '1';
        }
        else
        {
            echo $agreement_number+1;
        }
    }

    public function getCustomerAction()
    {
        $this->view->disable();
        $value = $_GET['term'];
        $customers = 
        $this->modelsManager->executeQuery(
            'SELECT c.*
            FROM Customer c
            WHERE       c.idcard    LIKE :value:
            OR          c.firstname LIKE :value:
            OR          c.lastname  LIKE :value:
            ORDER BY    c.idcard    ASC
            LIMIT 10',
            [
            "value" => '%'.$value.'%'
            ]
        );
        foreach($customers as $customer)
        {
                $data[] = array(
                    'label'     => $customer->idcard .' '. $customer->firstname.' '. $customer->lastname ,
                    'value'     => $customer->idcard,
                    'idcard'    => $customer->idcard,
                    'firstname' => $customer->firstname,
                    'lastname'  => $customer->lastname,
                    'image'     => $customer->image
            );
        }
      echo json_encode($data);
    }

    public function getCustomerImgAction()
    {
        $this->view->disable();
        $value = $_GET['term'];
        $customer = 
        $this->modelsManager->executeQuery(
            'SELECT c.*
            FROM Customer c
            WHERE       c.idcard    LIKE :value:
            OR          c.firstname LIKE :value:
            OR          c.lastname  LIKE :value:
            ORDER BY    c.idcard    ASC',
            [
            "value" => '%'.$value.'%'
            ]
        )->getFirst();

        $data = array(
            'image'     => $customer->image
        );

      echo json_encode($data);
    }

    public function getProductAction()
    {
        $this->view->disable();
        $value = $_GET['term'];
        if(substr_count($value, ' ') == 1){ // ค้นหาด้วย product name , product brand
            $param = explode(" ", $value);
            $products = $this->modelsManager->executeQuery(
                'SELECT P.*
                FROM product as P
                WHERE       P.name    LIKE :product_name: AND P.brand LIKE :product_brand:
                GROUP BY    P.name , P.brand , P.detail
                ORDER BY    P.value   DESC
                LIMIT 10',
                [
                    'product_name' => '%'.$param[0].'%',
                    'product_brand' => '%'.$param[1].'%'
                ]
            );
            foreach($products as $product)
            {
                    $data[] = array(
                        'label'             => $product->name.' '.$product->brand.' '.$product->detail.' '.$product->value,
                        'value'             => $product->name,
                        'product_name'      => $product->name,
                        'product_brand'     => $product->brand,
                        'product_detail'    => $product->detail,
                );
            }
        } else if (substr_count($value, ' ') == 2) { // ค้นหาด้วย product name , product brand , product detail
            $param = explode(" ", $value);
            $products = $this->modelsManager->executeQuery(
                'SELECT P.*
                FROM product as P
                WHERE       P.name    LIKE :product_name: AND P.brand LIKE :product_brand: AND P.detail LIKE :product_detail:
                GROUP BY    P.name , P.brand , P.detail
                ORDER BY    P.value   DESC
                LIMIT 10',
                [
                    'product_name' => '%'.$param[0].'%',
                    'product_brand' => '%'.$param[1].'%',
                    'product_detail' => '%'.$param[2].'%'
                ]
            );
            foreach($products as $product)
            {
                    $data[] = array(
                        'label'             => $product->name.' '.$product->brand.' '.$product->detail.' '.$product->value,
                        'value'             => $product->name,
                        'product_name'      => $product->name,
                        'product_brand'     => $product->brand,
                        'product_detail'    => $product->detail,
                );
            }
        } else {
            $products = $this->modelsManager->executeQuery(
                'SELECT P.*
                FROM product as P
                WHERE       P.name    LIKE :product_name:
                GROUP BY    P.name , P.brand , P.detail
                ORDER BY    P.value   DESC
                LIMIT 20',
                [
                    'product_name' => '%'.$value.'%',
                ]
            );
            foreach($products as $product)
            {
                    $data[] = array(
                        'label'             => $product->name.' '.$product->brand.' '.$product->detail.' '.$product->value,
                        'value'             => $product->name,
                        'product_name'      => $product->name,
                        'product_brand'     => $product->brand,
                        'product_detail'    => $product->detail,
                );
            }
        }


      echo json_encode($data);
    }

    public function getMaxAgreementNumberAction(){
        $this->view->disable();
        $max_agreement_number = Agreement::maximum([
            'column'     => 'agreement_number',
        ]);

        $max_agreement_number_array = array(
            'max_agreement_number'  => $max_agreement_number + 1
        );

        echo json_encode($max_agreement_number_array);
    }

    public function createAction()
    {
        // $this->view->disable();
        $this->view->setRenderLevel(
            View::LEVEL_ACTION_VIEW
        );
        $this->assets->addCss("css/paper.css");

        /****** Post Variable *******/
        $status = $this->request->getPost('status');

        $agreement_number = $this->request->getPost('agreement_number');
        $idcard  = trim($this->request->getPost('idcard'));
        $firstname  = $this->request->getPost('firstname');
        $lastname  = $this->request->getPost('lastname');
        $customerimg = $this->request->getPost('customerimg');
        $start_date = $this->request->getPost('start_date');
        $end_date = $this->request->getPost('end_date');
        /*************/
        
        /****** Customer *******/
        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            $customer  = Customer::findFirstByidcard($idcard);
            if($customer){
                $customer->firstname = $firstname;
                $customer->lastname = $lastname;
                // ถ้ามีรูปให้อัพเดท
                if($customerimg != ''){
                    $old_name = BASE_PATH.'/public/customerimg/'.$customerimg;
                    $new_name = BASE_PATH.'/public/customerimg/'.$idcard.'-'.$this->getCurrentDate().'.jpg';
                    if(file_exists($new_name)){

                    }else{
                        rename($old_name, $new_name);
                        $customer->image = $idcard.'-'.$this->getCurrentDate().'.jpg';
                    }
                }
                if ($customer->save() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลลูกค้า ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }
            }
            else{
                $customer = new Customer();
                $customer->idcard = $idcard;
                $customer->firstname = $firstname;
                $customer->lastname = $lastname;
                // ถ้ามีรูปให้อัพเดท
                if($customerimg != ''){
                    $old_name = BASE_PATH.'/public/customerimg/'.$customerimg;
                    $new_name = BASE_PATH.'/public/customerimg/'.$idcard.'.jpg';
                    if(file_exists($new_name)){

                    }else{
                        rename($old_name, $new_name);
                        $customer->image = $idcard.'.jpg';
                    }
                }
                $customer->create_date = $this->getCreateDate();
                if ($customer->save() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลลูกค้า ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }
            }
            $customer_array[] = array(
                'idcard' => $customer->idcard,
                'firstname' => $customer->firstname,
                'lastname'  => $customer->lastname,
                'image'     => $customer->image,
            );
            /*************/

            /****** Agreement *******/
            $agreement_value = array_sum($product_value = $this->request->getPost('product_value'));
            $agreement = Agreement::findFirstByagreement_number($agreement_number);
            if($agreement){

            }else{
                $agreement = new Agreement();
                $agreement->idcard = $idcard;
                $agreement->agreement_number = $agreement_number;
                $agreement->value = $agreement_value;
                $agreement->interest = $this->calculateInterest($agreement_value);
                $agreement->save();
                if ($agreement->save() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลสัญญา ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }
            }
            /*************/

            /****** Product *******/
            $product_name = $this->request->getPost('product_name');
            $product_brand = $this->request->getPost('product_brand');
            $product_detail = $this->request->getPost('product_detail');
            $product_value = $this->request->getPost('product_value');
            $count_product = 1;
            $total_value = 0;
            for ($i = 0;$i < count($product_name);$i++)
            {

                $uuid       =  $this->getUuid();
                
                if($product_name[$i] != "")
                {
                $product = new Product();
                $product->agreement_number = $agreement_number;
                $product->name = $product_name[$i];
                $product->brand = $product_brand[$i];
                $product->detail = $product_detail[$i];
                $product->value = $product_value[$i];
                $product->interest = $this->calculateInterest($product_value[$i]);
                if ( $product->save() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลสินค้า ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                $account = new Account();
                $account->uuid = $uuid;
                $account->value = $product_value[$i];
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
                $transaction->status = $status;
                $transaction->uuid = $uuid;
                if ( $transaction->save() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลการทำธุรกรรม ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }

                if($status == 'ฝาก'){
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
                }

                $total_value = $total_value + $product_value[$i];
                $count_products = $count_product++;

                $product_array[] = array(
                    'count'         =>  $i+1,
                    'name'			=>	$product_name[$i],
                    'brand'		    =>	$product_brand[$i],
                    'detail'		=>	$product_detail[$i],
                    'value'     	=>	$product_value[$i]
                );

                }
            }
            /*************/


            $agreement_array[] = array(
                "count_products"     => $count_products,
                "status"             => $status,
                "agreement_number"   => $agreement_number,
                "create_date"        => $this->convertToBuddhistEraWithTime($this->getCreateDate()),
                "start_date"         => $this->convertToBuddhistEra($start_date),
                "end_date"           => $this->convertToBuddhistEra($end_date),
                "customers"          => $customer_array,
                "products"           => $product_array,
                "total_value"        => $total_value
            );
            
            // Commit the transaction
            $transaction_ = $manager->commit();
            
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

            // echo $agreement_json;

        } catch (TxFailed $error) {
            echo $error->getMessage();
        }
    }

    public function getLatestFileAction(){
        $this->view->disable();
        $path = BASE_PATH.'/public/customerimg'; 
        $latest_ctime = 0;
        $latest_filename = "";    
        $d = dir($path);
        while (false !== ($entry = $d->read())) {
        $filepath = "{$path}/{$entry}";
            if (is_file($filepath) && filectime($filepath) > $latest_ctime) {
                $latest_ctime = filectime($filepath);
                $latest_filename = $entry;
            }
        }
        $data = array(
            'latest_file'     => $latest_filename
        );
        echo json_encode($data);
    }
}
