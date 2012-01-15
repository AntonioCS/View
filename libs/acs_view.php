<?php

/**
 * Acs_view
 * Class to load the views (templates)
 * 
 * version 0.2 
 *  loadview - now accepts an array as parameter, it will halt at the first view available
 *
 */
class acs_view {

    /**
     * This variable will be filled with the elements to be looped.
     * It will be an array of arrays following this format
     * array(
     * 		array('element_name',array data')
     * 		array('element_name2',array data2')
     * 		...
     * )
     * 
     * @var mixed
     */
    private $_loop_elements = array();

    /**
     * Subfolder in the cache directory reserved for the loop code
     * 
     * @var string
     */
    private $_loop_cache_subfolder = 'loop_views/';

    /**
     * Value of the extension of a view file (followed by a the normal extension
     * 
     * @var string
     */
    private $_view_extension = 'tpl';

    /**
     * Scripts to be added to the view when it's rendered
     * 
     * @var array
     */
    private $_scripts = array();

    /**
     * Css to be added to the view when it's rendered 
     * 
     * @var array
     */
    private $_css = array();

    /**
     * This will be set to the values of the view
     *
     * @var array
     */
    private $_data = array();

    /**
     * This will contain the path to the view to be loaded
     *
     * @var string
     */
    private $_view_path = null;

    /**
     * This will be set to the name of the view when the loadview is called
     * 
     * @var string
     */
    private $_view_name = null;

    /**
     * This will be used to check if the variabel has been rendered or not
     * 
     * @var bool
     */
    private $_seen = false;

    /**
     * Type of data, html, json etc
     * 
     * @var string
     */
    private $_onlydata_type = null;

    /**
     * When a method creates an instance of the view in a acs_dom class it shall be placed here
     * 
     * @var acs_dom
     */
    private $_dom = null;

    /**
     * Save the last render output      
     * 
     * @var string
     */
    private $_last_render = null;

    /**
     * To only show the data (variables set in the controller/model) and not the full view
     * 
     * @var mixed
     */
    public $onlydata = false;

    /**
     * Constructor!
     * This will call the loadview function if a file is given and it also defaults the caching to false
     *
     * @param string $view
     * @param bool $cache
     */
    public function __construct($view = null, $allowviewfail = true) {
        parent::__construct();

        //Load all the path variables that the view might need
        $this->create_paths();
        if ($view)
            $this->loadview($view, $allowviewfail);
    }

    /**
     * Simple method to create variables with the paths to the main dirs and also reset the _data array
     * 
     */
    private function create_paths() {
        $this->_data = array(
            'uri' => $this->configData->uri,
            'web' => $this->configData->web_dir_url,
            'css_dir' => $this->configData->css_dir_url,
            'js_dir' => $this->configData->js_dir_url,
            'images_dir' => $this->configData->images_dir_url,
            'flash_dir' => $this->configData->flash_dir_url
        );
    }

    /**
     * Return the contents of _view_path (where the path to the view will be)
     * 
     */
    public function getPath() {
        return $this->_view_path;
    }

    /**
     * 
     * This will set the file to be loaded as a view when the render method is called
     * 
     * @param string $view_file path to the view file, relative to the applications/View folder
     * @param bool $ifcachefounduse This is a boolean. And defines if this view is to be cached and if it will load that cache
     * 
     * */
    public function loadview($view_file, $allowfail = true) {

        $this->_view_path = null; //clear the view 
        $this->create_paths(); //This will set the _data to only the paths to the web dirs

        $ext = '.' . $this->_view_extension . '.' . $this->configData->common_extension;
        $ext_ = '.' . $this->configData->common_extension; //I will let a view with only php as extension and not only the .tpl.php extension

        if (!is_array($view_file))
            $view_file = array($view_file);

        $pathtoview = null;

        foreach ($view_file as $view) {

            $pathtoview = $this->configData->view_dir . $view . $ext; //Normal view folder
            if (file_exists($pathtoview))
                break;

            $pathtoview = $this->configData->view_dir . $view . $ext_;
            if (file_exists($pathtoview))
                break;

            $pathtoview = $this->configData->acs_view_dir . $view . $ext; //Framework's view folder
            if (file_exists($pathtoview))
                break;

            $pathtoview = $this->configData->acs_view_dir . $view . $ext_;
            if (file_exists($pathtoview))
                break;

            $pathtoview = null;
        }


        if ($pathtoview)
            return $this->setViewPath($pathtoview);

        if (!$allowfail)
            throw new acs_exception(VIEW_ERROR_NOFILE . ' - ' . implode("\n", $view_file));

        return null;
    }

