<?php

namespace View;

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
     * Default configurations
     *
     * @var array
     */
    public static $CONFIG = array(
        /**
        * Global extension for the view/templates
        *
        * @var mixed
        */
        'ext' => 'tpl.php',

        /**
        * Global path for the views/templates
        *
        * @var string
        */
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
     * Generate a view and return it
     * 
     * @param string $view
     * @param array $config
     * @param array $data
     * 
     * @return \self
     */
    public static function generate($view = null, $config = null, $data = array()) {
        $v = new self($view,$config);
        
        if (!empty($data)) {
            $v->set($data);
        }
        
        return $v;        
    }

    /**
    * Construct
    *
    * @param string|null $view  - Relative/Absolute path to the view/template
    * @param array|null $config - Config set up for the instance
    *
    * @return \View\view
    */
    public function __construct($view = null, $config = null) {
        $ext = null;
        $path = null;

        if (!empty($config)) {
            $ext = isset($config['ext']) ? $config['ext'] : self::$EXT;
            $path = isset($config['path']) ? $config['path'] : self::$PATH;
        }
        else {
            $ext = self::$CONFIG['ext'];
            $path = self::$CONFIG['path']; 
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
    * @return \View\view
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
    * @return \View\view
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
    * @return \View\view
    */
    public function setPath($path) {
        $this->_config['path'] = $path;
        return $this;
    }
    
    /**
     * Add a view path to the path list. setPath will only set one path as the main path. This will allow you to add the paths
     * 
     * @param string $path
     * @return \View\view 
     */
    public function addPath($path) {
        if (!is_array($this->_config['path']))
            $this->_config['path'] = array($this->_config['path']);
       
        $this->_config['path'][] = $path;
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
    * @return \View\view
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
    * @return \View\view
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
     * Return rendered view
     * 
     * @return string
     */
    public function __toString() {
        return $this->render();
    }

    /**
    * Set an item on the _data array
    *
    * @param mixed $item - Can be an array to set in one call multiple variables in the view
    * @param mixed $value
    *
    * @return \View\view
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
     * @param string $tpl Set a template to render
     * @param array $data
     *
     * @return string rendered template
     */
    public function render($tpl = null, $data = array()) {
        if ($tpl) {
            $this->load($tpl);            
        }
        
        if (!empty($data)) {
            $this->set($data);
        }        
        
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
    * @param array $data
    * @param array $config
    *
    * @return \View\view
    */
    public function subView($template = null, $data = array(), $config = null) {
        $class = __CLASS__;
        $v = new $class($template, $config ? $config : $this->_config);
        if (!empty($data)) {
            $v->set($data);
        }
        
        return $v;
    }
}

/**
 * Class exceptions
 */
class FileExtensionViewException extends \Exception {}
class NoPathViewException extends \Exception {}
class ViewNotFoundViewException extends \Exception {}
class FilterNotCallableViewException extends \Exception {}