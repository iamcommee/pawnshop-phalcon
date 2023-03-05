<?php

class Transaction extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(column="transaction_id", type="integer", length=11, nullable=false)
     */
    public $transaction_id;

    /**
     *
     * @var integer
     * @Column(column="agreement_number", type="integer", length=11, nullable=false)
     */
    public $agreement_number;

    /**
     *
     * @var integer
     * @Column(column="product_id", type="integer", length=11, nullable=true)
     */
    public $product_id;

    /**
     *
     * @var string
     * @Column(column="start_date", type="string", nullable=true)
     */
    public $start_date;

    /**
     *
     * @var string
     * @Column(column="end_date", type="string", nullable=true)
     */
    public $end_date;

    /**
     *
     * @var string
     * @Column(column="status", type="string", length=50, nullable=true)
     */
    public $status;

    /**
     *
     * @var string
     * @Column(column="note", type="string", length=128, nullable=true)
     */
    public $note;

    /**
     *
     * @var string
     * @Column(column="uuid", type="string", length=128, nullable=true)
     */
    public $uuid;

    /**
     *
     * @var string
     * @Column(column="related_uuid", type="string", length=128, nullable=true)
     */
    public $related_uuid;

    /**
     *
     * @var string
     * @Column(column="active", type="string", length=1, nullable=true)
     */
    public $active;

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
        $this->setSource("transaction");
        $this->belongsTo('uuid', '\Account', 'uuid', ['alias' => 'account']);
        $this->belongsTo('product_id', '\Product', 'product_id', ['alias' => 'product']);
        $this->belongsTo('agreement_number', '\Agreement', 'agreement_number', ['alias' => 'agreement']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'transaction';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Transaction[]|Transaction|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Transaction|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
