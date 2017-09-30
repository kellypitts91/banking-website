<?php
namespace agilman\a2\model;

use agilman\a2\Exception\BankException;
use mysqli;
require_once 'db_creds.php';

/**
 * Class Model
 *
 * @package kelly_ben/a2
 * @author  Kelly Pitts 09098321 & Ben Wilton 14262032
 */
class Model
{
    /*
     * @var mysqli $db Database
     */
    protected $db;
    /*
     * Count to keep track of how many tables have been created. only want to populate the tables once all tables have been created.
     * @var int $count
     */
    private $count;

    /**
     * Creates a new database connection
     * Populates with 3 tables
     * populates the 3 tables with dummy data on first load if the tables are being created for the first time
     *
     * Model constructor.
     * @throws BankException
     */
    function __construct()
    {
        $this->db = new mysqli(
            DB_HOST,
            DB_USER,
            DB_PASS
        );

        if (!$this->db) {
            throw new BankException("Cannot connect to database: (" . $this->db->errno . ") " . $this->db->error);
        }

        //----------------------------------------------------------------------------
        // This is to make our life easier
        // Create database
        $this->db->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME . ";");

        if (!$this->db->select_db(DB_NAME)) {
            throw new BankException("Mysql database not available!");
        }

        //storing the queries into a string for readability
        //when sending the string to the method to create the new table
        $sqlCustomer = "CREATE TABLE `customer` (
                                        `cus_id` INT NOT NULL AUTO_INCREMENT, 
										`cus_fName` VARCHAR(50) NOT NULL, 
										`cus_lName` VARCHAR(50) NOT NULL, 
										`cus_dob` VARCHAR(10) NOT NULL,
										`cus_phNumber` VARCHAR(20) NOT NULL,
										`cus_email` VARCHAR(50) NOT NULL, 
										`cus_pass` VARCHAR(100) NOT NULL,
										Primary key (cus_id));";

        $sqlAccount = "CREATE TABLE `account` (
                                        `acc_id` INT NOT NULL AUTO_INCREMENT, 
										`acc_bal` DECIMAL(15,2) NOT NULL, 
										`acc_dateCreated` VARCHAR(20) NOT NULL,
										`cus_id` INT,
										PRIMARY KEY (acc_id),
										CONSTRAINT FK_customer FOREIGN KEY (cus_id) REFERENCES customer(cus_id) ON DELETE CASCADE)";

        $sqlTransaction = "CREATE TABLE `transaction` (
                                        `trans_id` INT NOT NULL AUTO_INCREMENT, 
										`trans_type` CHAR(10) NOT NULL,
										`trans_amount` DECIMAL(15,2) NOT NULL,
										`trans_datetime` VARCHAR(20) NOT NULL,
										`acc_id` INT,
										`trans_description` VARCHAR(100),
										`trans_newBal` DECIMAL(15,2) NOT NULL,
										PRIMARY KEY (trans_id),
										CONSTRAINT FK_account FOREIGN KEY (acc_id) REFERENCES account(acc_id) ON DELETE CASCADE);";
        $this->count = 0;
        $this->createNewTable($sqlCustomer, 'customer');
        $this->createNewTable($sqlAccount, 'account');
        $this->createNewTable($sqlTransaction, 'transaction');
    }

    /**
     * Method to create the table if it does not already exist
     *
     * @param string $sqlQuery      query to create the new table
     * @param string $tableName     table name to check the table does not already exist
     * @throws BankException
     */
    public function createNewTable($sqlQuery, $tableName)
    {
        $result = $this->db->query("SHOW TABLES LIKE '$tableName';");
        if ($result->num_rows == 0) {
            // table doesn't exist
            // create it and populate with sample data

            if (!$result = $this->db->query($sqlQuery)) {
                // handle appropriately
                throw new BankException("Failed creating table: " . $tableName);
            }
            $this->count++;
            if($this->count == 3) { //only want to insert rows after all tables have been created
                $this->insertDummyData();
            }
        }
    }

    /**
     * Inserts dummy data into the 3 tables upon first load
     * only runs when the tables have been created for the first time
     */
    public function insertDummyData()
    {
        $pass = "1234";
        $this->insertCustomerAndAccount('Tony', 'Stark', '06/03/83', '1234567', 'tony.stark@gmail.com', $pass, 10000);
        $this->insertCustomerAndAccount('Steven', 'Rogers', '03/09/44', '2345678', 'captain.america@gmail.com', $pass, 1500);
        $this->insertCustomerAndAccount('Peter', 'Parker', '15/08/83', '3456789', 'spiderman@gmail.com', $pass, 1000);
        $this->insertCustomerAndAccount('Diana', 'Prince', '21/06/85', '4567891', 'wonder.women@gmail.com', $pass, 500);
        $this->insertCustomerAndAccount('Natasha', 'Romanoff', '02/05/91', '5678912', 'black.widow@gmail.com', $pass, 5600);


        $this->insertTransactions(1, 50, "Deposit", "Savings", 1);
        $this->insertTransactions(1, 150.50, "Withdraw", "Groceries",1);
        $this->insertTransactions(1, 20, "Deposit", "Savings",1);
        $this->insertTransactions(1, 55.28, "Deposit", "Savings",1);
        $this->insertTransactions(1, 1105.10, "Withdraw", "Xmas Shopping",1);

        $this->insertTransactions(2, 50.10, "Withdraw", "Shopping",2);
        $this->insertTransactions(2, 1550.33, "Deposit", "Income",2);

        $this->insertTransactions(3, 500, "Withdraw", "Bills",3);
        $this->insertTransactions(3, 400, "Withdraw", "Insurance",3);
        $this->insertTransactions(3, 50, "Withdraw", "Groceries",3);
        $this->insertTransactions(3, 50, "Withdraw", "Shopping",3);

        $this->insertTransactions(4, 600, "Deposit", "Income",4);
        $this->insertTransactions(4, 50.50, "Withdraw", "Shopping",4);
        $this->insertTransactions(4, 600, "Deposit", "Income",4);

        $this->insertTransactions(5, 350.60, "Deposit", "Savings",5);
    }

    /**
     * Method used to insert new customers and accounts
     *
     * @param string $fName     Customers first name
     * @param string $lName     Customers last name
     * @param string $dob       Customers date of birth
     * @param string $phone     Customers phone number
     * @param string $email     Customers email address
     * @param string $pass      Customers plain text password
     * @param float $bal        Customers balance
     */
    public function insertCustomerAndAccount($fName, $lName, $dob, $phone, $email, $pass, $bal) {
        $customer = new CustomerModel();
        $customer->setFirstName($fName);
        $customer->setLastName($lName);
        $customer->setDob($dob);
        $customer->setPhone($phone);
        $customer->setEmail($email);
        $customer->setPassword($pass, $pass);
        $customer->save();

        $account = new AccountModel();
        $account->setBal($bal);
        $account->setDateCreated(date("d/m/y h:i:sa"));
        $account->setCustomerId($customer->getId());
        $account->save();
    }

    /**
     * Method to insert new transactions
     *
     * @param int $accNum               Customers account number
     * @param float $accAmount          Amount to calculate
     * @param string $accTransType      Transaction type to perform calculation (Deposit or Withdraw)
     * @param string $accDescription    Optional description to give to the transaction
     * @param int $cusId                Customers ID - Foreign Key to the customer table
     */
    public function insertTransactions($accNum, $accAmount, $accTransType, $accDescription, $cusId) {
        $account = new AccountModel();
        $account->load($cusId);
        $account->CalculateTransactions($accNum, $accAmount, $accTransType, $accDescription);
    }
}