    /**
     * Set the path to the view to be loaded
     * 
     * @param string $pathtoview Path to the view
     */
    private function setViewPath($pathtoview) {
        $this->_view_path = $pathtoview;
        $this->_view_name = basename($pathtoview);

        $this->_seen = false; //new view. Reset the seen 		
        return $this;
    }

    /**
     * In the situations where I need to specify the full path of the view (including the name and extension) of the view I want to load
     * 
     * @param string $path
     */
    public function loadviewSpecificFolder($fullpathtoview) {
        if (!file_exists($fullpathtoview))
            throw new acs_exception(VIEW_ERROR_NOFILE . ' - ' . $view_file);

        return $this->setViewPath($fullpathtoview);
    }

    /**
     * Method to determine if a view has been loaded or not
     * The $view_path variable will only contain a value if the path to the view is valid
     *
     * @return bool
     */
    public function hasview() {
        return (bool) ($this->_view_path);
    }

    /**
     * Overloading to the set method
     * This will set all to variables to the $data array
     *
     * @param string $itemname
     * @param mixed $value
     */
    public function __set($itemname, $value) {
        $this->_data[$itemname] = $value;
    }

    public function __get($varname) {
        if (isset($this->_data[$varname]))
            return $this->_data[$varname];

        return parent::__get($varname);
    }

    /**
     * With this I will be able to call a variable with a default value
     * 
     * @param string $funcname
     * @param array $params
     * @return mixed
     */
    public function __call($funcname, $params) {
        if (!isset($this->_data[$funcname]) || $this->_data[$funcname] == '') {
            if (isset($params[0]))
                return $params[0];

            return null;
        }
        return $this->_data[$funcname];
    }

    /**
     * Renders the view
     * if $return is set to true the code will be returned
     *
     * @param bool $return
     * @param bool $parseWestern - If this is true the data will be parsed by the helper_string::htmlentities_specific method
     * @return string (if the $return var is set to true it will return the view code)
     */
    public function render($return = false, $parseWestern = null) {
        if (!$this->_view_path) {
            $db = debug_backtrace();
            $dump = array();
            foreach ($db as $item)
                $dump[] = 'file: ' . $item['file'] . "\n" . 'line:' . $item['line'];

            throw new acs_exception(VIEW_ERROR_NOVIEWSET . "\n" . "View call\n" . implode("\n", $dump));
        }

        if ($parseWestern === null)
            $parseWestern = $this->configData->parseWesternCharacters;

        ob_start();

        require($this->_view_path);

        $buffer = ob_get_clean();

        if ($parseWestern)
            $buffer = helper_strings::htmlentities_specific($buffer);

        $this->process_js_css($buffer);

        $this->process_loop($buffer);


        $this->_seen = true; //The view has been seen (or it's going to be :P)

        $this->_last_render = $buffer;

        if ($return)
            return $buffer;

        echo $buffer;
    }

    /**
     * Small wrapper for the option of returning the rendered code instead of echoing it
     * 
     * @param mixed $parseW
     * @return mixed
     */
    public function returnRender($parseW = null) {
        return $this->render(true, $parseW);
    }

    /**
     * Getter for the _last_render property
     * 
     */
    public function lastRender() {
        return $this->_last_render;
    }

    public function __toString() {
        return $this->render(true);
    }

    /**
     * This method will be used to access the value of the private variable seen
     *
     * @return unknown
     */
    public function seen() {
        return $this->_seen;
    }

    /**
     * Make the router class not show this view by setting the view variable to true, 
     * even though the view has not been seen
     *
     */
    public function noshow() {
        $this->_seen = true;
    }

    /**
     * Merge the given array with the _data array. The array, must be an associative array
     * 
     * @param array $data
     * @return acs_view $this class instance
     */
    public function addData(array $data) {
        $this->_data = array_merge($this->_data, $data);
        return $this;
    }

    //TODO: These all must be removed. They are in the acs_dom class

    /**
     * Method to add a script tag to the current view
     * 
     * @param mixed $script - Path to the script. If it doesn't start with http or https the path to the js folder will be added
     * @param mixed $position - To either put in the head or at the bottom (if the head tag is not found it will place at the most top position, same for the body)
     *                        - Values
      - 0 = head
     *                               - 1 = body top
     *                               - 2 = body bottom
     */
    public function addJS($script, $position = 2, $isCode = false) {
        if (!$iscode && !helper_url::ishttp($script)) //in case this is a local script (it must be in the js lib)
            $script = $this->configData->js_dir_url . $script;


        $this->_scripts[] = array(
            'js' => $script,
            'position' => $position,
            'isCode' => $isCode
        );
    }

    /**
     * Add code to the view
     * 
     * @param mixed $jscode Jscode
     * @param mixed $position Same as in the addJS method
     */
    public function addJSCode($jscode, $position = 2) {
        $this->addJS($jscode, $position, true);
    }

