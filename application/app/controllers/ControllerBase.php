<?php

use Phalcon\Mvc\Controller;
use Phalcon\Security\Random;

class ControllerBase extends Controller
{
    public function initialize(){

        // fix out of memmory
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');

        $styleCollection = $this->assets->collection('style');
        $styleCollection->addCss('css/style/style.css');
        $styleCollection->addJs('js/style/style.js');

        $jqueryCollection = $this->assets->collection('jquery');
        $jqueryCollection->addJs('jquery/jquery-3.3.1.js');
        $jqueryCollection->addJs('js/jquery-number/jquery.number.min.js');

        $jquery_uiCollection = $this->assets->collection('jquery_ui');
        $jquery_uiCollection->addCss('jquery-ui/jquery-ui.css');
        $jquery_uiCollection->addJs('jquery-ui/jquery-ui.js');
        
        $bootstrapCollection = $this->assets->collection('bootstrap');
        $bootstrapCollection->addCss('bootstrap/css/bootstrap.css');
        // $bootstrapCollection->addCss('bootstrap/css/bootstrap_sidebar.css');
        $bootstrapCollection->addJs('bootstrap/js/bootstrap.bundle.js');
        // $bootstrapCollection->addJs('bootstrap/js/bootstrap_sidebar.js');

        $bootstrap_datepickerCollection = $this->assets->collection('datepicker');
        $bootstrap_datepickerCollection->addCss('bootstrap-datepicker/css/datepicker.css');
        $bootstrap_datepickerCollection->addJs('bootstrap-datepicker/js/datepicker.js');
        $bootstrap_datepickerCollection->addJs('bootstrap-datepicker/js/i18n/datepicker.th-TH.js');
        $bootstrap_datepickerCollection->addJs('bootstrap-datepicker/js/moment-with-locales.js');

        $fontawesomeCollection = $this->assets->collection('fontawesome');
        $fontawesomeCollection->addCss('fontawesome/css/all.css');

        $datatableCollection = $this->assets->collection('datatable');
        $datatableCollection->addCss('dataTables/DataTables-1.10.18/css/dataTables.bootstrap4.min.css');
        $datatableCollection->addCss('datatables/Scroller-1.5.0/css/scroller.dataTables.css');
        $datatableCollection->addCss('datatables/Scroller-1.5.0/css/scroller.bootstrap4.min.css');
        $datatableCollection->addCss('dataTables/Select-1.2.6/css/select.bootstrap4.min.css');

        $datatableCollection->addJs('dataTables/DataTables-1.10.18/js/jquery.dataTables.min.js');
        $datatableCollection->addJs('dataTables/DataTables-1.10.18/js/dataTables.bootstrap4.min.js');
        $datatableCollection->addJs('datatables/Scroller-1.5.0/js/dataTables.scroller.min.js');
        $datatableCollection->addJs('datatables/Scroller-1.5.0/js/scroller.bootstrap4.min.js');
        $datatableCollection->addJs('dataTables/Buttons-1.5.4/js/dataTables.buttons.min.js');
        $datatableCollection->addJs('dataTables/Buttons-1.5.4/js/buttons.html5.min.js');
        $datatableCollection->addJs('dataTables/Buttons-1.5.4/js/buttons.print.min.js');
        $datatableCollection->addJs('dataTables/JSZip-2.5.0/jszip.min.js');
        $datatableCollection->addJs('dataTables/pdfmake-0.1.36/pdfmake.min.js');
        $datatableCollection->addJs('dataTables/pdfmake-0.1.36/vfs_fonts.js');
        $datatableCollection->addJs('dataTables/pdfmake-0.1.36/vfs_fonts.js');
        $datatableCollection->addJs('dataTables/Select-1.2.6/js/dataTables.select.js');
        $datatableCollection->addJs('dataTables/Select-1.2.6/js/select.bootstrap4.min.js');
        


        $yadcfCollection = $this->assets->collection('yadcf');
        $yadcfCollection->addCss('yadcf/jquery.dataTables.yadcf.css');
        $yadcfCollection->addJs('yadcf/jquery.dataTables.yadcf.js');

        $select2Collection = $this->assets->collection('select2');
        $select2Collection->addCss('select2/dist/css/select2.min.css');
        $select2Collection->addJs('select2/dist/js/select2.min.js');

        $mCustomCollection = $this->assets->collection('mCustomScrollbar');
        $mCustomCollection->addCss('mCustomScrollbar/css/jquery.mCustomScrollbar.min.css');
        $mCustomCollection->addJs('mCustomScrollbar/js/jquery.mCustomScrollbar.concat.min.js');

        $highchartCollection = $this->assets->collection('highchart');
        $highchartCollection->addCss('highcharts/code/css/highcharts.css');
        $highchartCollection->addJs('highcharts/code/highcharts.js');
        $highchartCollection->addJs('highcharts/code/modules/drilldown.js');

        error_reporting(0); // disable error php
    }

