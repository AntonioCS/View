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

    public static $EXT = 'tpl.php';

    /**
    * Construct
    *
    * @param string|null $view  - Relative/Absolute path to the view/template
    * @param array|null $config - Config set up for the instance
    * @return acs_view
    */
    public function __construct($view = null, $config = null) {
        $ext = null;
        $path = null;

        if (!empty($config)) {
            $ext = isset($config['ext']) ? $config['ext'] : self::$EXT;
            $path = isset($config['path']) ? $config['path'] : self::$PATH;
        }
        else {
            $ext = self::$EXT;
            $path = self::$PATH;
        }

        $this->setExt($ext);
        $this->setPath($path);

        if ($view)
            $this->load($view);
    }

    /**
    * Set the view to rendered
    *
    * @param string $view
    * @return bool
    *
    * @throws acs_viewExceptionNoPath, acs_viewExceptionExtension, acs_viewExceptionViewNotFound
    */
    public function load($view) {
        $paths = $this->getPath();
        $ext = $this->getExt();
        $testPath = null;
        $viewPath = null;

        if (empty($paths))
            throw new acs_viewExceptionNoPath();

        if (empty($ext))
            throw new acs_viewExceptionExtension();

        foreach ((array) $paths as $path) {
           $testPath = $path . $view . '.' . $ext;

           if (file_exists($testPath)) {
               $viewPath = $testPath;
               break;
           }
        }

        if (!$viewPath)
            throw new acs_viewExceptionViewNotFound($testPath);

        $this->setView($viewPath);

        return true;
    }

    /**
    * Return hasRendered property
    * @return bool
    */
    public function hasRendered() {
        return $this->_hasRendered;
    }

    /**
    * Return view path(s)
    *
    * @return string|array
    */
    public function getPath() {
        return $this->_config['path'];
    }

    /**
    * Set view path
    *
    * @param string|array $path
    */
    public function setPath($path) {
        $this->_config['path'] = $path;
    }

    /**
    * Return extension for views
    *
    * @return string
    */
    public function getExt() {
        return $this->_config['ext'];
    }

    /**
    * Set the extension for the views
    *
    * @param string $ext
    */
    public function setExt($ext) {
        $this->_config['ext'] = $ext;
    }


    /**
    * Set the path to the view in the private property _view
    *
    * @param string $view Full path to the view
    */
    private function setView($view) {
        $this->_view = $view;
    }

    /**
    * Return the contents of the private property _view
    *
    * @return string|null
    */
    private function getView() {
        return $this->_view;
    }
}

class acs_viewExceptionExtension extends Exception {}
class acs_viewExceptionNoPath extends Exception {}
class acs_viewExceptionViewNotFound extends Exception {}