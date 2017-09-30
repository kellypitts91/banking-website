<?php
namespace agilman\a2\controller;

use agilman\a2\Exception\BankException;
use agilman\a2\model\AccountModel;
use agilman\a2\model\CustomerModel;
use agilman\a2\model\CollectionModel;

/**
 * Class CustomerController
 *
 * @package kelly_ben/a2
 * @author  Kelly Pitts 09098321 & Ben Wilton 14262032
 */
class CustomerController extends Controller
{
    /**
     * Action called when user homes the homepage
     * Checks if user is already logged in or not
     * if they are logged in, they will be logged out for security reasons
     *
     * Customer Index action
     */
    public function indexAction()
    {
        session_start();
        //Bank is only available within NZ
        date_default_timezone_set("Pacific/Auckland");

        if (isset($_SESSION['loggedin'])) {
            $this->logout();
        } else {
            $this->showView('customerIndex');
        }
    }

    /**
     * Action called when user clicks sign up button
     * display new view
     *
     * Customer Register action
     */
    public function registerAction()
    {
        session_start();
        $this->showView('customerRegister');
    }

    /**
     * Action called when user creates a new account
     * Gets all the $_POST data and sets the private members of the CustomerModel
     * validating all data before saving the new customer
     * When a customer signs up, they automatically get an account associated with them
     * If any of the validation fails, will be redirected to an error page displaying the error
     *
     * Customer Create action
     */
    public function createAction()
    {
        session_start();
        try {
            $fName = ucfirst(strtolower($_POST['firstName']));
            $lName = ucfirst(strtolower($_POST['lastName']));

            $customer = new CustomerModel();
            $customer->setFirstName($fName);
            $customer->setLastName($lName);
            $customer->setDob($_POST['dateOfBirth']);
            $customer->setPhone($_POST['phone']);
            $customer->setEmail($_POST['eMail']);
            $customer->setPassword($_POST['pass1'], $_POST['pass2']);
            //throws an error if user enters a value that is too big to store. will NOT save the customer.
            //can store 13 digits before decimal point with 2 after decimal point
            $customer->checkDepositEntered($_POST['deposit']);
            $customer->save();

            $account = new AccountModel();
            if ((!isset($_POST['deposit'])) || ($_POST['deposit'] != null)) {
                $account->setBal($_POST['deposit']);
            } else {
                $account->setBal(0.0);
            }
            $account->setDateCreated(date("d/m/y h:i:sa"));
            $account->setCustomerId($customer->getId());
            $account->save();

            if(!isset($_SESSION['id'])) {
                //creating a new session and automatically logging them in
                $this->setSession($customer->getId(), $customer->getFirstName(), $customer->getLastName(), $customer->getEmail());
            }
            //getting data to display on the customers dashboard
            $currentBalance = array($account->getId(), $account->getBalance(), $customer->getFirstName(), $customer->getLastName());
            $this->showView('customerDashboard', $currentBalance);
        } catch (BankException $ex) {
            $this->showView('errorPage', $ex->getErrorMessage());
        }
    }

    /**
     * Action called when customer logs in
     * Gets the current account information for the customer along with all their transactions to display on the dashboard
     *
     * Customer Login action
     */
    public function loginAction()
    {
        session_start();
        try {
            $account = new AccountModel();
            $account->load($_SESSION['id']);
            $currentBalance = array($account->getId(), $account->getBalance(), $_SESSION['first_name'], $_SESSION['last_name']);

            $collection = new CollectionModel();
            $listOfTransactions = $collection->getTransactions($_SESSION['id']);
            //checking if they are logged in, should not be able to see the dashboard if not logged in
            if ($this->isLogedIn()) {
                $this->showView('customerDashboard', $currentBalance, $listOfTransactions);
            } else {
                $this->showView('customerLogin', $currentBalance);
            }
        } catch (BankException $ex) {
            $this->showView('errorPage', $ex->getErrorMessage());
        }
    }

    /**
     * Action called when customer is redirected to the dashboard
     * validates the email and password entered
     * creates a new session if one does not exist
     *
     * Customer Dashboard action
     */
    public function dashboardAction()
    {
        session_start();
        try {
            $email = $_POST['email'];
            $pass = $_POST['password'];

            $customer = new CustomerModel();
            $customer->validateUser($email, $pass); //will throw bank exception if not a valid log in

            $account = new AccountModel();
            $account = $account->getAccountByEmail($_POST['email']); //will throw bank exception if query fails
            if (!isset($_SESSION['id'])) {
                //Set the current session
                $this->setSession($account['cus_id'], $account['cus_fName'], $account['cus_lName'], $account['cus_email']);
            }

            $collection = new CollectionModel();
            $listOfTransactions = $collection->getTransactions($account['cus_id']);
            $currentBalance = array($account['acc_id'], $account['acc_bal'], $account['cus_fName'], $account['cus_lName']);

            if ($this->isLogedIn()) {
                $this->showView('customerDashboard', $currentBalance, $listOfTransactions);
            } else {
                $this->showView('customerLogin', $currentBalance, $listOfTransactions);
            }
        } catch (BankException $ex) {
            $this->showView('errorPage', $ex->getErrorMessage());
        }
    }
}