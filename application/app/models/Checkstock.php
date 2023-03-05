<?php

class Checkstock extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(column="checkstock_id", type="integer", length=11, nullable=false)
     */
    protected $checkstock_id;

    /**
     *
     * @var integer
     * @Column(column="product_id", type="integer", length=11, nullable=false)
     */
    protected $product_id;

    /**
     *
     * @var integer
     * @Column(column="time", type="integer", length=11, nullable=true)
     */
    protected $time;

    /**
     *
     * @var string
     * @Column(column="create_at", type="string", nullable=true)
     */
    protected $create_at;

    /**
     * Method to set the value of field checkstock_id
     *
     * @param integer $checkstock_id
     * @return $this
     */
    public function setCheckstockId($checkstock_id)
    {
        $this->checkstock_id = $checkstock_id;

        return $this;
    }

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
     * Method to set the value of field time
     *
     * @param integer $time
     * @return $this
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Method to set the value of field create_at
     *
     * @param string $create_at
     * @return $this
     */
    public function setCreateAt($create_at)
    {
        $this->create_at = $create_at;

        return $this;
    }

    /**
     * Returns the value of field checkstock_id
     *
     * @return integer
     */
    public function getCheckstockId()
    {
        return $this->checkstock_id;
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
     * Returns the value of field time
     *
     * @return integer
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Returns the value of field create_at
     *
     * @return string
     */
    public function getCreateAt()
    {
        return $this->create_at;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("pawnshop");
        $this->setSource("checkstock");
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Checkstock[]|Checkstock|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Checkstock|\Phalcon\Mvc\Model\ResultInterface
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
        return 'checkstock';
    }

}
