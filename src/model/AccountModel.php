<?php
namespace agilman\a2\model;

use agilman\a2\Exception\BankException;

/**
 * Class AccountModel
 *
 * @package kelly_ben/a2
 * @author  Kelly Pitts 09098321 & Ben Wilton 14262032
 */
class AccountModel extends Model
{
    /**
     * @var integer Account ID
     * @var float Account balance
     * @var string Account date time created
     * @var int Foreign Key Customer ID
     */
    private $_id;
    private $_floBal;
    private $_strDateCreated;
    private $_intCusId;

    /**
     * AccountModel constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * @return int Account ID
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @return float Account Balance
     */
    public function getBalance()
    {
        return $this->_floBal;
    }

    /**
     * @param float $bal Account Balance
     * @return $this AccountModel
     */
    public function setBal(float $bal)
    {
        $this->_floBal = $bal;
        return $this;
    }

    /**
     * @param string $date Account date and time
     * @return $this AccountModel
     */
    public function setDateCreated(string $date)
    {
        $this->_strDateCreated = $date;
        return $this;
    }

    /**
     * @param string $cId Account - Customer ID - Foreign Key
     * @return $this AccountModel
     */
    public function setCustomerId(string $cId)
    {
        $this->_intCusId = $cId;
        return $this;
    }

    /**
     * Loads current Account information from the database
     *
     * @param int $id Account ID
     * @return $this AccountModel
     * @throws BankException
     */
    public function load($id)
    {
        if (!$result = $this->db->query("SELECT * FROM `account` WHERE `cus_id` = '$id';")) {
            throw new BankException("Loading the account failed: (". $this->db->errno . ") " . $this->db->error);
        }
        if($result->num_rows > 0) {

            $row = $result->fetch_assoc();

            $this->_id = $row['acc_id'];
            $this->_floBal = $row['acc_bal'];
            $this->_strDateCreated = $row['acc_dateCreated'];
            $this->_intCusId = $row['cus_id'];
        }

        return $this;
    }

    /**
     * Saves current Account information to the database
     * if account already exist, updates the current account
     *
     * @return $this AccountModel
     * @throws BankException
     */
    public function save()
    {
        $bal = $this->_floBal??"NULL";
        $date = $this->_strDateCreated??"NULL";
        $cId = $this->_intCusId??"NULL";

        if (!isset($this->_id)) {
            // New Account - Perform INSERT
            if (!$result = $this->db->query("INSERT INTO `account` VALUES (NULL, '$bal', '$date', '$cId');")) {
                throw new BankException("Insert account failed: (". $this->db->errno . ") " . $this->db->error);
            }
            $this->_id = $this->db->insert_id;
        } else {
            // saving existing Account - perform UPDATE
            if (!$result = $this->db->query("UPDATE `account` SET 
					`acc_bal` = '$bal', 
					`cus_id` = '$cId'
					WHERE `acc_id` = '$this->_id';")) {
                throw new BankException("Update account failed: (". $this->db->errno . ") " . $this->db->error);
            }
        }
        return $this;
    }

    /**
     * Deletes Account from the database
     * not currently using this method. Future feature can be to remove accounts from users so they can de-register.
     *
     * @return $this AccountModel
     * @throws BankException
     */
    public function delete()
    {
        if (!$result = $this->db->query("DELETE FROM `account` WHERE `acc_id` = '$this->_id';")) {
            throw new BankException("Delete account failed: (". $this->db->errno . ") " . $this->db->error);
        }
        return $this;
    }

    /**
     * This method is needed when the customer first logs in, as they log in with their email address
     *
     * @param string $email     Customers email address used to search the database
     * @return array            Returns a single row by using the Distinct key word
     * @throws BankException
     */
    public function getAccountByEmail($email) {
        if (!$result = $this->db->query("SELECT DISTINCT c.*, a.`acc_id`, FORMAT(a.`acc_bal`, 2) As `acc_bal` 
                                                FROM `account` AS a 
                                                INNER JOIN `customer` AS c 
                                                WHERE a.`cus_id` = c.`cus_id` 
                                                AND c.`cus_email` = '$email';")) {
            throw new BankException("Select account by email failed: (". $this->db->errno . ") " . $this->db->error);
        }
        return $result->fetch_assoc();
    }

    /**
     * Method is used to calculate the transactions based on the information passed as parameters
     *
     * @param int $accNum               the account number entered by the customer
     * @param float $accAmount          the amount of the transaction to be calculated
     * @param string $accTransType      the type of the transaction, either Deposit or withdraw
     * @param string $accDescription    the description, is optional for the user to enter
     * @throws BankException
     */
    public function CalculateTransactions($accNum, $accAmount, $accTransType, $accDescription)
    {
        if ($this->_intCusId == $accNum || $_POST == null) {
            $t = new TransactionModel();
            $customer = new CustomerModel();
            $customer->load($_SESSION['id']);
            if ($accTransType == "Deposit") {
                $this->Deposit($accAmount);
                $t->setAmount($accAmount);
            } else if ($accTransType == "Withdraw") { //will throw error if try to withdraw more money than in the account
                $this->Withdraw($accAmount);
                $t->setAmount(-$accAmount);
            }
            $this->save(); //save the new balance to the account
            $t->setType($accTransType);
            $t->setDateTime(date("d/m/y h:i:sa"));
            $t->setAccountId($accNum);
            $t->setDescription($accDescription);
            $t->setNewBal($this->_floBal);

            $t->save(); //save the new transaction
        } else {
            throw new BankException("You do not own the account ".$accNum.". Please re-enter correct account number");
        }
    }

    /**
     * Method used to withdraw money from the customers account
     * checks to make sure not trying to withdraw more money than their current balance
     *
     * @param float $amount     amount to withdraw
     * @throws BankException
     */
    public function Withdraw($amount) {
        if($amount <= $this->_floBal) {
            $this->_floBal -= $amount;
        } else {
            //formating the number so it has commas for thousands and 2 places after the decimal
            $amount = number_format($amount, 2, '.', ',');
            $bal = number_format($this->_floBal, 2, '.', ',');
            throw new BankException("You have insufficient funds to withdraw $".$amount.", please enter a value lower than your current balance of $".$bal);
        }
    }

    /**
     * Method used to deposit money into the customers account
     *
     * @param float $amount   amount to deposit
     */
    public function Deposit($amount) {
        $this->_floBal += $amount;
    }
}