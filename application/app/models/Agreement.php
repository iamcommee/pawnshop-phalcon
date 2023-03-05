<?php

use Phalcon\Mvc\Model\Relation;

class Agreement extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(column="agreement_id", type="integer", length=11, nullable=false)
     */
    protected $agreement_id;

    /**
     *
     * @var integer
     * @Column(column="idcard", type="integer", length=11, nullable=false)
     */
    protected $idcard;

    /**
     *
     * @var integer
     * @Column(column="agreement_number", type="integer", length=11, nullable=true)
     */
    protected $agreement_number;

    /**
     *
     * @var string
     * @Column(column="agreement_date", type="string", nullable=true)
     */
    protected $agreement_date;

    /**
     *
     * @var string
     * @Column(column="create_date", type="string", nullable=true)
     */
    protected $create_date;

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
     * Method to set the value of field idcard
     *
     * @param integer $idcard
     * @return $this
     */
    public function setIdcard($idcard)
    {
        $this->idcard = $idcard;

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
     * Method to set the value of field agreement_date
     *
     * @param string $agreement_date
     * @return $this
     */
    public function setAgreementDate($agreement_date)
    {
        $agreement_date = str_replace('/', '-', $agreement_date);
        $agreement_date = date('Y-m-d',strtotime($agreement_date));
        $this->agreement_date = $agreement_date;

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
     * Returns the value of field agreement_id
     *
     * @return integer
     */
    public function getAgreementId()
    {
        return $this->agreement_id;
    }

    /**
     * Returns the value of field idcard
     *
     * @return integer
     */
    public function getIdcard()
    {
        return $this->idcard;
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
     * Returns the value of field agreement_date
     *
     * @return string
     */
    public function getAgreementDate()
    {
        return $this->agreement_date;
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
     * Method to set the value of field number
     *
     * @param integer $number
     * @return $this
     */
    public function setNumber($number)
    {
        $this->number = $number;

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
     * Returns the value of field number
     *
     * @return integer
     */
    public function getNumber()
    {
        return $this->number;
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
        $this->setSchema("pawn_db_new");
        $this->setSource("agreement");
        $this->hasMany(
            'agreement_number', 
            'Product', 
            'agreement_number', 
            [
                'alias' => 'product',
                'foreignKey' => [
                    'action' => Relation::ACTION_CASCADE,
                ]
            ]
        );
        $this->belongsTo('idcard', '\Customer', 'idcard', ['alias' => 'customer']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Agreement[]|Agreement|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Agreement|\Phalcon\Mvc\Model\ResultInterface
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
        return 'agreement';
    }

}
