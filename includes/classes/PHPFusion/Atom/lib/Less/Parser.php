<?php

require_once(__DIR__.'/Cache.php');

/**
 * Class for parsing and compiling less files into css
 *
 * @package Less
 * @subpackage parser
 *
 */
class Less_Parser {


    /**
     * Default parser options
     */
    public static $default_options = [
        'compress'     => FALSE,            // option - whether to compress
        'strictUnits'  => FALSE,            // whether units need to evaluate correctly
        'strictMath'   => FALSE,            // whether math has to be within parenthesis
        'relativeUrls' => TRUE,            // option - whether to adjust URL's to be relative
        'urlArgs'      => [],            // whether to add args into url tokens
        'numPrecision' => 8,

        'import_dirs'        => [],
        'import_callback'    => NULL,
        'cache_dir'          => NULL,
        'cache_method'       => 'php',            // false, 'serialize', 'php', 'var_export', 'callback';
        'cache_callback_get' => NULL,
        'cache_callback_set' => NULL,

        'sourceMap'         => FALSE,            // whether to output a source map
        'sourceMapBasepath' => NULL,
        'sourceMapWriteTo'  => NULL,
        'sourceMapURL'      => NULL,

        'plugins' => [],

    ];

    public static $options = [];
    public static $has_extends = FALSE;                    // Less input string
    public static $next_id = 0;                // input string length
    /**
     * Filename to contents of all parsed the files
     *
     * @var array
     */
    public static $contentsMap = [];                    // current index in `input`
    private static $imports = [];    // holds state for backtracking
    private $input;
    private $input_len;
    private $pos;
    private $saveStack = [];
    private $furthest;
    /**
     * @var Less_Environment
     */
    private $env;
    private $rules = [];

    /**
     * @param Less_Environment|array|null $env
     */
    public function __construct($env = NULL) {

        // Top parser on an import tree must be sure there is one "env"
        // which will then be passed around by reference.
        if ($env instanceof \Less_Environment) {
            $this->env = $env;
        } else {
            $this->SetOptions(Less_Parser::$default_options);
            $this->Reset($env);
        }

    }

    /**
     * Set one or more compiler options
     *  options: import_dirs, cache_dir, cache_method
     *
     */
    public function SetOptions($options) {
        foreach ($options as $option => $value) {
            $this->SetOption($option, $value);
        }
    }

    /**
     * Set one compiler option
     *
     */
    public function SetOption($option, $value) {

        switch ($option) {

            case 'import_dirs':
                $this->SetImportDirs($value);

                return;

            case 'cache_dir':
                if (is_string($value)) {
                    \Less_Cache::SetCacheDir($value);
                    \Less_Cache::CheckCacheDir();
                }

                return;
        }

        Less_Parser::$options[$option] = $value;
    }

    /**
     * Set a list of directories or callbacks the parser should use for determining import paths
     *
     * @param array $dirs
     */
    public function SetImportDirs($dirs) {
        Less_Parser::$options['import_dirs'] = [];

        foreach ($dirs as $path => $uri_root) {

            $path = self::WinPath($path);
            if (!empty($path)) {
                $path = rtrim($path, '/').'/';
            }

            if (!is_callable($uri_root)) {
                $uri_root = self::WinPath($uri_root);
                if (!empty($uri_root)) {
                    $uri_root = rtrim($uri_root, '/').'/';
                }
            }

            Less_Parser::$options['import_dirs'][$path] = $uri_root;
        }
    }

    public static function WinPath($path) {
        return str_replace('\\', '/', $path);
    }

    /**
     * Reset the parser state completely
     *
     */
    public function Reset($options = NULL) {
        $this->rules = [];
        self::$imports = [];
        self::$has_extends = FALSE;
        self::$imports = [];
        self::$contentsMap = [];

        $this->env = new \Less_Environment($options);
        $this->env->Init();

        //set new options
        if (is_array($options)) {
            $this->SetOptions(Less_Parser::$default_options);
            $this->SetOptions($options);
        }
    }

    static function AllParsedFiles() {
        return self::$imports;
    }

    /**
     * @param string $file
     */
    static function FileParsed($file) {
        return in_array($file, self::$imports);
    }

    /**
     * Some versions of php have trouble with method_exists($a,$b) if $a is not an object
     *
     * @param string $b
     */
    public static function is_method($a, $b) {
        return is_object($a) && method_exists($a, $b);
    }

    /**
     * Round numbers similarly to javascript
     * eg: 1.499999 to 1 instead of 2
     *
     */
    public static function round($i, $precision = 0) {

        $precision = pow(10, $precision);
        $i = $i * $precision;

        $ceil = ceil($i);
        $floor = floor($i);
        if (($ceil - $i) <= ($i - $floor)) {
            return $ceil / $precision;
        } else {
            return $floor / $precision;
        }
    }

    /**
     * Registers a new custom function
     *
     * @param  string   $name function name
     * @param  callable $callback callback
     */
    public function registerFunction($name, $callback) {
        $this->env->functions[$name] = $callback;
    }

    /**
     * Removed an already registered function
     *
     * @param  string $name function name
     */
    public function unregisterFunction($name) {
        if (isset($this->env->functions[$name])) {
            unset($this->env->functions[$name]);
        }
    }

    /**
     * Get the current css buffer
     *
     * @return string
     */
    public function getCss() {

        $precision = ini_get('precision');
        @ini_set('precision', 16);
        $locale = setlocale(LC_NUMERIC, 0);
        setlocale(LC_NUMERIC, "C");

        $root = new \Less_Tree_Ruleset([], $this->rules);
        $root->root = TRUE;
        $root->firstRoot = TRUE;


        $this->PreVisitors($root);

        self::$has_extends = FALSE;
        $evaldRoot = $root->compile($this->env);


        $this->PostVisitors($evaldRoot);

        if (Less_Parser::$options['sourceMap']) {
            $generator = new \Less_SourceMap_Generator($evaldRoot, Less_Parser::$contentsMap, Less_Parser::$options);
            // will also save file
            // FIXME: should happen somewhere else?
            $css = $generator->generateCSS();
        } else {
            $css = $evaldRoot->toCSS();
        }

        if (Less_Parser::$options['compress']) {
            $css = preg_replace('/(^(\s)+)|((\s)+$)/', '', $css);
        }

        //reset php settings
        @ini_set('precision', $precision);
        setlocale(LC_NUMERIC, $locale);

        return $css;
    }

    /**
     * Run pre-compile visitors
     *
     */
    private function PreVisitors($root) {

        if (Less_Parser::$options['plugins']) {
            foreach (Less_Parser::$options['plugins'] as $plugin) {
                if (!empty($plugin->isPreEvalVisitor)) {
                    $plugin->run($root);
                }
            }
        }
    }

    /**
     * Run post-compile visitors
     *
     */
    private function PostVisitors($evaldRoot) {

        $visitors = [];
        $visitors[] = new \Less_Visitor_joinSelector();
        if (self::$has_extends) {
            $visitors[] = new \Less_Visitor_processExtends();
        }
        $visitors[] = new \Less_Visitor_toCSS();


        if (Less_Parser::$options['plugins']) {
            foreach (Less_Parser::$options['plugins'] as $plugin) {
                if (property_exists($plugin, 'isPreEvalVisitor') && $plugin->isPreEvalVisitor) {
                    continue;
                }

                if (property_exists($plugin, 'isPreVisitor') && $plugin->isPreVisitor) {
                    array_unshift($visitors, $plugin);
                } else {
                    $visitors[] = $plugin;
                }
            }
        }


        for ($i = 0; $i < count($visitors); $i++) {
            $visitors[$i]->run($evaldRoot);
        }

    }

    /**
     * Parse a Less string into css
     *
     * @param string $str The string to convert
     * @param string $uri_root The url of the file
     *
     * @return Less_Tree_Ruleset|Less_Parser
     */
    public function parse($str, $file_uri = NULL) {

        if (!$file_uri) {
            $uri_root = '';
            $filename = 'anonymous-file-'.Less_Parser::$next_id++.'.less';
        } else {
            $file_uri = self::WinPath($file_uri);
            $filename = basename($file_uri);
            $uri_root = dirname($file_uri);
        }

        $previousFileInfo = $this->env->currentFileInfo;
        $uri_root = self::WinPath($uri_root);
        $this->SetFileInfo($filename, $uri_root);

        $this->input = $str;
        $this->_parse();

        if ($previousFileInfo) {
            $this->env->currentFileInfo = $previousFileInfo;
        }

        return $this;
    }

