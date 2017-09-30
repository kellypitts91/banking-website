<?php

namespace agilman\a2\view;

use agilman\a2\Exception\BankException;

/**
 * Class View
 *
 * A wrapper for the view templates.
 * Limits the accessible scope available to phtml templates.
 *
 * @package kelly_ben/a2
 * @author  Kelly Pitts 09098321 & Ben Wilton 14262032
 */
class View
{

    /**
     * @var string path to template being rendered
     */
    protected $template = null;

    /**
     * @var array data to be made available to the template
     */
    protected $data = array();

    public function __construct($template) {
        try {
            $file =  APP_ROOT . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $template . '.phtml';

            if (file_exists($file)) {
                $this->template = $file;
            } else {
                throw new BankException('Template ' . $template . ' not found!');
            }
        }
        catch (BankException $ex) {
            echo $ex->getErrorMessage();
        }
    }

    /**
     * Adds a key/value pair to be available to phtml template
     *
     * @param string $key name of the data to be available
     * @param array $val value of the data to be available
     *
     * @return $this View
     */
    public function addData($key, $val) {
        $this->data[$key] = $val;
        return $this;
    }

    /**
     * Render the template, returning it's content.
     *
     * @return string The rendered template.
     */
    public function render() {
        extract($this->data);

        ob_start();
        include($this->template);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
}