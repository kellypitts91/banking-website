<?php
namespace agilman\a2\model;

use agilman\a2\Exception\BankException;


/**
 * Class CustomerModel
 *
 * @package kelly_ben/a2
 * @author  Kelly Pitts 09098321 & Ben Wilton 14262032
 */
class TransactionModel extends Model
{
    /**
     * @var integer $_id            Transaction ID
     * @var string $_strype         Transaction type (Deposit or Withdraw)
     * @var string $_strAmount      Amount of Transaction
     * @var string $_strDateTime    Date and time of Transaction
     * @var string $_strAccId       Account ID
     * @var string $_strDescription Transaction Description
     * @var float $_floNewBal       Balance after Transaction
     */
    private $_id;
    private $_strType;
    private $_strAmount;
    private $_strDateTime;
    private $_strAccId;
    private $_strDescription;
    private $_floNewBal;

    public function __construct() {
        parent::__construct();
    }

    /**
     * @param string $type Transaction type
     * @return $this TransactionModel
     */
    public function setType(string $type)
    {
        $this->_strType = $type;
        return $this;
    }

    /**
     * @param string $amount Transaction Amount
     * @return $this TransactionModel
     */
    public function setAmount(string $amount)
    {
        $this->_strAmount = $amount;
        return $this;
    }

    /**
     * @param string $date date and time of transaction
     * @return $this TransactionModel
     */
    public function setDateTime(string $date)
    {
        $this->_strDateTime = $date;
        return $this;
    }

    /**
     * @param string $accId Account ID - Foreign Key
     * @return $this TransactionModel
     */
    public function setAccountId(string $accId)
    {
        $this->_strAccId = $accId;
        return $this;
    }

    /**
     * @param string $desc Transaction description
     * @return $this TransactionModel
     */
    public function setDescription(string $desc)
    {
        $this->_strDescription = $desc;
        return $this;
    }

    /**
     * @param float $newBal Balance after transaction
     * @return $this TransactionModel
     */
    public function setNewBal($newBal) {
        $this->_floNewBal = $newBal;
        return $this;
    }

    /**
     * Saves Transaction information to the database
     *
     * @return $this TransactionModel
     * @throws BankException
     */
    public function save()
    {
        $type = $this->_strType??"NULL";
        $amount = $this->_strAmount??"NULL";
        $date = $this->_strDateTime??"NULL";
        $accId = $this->_strAccId??"NULL";
        $desc = $this->_strDescription??"NULL";
        $newBal = $this->_floNewBal??"NULL";

        if (!isset($this->_id)) {
            // New Transaction - Perform INSERT
            if (!$result = $this->db->query("INSERT INTO `transaction` VALUES (NULL, '$type', '$amount', '$date', '$accId', '$desc', '$newBal');")) {
                throw new BankException("Insert transaction failed: (". $this->db->errno . ") " . $this->db->error);
            }
            $this->_id = $this->db->insert_id;
        } else {
            // saving existing Transaction - perform UPDATE
            if (!$result = $this->db->query("UPDATE `transaction` SET 
					`trans_type` = '$type', 
					`trans_amount` = '$amount',
					`trans_datetime` = '$date',
					`acc_id` = '$accId',
					`trans_description`,
					`trans_newBal`
					WHERE `trans_id` = '$this->_id';")) {
                throw new BankException("Update transaction failed: (". $this->db->errno . ") " . $this->db->error);

            }
        }
        return $this;
    }
}