    /**
     * @param string $filename
     */
    public function SetFileInfo($filename, $uri_root = '') {

        $filename = \Less_Environment::normalizePath($filename);
        $dirname = preg_replace('/[^\/\\\\]*$/', '', $filename);

        if (!empty($uri_root)) {
            $uri_root = rtrim($uri_root, '/').'/';
        }

        $currentFileInfo = [];

        //entry info
        if (isset($this->env->currentFileInfo)) {
            $currentFileInfo['entryPath'] = $this->env->currentFileInfo['entryPath'];
            $currentFileInfo['entryUri'] = $this->env->currentFileInfo['entryUri'];
            $currentFileInfo['rootpath'] = $this->env->currentFileInfo['rootpath'];

        } else {
            $currentFileInfo['entryPath'] = $dirname;
            $currentFileInfo['entryUri'] = $uri_root;
            $currentFileInfo['rootpath'] = $dirname;
        }

        $currentFileInfo['currentDirectory'] = $dirname;
        $currentFileInfo['currentUri'] = $uri_root.basename($filename);
        $currentFileInfo['filename'] = $filename;
        $currentFileInfo['uri_root'] = $uri_root;


        //inherit reference
        if (isset($this->env->currentFileInfo['reference']) && $this->env->currentFileInfo['reference']) {
            $currentFileInfo['reference'] = TRUE;
        }

        $this->env->currentFileInfo = $currentFileInfo;
    }

    /**
     * @param string $file_path
     */
    private function _parse($file_path = NULL) {
        if (ini_get("mbstring.func_overload")) {
            $mb_internal_encoding = ini_get("mbstring.internal_encoding");
            @ini_set("mbstring.internal_encoding", "ascii");
        }

        $this->rules = array_merge($this->rules, $this->GetRules($file_path));

        //reset php settings
        if (isset($mb_internal_encoding)) {
            @ini_set("mbstring.internal_encoding", $mb_internal_encoding);
        }
    }

    /**
     * Return the results of parsePrimary for $file_path
     * Use cache and save cached results if possible
     *
     * @param string|null $file_path
     */
    private function GetRules($file_path) {

        $this->SetInput($file_path);

        $cache_file = $this->CacheFile($file_path);
        if ($cache_file) {
            if (Less_Parser::$options['cache_method'] == 'callback') {
                if (is_callable(Less_Parser::$options['cache_callback_get'])) {
                    $cache = call_user_func_array(
                        Less_Parser::$options['cache_callback_get'],
                        [$this, $file_path, $cache_file]
                    );

                    if ($cache) {
                        $this->UnsetInput();

                        return $cache;
                    }
                }

            } else if (file_exists($cache_file)) {
                switch (Less_Parser::$options['cache_method']) {

                    // Using serialize
                    // Faster but uses more memory
                    case 'serialize':
                        $cache = unserialize(file_get_contents($cache_file));
                        if ($cache) {
                            touch($cache_file);
                            $this->UnsetInput();

                            return $cache;
                        }
                        break;


                    // Using generated php code
                    case 'var_export':
                    case 'php':
                        $this->UnsetInput();

                        return include($cache_file);
                }
            }
        }

        $rules = $this->parsePrimary();

        if ($this->pos < $this->input_len) {
            throw new Less_Exception_Chunk($this->input, NULL, $this->furthest, $this->env->currentFileInfo);
        }

        $this->UnsetInput();


        //save the cache
        if ($cache_file) {
            if (Less_Parser::$options['cache_method'] == 'callback') {
                if (is_callable(Less_Parser::$options['cache_callback_set'])) {
                    call_user_func_array(
                        Less_Parser::$options['cache_callback_set'],
                        [$this, $file_path, $cache_file, $rules]
                    );
                }

            } else {
                //msg('write cache file');
                switch (Less_Parser::$options['cache_method']) {
                    case 'serialize':
                        file_put_contents($cache_file, serialize($rules));
                        break;
                    case 'php':
                        file_put_contents($cache_file, '<?php return '.self::ArgString($rules).'; ?>');
                        break;
                    case 'var_export':
                        //Requires __set_state()
                        file_put_contents($cache_file, '<?php return '.var_export($rules, TRUE).'; ?>');
                        break;
                }

                \Less_Cache::CleanCache();
            }
        }

        return $rules;
    }

    /**
     * Set up the input buffer
     *
     */
    public function SetInput($file_path) {

        if ($file_path) {
            $this->input = file_get_contents($file_path);
        }

        $this->pos = $this->furthest = 0;

        // Remove potential UTF Byte Order Mark
        $this->input = preg_replace('/\\G\xEF\xBB\xBF/', '', $this->input);
        $this->input_len = strlen($this->input);


        if (Less_Parser::$options['sourceMap'] && $this->env->currentFileInfo) {
            $uri = $this->env->currentFileInfo['currentUri'];
            Less_Parser::$contentsMap[$uri] = $this->input;
        }

    }

    public function CacheFile($file_path) {

        if ($file_path && $this->CacheEnabled()) {

            $env = get_object_vars($this->env);
            unset($env['frames']);

            $parts = [];
            $parts[] = $file_path;
            $parts[] = filesize($file_path);
            $parts[] = filemtime($file_path);
            $parts[] = $env;
            $parts[] = \Less_Version::cache_version;
            $parts[] = Less_Parser::$options['cache_method'];

            return \Less_Cache::$cache_dir.'lessphp_'.base_convert(sha1(json_encode($parts)), 16, 36).'.lesscache';
        }
    }

    public function CacheEnabled() {
        return (Less_Parser::$options['cache_method'] && (\Less_Cache::$cache_dir || (Less_Parser::$options['cache_method'] == 'callback')));
    }

    /**
     * Free up some memory
     *
     */
    public function UnsetInput() {
        unset($this->input, $this->pos, $this->input_len, $this->furthest);
        $this->saveStack = [];
    }

    private function parsePrimary() {
        $root = [];

        while (TRUE) {

            if ($this->pos >= $this->input_len) {
                break;
            }

            $node = $this->parseExtend(TRUE);
            if ($node) {
                $root = array_merge($root, $node);
                continue;
            }

            //$node = $this->MatchFuncs( array( 'parseMixinDefinition', 'parseRule', 'parseRuleset', 'parseMixinCall', 'parseComment', 'parseDirective'));
            $node = $this->MatchFuncs([
                'parseMixinDefinition', 'parseNameValue', 'parseRule', 'parseRuleset', 'parseMixinCall', 'parseComment',
                'parseRulesetCall', 'parseDirective'
            ]);

            if ($node) {
                $root[] = $node;
            } else if (!$this->MatchReg('/\\G[\s\n;]+/')) {
                break;
            }

            if ($this->PeekChar('}')) {
                break;
            }
        }

        return $root;
    }

    function parseExtend($isRule = FALSE) {

        $index = $this->pos;
        $extendList = [];


        if (!$this->MatchReg($isRule ? '/\\G&:extend\(/' : '/\\G:extend\(/')) {
            return;
        }

        do {
            $option = NULL;
            $elements = [];
            while (TRUE) {
                $option = $this->MatchReg('/\\G(all)(?=\s*(\)|,))/');
                if ($option) {
                    break;
                }
                $e = $this->parseElement();
                if (!$e) {
                    break;
                }
                $elements[] = $e;
            }

            if ($option) {
                $option = $option[1];
            }

            $extendList[] = $this->NewObj3('Less_Tree_Extend', [$this->NewObj1('Less_Tree_Selector', $elements), $option, $index]);

        } while ($this->MatchChar(","));

        $this->expect('/\\G\)/');

        if ($isRule) {
            $this->expect('/\\G;/');
        }

        return $extendList;
    }

    private function MatchReg($tok) {

        if (preg_match($tok, $this->input, $match, 0, $this->pos)) {
            $this->skipWhitespace(strlen($match[0]));

            return $match;
        }
    }

    /**
     * @param integer $length
     */
    public function skipWhitespace($length) {

        $this->pos += $length;

        for (; $this->pos < $this->input_len; $this->pos++) {
            $c = $this->input[$this->pos];

            if (($c !== "\n") && ($c !== "\r") && ($c !== "\t") && ($c !== ' ')) {
                break;
            }
        }
    }

    private function parseElement() {
        $c = $this->parseCombinator();
        $index = $this->pos;

        $e = $this->match([
            '/\\G(?:\d+\.\d+|\d+)%/', '/\\G(?:[.#]?|:*)(?:[\w-]|[^\x00-\x9f]|\\\\(?:[A-Fa-f0-9]{1,6} ?|[^A-Fa-f0-9]))+/',
            '#*', '#&', 'parseAttribute', '/\\G\([^()@]+\)/', '/\\G[\.#](?=@)/', 'parseEntitiesVariableCurly'
        ]);

        if (is_null($e)) {
            $this->save();
            if ($this->MatchChar('(')) {
                if (($v = $this->parseSelector()) && $this->MatchChar(')')) {
                    $e = $this->NewObj1('Less_Tree_Paren', $v);
                    $this->forget();
                } else {
                    $this->restore();
                }
            } else {
                $this->forget();
            }
        }

        if (!is_null($e)) {
            return $this->NewObj4('Less_Tree_Element', [$c, $e, $index, $this->env->currentFileInfo]);
        }
    }

