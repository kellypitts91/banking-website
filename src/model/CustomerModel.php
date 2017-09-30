<?php
namespace agilman\a2\model;

use agilman\a2\Exception\BankException;


/**
 * Class CustomerModel
 *
 * @package kelly_ben/a2
 * @author  Kelly Pitts 09098321 & Ben Wilton 14262032
 */
class CustomerModel extends Model
{
    /**
     * @var integer Customer ID
     * @var string Customer first name
     * @var string Customer last name
     * @var string Customer date of birth
     * @var string Customer phone number
     * @var string Customer email address
     * @var string Customer hashed password
     */
    private $_id;
	private $_strFirstName;
	private $_strLastName;
	private $_strDateofBirth;
	private $_strPhoneNo;
	private $_strEmail;
	private $_strHashedPassword;

    public function __construct() {
        parent::__construct();
    }

    /**
     * @return int Customer ID
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @return string Customer first name
     */
    public function getFirstName()
    {
        return $this->_strFirstName;
    }
	
	/**
     * @return string Customer last name
     */
    public function getLastName()
    {
        return $this->_strLastName;
    }

	/**
     * @return string Customer email address
     */
    public function getEmail()
    {
        return $this->_strEmail;
    }

    /**
     * @param string $fName Customer first name
     *
     * @return $this CustomerModel
     */
    public function setFirstName(string $fName)
    {
        $this->_strFirstName = $fName;
        return $this;
    }
	
	/**
     * @param string $lName Customer last name
     *
     * @return $this CustomerModel
     */
    public function setLastName(string $lName)
    {
        $this->_strLastName = $lName;
        return $this;
    }
	
	/**
     * @param string $dob Customer date of birth
     *
     * @return $this CustomerModel
     */
    public function setDob(string $dob)
    {
        $this->_strDateofBirth = $dob;
        return $this;
    }

    /**
     * @param string $phone Customer phone number
     *
     * @return $this CustomerModel
     */
    public function setPhone(string $phone)
    {
        $this->_strPhoneNo = $phone;
        return $this;
    }
	
	/**
     * @param string $email Customer email address
     *
     * @return $this CustomerModel
     */
    public function setEmail(string $email)
    {
        $this->_strEmail = $email;
        return $this;
    }

    /**
     * Passwords need to be checked that they match before continuing and must be at least 4 characters long
     *
     * @param string $p1 Customer first Password
     * @param string $p2 Customer second Password
     * @return $this CustomerModel
     * @throws BankException
     */
    public function setPassword(string $p1, string $p2) {
        if(strlen($p1) >= 4) {
            $this->_strHashedPassword = $this->checkPasswordMatch($p1, $p2);
            return $this;
        }
        throw new BankException("Error: the password must be at least 4 characters long");
    }

    /**
     * Checking passwords match
     *
     * @param string $p1 Customer first Password
     * @param string $p2 Customer second Password
     * @return string   Returns the hashed password
     * @throws BankException
     */
    public function checkPasswordMatch(string $p1, string $p2) {
        if($p1 == $p2) {
            return password_hash($p1, PASSWORD_DEFAULT);
        } else {
            throw new BankException("Passwords do not match");
        }
    }

    /**
     * Loads Customer information from the database
     *
     * @param int $id Customer ID
     * @return $this CustomerModel
     * @throws BankException
     */
    public function load($id)
    {
        if (!$result = $this->db->query("SELECT * FROM `customer` WHERE `cus_id` = '$id';")) {
            throw new BankException("Select customer failed: (". $this->db->errno . ") " . $this->db->error);
        }
		if($result->num_rows > 0) {
			
			$row = $result->fetch_assoc();
			
			$this->_id = $id;
			$this->_strFirstName = $row['cus_fName'];
			$this->_strLastName = $row['cus_lName'];
			$this->_strDateofBirth = $row['cus_dob'];
			$this->_strPhoneNo = $row['cus_phNumber'];
			$this->_strEmail = $row['cus_email'];
			$this->_strHashedPassword = $row['cus_pass'];
		}
        return $this;
    }

    /**
     * Saves Customer information to the database
     * if customer already exist, updates the current customer
     *
     * @return $this CustomerModel
     * @throws BankException
     */
    public function save()
    {
        if($this->userExist() > 0 && $_POST != null) {
            throw new BankException("The email you entered already exist");
        }
        $fName = $this->_strFirstName??"NULL";
		$lName = $this->_strLastName??"NULL";
		$dob = $this->_strDateofBirth??"NULL";
		$phone = $this->_strPhoneNo??"NULL";
		$email = $this->_strEmail??"NULL";
		$pass = $this->_strHashedPassword??"NULL";

        if (!isset($this->_id)) {
            // New Customer - Perform INSERT
            if (!$result = $this->db->query("INSERT INTO `customer` VALUES (NULL, '$fName', '$lName', '$dob', '$phone', '$email', '$pass');")) {
                throw new BankException("Insert customer failed: (". $this->db->errno . ") " . $this->db->error);
            }

            $this->_id = $this->db->insert_id;
        } else {
            // saving existing Customer - perform UPDATE
            if (!$result = $this->db->query("UPDATE `customer` SET 
					`cus_fName` = '$fName', 
					`cus_lName` = '$lName',
					`cus_dob` = '$dob',
					`cus_phNumber` = '$phone',
					`cus_email` = '$email',
					`cus_pass` = '$pass'
					WHERE `cus_id` = $this->_id;")) {
                throw new BankException("Update customer failed: (". $this->db->errno . ") " . $this->db->error);
            }
        }
        return $this;
    }

    /**
     * Checks if user already exist in the database
     * if 1 or more rows are returned, the customer already exist (should only ever be 0 or 1)
     *
     * @return int  Returns number of rows found with that email
     * @throws BankException
     */
    public function userExist() {
        if(!$result = $this->db->query("SELECT * FROM `customer` WHERE cus_email = '$this->_strEmail'")) {
            throw new BankException("Check email failed: (". $this->db->errno . ") " . $this->db->error);
        }
        return $result->num_rows;
    }

    /**
     * Method checks if the password and email entered match a valid email and (hashed)password in the database
     *
     * @param string $email     email entered by the user
     * @param string $pass      plain text password entered by the user
     * @return bool
     * @throws BankException
     */
    public function validateUser($email, $pass) {

        if(!$res = $this->db->query("SELECT * FROM `customer` WHERE `cus_email` = '$email'")){
            throw new BankException("Validate customer failed: (". $this->db->errno . ") " . $this->db->error);
        }

        if($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            if(password_verify($pass, $row['cus_pass'])) {
                return true; //email and password match
            }
            throw new BankException("Password incorrect");
            //return false; //password incorrect
        }
        throw new BankException("Email incorrect");
        //return false; //email incorrect
    }

    /**
     * Deletes Customer from the database
     * not currently using this method. Future feature can be to de-register as a customer.
     *
     * @return $this CustomerModel
     * @throws BankException
     */
    public function delete()
    {
        if (!$result = $this->db->query("DELETE FROM `customer` WHERE `cus_id` = $this->_id;")) {
            throw new BankException("Delete customer failed: (". $this->db->errno . ") " . $this->db->error);
        }
        return $this;
    }

    /**
     * Method to ensure customer doesn't enter an amount that is greater than the size of the column in the database
     *
     * @param float $amount     Amount entered by the customer when creating a new account
     * @throws BankException
     */
    public function checkDepositEntered($amount)
    {
        if ($amount > 999999999999.99) {
            throw new BankException("Deposit amount is too high, please enter a lower amount to get started");
        }
    }
}