<?php
/**
 * 159.339 Internet Programming 2017.2
 * Student ID: 09098321 & 14262032
 * Assignment: 2   Date: 01/10/17
 * System: PHP 7.1
 * Code guidelines: PSR-1, PSR-2
 *
 * FRONT CONTROLLER - Responsible for URL routing and User Authentication
 *
 * @package agilman/a2
 * @author  Kelly Pitts 09098321 & Ben Wilton 14262032
 **/
date_default_timezone_set('Pacific/Auckland');

require __DIR__ . '/vendor/autoload.php';

use PHPRouter\RouteCollection;
use PHPRouter\Router;
use PHPRouter\Route;

define('APP_ROOT', __DIR__);

$collection = new RouteCollection();

$collection->attachRoute(
    new Route(
        '/FirstNationalBank/', array(
            '_controller' => 'agilman\a2\controller\CustomerController::indexAction',
            'methods' => 'GET',
            'name' => 'customerIndex'
        )
    )
);

$collection->attachRoute(
    new Route(
        '/FirstNationalBank/', array(
            '_controller' => 'agilman\a2\controller\CustomerController::indexAction',
            'methods' => 'POST',
            'name' => 'customerIndex'
        )
    )
);

$collection->attachRoute(
    new Route(
        '/FirstNationalBank/register/', array(
        '_controller' => 'agilman\a2\controller\CustomerController::registerAction',
        'methods' => 'GET',
        'name' => 'customerRegister'
        )
    )
);

$collection->attachRoute(
    new Route(
        '/FirstNationalBank/create/', array(
        '_controller' => 'agilman\a2\controller\CustomerController::createAction',
        'methods' => 'POST',
        'name' => 'customerCreate'
        )
    )
);

$collection->attachRoute(
    new Route(
        '/FirstNationalBank/login/', array(
            '_controller' => 'agilman\a2\controller\CustomerController::loginAction',
            'methods' => 'GET',
            'name' => 'customerLogin'
        )
    )
);

$collection->attachRoute(
    new Route(
        '/FirstNationalBank/login/dashboard/', array(
            '_controller' => 'agilman\a2\controller\CustomerController::dashboardAction',
            'methods' => 'POST',
            'name' => 'customerDashboard'
        )
    )
);

$collection->attachRoute(
    new Route(
        '/FirstNationalBank/login/dashboard/', array(
            '_controller' => 'agilman\a2\controller\CustomerController::dashboardAction',
            'methods' => 'GET',
            'name' => 'customerDashboard'
        )
    )
);

$collection->attachRoute(
    new Route(
        '/FirstNationalBank/login/dashboard/entertransaction/', array(
            '_controller' => 'agilman\a2\controller\TransactionController::enterTransactionAction',
            'methods' => 'GET',
            'name' => 'customerEnterTransaction'
        )
    )
);

$collection->attachRoute(
    new Route(
        '/FirstNationalBank/login/dashboard/submittransaction', array(
            '_controller' => 'agilman\a2\controller\TransactionController::submitTransactionAction',
            'methods' => 'POST',
            'name' => 'customerDashboard'
        )
    )
);

$collection->attachRoute(
    new Route(
        '/FirstNationalBank/error', array(
            '_controller' => 'agilman\a2\controller\CustomerController::errorAction',
            'methods' => 'POST',
            'name' => 'errorPage'
        )
    )
);

$router = new Router($collection);
$router->setBasePath('/');

$route = $router->matchCurrentRequest();

// If route was dispatched successfully - return
if ($route) {
    // true indicates to webserver that the route was successfully served
    return true;
}

// Otherwise check if the request is for a static resource
$info = parse_url($_SERVER['REQUEST_URI']);
// check if its allowed static resource type and that the file exists
if (preg_match('/\.(?:png|jpg|jpeg|css|js)$/', "$info[path]")
    && file_exists("./$info[path]")
) {
    // false indicates to web server that the route is for a static file - fetch it and return to client
    return false;
} else {
    header("HTTP/1.0 404 Not Found");
    // Custom error page
    // require 'static/html/404.html';
    return true;
}