    private function parseCombinator() {
        if ($this->pos < $this->input_len) {
            $c = $this->input[$this->pos];
            if ($c === '>' || $c === '+' || $c === '~' || $c === '|' || $c === '^') {

                $this->pos++;
                if ($this->input[$this->pos] === '^') {
                    $c = '^^';
                    $this->pos++;
                }

                $this->skipWhitespace(0);

                return $c;
            }

            if ($this->pos > 0 && $this->isWhitespace(-1)) {
                return ' ';
            }
        }
    }

    // Match a single character in the input,

    private function isWhitespace($offset = 0) {
        return preg_match('/\s/', $this->input[$this->pos + $offset]);
    }

    // Match a regexp from the current start point

    /**
     * Parse from a token, regexp or string, and move forward if match
     *
     * @param array $toks
     *
     * @return array
     */
    private function match($toks) {

        // The match is confirmed, add the match length to `this::pos`,
        // and consume any extra white-space characters (' ' || '\n')
        // which come after that. The reason for this is that LeSS's
        // grammar is mostly white-space insensitive.
        //

        foreach ($toks as $tok) {

            $char = $tok[0];

            if ($char === '/') {
                $match = $this->MatchReg($tok);

                if ($match) {
                    return count($match) === 1 ? $match[0] : $match;
                }

            } else if ($char === '#') {
                $match = $this->MatchChar($tok[1]);

            } else {
                // Non-terminal, match using a function call
                $match = $this->$tok();

            }

            if ($match) {
                return $match;
            }
        }
    }

    private function MatchChar($tok) {
        if (($this->pos < $this->input_len) && ($this->input[$this->pos] === $tok)) {
            $this->skipWhitespace(1);

            return $tok;
        }
    }

    function save() {
        $this->saveStack[] = $this->pos;
    }

    private function parseSelector($isLess = FALSE) {
        $elements = [];
        $extendList = [];
        $condition = NULL;
        $when = FALSE;
        $extend = FALSE;
        $e = NULL;
        $c = NULL;
        $index = $this->pos;

        while (($isLess && ($extend = $this->parseExtend())) || ($isLess && ($when = $this->MatchReg('/\\Gwhen/'))) || ($e = $this->parseElement())) {
            if ($when) {
                $condition = $this->expect('parseConditions', 'expected condition');
            } else if ($condition) {
                //error("CSS guard can only be used at the end of selector");
            } else if ($extend) {
                $extendList = array_merge($extendList, $extend);
            } else {
                //if( count($extendList) ){
                //error("Extend can only be used at the end of selector");
                //}
                if ($this->pos < $this->input_len) {
                    $c = $this->input[$this->pos];
                }
                $elements[] = $e;
                $e = NULL;
            }

            if ($c === '{' || $c === '}' || $c === ';' || $c === ',' || $c === ')') {
                break;
            }
        }

        if ($elements) {
            return $this->NewObj5('Less_Tree_Selector', [$elements, $extendList, $condition, $index, $this->env->currentFileInfo]);
        }
        if ($extendList) {
            $this->Error('Extend must be used to extend a selector, it cannot be used on its own');
        }
    }

    /**
     * @param string      $tok
     * @param string|null $msg
     */
    public function expect($tok, $msg = NULL) {
        $result = $this->match([$tok]);
        if (!$result) {
            $this->Error($msg ? "Expected '".$tok."' got '".$this->input[$this->pos]."'" : $msg);
        } else {
            return $result;
        }
    }

    public function Error($msg) {
        throw new \Less_Exception_Parser($msg, NULL, $this->furthest, $this->env->currentFileInfo);
    }

    //
    // Here in, the parsing rules/functions
    //
    // The basic structure of the syntax tree generated is as follows:
    //
    //   Ruleset ->  Rule -> Value -> Expression -> Entity
    //
    // Here's some LESS code:
    //
    //	.class {
    //	  color: #fff;
    //	  border: 1px solid #000;
    //	  width: @w + 4px;
    //	  > .child {...}
    //	}
    //
    // And here's what the parse tree might look like:
    //
    //	 Ruleset (Selector '.class', [
    //		 Rule ("color",  Value ([Expression [Color #fff]]))
    //		 Rule ("border", Value ([Expression [Dimension 1px][Keyword "solid"][Color #000]]))
    //		 Rule ("width",  Value ([Expression [Operation "+" [Variable "@w"][Dimension 4px]]]))
    //		 Ruleset (Selector [Element '>', '.child'], [...])
    //	 ])
    //
    //  In general, most rules will try to parse a token with the `$()` function, and if the return
    //  value is truly, will return a new node, of the relevant type. Sometimes, we need to check
    //  first, before parsing, that's when we use `peek()`.
    //

    //
    // The `primary` rule is the *entry* and *exit* point of the parser.
    // The rules here can appear at any level of the parse tree.
    //
    // The recursive nature of the grammar is an interplay between the `block`
    // rule, which represents `{ ... }`, the `ruleset` rule, and this `primary` rule,
    // as represented by this simplified grammar:
    //
    //	 primary  →  (ruleset | rule)+
    //	 ruleset  →  selector+ block
    //	 block	→  '{' primary '}'
    //
    // Only at one point is the primary rule not called from the
    // block rule: at the root level.
    //

    public function NewObj5($class, $args) {
        $obj = new $class($args[0], $args[1], $args[2], $args[3], $args[4]);
        if ($this->CacheEnabled()) {
            $this->ObjCache($obj, $class, $args);
        }

        return $obj;
    }



    // We create a Comment node for CSS comments `/* */`,
    // but keep the LeSS comments `//` silent, by just skipping
    // over them.

    public function ObjCache($obj, $class, $args = []) {
        $obj->cache_string = ' new '.$class.'('.self::ArgCache($args).')';
    }

    public function ArgCache($args) {
        return implode(',', array_map(['Less_Parser', 'ArgString'], $args));
    }



    //
    // A string, which supports escaping " and '
    //
    //	 "milky way" 'he\'s the one!'
    //

    public function NewObj1($class, $arg) {
        $obj = new $class($arg);
        if ($this->CacheEnabled()) {
            $obj->cache_string = ' new '.$class.'('.Less_Parser::ArgString($arg).')';
        }

        return $obj;
    }


    //
    // A catch-all word, such as:
    //
    //	 black border-collapse
    //

    /**
     * Convert an argument to a string for use in the parser cache
     *
     * @return string
     */
    public static function ArgString($arg) {

        $type = gettype($arg);

        if ($type === 'object') {
            $string = $arg->cache_string;
            unset($arg->cache_string);

            return $string;

        } else if ($type === 'array') {
            $string = ' Array(';
            foreach ($arg as $k => $a) {
                $string .= var_export($k, TRUE).' => '.self::ArgString($a).',';
            }

            return $string.')';
        }

        return var_export($arg, TRUE);
    }

    // duplicate of Less_Tree_Color::FromKeyword

    private function forget() {
        array_pop($this->saveStack);
    }

    //
    // A function call
    //
    //	 rgb(255, 0, 255)
    //
    // We also try to catch IE's `alpha()`, but let the `alpha` parser
    // deal with the details.
    //
    // The arguments are parsed with the `entities.arguments` parser.
    //

    private function restore() {
        $this->pos = array_pop($this->saveStack);
    }

    public function NewObj4($class, $args) {
        $obj = new $class($args[0], $args[1], $args[2], $args[3]);
        if ($this->CacheEnabled()) {
            $this->ObjCache($obj, $class, $args);
        }

        return $obj;
    }

    public function NewObj3($class, $args) {
        $obj = new $class($args[0], $args[1], $args[2]);
        if ($this->CacheEnabled()) {
            $this->ObjCache($obj, $class, $args);
        }

        return $obj;
    }

    // Assignments are argument entities for calls.
    // They are present in ie filter properties as shown below.
    //
    //	 filter: progid:DXImageTransform.Microsoft.Alpha( *opacity=50* )
    //

    /**
     * @param string[] $toks
     *
     * @return string
     */
    private function MatchFuncs($toks) {

        if ($this->pos < $this->input_len) {
            foreach ($toks as $tok) {
                $match = $this->$tok();
                if ($match) {
                    return $match;
                }
            }
        }

    }

    //
    // Parse url() tokens
    //
    // We use a specific rule for urls, because they don't really behave like
    // standard function calls. The difference is that the argument doesn't have
    // to be enclosed within a string, so it can't be parsed as an Expression.
    //

