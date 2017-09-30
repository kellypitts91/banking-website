<?php

namespace agilman\a2\controller;

use agilman\a2\view\View;
/**
 * Base Class Controller
 * Includes all methods that are not associated with just 1 controller
 *
 * @package kelly_ben/a2
 * @author  Kelly Pitts 09098321 & Ben Wilton 14262032
 */
class Controller
{
    /**
     * Determines if a user is logged in or not
     *
     * @return bool  Returns true if logged in otherwise returns false
     */
    public function isLogedIn() {
        if ($_SESSION != null) {
            return true;
        }
        return false;
    }

    /**
     * Generate a link URL for a named route
     *
     * @param string $route  Named route to generate the link URL for
     * @param array  $params Any parameters required for the route
     *
     * @return string  URL for the route
     */
    static function linkTo($route, $params=[])
    {
        // cheating here! What is a better way of doing this?
        $router = $GLOBALS['router'];
        return $router->generate($route, $params);
    }

    /**
     * This method gets called whenever a BankException is thrown.
     * Do nothing other than keep the current session
     *
     * Customer Error Action
     */
    public function errorAction() {
        session_start();
    }

    /**
     * End session and log the customer out, returning them to the homepage
     */
    public function logout() {
        session_destroy();
        $this->showView('customerIndex');
    }

    /**
     * Start a new session based on the information provided when the user logs in or registers
     *
     * @param int $id           The customers Id
     * @param string $fName     The customers first name
     * @param string $lName     The customers last name
     * @param string $email     The customers email address
     */
    public function setSession($id, $fName, $lName, $email)
    {
        $_SESSION['id'] = $id;
        $_SESSION['first_name'] = $fName;
        $_SESSION['last_name'] = $lName;
        $_SESSION['email'] = $email;
        $_SESSION['loggedin'] = true;
    }

    /**
     * Determines which view to display to the user based on the filename and the data needed to display on that page
     *
     * @param string $fileName  The name of the template file to be viewed
     * @param null $data        Optional param for data to be sent to the view
     * @param null $data2       Optional param for a second set of data to be sent to the view
     */
    public function showView($fileName, $data = null, $data2 = null)
    {
        $view = new View($fileName);
        if($data == null) {
            echo $view->addData('linkTo', function ($route, $params = []) {
                return $this->linkTo($route, $params);
            })->render();
        } else if($data2 == null) {
            echo $view->addData('data', $data)
                ->addData('linkTo', function ($route, $params = []) {
                    return $this->linkTo($route, $params);
                })->render();
        } else {
            echo $view->addData('data', $data)->addData('data2', $data2)
                ->addData('linkTo', function ($route, $params = []) {
                    return $this->linkTo($route, $params);
                })->render();
        }
    }
}