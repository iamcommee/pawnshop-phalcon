<?php
use Phalcon\Mvc\View;

class ListController extends ControllerBase
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
        $this->tag->prependTitle("รายการทั้งหมด ");
        $this->assets->addJs("js/list/index.js");
    }

    public function server_processingAction()
    {
        $this->view->disable();
        $transactions = $this->modelsManager->executeQuery(
            "SELECT T.agreement_number,
            P.name,
            P.brand,
            P.detail,
            T.start_date,
            T.end_date,
            T.status,
            P.value,
            A.transaction_date,
            A.transaction_time,
            AG.idcard
            FROM transaction AS T
            JOIN product AS P
            ON T.product_id = P.product_id
            LEFT JOIN account AS A
            ON T.uuid = A.uuid
            JOIN agreement AS AG
            ON T.agreement_number  = AG.agreement_number
            WHERE P.related_product IS NULL AND T.active = 'T' AND T.status != 'ฝาก'
            ORDER BY P.product_id , T.transaction_id ASC"
        );
        foreach($transactions as $transaction){
            if($this->session->get("role") == 'administrator'){
                $agreement_number = '<a href="../payment/search/'.$transaction->agreement_number.'" target="_blank">'.$transaction->agreement_number.'</a>';
                $idcard = '<a href="../customer/search/'.$transaction->idcard.'" target="_blank">'.$transaction->idcard.'</a>';
            }else{
                $agreement_number = $transaction->agreement_number;
                $idcard = $transaction->idcard;
            }
           $data[] = array
           (
               'agreement_number' => $agreement_number,
               'product_name' => $transaction->name,
               'product_brand' => $transaction->brand,
               'product_detail' => $transaction->detail,
               'start_date' => $this->convertToBuddhistEra($transaction->start_date),
               'end_date' => $this->convertToBuddhistEra($transaction->end_date),
               'product_value' => number_format($transaction->value),
               'status' => $transaction->status,
               'transaction_date' => $this->convertToBuddhistEra($transaction->transaction_date).' '.$transaction->transaction_time,
               'idcard' => $idcard
           );
        }

        $data_json = json_encode($data);
        echo $data_json;
        
    }

    public function exportExcelAction()
    {
        $this->view->disable();
    }
    
}