    /**
     * Add css to the current view
     * 
     * @param string $css path to css file or css code
     * @param bool $process If the css is to be processed. When this is true keywords in the css code/file will be replaced. Also if this isn't css code it will be inserted as css code
     * @param bool $isCode To determine if $css is css code or a file path to a css file
     */
    public function addCss($css, $process = false, $isCode = false, $media = 'all') {
        if (!$iscode && !helper_url::ishttp($css)) //in case this is a local script (it must be in the css lib)
            $css = $this->configData->css_dir_url . $css;

        $this->_css[] = array(
            'css' => $css,
            'process' => $process,
            'iscsscode' => $isCode,
            'media' => $media
        );
    }

    /**
     * Wrapper to add css code via the addCss
     * 
     * @param string $csscode
     * @param bool $process
     */
    public function addCssCode($csscode, $process = false) {
        $this->addCss($csscode, $process, true);
    }

    /**
     * This method will add the scripts (if any) to the view
     * 
     * @param mixed $data - The view passed by reference
     */
    //TODO: Remove this as the method process_js_css_ (which is below this one) is much better
    private function process_js_css(&$data) {
        if (!empty($this->_scripts) || !empty($this->_css)) { //Only do something if there is something to do :D		
            require_once('lib/simple_html_dom.php');
            $dom = new simple_html_dom($data);

            $nob = $noh = false;
            if (($h = $dom->find('head', 0)) === null) {
                $h = $dom->firstChild(); //no head
                $noh = true;
            }
            if (($b = $dom->find('body', 0)) === null) {
                $b = $dom->firstChild(); //no body
                $nob = true;
            }

            $batch = array_merge($this->_scripts, $this->_css);
            foreach ($batch as $code) {
                if (is_array($code)) { //script
                    list($path, $pos) = $code;
                    if (isset($code['code'])) //If this is actually javascript (inline)        
                        $tag = '<script type="text/javascript">' . PHP_EOL . $path . PHP_EOL . '</script>' . PHP_EOL;
                    else
                        $tag = '<script type="text/javascript" src="' . $path . '"></script>' . PHP_EOL;
                }
                else {
                    if ($noh)
                        continue; //There is no head, there is no place to put the css					
                    $pos = 0; //css is always in the head (or so says the standard)
                    $tag = '<link rel="stylesheet" type="text/css" href="' . $code . '" />' . PHP_EOL;
                }

                if ($pos === 0) {//top
                    if ($noh)
                        $h->outertext = $tag . $h->outertext;
                    else
                        $h->innertext = $h->innertext . $tag;
                }
                else {
                    if ($nob)
                        $b->outertext = $b->outertext . $tag;
                    else
                        $b->innertext = $b->innertext . $tag;
                }
            }
            $data = $dom->root->innertext(); //This will return just the html
        }
    }

    /**
     * This method will add the scripts (if any) to the view
     * 
     * @param string $data Render data
     */
    private function process_js_css_(&$data) {
        if (!empty($this->_scripts) || !empty($this->_css)) { //Only do something if there is something to do :D         
            $this->clearDom()->domify($data);

            if ($this->_dom->head && !empty($this->_css)) {
                foreach ($this->_css as $css) {
                    list($code, $process, $iscsscode, $media) = $css;

                    if ($process) {
                        if (!$iscsscode) {
                            $code = file_get_contents($code);
                            $iscsscode = true;
                        }
                        $this->process_keywords($code);
                    }

                    if ($iscsscode)
                        $this->_dom->addCssCode($code, $media);
                    else
                        $this->_dom->addCss($code, $media);
                }
            }

            if (!empty($this->_scripts)) {
                foreach ($this->_scripts as $script) {
                    list($js, $position, $iscode) = $script;

                    if ($iscode)
                        $this->_dom->addJsCode($js, $position);
                    else
                        $this->_dom->addJs($js, $position);
                }
            }
            //$data = $this->_dom->dom->saveHTML();
            $data = $this->_dom->saveHTMLExact();
        }
    }

    private function process_keywords(&$data) {
        $data = str_replace(
                array(
            '{WEB}',
            '{CSS_DIR}',
            '{JS_DIR}',
            '{IMAGES_DIR}',
            '{FLASH_DIR}'
                ), array(
            $this->configData->web_dir_url,
            $this->configData->css_dir_url,
            $this->configData->js_dir_url,
            $this->configData->images_dir_url,
            $this->configData->flash_dir_url
                ), $data
        );
    }

