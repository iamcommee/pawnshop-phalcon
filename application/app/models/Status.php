<?php

class Status extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(column="status_id", type="integer", length=11, nullable=false)
     */
    public $status_id;

    /**
     *
     * @var string
     * @Column(column="status", type="string", length=50, nullable=true)
     */
    public $status;

    /**
     *
     * @var integer
     * @Column(column="allow", type="integer", length=1, nullable=true)
     */
    public $allow;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("pawn_db_new");
        $this->setSource("status");
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'status';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Status[]|Status|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Status|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
