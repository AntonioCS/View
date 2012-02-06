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
    * Has the view been rendered?
    *
    * @return bool
    */
    public function hasRendered() {
        return $this->_hasRendered;
    }

    /**
    * Set the view as rendered
    *
    */
    public function isRendered() {
        $this->_hasRendered = true;
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

    /**
     * Call set() method
     *
     * @param string $item
     * @param mixed $value
     */
    public function __set($item, $value) {
        $this->set($item,$value);
    }

    /**
    * Call get() method
    *
    * @param mixed $item
    */
    public function __get($item) {
        return $this->get($item);
    }

    /**
    * Set an item on the _data array
    *
    * @param mixed $item
    * @param mixed $value
    */
    public function set($item,$value) {
        $this->_data[$item] = $value;
    }

    /**
    * Return an item from the _data array
    *
    * @param mixed $item Key name to get the value from
    * @param mixed $defaultValue A default value to return in case there must be something returned
    *
    * @return mixed
    */
    public function get($item, $defaultValue = null) {
        return isset($this->_data[$item]) ? $this->_data[$item] : $defaultValue;
    }

    /**
     * Renders the view
     * if $return is set to true the code will be returned
     *
     */
    public function render() {
        ob_start();
        require($this->getView());
        $buffer = ob_get_clean();

        $this->isRendered();

        return $buffer;
    }


    /**
    * Array for the blocks contents
    *
    * @var array
    */
    private $_blocks = array();

    private $_blocksOrder = array();
    private $_blocksAppend = array();
    private $_blocksFilters = array();

    private $_expands = null;

    public function blockStart($bname, $append = false, $filters = null) {
        array_push($this->_blocksOrder,$bname);

        if ($append)
            $this->_blocksAppend[$bname] = true;

        if ($filters)
            $this->_blocksFilters[$bname] = $filters;

        ob_start();
    }

    public function blockEnd() {
        $buffer = ob_get_clean();
        $bname = array_shift($this->_blocksOrder);

        if (!isset($this->_blocks[$bname]))
            $this->_blocks[$bname] = $buffer;
        elseif (isset($this->_blocksAppend[$bname]))
            $this->_blocks[$bname] .= $buffer;

        echo $this->_blocks[$bname];
    }

    public function block($bname) {
        return isset($this->_blocks[$bname]) ? $this->_blocks[$bname] : null;
    }

    public function expands($template) {
        $this->_expands = $template;
    }
}

class acs_viewExceptionExtension extends Exception {}
class acs_viewExceptionNoPath extends Exception {}
class acs_viewExceptionViewNotFound extends Exception {}