    /**
     * @param string $tok
     */
    public function PeekChar($tok) {
        //return ($this->input[$this->pos] === $tok );
        return ($this->pos < $this->input_len) && ($this->input[$this->pos] === $tok);
    }


    //
    // A Variable entity, such as `@fink`, in
    //
    //	 width: @fink + 2px
    //
    // We use a different parser for variable definitions,
    // see `parsers.variable`.
    //

    /**
     * Parse a Less string from a given file
     *
     * @throws Less_Exception_Parser
     *
     * @param string $filename The file to parse
     * @param string $uri_root The url of the file
     * @param bool   $returnRoot Indicates whether the return value should be a css string a root node
     *
     * @return Less_Tree_Ruleset|Less_Parser
     */
    public function parseFile($filename, $uri_root = '', $returnRoot = FALSE) {

        if (!file_exists($filename)) {
            $this->Error(sprintf('File `%s` not found.', $filename));
        }


        // fix uri_root?
        // Instead of The mixture of file path for the first argument and directory path for the second argument has bee
        if (!$returnRoot && !empty($uri_root) && basename($uri_root) == basename($filename)) {
            $uri_root = dirname($uri_root);
        }


        $previousFileInfo = $this->env->currentFileInfo;
        $filename = self::WinPath($filename);
        $uri_root = self::WinPath($uri_root);
        $this->SetFileInfo($filename, $uri_root);

        self::AddParsedFile($filename);

        if ($returnRoot) {
            $rules = $this->GetRules($filename);
            $return = new \Less_Tree_Ruleset([], $rules);
        } else {
            $this->_parse($filename);
            $return = $this;
        }

        if ($previousFileInfo) {
            $this->env->currentFileInfo = $previousFileInfo;
        }

        return $return;
    }


    // A variable entity useing the protective {} e.g. @{var}

    static function AddParsedFile($file) {
        self::$imports[] = $file;
    }

    //
    // A Hexadecimal color
    //
    //	 #4F3C2F
    //
    // `rgb` and `hsl` colors are parsed through the `entities.call` parser.
    //

    /**
     * Allows a user to set variables values
     *
     * @param array $vars
     *
     * @return Less_Parser
     */
    public function ModifyVars($vars) {

        $this->input = Less_Parser::serializeVars($vars);
        $this->_parse();

        return $this;
    }

    //
    // A Dimension, that is, a number and a unit
    //
    //	 0.5em 95%
    //

    public static function serializeVars($vars) {
        $s = '';

        foreach ($vars as $name => $value) {
            $s .= (($name[0] === '@') ? '' : '@').$name.': '.$value.((substr($value, -1) === ';') ? '' : ';');
        }

        return $s;
    }


    //
    // A unicode descriptor, as is used in unicode-range
    //
    // U+0?? or U+00A1-00A9
    //

    /**
     * @deprecated 1.5.1.2
     *
     */
    public function SetCacheDir($dir) {

        if (!file_exists($dir)) {
            if (mkdir($dir)) {
                return TRUE;
            }
            throw new \Less_Exception_Parser('Less.php cache directory couldn\'t be created: '.$dir);

        } else if (!is_dir($dir)) {
            throw new \Less_Exception_Parser('Less.php cache directory doesn\'t exist: '.$dir);

        } else if (!is_writable($dir)) {
            throw new \Less_Exception_Parser('Less.php cache directory isn\'t writable: '.$dir);

        } else {
            $dir = self::WinPath($dir);
            \Less_Cache::$cache_dir = rtrim($dir, '/').'/';

            return TRUE;
        }
    }


    //
    // JavaScript code to be evaluated
    //
    //	 `window.location.href`
    //

    function parseUnicodeDescriptor() {
        $ud = $this->MatchReg('/\\G(U\+[0-9a-fA-F?]+)(\-[0-9a-fA-F?]+)?/');
        if ($ud) {
            return $this->NewObj1('Less_Tree_UnicodeDescriptor', $ud[0]);
        }
    }


    //
    // The variable part of a variable definition. Used in the `rule` parser
    //
    //	 @fink:
    //

    function parseAnonymousValue() {

        if (preg_match('/\\G([^@+\/\'"*`(;{}-]*);/', $this->input, $match, 0, $this->pos)) {
            $this->pos += strlen($match[1]);

            return $this->NewObj1('Less_Tree_Anonymous', $match[1]);
        }
    }


    //
    // The variable part of a variable definition. Used in the `rule` parser
    //
    // @fink();
    //

    /**
     * Create Less_Tree_* objects and optionally generate a cache string
     *
     * @return mixed
     */
    public function NewObj0($class) {
        $obj = new $class();
        if ($this->CacheEnabled()) {
            $obj->cache_string = ' new '.$class.'()';
        }

        return $obj;
    }


    //
    // extend syntax - used to extend selectors
    //

    private function parseEntitiesQuoted() {
        $j = $this->pos;
        $e = FALSE;
        $index = $this->pos;

        if ($this->input[$this->pos] === '~') {
            $j++;
            $e = TRUE; // Escaped strings
        }

        if ($this->input[$j] != '"' && $this->input[$j] !== "'") {
            return;
        }

        if ($e) {
            $this->MatchChar('~');
        }

        // Fix for #124: match escaped newlines
        //$str = $this->MatchReg('/\\G"((?:[^"\\\\\r\n]|\\\\.)*)"|\'((?:[^\'\\\\\r\n]|\\\\.)*)\'/');
        $str = $this->MatchReg('/\\G"((?:[^"\\\\\r\n]|\\\\.|\\\\\r\n|\\\\[\n\r\f])*)"|\'((?:[^\'\\\\\r\n]|\\\\.|\\\\\r\n|\\\\[\n\r\f])*)\'/');

        if ($str) {
            $result = $str[0][0] == '"' ? $str[1] : $str[2];

            return $this->NewObj5('Less_Tree_Quoted', [$str[0], $result, $e, $index, $this->env->currentFileInfo]);
        }

        return;
    }


    //
    // A Mixin call, with an optional argument list
    //
    //	 #mixins > .square(#fff);
    //	 .rounded(4px, black);
    //	 .button;
    //
    // The `while` loop is there because mixins can be
    // namespaced, but we only support the child and descendant
    // selector for now.
    //

    private function parseEntitiesKeyword() {

        //$k = $this->MatchReg('/\\G[_A-Za-z-][_A-Za-z0-9-]*/');
        $k = $this->MatchReg('/\\G%|\\G[_A-Za-z-][_A-Za-z0-9-]*/');
        if ($k) {
            $k = $k[0];
            $color = $this->fromKeyword($k);
            if ($color) {
                return $color;
            }

            return $this->NewObj1('Less_Tree_Keyword', $k);
        }
    }

    private function FromKeyword($keyword) {
        $keyword = strtolower($keyword);

        if (Less_Colors::hasOwnProperty($keyword)) {
            // detect named color
            return $this->NewObj1('Less_Tree_Color', substr(Less_Colors::color($keyword), 1));
        }

        if ($keyword === 'transparent') {
            return $this->NewObj3('Less_Tree_Color', [[0, 0, 0], 0, TRUE]);
        }
    }

    private function parseEntitiesCall() {
        $index = $this->pos;

        if (!preg_match('/\\G([\w-]+|%|progid:[\w\.]+)\(/', $this->input, $name, 0, $this->pos)) {
            return;
        }
        $name = $name[1];
        $nameLC = strtolower($name);

        if ($nameLC === 'url') {
            return NULL;
        }

        $this->pos += strlen($name);

        if ($nameLC === 'alpha') {
            $alpha_ret = $this->parseAlpha();
            if ($alpha_ret) {
                return $alpha_ret;
            }
        }

        $this->MatchChar('('); // Parse the '(' and consume whitespace.

        $args = $this->parseEntitiesArguments();

        if (!$this->MatchChar(')')) {
            return;
        }

        if ($name) {
            return $this->NewObj4('Less_Tree_Call', [$name, $args, $index, $this->env->currentFileInfo]);
        }
    }



    //
    // A Mixin definition, with a list of parameters
    //
    //	 .rounded (@radius: 2px, @color) {
    //		...
    //	 }
    //
    // Until we have a finer grained state-machine, we have to
    // do a look-ahead, to make sure we don't have a mixin call.
    // See the `rule` function for more information.
    //
    // We start by matching `.rounded (`, and then proceed on to
    // the argument list, which has optional default values.
    // We store the parameters in `params`, with a `value` key,
    // if there is a value, such as in the case of `@radius`.
    //
    // Once we've got our params list, and a closing `)`, we parse
    // the `{...}` block.
    //

    private function parseAlpha() {

        if (!$this->MatchReg('/\\G\(opacity=/i')) {
            return;
        }

        $value = $this->MatchReg('/\\G[0-9]+/');
        if ($value) {
            $value = $value[0];
        } else {
            $value = $this->parseEntitiesVariable();
            if (!$value) {
                return;
            }
        }

        $this->expectChar(')');

        return $this->NewObj1('Less_Tree_Alpha', $value);
    }