    /**
     * Process the loop data set by the loop method
     * 
     * @param string $data Render data
     */
    private function process_loop(&$data) {
        if (!empty($this->_loop_elements)) {
            $this->clearDom()->domify($data);

            $c = new acs_cache($this->_loop_cache_subfolder);
            $v = new acs_view();

            foreach ($this->_loop_elements as $loop) {
                $view_render = '';

                $ckey = $loop['ckey'];
                $element_id = $loop['element_id'];
                $element_data = $loop['element_data'];

                $v->loadviewSpecificFolder($c->CacheFilePath($ckey));
                foreach ($element_data as $current_data) {
                    $v->addData($current_data);
                    $view_render .= $v->returnRender();
                }

                $this->_dom->setInnerHTML($element_id, $view_render);
            }

            $data = $this->_dom->saveHTMLExact(); //$this->_dom->dom->saveHTML();		
        }
    }

    public function loop($element_id, array $data) {
        $this->clearDom()->domify();

        $element = $this->_dom->getElementById($element_id);

        if ($element) {
            $code = $this->_dom->getInnerHTML($element);

            if (!is_dir($this->configData->cache_dir . $this->_loop_cache_subfolder))
                mkdir($this->configData->cache_dir . $this->_loop_cache_subfolder);

            $c = new acs_cache($this->_loop_cache_subfolder);
            $ckey = $this->view_name . $element_id;
            //$c->save($ckey,urldecode(html_entity_decode($code)),array('remove_crlf' => true)); //php tags inside html attributes are getting messed up :(
            $c->save($ckey, urldecode(html_entity_decode($code))); //php tags inside html attributes are getting messed up :(

            $this->_loop_elements[] = array(
                'ckey' => $ckey,
                'element_id' => $element_id,
                'element_data' => $data
            );
        }
        else
            throw new acs_exception('Invalid DOM Element');
    }

    /**
     * Return an instance of acs_dom with the current view or data (if given) and set it to the _dom property
     * 
     * @return acs_dom
     */
    //private function domify(&$data) {
    private function domify($data = null) {
        if (!$data) {
            if ($this->_view_path)
                $data = file_get_contents($this->_view_path);
            else
                throw new acs_exception(VIEW_ERROR_NOVIEWSET);   //erro
        }
        if (!$this->_dom) {
            if ($this->_view_path)
                $this->_dom = new acs_dom($data);
            else
                throw new acs_exception(VIEW_ERROR_NOVIEWSET);
        }

        return $this->_dom;
    }

    /**
     * Clear the _dom propery 
     * 
     * @return acs_view $this class instance
     */
    private function clearDom() {
        $this->_dom = null;
        return $this;
    }

    /**
     * This method will include the css file and replace the following tags (tags wich are in the file)
     *       {WEB} 'web' => $this->configData->web_dir_url,
      {CSS_DIR} 'css_dir' => $this->configData->css_dir_url,
      {JS_DIR} 'js_dir' => $this->configData->js_dir_url,
      {IMAGES_DIR} 'images_dir' => $this->configData->images_dir_url,
      {FLASH_DIR} 'flash_dir' => $this->configData->flash_dir_url
     * @param string $file
     * 
     * Example: <?php $this->getCSS('boxes.css') ?>
     * 
     * @deprecated Use the process_js_css method (which is called when you use addJs/addJsCode/addCss/addCssCode)
     */
    public function getCSS($file) {
        echo helper_css::compress(
                str_replace(
                        array(
                    '{WEB}',
                    '{CSS_DIR}',
                    '{JS_DIR}',
                    '{IMAGES_DIR}',
                    '{FLASH_DIR}'
                        ), array(
                    $this->configData->web_dir_url,
                    $this->configData->css_dir_url,
                    $this->configData->js_dir_url,
                    $this->configData->images_dir_url,
                    $this->configData->flash_dir_url
                        ), file_get_contents($this->configData->css_dir . $file)
                )
        );
    }

    /**
     * Say that I only want to show the data and the the view itself
     * 
     * @param string $type
     */
    public function onlydata($type = 'html') {
        $this->onlydata = true;
        $this->_onlydata_type = $type;
    }

    /**
     * Echo the data as I want it to be echoed
     * 
     */
    public function showonlydata() {
        switch ($this->_onlydata_type) {
            case 'html':
                header('Content-type: text/html');
                foreach ($this->_data as $data)
                    echo $data;
                break;

            case 'json':
                $json = json_encode($this->_data);
                header('Content-type: application/x-json');
                //There are limitations to the headers. So it is best just to send it in the echo
                //header('X-JSON: ('. $json .')'); 
                echo $json;
                break;
        }
    }

    /**
     * Create a new view in the _data array and return it
     * 
     * @param acs_view $view
     */
    public function subView($vname, $vpath) {
        $v = new acs_view($vpath);
        $this->_data[$vname] = $v;

        return $this->_data[$vname];
    }

}
