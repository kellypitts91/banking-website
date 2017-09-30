<?php
namespace agilman\a2\model;

use agilman\a2\Exception\BankException;

/**
 * Class CollectionModel
 *
 * @package kelly_ben/a2
 * @author  Kelly Pitts 09098321 & Ben Wilton 14262032
 */
class CollectionModel extends Model
{
    function __construct() {
        parent::__construct();
    }

    /**
     * Returns the top 5 transactions given a customer id
     *
     * @param $id
     * @return mixed
     * @throws BankException
     */
    public function getTransactions($id) {
        if (!$result = $this->db->query("SELECT t.`trans_id`, t.`trans_type`, FORMAT(t.`trans_amount`, 2), t.`trans_datetime`, t.`trans_description`, FORMAT(t.`trans_newBal`, 2) 
                                                FROM `transaction` AS t 
                                                INNER JOIN `account` AS a 
                                                WHERE t.`acc_id` = a.`acc_id` 
                                                AND a.`cus_id` = '$id'
                                                ORDER by t.`trans_id` DESC
                                                LIMIT 0,5;")) {
            throw new BankException("Failed to retrieve any transactions from the database: (". $this->db->errno . ") " . $this->db->error);
        }
        return $result->fetch_all();
    }
}