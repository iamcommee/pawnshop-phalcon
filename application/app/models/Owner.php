<?php

class Owner extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(column="owner_id", type="integer", length=11, nullable=false)
     */
    protected $owner_id;

    /**
     *
     * @var string
     * @Column(column="storename", type="string", length=50, nullable=true)
     */
    protected $storename;

    protected $value_for_high_interest_rate;
    protected $min_due_date;
    protected $max_due_date;

    /**
     *
     * @var double
     * @Column(column="low_interest_rate", type="double", nullable=true)
     */
    protected $low_interest_rate;

    /**
     *
     * @var double
     * @Column(column="high_interest_rate", type="double", nullable=true)
     */
    protected $high_interest_rate;

    /**
     *
     * @var string
     * @Column(column="firstname", type="string", length=50, nullable=true)
     */
    protected $firstname;

    /**
     *
     * @var string
     * @Column(column="lastname", type="string", length=50, nullable=true)
     */
    protected $lastname;

    /**
     *
     * @var string
     * @Column(column="tel", type="string", length=50, nullable=true)
     */
    protected $tel;

    /**
     *
     * @var string
     * @Column(column="house_no", type="string", length=8, nullable=true)
     */
    protected $house_no;

    /**
     *
     * @var string
     * @Column(column="village_no", type="string", length=8, nullable=true)
     */
    protected $village_no;

    /**
     *
     * @var string
     * @Column(column="lane", type="string", length=50, nullable=true)
     */
    protected $lane;

    /**
     *
     * @var string
     * @Column(column="road", type="string", length=50, nullable=true)
     */
    protected $road;

    /**
     *
     * @var string
     * @Column(column="sub_district", type="string", length=50, nullable=true)
     */
    protected $sub_district;

    /**
     *
     * @var string
     * @Column(column="district", type="string", length=50, nullable=true)
     */
    protected $district;

    /**
     *
     * @var string
     * @Column(column="province", type="string", length=50, nullable=true)
     */
    protected $province;

    /**
     *
     * @var string
     * @Column(column="zip_code", type="string", length=50, nullable=true)
     */
    protected $zip_code;

    /**
     *
     * @var string
     * @Column(column="line", type="string", length=50, nullable=true)
     */
    protected $line;

    /**
     * Method to set the value of field owner_id
     *
     * @param integer $owner_id
     * @return $this
     */
    public function setOwnerId($owner_id)
    {
        $this->owner_id = $owner_id;

        return $this;
    }

    /**
     * Method to set the value of field storename
     *
     * @param string $storename
     * @return $this
     */
    public function setStorename($storename)
    {
        $this->storename = $storename;

        return $this;
    }

    public function setValueForHighInterestRate($value_for_high_interest_rate)
    {
        $this->value_for_high_interest_rate = $value_for_high_interest_rate;

        return $this;
    }

    public function setMinDueDate($min_due_date)
    {
        $this->min_due_date = $min_due_date;
        
        return $this;
    }

    public function setMaxDueDate($max_due_date)
    {
        $this->max_due_date = $max_due_date;
        
        return $this;
    }

    /**
     * Method to set the value of field low_interest_rate
     *
     * @param double $low_interest_rate
     * @return $this
     */
    public function setLowInterestRate($low_interest_rate)
    {
        $this->low_interest_rate = $low_interest_rate;

        return $this;
    }

    /**
     * Method to set the value of field high_interest_rate
     *
     * @param double $high_interest_rate
     * @return $this
     */
    public function setHighInterestRate($high_interest_rate)
    {
        $this->high_interest_rate = $high_interest_rate;

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
     * Method to set the value of field tel
     *
     * @param string $tel
     * @return $this
     */
    public function setTel($tel)
    {
        $this->tel = $tel;

        return $this;
    }

    /**
     * Method to set the value of field house_no
     *
     * @param string $house_no
     * @return $this
     */
    public function setHouseNo($house_no)
    {
        $this->house_no = $house_no;

        return $this;
    }

    /**
     * Method to set the value of field village_no
     *
     * @param string $village_no
     * @return $this
     */
    public function setVillageNo($village_no)
    {
        $this->village_no = $village_no;

        return $this;
    }

    /**
     * Method to set the value of field lane
     *
     * @param string $lane
     * @return $this
     */
    public function setLane($lane)
    {
        $this->lane = $lane;

        return $this;
    }

    /**
     * Method to set the value of field road
     *
     * @param string $road
     * @return $this
     */
    public function setRoad($road)
    {
        $this->road = $road;

        return $this;
    }

    /**
     * Method to set the value of field sub_district
     *
     * @param string $sub_district
     * @return $this
     */
    public function setSubDistrict($sub_district)
    {
        $this->sub_district = $sub_district;

        return $this;
    }

    /**
     * Method to set the value of field district
     *
     * @param string $district
     * @return $this
     */
    public function setDistrict($district)
    {
        $this->district = $district;

        return $this;
    }

    /**
     * Method to set the value of field province
     *
     * @param string $province
     * @return $this
     */
    public function setProvince($province)
    {
        $this->province = $province;

        return $this;
    }

    /**
     * Method to set the value of field zip_code
     *
     * @param string $zip_code
     * @return $this
     */
    public function setZipCode($zip_code)
    {
        $this->zip_code = $zip_code;

        return $this;
    }

    /**
     * Method to set the value of field line
     *
     * @param string $line
     * @return $this
     */
    public function setLine($line)
    {
        $this->line = $line;

        return $this;
    }

    /**
     * Returns the value of field owner_id
     *
     * @return integer
     */
    public function getOwnerId()
    {
        return $this->owner_id;
    }

    /**
     * Returns the value of field storename
     *
     * @return string
     */
    public function getStorename()
    {
        return $this->storename;
    }

    public function getValueForHighInterestRate()
    {
        return $this->value_for_high_interest_rate;
    }

    public function getMinDueDate()
    {
        return $this->min_due_date;
    }

    public function getMaxDueDate()
    {
        return $this->max_due_date;
    }

    /**
     * Returns the value of field low_interest_rate
     *
     * @return double
     */
    public function getLowInterestRate()
    {
        return $this->low_interest_rate;
    }

    /**
     * Returns the value of field high_interest_rate
     *
     * @return double
     */
    public function getHighInterestRate()
    {
        return $this->high_interest_rate;
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
     * Returns the value of field tel
     *
     * @return string
     */
    public function getTel()
    {
        return $this->tel;
    }

    /**
     * Returns the value of field house_no
     *
     * @return string
     */
    public function getHouseNo()
    {
        return $this->house_no;
    }

    /**
     * Returns the value of field village_no
     *
     * @return string
     */
    public function getVillageNo()
    {
        return $this->village_no;
    }

    /**
     * Returns the value of field lane
     *
     * @return string
     */
    public function getLane()
    {
        return $this->lane;
    }

    /**
     * Returns the value of field road
     *
     * @return string
     */
    public function getRoad()
    {
        return $this->road;
    }

    /**
     * Returns the value of field sub_district
     *
     * @return string
     */
    public function getSubDistrict()
    {
        return $this->sub_district;
    }

    /**
     * Returns the value of field district
     *
     * @return string
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * Returns the value of field province
     *
     * @return string
     */
    public function getProvince()
    {
        return $this->province;
    }

    /**
     * Returns the value of field zip_code
     *
     * @return string
     */
    public function getZipCode()
    {
        return $this->zip_code;
    }

    /**
     * Returns the value of field line
     *
     * @return string
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("pawnshop");
        $this->setSource("owner");
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Owner[]|Owner|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Owner|\Phalcon\Mvc\Model\ResultInterface
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
        return 'owner';
    }

}
