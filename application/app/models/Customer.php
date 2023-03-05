<?php

use Phalcon\Mvc\Model\Relation;

class Customer extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(column="customer_id", type="integer", length=11, nullable=false)
     */
    protected $customer_id;

    /**
     *
     * @var string
     * @Column(column="idcard", type="string", length=45, nullable=true)
     */
    protected $idcard;

    /**
     *
     * @var string
     * @Column(column="firstname", type="string", length=45, nullable=true)
     */
    protected $firstname;

    /**
     *
     * @var string
     * @Column(column="lastname", type="string", length=45, nullable=true)
     */
    protected $lastname;

    /**
     *
     * @var string
     * @Column(column="image", type="string", length=45, nullable=true)
     */
    protected $image;

    /**
     *
     * @var string
     * @Column(column="create_date", type="string", nullable=true)
     */
    protected $create_date;

    /**
     * Method to set the value of field customer_id
     *
     * @param integer $customer_id
     * @return $this
     */
    public function setCustomerId($customer_id)
    {
        $this->customer_id = $customer_id;

        return $this;
    }

    /**
     * Method to set the value of field idcard
     *
     * @param string $idcard
     * @return $this
     */
    public function setIdcard($idcard)
    {
        $this->idcard = $idcard;

        return $this;
    }

    /**
     * Method to set the value of field firstname
     *
     * @param string $firstname
     * @return $this
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Method to set the value of field lastname
     *
     * @param string $lastname
     * @return $this
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Method to set the value of field image
     *
     * @param string $image
     * @return $this
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Method to set the value of field create_date
     *
     * @param string $create_date
     * @return $this
     */
    public function setCreateDate($create_date)
    {
        $this->create_date = $create_date;

        return $this;
    }

    /**
     * Returns the value of field customer_id
     *
     * @return integer
     */
    public function getCustomerId()
    {
        return $this->customer_id;
    }

    /**
     * Returns the value of field idcard
     *
     * @return string
     */
    public function getIdcard()
    {
        return $this->idcard;
    }

    /**
     * Returns the value of field firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Returns the value of field lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Returns the value of field image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Returns the value of field create_date
     *
     * @return string
     */
    public function getCreateDate()
    {
        return $this->create_date;
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
        $this->setSource("customer");
        $this->hasMany(
            'idcard', 
            'Agreement', 
            'idcard', 
            [
                'alias' => 'Agreement'
            ]
        );
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Customer[]|Customer|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Customer|\Phalcon\Mvc\Model\ResultInterface
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
        return 'customer';
    }

}
