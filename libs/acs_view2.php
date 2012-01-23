<?php


class acs_view {

    /**
    * Array that holds the data for the view
    *
    * @var array
    */
    private $_data = array();

    /**
    * Configurations for this view
    *
    * @var array
    */
    private $_config = array(
                            'ext' => 'tpl.php',
                            'path' => 'tpls/'
                        );

    /**
    * Bool to check if the view has been rendered
    *
    * @var bool
    */
    private $_hasRendered = false;


    /**
    * Path to the view
    *
    * @var string
    */
    private $_view = null;


    /**
    * Global path for the views/templates
    *
    * @var string
    */
    public static $PATH = 'tpls/';

    /**
    * Construct
    *
    * @param string|null $view  - Relative/Absolute path to the view/template
    * @param array|null $config - Config set up for the instance
    * @return acs_view
    */
    public function __construct($view = null, $config = null) {

        if ($config) {
            $this->_config = $config;
        }
        else {
            $this->_config['path'] = self::$PATH;
        }

        if ($view)
            $this->load($view);
    }

    /**
    * Set the view to rendered
    *
    * @param string $view
    */
    public function load($view) {

    }

    /**
    * Return hasRendered property
    *
    */
    public function hasRendered() {
        return $this->_hasRendered;
    }


    /**
    * Return view path(s)
    *
    * @return string|array
    */
    public function getViewPath() {
        return $this->_config['path'];
    }

    /**
    * Set view path
    *
    * @param string|array $path
    */
    public function setViewPath($path = null) {
        $this->_config['path'] = $path ?: self::$PATH;
    }

}
