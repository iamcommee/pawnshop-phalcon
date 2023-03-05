<?php
use Phalcon\Crypt;
use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;
use Phalcon\Mvc\View;

class SettingController extends ControllerBase
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

    // * OK
    public function indexAction()
    {
        $this->tag->setTitle("| ประเสริฐรัตน์บริการ");
        $this->tag->prependTitle("ตั้งค่า ");
        $this->assets->addJs("js/setting/index.js");

        $owner = Owner::findFirst('1');        

        $this->view->setVars(
            [
                "storename"      =>  $owner->storename,
                "value_for_high_interest_rate" => $owner->value_for_high_interest_rate,
                "min_due_date"          => $owner->min_due_date,
                "max_due_date"          => $owner->max_due_date,
                "low_interest_rate"     => $owner->low_interest_rate,
                "high_interest_rate"    => $owner->high_interest_rate,
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
                "line"           =>  $owner->line,
                "status_json"    =>  $status_json
            ]
        );
    }

    // * OK
    public function editOwnerInformationAction()
    {
        $this->view->disable();
        try {

            $manager = new TxManager();
            $transaction_ = $manager->get();

            $owner = Owner::findFirst('1');
            $owner->storename = $this->request->getPOST('storename');
            $owner->value_for_high_interest_rate = $this->request->getPost('value_for_high_interest_rate');
            $owner->min_due_date = $this->request->getPost('min_due_date');
            $owner->max_due_date = $this->request->getPost('max_due_date');
            $owner->low_interest_rate = $this->request->getPOST('low_interest_rate');
            $owner->high_interest_rate = $this->request->getPOST('high_interest_rate');
            $owner->firstname = $this->request->getPOST('firstname');
            $owner->lastname = $this->request->getPOST('lastname');
            $owner->tel = $this->request->getPOST('tel');
            $owner->m_tel = $this->request->getPOST('m_tel');
            $owner->house_no = $this->request->getPOST('house_no');
            $owner->village_no = $this->request->getPOST('village_no');
            $owner->lane = $this->request->getPOST('lane');
            $owner->road = $this->request->getPOST('road');
            $owner->sub_district = $this->request->getPOST('sub_district');
            $owner->district = $this->request->getPOST('district');
            $owner->province = $this->request->getPOST('province');
            $owner->zip_code = $this->request->getPOST('zip_code');
            $owner->line     = $this->request->getPOST('line');
            if ($owner->save() === false) {
                $transaction_->rollback(
                    'ขออภัย รายละเอียดในสัญญา ผิดพลาดไม่สามารถทำรายการได้ในขณะนี้'
                );
            }
            $transaction_ = $manager->commit();
            return $this->response->redirect('setting/');

        } catch (TxFailed $error) {
            echo $error->getMessage();
        }
    }
}
