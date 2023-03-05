<?php

use Phalcon\Mvc\Model\Relation;

class Product extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(column="product_id", type="integer", length=11, nullable=false)
     */
    protected $product_id;

    /**
     *
     * @var integer
     * @Column(column="agreement_number", type="integer", length=11, nullable=false)
     */
    protected $agreement_number;

    /**
     *
     * @var string
     * @Column(column="name", type="string", length=45, nullable=true)
     */
    protected $name;

    /**
     *
     * @var string
     * @Column(column="brand", type="string", length=45, nullable=true)
     */
    protected $brand;

    /**
     *
     * @var string
     * @Column(column="detail", type="string", length=45, nullable=true)
     */
    protected $detail;

    /**
     *
     * @var integer
     * @Column(column="value", type="integer", length=11, nullable=true)
     */
    protected $value;

    /**
     *
     * @var integer
     * @Column(column="sub_product", type="integer", length=11, nullable=true)
     */
    protected $sub_product;

    /**
     * Method to set the value of field product_id
     *
     * @param integer $product_id
     * @return $this
     */
    public function setProductId($product_id)
    {
        $this->product_id = $product_id;

        return $this;
    }

    /**
     * Method to set the value of field agreement_number
     *
     * @param integer $agreement_number
     * @return $this
     */
    public function setAgreementNumber($agreement_number)
    {
        $this->agreement_number = $agreement_number;

        return $this;
    }

    /**
     * Method to set the value of field name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Method to set the value of field brand
     *
     * @param string $brand
     * @return $this
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Method to set the value of field detail
     *
     * @param string $detail
     * @return $this
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;

        return $this;
    }

    /**
     * Method to set the value of field value
     *
     * @param integer $value
     * @return $this
     */
    public function setvalue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Method to set the value of field sub_product
     *
     * @param integer $sub_product
     * @return $this
     */
    public function setSubProduct($sub_product)
    {
        $this->sub_product = $sub_product;

        return $this;
    }

    /**
     * Returns the value of field product_id
     *
     * @return integer
     */
    public function getProductId()
    {
        return $this->product_id;
    }

    /**
     * Returns the value of field agreement_number
     *
     * @return integer
     */
    public function getAgreementNumber()
    {
        return $this->agreement_number;
    }

    /**
     * Returns the value of field name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the value of field brand
     *
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Returns the value of field detail
     *
     * @return string
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * Returns the value of field value
     *
     * @return integer
     */
    public function getvalue()
    {
        return $this->value;
    }

    /**
     * Returns the value of field sub_product
     *
     * @return integer
     */
    public function getSubProduct()
    {
        return $this->sub_product;
    }

    /**
     * Method to set the value of field agreement_id
     *
     * @param integer $agreement_id
     * @return $this
     */
    public function setAgreementId($agreement_id)
    {
        $this->agreement_id = $agreement_id;

        return $this;
    }

    /**
     * Returns the value of field agreement_id
     *
     * @return integer
     */
    public function getAgreementId()
    {
        return $this->agreement_id;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("pawn_db_new");
        $this->setSource("product");
        $this->hasMany(
            'product_id', 
            'Transaction', 
            'product_id', 
            [
                'alias' => 'transaction',
                'foreignKey' => [
                    'action' => Relation::ACTION_CASCADE,
                ]           
            ]
        );
        $this->belongsTo('agreement_number', '\Agreement', 'agreement_number', ['alias' => 'Agreement']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Product[]|Product|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Product|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'product';
    }

}