    public function getSecretkey(){
        return "ประเสริฐรัตน์";
    }

    public function getCreateDate(){
        date_default_timezone_set('Asia/Bangkok');
        $create_date = new DateTime();
        $create_date  =  $create_date->format("Y-m-d H:i:s");
        return $create_date;
    }

    public function getCurrentDate(){
        date_default_timezone_set('Asia/Bangkok');
        $current_date = new DateTime();
        $current_date  =  $current_date->format("Y-m-d");
        return $current_date;
    }

    public function getCurrentTime(){
        date_default_timezone_set('Asia/Bangkok');
        $current_time = new DateTime();
        $current_time  =  $current_time->format("H:i:s");
        return $current_time;
    }

    public function convertToBuddhistEra($date){   
        if($date == null){
            return '-';
        }
        else {
        $date_ = str_replace('/', '-', $date);
        $d = $this->getDate($date_);
        $m = $this->getMonth($date_);
        $y = $this->getYear($date_) + 543;
        return $d.'/'.$m.'/'.$y;
        }
    }

    public function convertToBuddhistEraWithTime($date){     
        if($date == null){
            return '-';
        }
        else {
            $date_ = str_replace('/', '-', $date);
            $d = $this->getDate($date_);
            $m = $this->getMonth($date_);
            $y = $this->getYear($date_) + 543;
            $t = date('H:i:s',strtotime($date_));
            return $d.'/'.$m.'/'.$y.' '.$t;
        }
    }

    public function convertToCommonEra($date){  
        if($date == null){
            return '-';
        }
        else {   
        $date_ = str_replace('/', '-', $date);
        $date_ = date('d/m/Y',strtotime($date_));
        return $date_;
        }
    }

    public function convertToCommonEraSQL($date){  
        if($date == null){
            return null;
        }
        else {   
        $date_ = str_replace('/', '-', $date);
        $date_ = date('Y-m-d',strtotime($date_));
        return $date_;
        }
    }

    public function convertToTimeSQL($time){  
        if($time == null){
            return null;
        }
        else {   
        $time_ = str_replace('.', ':', $time);
        $time_ = date('H:i:s',strtotime($time_));
        return $time_;
        }
    }

    public function getUuid(){
        $random     = new Random();
        return $random->uuid();
    }

    public function getPrevMonth($date){
        $date_ = str_replace('/', '-', $date);
        $date_ = date('d/m/Y',strtotime("-1 month", strtotime($date_)));
        return $date_;
    }

    public function getNextMonth($date){
        $date_ = str_replace('/', '-', $date);
        $date_ = date('d/m/Y',strtotime("+1 month", strtotime($date_)));
        return $date_;
    }

    public function getNumberDays($date){
        $date_ = str_replace('/', '-', $date);
        $date_ = date('t',strtotime($date_));
        return $date_;
    }

    public function getDate($date){
        $date_ = str_replace('/', '-', $date);
        $date_ = date('j',strtotime($date_));
        return $date_;
    }

    public function getMonth($date){
        $date_ = str_replace('/', '-', $date);
        $date_ = date('m',strtotime($date_));
        return $date_;
    }

    public function getYear($date){
        $date_ = str_replace('/', '-', $date);
        $date_ = date('Y',strtotime($date_));
        return $date_;
    }

    public function convertToNumber($value)
    {
        $value_ = preg_replace( '/[^0-9]/', '', $value );
        return (int)($value_);
    }

    public function calculateInterest($value)
    {
        $owner = Owner::findFirst();
        if($value >= $owner->ValueForHighInterestRate)
        {
            $interest = ($value*$owner->LowInterestRate)/100;
        }
        else
        {   
            $interest = ($value*$owner->HighInterestRate)/100;
        }
        return round($interest);
    }
}
