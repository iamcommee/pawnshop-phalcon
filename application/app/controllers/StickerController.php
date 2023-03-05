<?php
use Phalcon\Crypt;
use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;
use Phalcon\Mvc\View;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class StickerController extends ControllerBase
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
        $this->view->disable();
        $this->tag->setTitle("| ประเสริฐรัตน์บริการ");
        $this->tag->prependTitle("รายชื่อลูกค้า ");
        $this->assets->addJs("js/customer/index.js");
    }

    public function getProductAction(){
        $this->view->disable();
        $value = $_GET['term'];
        $products = $this->modelsManager->executeQuery("SELECT P.*
        FROM Product as P
        WHERE P.agreement_number LIKE :search:
        ORDER BY P.product_id ASC
        LIMIT 10",
            [
                "search" => '%'.$value.'%'
            ]
        );

        foreach($products as $product){
                $data[] = array(
                    'label' => $product->agreement_number.'-'.$product->product_id.' '. $product->name.' '. $product->brand.' '.$product->detail,
                    'product'   => $product->agreement_number .' '. $product->name.' '. $product->brand.' '.$product->detail,
                    'agreement_number' => $product->agreement_number,
                    'product_id' => $product->product_id,
            );
        }
      echo json_encode($data);
    }

    public function generateAction($page_size){
        $this->view->setRenderLevel(
            View::LEVEL_ACTION_VIEW
        );
        $this->assets->addCss("css/sticker.css");
        $this->assets->addCss('css/style/style.css');
        $this->assets->addCss('bootstrap/css/bootstrap.css');
        $this->assets->addCss('jquery-ui/jquery-ui.css');
        $this->assets->addCss('fontawesome/css/all.css');

        $this->assets->addJs('jquery/jquery-3.3.1.js');
        $this->assets->addJs('jquery-ui/jquery-ui.js');
        $this->assets->addJs('js/sticker/generate.js');
        if($page_size == 'A5'){
            $this->view->setVars(
                [
                    "page_size" => 'A5',
                    "size"    => 56,
                    "height"  => '1.61cm',
                    "width"   => '4.05cm',
                    "margin"  => '0.175cm 0.2cm -0.1cm 0.2cm',
                ]
            );
        }
        elseif($page_size == 'A10'){
            $this->view->setVars(
                [
                    "page_size" => 'A10',
                    "size"    => 24,
                    "height"  => '2.7cm',
                    "width"   => '5cm',
                    "margin"  => '0.175cm 0.2cm -0.1cm 0.2cm',
                ]
            );
        }
    }

    public function printStickerAction($size_label){
        $this->view->setRenderLevel(
            View::LEVEL_ACTION_VIEW
        );

        $this->assets->addCss("css/sticker.css");

        if($size_label == 'A5'){
            $size = 56;
            $this->view->setVars(
                [
                    "size_label" => $size_label,
                    "size"    => $size,
                    "height"  => '1.61cm',
                    "width"   => '4.05cm',
                    "margin"  => '0.175cm 0.2cm -0.1cm 0.2cm',
                    // barcode css
                    "position" => 'absolute',
                    "barcode_width" => '1.2cm',
                    "barcode_height" => '1.2cm',
                    "barcode_left"    => '5',
                    "barcode_top" => '24%',
                    // agreement_number-text css
                    "agreement_number_text_right" => '10',
                    "agreement_number_text_top" => '21%',
                    "agreement_number_text_font_size" => '36px',
                    //product-text css
                    "product_text_right"  => '10px',
                    "product_text_top"  => '10%',
                    "product_text_font_size" => '8px',
                ]
            );
        }
        elseif($size_label == 'A10'){
            $size = 24;
            $this->view->setVars(
                [
                    "size_label" => $size_label,
                    "size"    => $size,
                    "height"  => '2.7cm',
                    "width"   => '5cm',
                    "margin"  => '0.1cm 0.5cm 0.175cm 0.5cm',
                    // sticker css
                    "position" => 'absolute',
                    "barcode_width" => '2.2cm',
                    "barcode_height" => '2.2cm',
                    "barcode_left"    => '0',
                    "barcode_top" => '15%',
                    // agreement_number-text css
                    "agreement_number_text_right" => '30',
                    "agreement_number_text_top" => '46%',
                    "agreement_number_text_font_size" => '36px',
                    //product-text css
                    "product_text_right"  => '30px',
                    "product_text_top"  => '8%',
                    "product_text_font_size" => '12px',
                ]
            );
        }

        $options = new QROptions([
            'version'    => 1,
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'eccLevel'   => QRCode::ECC_H,
        ]);
        
        $qrcode = new QRCode($options);
    
        for($i = 1; $i <= $size; $i++) {
            if(${"agreement_number_$i"} = $this->request->getPost('agreement_number_'.$i)){
                    ${"product_information_$i"} = $this->request->getPost('product_information_'.$i);
                    ${"product_id_$i"} = $this->request->getPost('product_id_'.$i);
                    $qrcode->render(${"product_id_$i"}, BASE_PATH."/public/barcodeimg/${"product_id_$i"}.svg");
                    $this->view->{"product_information_$i"} = ${"product_information_$i"};
                    $this->view->{"agreement_number_$i"} = ${"agreement_number_$i"};
                    $this->view->{"product_id_$i"} = ${"product_id_$i"};
            }
       }
    }
}
