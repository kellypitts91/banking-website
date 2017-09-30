<?php

namespace agilman\a2\controller;

use agilman\a2\Exception\BankException;
use agilman\a2\model\AccountModel;
use agilman\a2\model\CollectionModel;

/**
 * Class TransactionController
 *
 * @package kelly_ben/a2
 * @author  Kelly Pitts 09098321 & Ben Wilton 14262032
 */
class TransactionController extends Controller {

    /**
     * Action called when user clicks enter transaction button
     * load the current account for the customer to display the account Id and Balance at the top of the page
     *
     * Enter Transaction Action
     */
    public function enterTransactionAction() {
        session_start();
        $account = new AccountModel();
        $account->load($_SESSION['id']);
        $currentBalance = array($account->getId(), $account->getBalance());
        $this->showView('customerEnterTransaction', $currentBalance);
    }

    /**
     * Action called when user submits the new transaction
     * load the current account for the customer to check all the details and calculate the new balance
     * once transaction complete, returns to the dashboard
     *
     * Submit Transaction Action
     */
    public function submitTransactionAction() {
        session_start();
        try {
            $account = new AccountModel();
            $currentAccount = $account->load($_SESSION['id']);

            if (isset($_POST['submitTransaction']) && $_POST['submitTransaction'] == 'submit') {
                $accNum = $_POST['accNumber'];
                $accAmount = $_POST['amount'];
                $accTransType = $_POST['transactionType'];
                $accDescription = $_POST['description'];
                //throws a BankException if try to make transaction on someone else account
                $currentAccount->CalculateTransactions($accNum, $accAmount, $accTransType, $accDescription);
            }
            $currentBalance = array($account->getId(), $account->getBalance(), $_SESSION['first_name'], $_SESSION['last_name']);

            $collection = new CollectionModel();
            $listOfTransactions = $collection->getTransactions($_SESSION['id']);
            $this->showView('customerDashboard', $currentBalance, $listOfTransactions);
        } catch (BankException $ex) {
            $this->showView('errorPage', $ex->getErrorMessage());
        }
    }
}