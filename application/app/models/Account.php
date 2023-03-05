<?php

use Phalcon\Mvc\Model\Relation;

class Account extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(column="account_id", type="integer", length=11, nullable=false)
     */
    public $account_id;

    /**
     *
     * @var string
     * @Column(column="uuid", type="string", length=128, nullable=false)
     */
    public $uuid;

    /**
     *
     * @var integer
     * @Column(column="principal", type="integer", length=8, nullable=true)
     */
    public $principal;

    /**
     *
     * @var integer
     * @Column(column="value", type="integer", length=8, nullable=true)
     */
    public $value;

    /**
     *
     * @var string
     * @Column(column="transaction_date", type="string", nullable=true)
     */
    public $transaction_date;

    /**
     *
     * @var string
     * @Column(column="transaction_time", type="string", nullable=true)
     */
    public $transaction_time;

    /**
     *
     * @var string
     * @Column(column="created_date", type="string", nullable=true)
     */
    public $created_date;
    
    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("pawnshop");
        $this->setSource("account");
        $this->hasMany(
            'uuid',
            'Transaction',
            'uuid',
            [
                'alias'      => 'transactionDetail',
                'foreignKey' => [
                    'action' => Relation::ACTION_CASCADE,
                ]
            ]
        );
        $this->hasMany(
            'uuid',
            'Transaction',
            'related_uuid',
            [
                'alias'      => 'relatedTransactionDetail',
                'foreignKey' => [
                    'action' => Relation::ACTION_CASCADE,
                ]
            ]
        );
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'account';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Account[]|Account|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Account|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