    //
    // Entities are the smallest recognized token,
    // and can be found inside a rule's value.
    //

    private function parseEntitiesVariable() {
        $index = $this->pos;
        if ($this->PeekChar('@') && ($name = $this->MatchReg('/\\G@@?[\w-]+/'))) {
            return $this->NewObj3('Less_Tree_Variable', [$name[0], $index, $this->env->currentFileInfo]);
        }
    }

    //
    // A Rule terminator. Note that we use `peek()` to check for '}',
    // because the `block` rule will be expecting it, but we still need to make sure
    // it's there, if ';' was ommitted.
    //

    /**
     * @param string $tok
     */
    public function expectChar($tok, $msg = NULL) {
        $result = $this->MatchChar($tok);
        if (!$result) {
            $this->Error($msg ? "Expected '".$tok."' got '".$this->input[$this->pos]."'" : $msg);
        } else {
            return $result;
        }
    }

    //
    // IE's alpha function
    //
    //	 alpha(opacity=88)
    //

    /**
     * Parse a list of arguments
     *
     * @return array
     */
    private function parseEntitiesArguments() {

        $args = [];
        while (TRUE) {
            $arg = $this->MatchFuncs(['parseEntitiesAssignment', 'parseExpression']);
            if (!$arg) {
                break;
            }

            $args[] = $arg;
            if (!$this->MatchChar(',')) {
                break;
            }
        }

        return $args;
    }


    //
    // A Selector Element
    //
    //	 div
    //	 + h1
    //	 #socks
    //	 input[type="text"]
    //
    // Elements are the building blocks for Selectors,
    // they are made out of a `Combinator` (see combinator rule),
    // and an element name, such as a tag a class, or `*`.
    //

    private function parseEntitiesLiteral() {
        return $this->MatchFuncs(['parseEntitiesDimension', 'parseEntitiesColor', 'parseEntitiesQuoted', 'parseUnicodeDescriptor']);
    }

    //
    // Combinators combine elements together, in a Selector.
    //
    // Because our parser isn't white-space sensitive, special care
    // has to be taken, when parsing the descendant combinator, ` `,
    // as it's an empty space. We have to check the previous character
    // in the input, to see if it's a ` ` character.
    //

    private function parseEntitiesAssignment() {

        $key = $this->MatchReg('/\\G\w+(?=\s?=)/');
        if (!$key) {
            return;
        }

        if (!$this->MatchChar('=')) {
            return;
        }

        $value = $this->parseEntity();
        if ($value) {
            return $this->NewObj2('Less_Tree_Assignment', [$key[0], $value]);
        }
    }

    //
    // A CSS selector (see selector below)
    // with less extensions e.g. the ability to extend and guard
    //

    private function parseEntity() {

        return $this->MatchFuncs([
            'parseEntitiesLiteral', 'parseEntitiesVariable', 'parseEntitiesUrl', 'parseEntitiesCall', 'parseEntitiesKeyword',
            'parseEntitiesJavascript', 'parseComment'
        ]);
    }

    //
    // A CSS Selector
    //
    //	 .class > div + h1
    //	 li a:hover
    //
    // Selectors are made out of one or more Elements, see above.
    //

    public function NewObj2($class, $args) {
        $obj = new $class($args[0], $args[1]);
        if ($this->CacheEnabled()) {
            $this->ObjCache($obj, $class, $args);
        }

        return $obj;
    }

    private function parseEntitiesUrl() {


        if ($this->input[$this->pos] !== 'u' || !$this->matchReg('/\\Gurl\(/')) {
            return;
        }

        $value = $this->match([
            'parseEntitiesQuoted', 'parseEntitiesVariable', '/\\Gdata\:.*?[^\)]+/', '/\\G(?:(?:\\\\[\(\)\'"])|[^\(\)\'"])+/'
        ]);
        if (!$value) {
            $value = '';
        }


        $this->expectChar(')');


        if (isset($value->value) || $value instanceof \Less_Tree_Variable) {
            return $this->NewObj2('Less_Tree_Url', [$value, $this->env->currentFileInfo]);
        }

        return $this->NewObj2('Less_Tree_Url', [$this->NewObj1('Less_Tree_Anonymous', $value), $this->env->currentFileInfo]);
    }

    private function parseEntitiesColor() {
        if ($this->PeekChar('#') && ($rgb = $this->MatchReg('/\\G#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})/'))) {
            return $this->NewObj1('Less_Tree_Color', $rgb[1]);
        }
    }

    //
    // The `block` rule is used by `ruleset` and `mixin.definition`.
    // It's a wrapper around the `primary` rule, with added `{}`.
    //

    private function parseEntitiesDimension() {

        $c = @ord($this->input[$this->pos]);

        //Is the first char of the dimension 0-9, '.', '+' or '-'
        if (($c > 57 || $c < 43) || $c === 47 || $c == 44) {
            return;
        }

        $value = $this->MatchReg('/\\G([+-]?\d*\.?\d+)(%|[a-z]+)?/');
        if ($value) {

            if (isset($value[2])) {
                return $this->NewObj2('Less_Tree_Dimension', [$value[1], $value[2]]);
            }

            return $this->NewObj1('Less_Tree_Dimension', $value[1]);
        }
    }

    private function parseEntitiesJavascript() {
        $e = FALSE;
        $j = $this->pos;
        if ($this->input[$j] === '~') {
            $j++;
            $e = TRUE;
        }
        if ($this->input[$j] !== '`') {
            return;
        }
        if ($e) {
            $this->MatchChar('~');
        }
        $str = $this->MatchReg('/\\G`([^`]*)`/');
        if ($str) {
            return $this->NewObj3('Less_Tree_Javascript', [$str[1], $this->pos, $e]);
        }
    }

    private function parseVariable() {
        if ($this->PeekChar('@') && ($name = $this->MatchReg('/\\G(@[\w-]+)\s*:/'))) {
            return $name[1];
        }
    }

    //
    // div, .class, body > p {...}
    //

    private function parseRulesetCall() {

        if ($this->input[$this->pos] === '@' && ($name = $this->MatchReg('/\\G(@[\w-]+)\s*\(\s*\)\s*;/'))) {
            return $this->NewObj1('Less_Tree_RulesetCall', $name[1]);
        }
    }

    private function parseMixinCall() {

        $char = $this->input[$this->pos];
        if ($char !== '.' && $char !== '#') {
            return;
        }

        $index = $this->pos;
        $this->save(); // stop us absorbing part of an invalid selector

        $elements = $this->parseMixinCallElements();

        if ($elements) {

            if ($this->MatchChar('(')) {
                $returned = $this->parseMixinArgs(TRUE);
                $args = $returned['args'];
                $this->expectChar(')');
            } else {
                $args = [];
            }

            $important = $this->parseImportant();

            if ($this->parseEnd()) {
                $this->forget();

                return $this->NewObj5('Less_Tree_Mixin_Call', [$elements, $args, $index, $this->env->currentFileInfo, $important]);
            }
        }

        $this->restore();
    }

    private function parseMixinCallElements() {
        $elements = [];
        $c = NULL;

        while (TRUE) {
            $elemIndex = $this->pos;
            $e = $this->MatchReg('/\\G[#.](?:[\w-]|\\\\(?:[A-Fa-f0-9]{1,6} ?|[^A-Fa-f0-9]))+/');
            if (!$e) {
                break;
            }
            $elements[] = $this->NewObj4('Less_Tree_Element', [$c, $e[0], $elemIndex, $this->env->currentFileInfo]);
            $c = $this->MatchChar('>');
        }

        return $elements;
    }

