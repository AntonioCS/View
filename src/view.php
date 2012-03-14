<?php


class view {

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
    * Array for the blocks contents
    *
    * @var array
    */
    private $_blocks = array();

    /**
    * @var array
    */
    private $_blocksOrder = array();

    /**
    * @var array
    */
    private $_blocksAppend = array();

    /**
    * @var array
    */
    private $_blocksFilters = array();

    /**
    * @var array
    */
    private $_blocksPriority = array();

    /**
    * Hold the name of the template to expand from
    *
    * @var string
    */
    private $_expands = null;

    /**
    * Global path for the views/templates
    *
    * @var string
    */
    public static $PATH = 'tpls/';

    /**
    * Global extension for the view/templates
    *
    * @var mixed
    */
    public static $EXT = 'tpl.php';

    /**
    * Construct
    *
    * @param string|null $view  - Relative/Absolute path to the view/template
    * @param array|null $config - Config set up for the instance
    *
    * @return view
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
    *
    * @return view
    *
    * @throws NoPathViewException
    * @throws FileExtensionViewException
    * @throws ViewNotFoundViewException
    */
    public function load($view) {
        $view = str_replace(chr(0), '', $view); //Prevent Poison Null Byte
        $paths = $this->getPath();
        $ext = $this->getExt();
        $testPath = null;
        $viewPath = null;

        if (empty($paths))
            throw new NoPathViewException();

        if (empty($ext))
            throw new FileExtensionViewException();

        foreach ((array) $paths as $path) {
           $testPath = $path . $view . '.' . $ext;

           if (file_exists($testPath)) {
               $viewPath = $testPath;
               break;
           }
        }

        if (!$viewPath)
            throw new ViewNotFoundViewException($testPath);

        $this->setView($viewPath);

        return $this;
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
    * @return view
    */
    public function isRendered() {
        $this->_hasRendered = true;
        return $this;
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
    *
    * @return view
    */
    public function setPath($path) {
        $this->_config['path'] = $path;
        return $this;
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
    *
    * @return view
    */
    public function setExt($ext) {
        $this->_config['ext'] = $ext;
        return $this;
    }

    /**
    * Set the path to the view in the private property _view
    *
    * @param string $view Full path to the view
    *
    * @return view
    */
    private function setView($view) {
        $this->_view = $view;
        return $this;
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
    * @param mixed $item - Can be an array to set in one call multiple variables in the view
    * @param mixed $value
    *
    * @return view
    */
    public function set($item,$value = null) {
        if (is_array($item)) {
            foreach ($item as $k=>$v)
                $this->set($k,$v);
        }
        else
            $this->_data[$item] = $value;

        return $this;
    }

    /**
    * Return an item from the _data array
    *
    * @param string $item Key name to get the value from
    * @param mixed $defaultValue A default value to return in case there must be something returned
    *
    * @return mixed
    */
    public function get($item, $defaultValue = null) {
        return isset($this->_data[$item]) ? $this->_data[$item] : $defaultValue;
    }

    /**
     * Renders the view
     *
     * @return string rendered template
     */
    public function render() {
        ob_start();
        require($this->getView());
        $buffer = ob_get_clean();

        if (($e = $this->getExpands()) != null) {
            $buffer = $this->clearExpands()->load($e)->render();
        }

        $this->isRendered();

        return $buffer;
    }

    /**
    * Start a block
    *
    * @param string $bname
    * @param bool $append
    * @param int $priority - Lever of priority of this block in relation to the others. Default 1
    * @param mixed $filters - Array of functions to call on the block (when ended)
    *
    */
    public function blockStart($bname, $append = false, $priority = 1, $filters = null) {
        array_push($this->_blocksOrder,$bname);

        if ($append)
            $this->_blocksAppend[$bname] = true;

        $this->_blocksPriority[$bname] = (int)$priority;


        if ($filters)
            $this->_blocksFilters[$bname] = (array)$filters;

        ob_start();
    }

    /**
    * End the block and echo it's contents
    *
    * @throws FilterNotCallableViewException
    *
    */
    public function blockEnd() {
        $buffer = ob_get_clean();
        $bname = array_shift($this->_blocksOrder);
        $bpri = $this->_blocksPriority[$bname];

        if (isset($this->_blocksFilters[$bname])) {
            foreach ($this->_blocksFilters[$bname] as $filter) {
                if (is_callable($filter)) {
                    $buffer = $filter($buffer);
                }
                else {
                    //Format should be array('function',array('option1','option2'...)) I will then unshift $buffer into the array
                    if (is_array($filter)) {
                        $filter_ = $filter[0];
                        $parameters = $filter[1];

                        array_unshift($parameters, $buffer);

                        $buffer = call_user_func_array($filter_, $parameters);
                    }
                    else
                        throw new FilterNotCallableViewException($filter);
                }
            }
        }

        if (!isset($this->_blocks[$bname])) {
            $this->_blocks[$bname][$bpri] = $buffer;
        }

        elseif (isset($this->_blocksAppend[$bname])) {
            if (isset($this->_blocks[$bname][$bpri]))
                $this->_blocks[$bname][$bpri] .= $buffer;
            else
                $this->_blocks[$bname][$bpri] = $buffer;
        }

        //to prevent code duplication
        echo $this->block($bname);
    }

    /**
    * Return blocks content by highest priority
    *
    * @param string $bname Block name
    */
    public function block($bname) {
        return isset($this->_blocks[$bname]) ? implode('',array_reverse($this->_blocks[$bname])) : null;
    }

    /**
    * Set the template the current template is expanding
    *
    * @param string $template
    */
    public function expands($template) {
        if ($this->_expands)
            throw new Exception('Double expanding');

        $this->_expands = $template;
    }

    /**
    * Get the expanding template set by the expands() method
    *
    */
    public function getExpands() {
        return $this->_expands;
    }

    /**
    * Clear the expand property
    *
    */
    public function clearExpands() {
        $this->_expands = null;
        return $this;
    }

    /**
    * Create a new instance of this class and return it
    *
    * @param string $template Template to load
    *
    * @return view
    */
    public function subView($template = null, $config = null) {
        $class = __CLASS__;
        return new $class($template, $config ? $config : $this->_config);
    }
}

/**
 * Class exceptions
 */
class FileExtensionViewException extends Exception {}
class NoPathViewException extends Exception {}
class ViewNotFoundViewException extends Exception {}
class FilterNotCallableViewException extends Exception {}