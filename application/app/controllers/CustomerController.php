<?php
use Phalcon\Crypt;
use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;
use Phalcon\Mvc\View;

class CustomerController extends ControllerBase
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
        $this->tag->prependTitle("รายชื่อลูกค้า ");
        $this->assets->addJs("js/customer/index.js");
    }

    // * OK
    public function server_processingAction()
    {
        $this->view->disable();
        $data = array();
        $customers = Customer::find();

        foreach($customers as $customer){
           $data[] = array
           (
               'customer_id' => $customer->customer_id,
               'idcard'    => $customer->idcard,
               'firstname' => $customer->firstname,
               'lastname'  => $customer->lastname,
               'image'     => $customer->image
           );
        }

        $data_json = json_encode($data);
        echo $data_json;
    }

    public function customer_server_processingAction($idcard)
    {
        $this->view->disable();
        $data = array();
        $transactions = $this->modelsManager->executeQuery(
            "SELECT T.agreement_number,
            P.name,
            P.brand,
            P.detail,
            T.start_date,
            T.end_date,
            P.value,
            T.status,
            A.transaction_date ,
            A.transaction_time,
            AG.idcard
            FROM transaction AS T
            JOIN product AS P
            ON T.product_id = P.product_id
            LEFT JOIN account AS A
            ON T.uuid = A.uuid
            JOIN agreement AS AG
            ON T.agreement_number  = AG.agreement_number
            WHERE AG.idcard = :idcard: AND P.related_product IS NULL AND T.active = 'T' AND T.status != 'ฝาก' AND T.status != 'เพิ่มสินค้า' ",
            [
                'idcard' => $idcard
            ]
        );
        foreach($transactions as $transaction){
           $data[] = array
           (
               'agreement_number' => $transaction->agreement_number,
               'product_name' => $transaction->name,
               'product_brand' => $transaction->brand,
               'product_detail' => $transaction->detail,
               'start_date' => $this->convertToBuddhistEra($transaction->start_date),
               'end_date' => $this->convertToBuddhistEra($transaction->end_date),
               'product_value' => $transaction->value,
               'status' => $transaction->status,
               'transaction_date' => $this->convertToBuddhistEra($transaction->transaction_date).' '.$transaction->transaction_time,
               'idcard' => $transaction->idcard
           );
        }

        $data_json = json_encode($data);
        echo $data_json;
    }

    public function searchAction($idcard)
    {
        $this->view->setMainView('desktop-layout');
        $this->tag->setTitle("| ประเสริฐรัตน์บริการ");
        $this->tag->prependTitle("รายละเอียดลูกค้า : $idcard ");
        $this->assets->addJs("js/customer/search.js");
        if($idcard){
            // nothing
        } else {
            $this->response->redirect("error404");
        }
    }

    // * OK
    public function editCustomerInformationAction()
    {
        $this->view->disable();
        try {
            $manager = new TxManager();
            $transaction_ = $manager->get();
            if ($customer_id = $this->request->get('customer_id'))
            {
                $idcard =  $this->request->get('idcard');
                $firstname =  $this->request->get('firstname');
                $lastname = $this->request->get('lastname');
                $image = $this->request->get('image');
                $customer = Customer::findFirst($customer_id);
                $customer->idcard = $idcard;
                $customer->firstname = $firstname;
                $customer->lastname = $lastname;
                $customer->image = $image;
                if ($customer->save() === false) {
                    $transaction_->rollback(
                        'ขออภัย ข้อมูลลูกค้า ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                    );
                }
            }
            $transaction_ = $manager->commit();
        } catch (TxFailed $error) {
            echo $error->getMessage();
        }
    }
}