    /**
     * @param boolean $isCall
     */
    private function parseMixinArgs($isCall) {
        $expressions = [];
        $argsSemiColon = [];
        $isSemiColonSeperated = NULL;
        $argsComma = [];
        $expressionContainsNamed = NULL;
        $name = NULL;
        $returner = ['args' => [], 'variadic' => FALSE];

        $this->save();

        while (TRUE) {
            if ($isCall) {
                $arg = $this->MatchFuncs(['parseDetachedRuleset', 'parseExpression']);
            } else {
                $this->parseComments();
                if ($this->input[$this->pos] === '.' && $this->MatchReg('/\\G\.{3}/')) {
                    $returner['variadic'] = TRUE;
                    if ($this->MatchChar(";") && !$isSemiColonSeperated) {
                        $isSemiColonSeperated = TRUE;
                    }

                    if ($isSemiColonSeperated) {
                        $argsSemiColon[] = ['variadic' => TRUE];
                    } else {
                        $argsComma[] = ['variadic' => TRUE];
                    }
                    break;
                }
                $arg = $this->MatchFuncs(['parseEntitiesVariable', 'parseEntitiesLiteral', 'parseEntitiesKeyword']);
            }

            if (!$arg) {
                break;
            }


            $nameLoop = NULL;
            if ($arg instanceof Less_Tree_Expression) {
                $arg->throwAwayComments();
            }
            $value = $arg;
            $val = NULL;

            if ($isCall) {
                // Variable
                if (property_exists($arg, 'value') && count($arg->value) == 1) {
                    $val = $arg->value[0];
                }
            } else {
                $val = $arg;
            }


            if ($val instanceof \Less_Tree_Variable) {

                if ($this->MatchChar(':')) {
                    if ($expressions) {
                        if ($isSemiColonSeperated) {
                            $this->Error('Cannot mix ; and , as delimiter types');
                        }
                        $expressionContainsNamed = TRUE;
                    }

                    // we do not support setting a ruleset as a default variable - it doesn't make sense
                    // However if we do want to add it, there is nothing blocking it, just don't error
                    // and remove isCall dependency below
                    $value = NULL;
                    if ($isCall) {
                        $value = $this->parseDetachedRuleset();
                    }
                    if (!$value) {
                        $value = $this->parseExpression();
                    }

                    if (!$value) {
                        if ($isCall) {
                            $this->Error('could not understand value for named argument');
                        } else {
                            $this->restore();
                            $returner['args'] = [];

                            return $returner;
                        }
                    }

                    $nameLoop = ($name = $val->name);
                } else if (!$isCall && $this->MatchReg('/\\G\.{3}/')) {
                    $returner['variadic'] = TRUE;
                    if ($this->MatchChar(";") && !$isSemiColonSeperated) {
                        $isSemiColonSeperated = TRUE;
                    }
                    if ($isSemiColonSeperated) {
                        $argsSemiColon[] = ['name' => $arg->name, 'variadic' => TRUE];
                    } else {
                        $argsComma[] = ['name' => $arg->name, 'variadic' => TRUE];
                    }
                    break;
                } else if (!$isCall) {
                    $name = $nameLoop = $val->name;
                    $value = NULL;
                }
            }

            if ($value) {
                $expressions[] = $value;
            }

            $argsComma[] = ['name' => $nameLoop, 'value' => $value];

            if ($this->MatchChar(',')) {
                continue;
            }

            if ($this->MatchChar(';') || $isSemiColonSeperated) {

                if ($expressionContainsNamed) {
                    $this->Error('Cannot mix ; and , as delimiter types');
                }

                $isSemiColonSeperated = TRUE;

                if (count($expressions) > 1) {
                    $value = $this->NewObj1('Less_Tree_Value', $expressions);
                }
                $argsSemiColon[] = ['name' => $name, 'value' => $value];

                $name = NULL;
                $expressions = [];
                $expressionContainsNamed = FALSE;
            }
        }

        $this->forget();
        $returner['args'] = ($isSemiColonSeperated ? $argsSemiColon : $argsComma);

        return $returner;
    }

    //
    // An @import directive
    //
    //	 @import "lib";
    //
    // Depending on our environment, importing is done differently:
    // In the browser, it's an XHR request, in Node, it would be a
    // file-system operation. The function used for importing is
    // stored in `import`, which we pass to the Import constructor.
    //

    private function parseComments() {
        $comments = [];

        while ($this->pos < $this->input_len) {
            $comment = $this->parseComment();
            if (!$comment) {
                break;
            }

            $comments[] = $comment;
        }

        return $comments;
    }

    private function parseComment() {

        if ($this->input[$this->pos] !== '/') {
            return;
        }

        if ($this->input[$this->pos + 1] === '/') {
            $match = $this->MatchReg('/\\G\/\/.*/');

            return $this->NewObj4('Less_Tree_Comment', [$match[0], TRUE, $this->pos, $this->env->currentFileInfo]);
        }

        //$comment = $this->MatchReg('/\\G\/\*(?:[^*]|\*+[^\/*])*\*+\/\n?/');
        $comment = $this->MatchReg('/\\G\/\*(?s).*?\*+\/\n?/');//not the same as less.js to prevent fatal errors
        if ($comment) {
            return $this->NewObj4('Less_Tree_Comment', [$comment[0], FALSE, $this->pos, $this->env->currentFileInfo]);
        }
    }

    private function parseDetachedRuleset() {
        $blockRuleset = $this->parseBlockRuleset();
        if ($blockRuleset) {
            return $this->NewObj1('Less_Tree_DetachedRuleset', $blockRuleset);
        }
    }

    private function parseBlockRuleset() {
        $block = $this->parseBlock();

        if ($block) {
            $block = $this->NewObj2('Less_Tree_Ruleset', [NULL, $block]);
        }

        return $block;
    }

    private function parseBlock() {
        if ($this->MatchChar('{')) {
            $content = $this->parsePrimary();
            if ($this->MatchChar('}')) {
                return $content;
            }
        }
    }

    /**
     * Expressions either represent mathematical operations,
     * or white-space delimited Entities.
     *
     *     1px solid black
     *
     * @var * 2
     *
     * @return Less_Tree_Expression|null
     */
    private function parseExpression() {
        $entities = [];

        do {
            $e = $this->MatchFuncs(['parseAddition', 'parseEntity']);
            if ($e) {
                $entities[] = $e;
                // operations do not allow keyword "/" dimension (e.g. small/20px) so we support that here
                if (!$this->PeekReg('/\\G\/[\/*]/')) {
                    $delim = $this->MatchChar('/');
                    if ($delim) {
                        $entities[] = $this->NewObj1('Less_Tree_Anonymous', $delim);
                    }
                }
            }
        } while ($e);

        if ($entities) {
            return $this->NewObj1('Less_Tree_Expression', $entities);
        }
    }


    //
    // A CSS Directive
    //
    // @charset "utf-8";
    //

    /**
     * Same as match(), but don't change the state of the parser,
     * just return the match.
     *
     * @param string $tok
     *
     * @return integer
     */
    public function PeekReg($tok) {
        return preg_match($tok, $this->input, $match, 0, $this->pos);
    }


    //
    // A Value is a comma-delimited list of Expressions
    //
    //	 font-family: Baskerville, Georgia, serif;
    //
    // In a Rule, a Value represents everything after the `:`,
    // and before the `;`.
    //

    private function parseImportant() {
        if ($this->PeekChar('!') && $this->MatchReg('/\\G! *important/')) {
            return ' !important';
        }
    }

    private function parseEnd() {
        return $this->MatchChar(';') || $this->PeekChar('}');
    }

    private function parseMixinDefinition() {
        $cond = NULL;

        $char = $this->input[$this->pos];
        if (($char !== '.' && $char !== '#') || ($char === '{' && $this->PeekReg('/\\G[^{]*\}/'))) {
            return;
        }

        $this->save();

        $match = $this->MatchReg('/\\G([#.](?:[\w-]|\\\(?:[A-Fa-f0-9]{1,6} ?|[^A-Fa-f0-9]))+)\s*\(/');
        if ($match) {
            $name = $match[1];

            $argInfo = $this->parseMixinArgs(FALSE);
            $params = $argInfo['args'];
            $variadic = $argInfo['variadic'];


            // .mixincall("@{a}");
            // looks a bit like a mixin definition..
            // also
            // .mixincall(@a: {rule: set;});
            // so we have to be nice and restore
            if (!$this->MatchChar(')')) {
                $this->furthest = $this->pos;
                $this->restore();

                return;
            }


            $this->parseComments();

            if ($this->MatchReg('/\\Gwhen/')) { // Guard
                $cond = $this->expect('parseConditions', 'Expected conditions');
            }

            $ruleset = $this->parseBlock();

            if (is_array($ruleset)) {
                $this->forget();

                return $this->NewObj5('Less_Tree_Mixin_Definition', [$name, $params, $ruleset, $cond, $variadic]);
            }

            $this->restore();
        } else {
            $this->forget();
        }
    }

    private function parseTag() {
        return ($tag = $this->MatchReg('/\\G[A-Za-z][A-Za-z-]*[0-9]?/')) ? $tag : $this->MatchChar('*');
    }

    private function parseAttribute() {

        $val = NULL;

        if (!$this->MatchChar('[')) {
            return;
        }

        $key = $this->parseEntitiesVariableCurly();
        if (!$key) {
            $key = $this->expect('/\\G(?:[_A-Za-z0-9-\*]*\|)?(?:[_A-Za-z0-9-]|\\\\.)+/');
        }

        $op = $this->MatchReg('/\\G[|~*$^]?=/');
        if ($op) {
            $val = $this->match(['parseEntitiesQuoted', '/\\G[0-9]+%/', '/\\G[\w-]+/', 'parseEntitiesVariableCurly']);
        }

        $this->expectChar(']');

        return $this->NewObj3('Less_Tree_Attribute', [$key, $op[0], $val]);
    }

    private function parseEntitiesVariableCurly() {
        $index = $this->pos;

        if ($this->input_len > ($this->pos + 1) && $this->input[$this->pos] === '@' && ($curly = $this->MatchReg('/\\G@\{([\w-]+)\}/'))) {
            return $this->NewObj3('Less_Tree_Variable', ['@'.$curly[1], $index, $this->env->currentFileInfo]);
        }
    }

    private function parseRuleset() {
        $selectors = [];

        $this->save();

        while (TRUE) {
            $s = $this->parseLessSelector();
            if (!$s) {
                break;
            }
            $selectors[] = $s;
            $this->parseComments();

            if ($s->condition && count($selectors) > 1) {
                $this->Error('Guards are only currently allowed on a single selector.');
            }

            if (!$this->MatchChar(',')) {
                break;
            }
            if ($s->condition) {
                $this->Error('Guards are only currently allowed on a single selector.');
            }
            $this->parseComments();
        }


        if ($selectors) {
            $rules = $this->parseBlock();
            if (is_array($rules)) {
                $this->forget();

                return $this->NewObj2('Less_Tree_Ruleset', [$selectors, $rules]); //Less_Environment::$strictImports
            }
        }

        // Backtrack
        $this->furthest = $this->pos;
        $this->restore();
    }

    private function parseLessSelector() {
        return $this->parseSelector(TRUE);
    }

    /**
     * Custom less.php parse function for finding simple name-value css pairs
     * ex: width:100px;
     *
     */
    private function parseNameValue() {

        $index = $this->pos;
        $this->save();


        //$match = $this->MatchReg('/\\G([a-zA-Z\-]+)\s*:\s*((?:\'")?[a-zA-Z0-9\-% \.,!]+?(?:\'")?)\s*([;}])/');
        $match = $this->MatchReg('/\\G([a-zA-Z\-]+)\s*:\s*([\'"]?[#a-zA-Z0-9\-%\.,]+?[\'"]?) *(! *important)?\s*([;}])/');
        if ($match) {

            if ($match[4] == '}') {
                $this->pos = $index + strlen($match[0]) - 1;
            }

            if ($match[3]) {
                $match[2] .= ' !important';
            }

            return $this->NewObj4('Less_Tree_NameValue', [$match[1], $match[2], $index, $this->env->currentFileInfo]);
        }

        $this->restore();
    }

    private function parseRule($tryAnonymous = NULL) {

        $merge = FALSE;
        $startOfRule = $this->pos;

        $c = $this->input[$this->pos];
        if ($c === '.' || $c === '#' || $c === '&') {
            return;
        }

        $this->save();
        $name = $this->MatchFuncs(['parseVariable', 'parseRuleProperty']);

        if ($name) {

            $isVariable = is_string($name);

            $value = NULL;
            if ($isVariable) {
                $value = $this->parseDetachedRuleset();
            }

            $important = NULL;
            if (!$value) {

                // prefer to try to parse first if its a variable or we are compressing
                // but always fallback on the other one
                //if( !$tryAnonymous && is_string($name) && $name[0] === '@' ){
                if (!$tryAnonymous && (Less_Parser::$options['compress'] || $isVariable)) {
                    $value = $this->MatchFuncs(['parseValue', 'parseAnonymousValue']);
                } else {
                    $value = $this->MatchFuncs(['parseAnonymousValue', 'parseValue']);
                }

                $important = $this->parseImportant();

                // a name returned by this.ruleProperty() is always an array of the form:
                // [string-1, ..., string-n, ""] or [string-1, ..., string-n, "+"]
                // where each item is a tree.Keyword or tree.Variable
                if (!$isVariable && is_array($name)) {
                    $nm = array_pop($name);
                    if ($nm->value) {
                        $merge = $nm->value;
                    }
                }
            }


            if ($value && $this->parseEnd()) {
                $this->forget();

                return $this->NewObj6('Less_Tree_Rule', [$name, $value, $important, $merge, $startOfRule, $this->env->currentFileInfo]);
            } else {
                $this->furthest = $this->pos;
                $this->restore();
                if ($value && !$tryAnonymous) {
                    return $this->parseRule(TRUE);
                }
            }
        } else {
            $this->forget();
        }
    }

    public function NewObj6($class, $args) {
        $obj = new $class($args[0], $args[1], $args[2], $args[3], $args[4], $args[5]);
        if ($this->CacheEnabled()) {
            $this->ObjCache($obj, $class, $args);
        }

        return $obj;
    }

    private function parseImport() {

        $this->save();

        $dir = $this->MatchReg('/\\G@import?\s+/');

        if ($dir) {
            $options = $this->parseImportOptions();
            $path = $this->MatchFuncs(['parseEntitiesQuoted', 'parseEntitiesUrl']);

            if ($path) {
                $features = $this->parseMediaFeatures();
                if ($this->MatchChar(';')) {
                    if ($features) {
                        $features = $this->NewObj1('Less_Tree_Value', $features);
                    }

                    $this->forget();

                    return $this->NewObj5('Less_Tree_Import', [$path, $features, $options, $this->pos, $this->env->currentFileInfo]);
                }
            }
        }

        $this->restore();
    }

    private function parseImportOptions() {

        $options = [];

        // list of options, surrounded by parens
        if (!$this->MatchChar('(')) {
            return $options;
        }
        do {
            $optionName = $this->parseImportOption();
            if ($optionName) {
                $value = TRUE;
                switch ($optionName) {
                    case "css":
                        $optionName = "less";
                        $value = FALSE;
                        break;
                    case "once":
                        $optionName = "multiple";
                        $value = FALSE;
                        break;
                }
                $options[$optionName] = $value;
                if (!$this->MatchChar(',')) {
                    break;
                }
            }
        } while ($optionName);
        $this->expectChar(')');

        return $options;
    }

    private function parseImportOption() {
        $opt = $this->MatchReg('/\\G(less|css|multiple|once|inline|reference)/');
        if ($opt) {
            return $opt[1];
        }
    }

    private function parseMediaFeatures() {
        $features = [];

        do {
            $e = $this->parseMediaFeature();
            if ($e) {
                $features[] = $e;
                if (!$this->MatchChar(',')) {
                    break;
                }
            } else {
                $e = $this->parseEntitiesVariable();
                if ($e) {
                    $features[] = $e;
                    if (!$this->MatchChar(',')) {
                        break;
                    }
                }
            }
        } while ($e);

        return $features ? $features : NULL;
    }

    private function parseMediaFeature() {
        $nodes = [];

        do {
            $e = $this->MatchFuncs(['parseEntitiesKeyword', 'parseEntitiesVariable']);
            if ($e) {
                $nodes[] = $e;
            } else if ($this->MatchChar('(')) {
                $p = $this->parseProperty();
                $e = $this->parseValue();
                if ($this->MatchChar(')')) {
                    if ($p && $e) {
                        $r = $this->NewObj7('Less_Tree_Rule', [$p, $e, NULL, NULL, $this->pos, $this->env->currentFileInfo, TRUE]);
                        $nodes[] = $this->NewObj1('Less_Tree_Paren', $r);
                    } else if ($e) {
                        $nodes[] = $this->NewObj1('Less_Tree_Paren', $e);
                    } else {
                        return NULL;
                    }
                } else {
                    return NULL;
                }
            }
        } while ($e);

        if ($nodes) {
            return $this->NewObj1('Less_Tree_Expression', $nodes);
        }
    }

    /**
     * Parse a property
     * eg: 'min-width', 'orientation', etc
     *
     * @return string
     */
    private function parseProperty() {
        $name = $this->MatchReg('/\\G(\*?-?[_a-zA-Z0-9-]+)\s*:/');
        if ($name) {
            return $name[1];
        }
    }

    private function parseValue() {
        $expressions = [];

        do {
            $e = $this->parseExpression();
            if ($e) {
                $expressions[] = $e;
                if (!$this->MatchChar(',')) {
                    break;
                }
            }
        } while ($e);

        if ($expressions) {
            return $this->NewObj1('Less_Tree_Value', $expressions);
        }
    }

    public function NewObj7($class, $args) {
        $obj = new $class($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6]);
        if ($this->CacheEnabled()) {
            $this->ObjCache($obj, $class, $args);
        }

        return $obj;
    }

    private function parseMedia() {
        if ($this->MatchReg('/\\G@media/')) {
            $features = $this->parseMediaFeatures();
            $rules = $this->parseBlock();

            if (is_array($rules)) {
                return $this->NewObj4('Less_Tree_Media', [$rules, $features, $this->pos, $this->env->currentFileInfo]);
            }
        }
    }

    private function parseDirective() {

        if (!$this->PeekChar('@')) {
            return;
        }

        $rules = NULL;
        $index = $this->pos;
        $hasBlock = TRUE;
        $hasIdentifier = FALSE;
        $hasExpression = FALSE;
        $hasUnknown = FALSE;


        $value = $this->MatchFuncs(['parseImport', 'parseMedia']);
        if ($value) {
            return $value;
        }

        $this->save();

        $name = $this->MatchReg('/\\G@[a-z-]+/');

        if (!$name) {
            return;
        }
        $name = $name[0];


        $nonVendorSpecificName = $name;
        $pos = strpos($name, '-', 2);
        if ($name[1] == '-' && $pos > 0) {
            $nonVendorSpecificName = "@".substr($name, $pos + 1);
        }


        switch ($nonVendorSpecificName) {
            /*
			case "@font-face":
			case "@viewport":
			case "@top-left":
			case "@top-left-corner":
			case "@top-center":
			case "@top-right":
			case "@top-right-corner":
			case "@bottom-left":
			case "@bottom-left-corner":
			case "@bottom-center":
			case "@bottom-right":
			case "@bottom-right-corner":
			case "@left-top":
			case "@left-middle":
			case "@left-bottom":
			case "@right-top":
			case "@right-middle":
			case "@right-bottom":
			hasBlock = true;
			break;
			*/
            case "@charset":
                $hasIdentifier = TRUE;
                $hasBlock = FALSE;
                break;
            case "@namespace":
                $hasExpression = TRUE;
                $hasBlock = FALSE;
                break;
            case "@keyframes":
                $hasIdentifier = TRUE;
                break;
            case "@host":
            case "@page":
            case "@document":
            case "@supports":
                $hasUnknown = TRUE;
                break;
        }

        if ($hasIdentifier) {
            $value = $this->parseEntity();
            if (!$value) {
                $this->error("expected ".$name." identifier");
            }
        } else {
            if ($hasExpression) {
                $value = $this->parseExpression();
                if (!$value) {
                    $this->error("expected ".$name." expression");
                }
            } else {
                if ($hasUnknown) {

                    $value = $this->MatchReg('/\\G[^{;]+/');
                    if ($value) {
                        $value = $this->NewObj1('Less_Tree_Anonymous', trim($value[0]));
                    }
                }
            }
        }

        if ($hasBlock) {
            $rules = $this->parseBlockRuleset();
        }

        if ($rules || (!$hasBlock && $value && $this->MatchChar(';'))) {
            $this->forget();

            return $this->NewObj5('Less_Tree_Directive', [$name, $value, $rules, $index, $this->env->currentFileInfo]);
        }

        $this->restore();
    }

    private function parseSub() {

        if ($this->MatchChar('(')) {
            $a = $this->parseAddition();
            if ($a) {
                $this->expectChar(')');

                return $this->NewObj2('Less_Tree_Expression', [[$a], TRUE]); //instead of $e->parens = true so the value is cached
            }
        }
    }

    /**
     * Parses an addition operation
     *
     * @return Less_Tree_Operation|null
     */
    private function parseAddition() {

        $return = $m = $this->parseMultiplication();
        if ($return) {
            while (TRUE) {

                $isSpaced = $this->isWhitespace(-1);

                $op = $this->MatchReg('/\\G[-+]\s+/');
                if ($op) {
                    $op = $op[0];
                } else {
                    if (!$isSpaced) {
                        $op = $this->match(['#+', '#-']);
                    }
                    if (!$op) {
                        break;
                    }
                }

                $a = $this->parseMultiplication();
                if (!$a) {
                    break;
                }

                $m->parensInOp = TRUE;
                $a->parensInOp = TRUE;
                $return = $this->NewObj3('Less_Tree_Operation', [$op, [$return, $a], $isSpaced]);
            }
        }

        return $return;
    }

    //caching

    /**
     * Parses multiplication operation
     *
     * @return Less_Tree_Operation|null
     */
    function parseMultiplication() {

        $return = $m = $this->parseOperand();
        if ($return) {
            while (TRUE) {

                $isSpaced = $this->isWhitespace(-1);

                if ($this->PeekReg('/\\G\/[*\/]/')) {
                    break;
                }

                $op = $this->MatchChar('/');
                if (!$op) {
                    $op = $this->MatchChar('*');
                    if (!$op) {
                        break;
                    }
                }

                $a = $this->parseOperand();

                if (!$a) {
                    break;
                }

                $m->parensInOp = TRUE;
                $a->parensInOp = TRUE;
                $return = $this->NewObj3('Less_Tree_Operation', [$op, [$return, $a], $isSpaced]);
            }
        }

        return $return;

    }

    /**
     * An operand is anything that can be part of an operation,
     * such as a Color, or a Variable
     *
     */
    private function parseOperand() {

        $negate = FALSE;
        $offset = $this->pos + 1;
        if ($offset >= $this->input_len) {
            return;
        }
        $char = $this->input[$offset];
        if ($char === '@' || $char === '(') {
            $negate = $this->MatchChar('-');
        }

        $o = $this->MatchFuncs(['parseSub', 'parseEntitiesDimension', 'parseEntitiesColor', 'parseEntitiesVariable', 'parseEntitiesCall']);

        if ($negate) {
            $o->parensInOp = TRUE;
            $o = $this->NewObj1('Less_Tree_Negative', $o);
        }

        return $o;
    }

    /**
     * Parses the conditions
     *
     * @return Less_Tree_Condition|null
     */
    private function parseConditions() {
        $index = $this->pos;
        $return = $a = $this->parseCondition();
        if ($a) {
            while (TRUE) {
                if (!$this->PeekReg('/\\G,\s*(not\s*)?\(/') || !$this->MatchChar(',')) {
                    break;
                }
                $b = $this->parseCondition();
                if (!$b) {
                    break;
                }

                $return = $this->NewObj4('Less_Tree_Condition', ['or', $return, $b, $index]);
            }

            return $return;
        }
    }

    private function parseCondition() {
        $index = $this->pos;
        $negate = FALSE;
        $c = NULL;

        if ($this->MatchReg('/\\Gnot/')) {
            $negate = TRUE;
        }
        $this->expectChar('(');
        $a = $this->MatchFuncs(['parseAddition', 'parseEntitiesKeyword', 'parseEntitiesQuoted']);

        if ($a) {
            $op = $this->MatchReg('/\\G(?:>=|<=|=<|[<=>])/');
            if ($op) {
                $b = $this->MatchFuncs(['parseAddition', 'parseEntitiesKeyword', 'parseEntitiesQuoted']);
                if ($b) {
                    $c = $this->NewObj5('Less_Tree_Condition', [$op[0], $a, $b, $index, $negate]);
                } else {
                    $this->Error('Unexpected expression');
                }
            } else {
                $k = $this->NewObj1('Less_Tree_Keyword', 'true');
                $c = $this->NewObj5('Less_Tree_Condition', ['=', $a, $k, $index, $negate]);
            }
            $this->expectChar(')');

            return $this->MatchReg('/\\Gand/') ? $this->NewObj3('Less_Tree_Condition', ['and', $c, $this->parseCondition()]) : $c;
        }
    }

    /**
     * Parse a rule property
     * eg: 'color', 'width', 'height', etc
     *
     * @return string
     */
    private function parseRuleProperty() {
        $offset = $this->pos;
        $name = [];
        $index = [];
        $length = 0;


        $this->rulePropertyMatch('/\\G(\*?)/', $offset, $length, $index, $name);
        while ($this->rulePropertyMatch('/\\G((?:[\w-]+)|(?:@\{[\w-]+\}))/', $offset, $length, $index, $name)) {
            ;
        } // !

        if ((count($name) > 1) && $this->rulePropertyMatch('/\\G\s*((?:\+_|\+)?)\s*:/', $offset, $length, $index, $name)) {
            // at last, we have the complete match now. move forward,
            // convert name particles to tree objects and return:
            $this->skipWhitespace($length);

            if ($name[0] === '') {
                array_shift($name);
                array_shift($index);
            }
            foreach ($name as $k => $s) {
                if (!$s || $s[0] !== '@') {
                    $name[$k] = $this->NewObj1('Less_Tree_Keyword', $s);
                } else {
                    $name[$k] = $this->NewObj3('Less_Tree_Variable', ['@'.substr($s, 2, -1), $index[$k], $this->env->currentFileInfo]);
                }
            }

            return $name;
        }


    }

    private function rulePropertyMatch($re, &$offset, &$length, &$index, &$name) {
        preg_match($re, $this->input, $a, 0, $offset);
        if ($a) {
            $index[] = $this->pos + $length;
            $length += strlen($a[0]);
            $offset += strlen($a[0]);
            $name[] = $a[1];

            return TRUE;
        }
    }

}


