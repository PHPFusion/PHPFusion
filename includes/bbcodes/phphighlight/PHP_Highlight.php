<?php
/**
 * Define constants for PHP 4 / PHP 5 compatability
 * The source of this file can be found at:
 * http://cvs.php.net/co.php/pear/PHP_Compat/Compat/Constant/T.php?r=HEAD
 * It is part of the PEAR PHP_Compat package:
 * http://pear.php.net/package/PHP_Compat
 */
//modified by Wooya for mFusion
require_once INCLUDES.'bbcodes/phphighlight/T.php';

/**
 * Improved PHP syntax highlighting.
 * Generates valid XHTML output with function referencing
 * and line numbering.
 * Four output methods provide maximum flexibility.
 *  * toHTML        => Formatted HTML
 *  * toHtmlComment => Formatted HTML, specifically for comments on messageboards
 *  * toList        => Ordered lists
 *  * toArray       => Associative array
 * Highlighting can be inline (with styles), or the same as
 * highlight_file() where colors are taken from php.ini.
 * @author      Aidan Lister <aidan@php.net>
 * @author      Based on an idea by Matthew Harris <shugotenshi@gmail.com>
 * @version     1.3.0
 * @link        http://aidanlister.com/repos/v/PHP_Highlight.php
 */
class PHP_Highlight {
    /**
     * Hold highlight colors
     * Contains an associative array of token types and colours.
     * By default, it contains the colours as specified by php.ini
     * For example, to change the colour of strings, use something
     * simular to $h->highlight['string'] = 'blue';
     * @var         array
     * @access      public
     */
    var $highlight;
    /**
     * Things to be replaced for formatting or otherwise reasons
     * The first element contains the match array, the second the replace
     * array.
     * @var         array
     * @access      public
     */
    var $replace = array(0 => array("\t", ' '), 1 => array('&nbsp;&nbsp;&nbsp;&nbsp;', '&nbsp;'));
    /**
     * Format of the link to the PHP manual page
     * @var         string
     * @access      public
     */
    var $manual = '<a href="http://www.php.net/function.%s">%s</a>';
    /**
     * Format of the span tag to be wrapped around each token
     * @var         string
     * @access      public
     */
    var $span;
    /**
     * Hold the source
     * @var         string
     * @access      private
     */
    var $_source = FALSE;
    /**
     * Hold plaintext keys
     * An array of lines which are plaintext
     * @var         array
     * @access      private
     */
    var $_plaintextkeys = array();

    /**
     * Constructor
     * Populates highlight array
     * @param   bool $inline If inline styles rather than colors are to be used
     * @param   bool $plaintext Do not format code outside PHP tags
     */
    function __construct($inline = FALSE) {
        // Inline
        if ($inline === FALSE) {
            // Default colours from php.ini
            $this->highlight = array(
                'string' => ini_get('highlight.string'), 'comment' => ini_get('highlight.comment'),
                'keyword' => ini_get('highlight.keyword'), 'bg' => ini_get('highlight.bg'),
                'default' => ini_get('highlight.default'), 'html' => ini_get('highlight.html')
            );
            $this->span = '<span style="color: %s;">%s</span>';
        } else {
            // Basic styles
            $this->highlight = array(
                'string' => 'string', 'comment' => 'comment', 'keyword' => 'keyword', 'bg' => 'bg',
                'default' => 'default', 'html' => 'html'
            );
            $this->span = '<span class="%s">%s</span>';
        }
    }

    /**
     * Load a file
     * @access  public
     * @param   string $file The file to load
     * @return  bool        Returns TRUE
     */
    function loadFile($file) {
        $this->_source = file_get_contents($file);

        return TRUE;
    }

    /**
     * Load a string
     * @access  public
     * @param   string $string The string to load
     * @return  bool        Returns TRUE
     */
    function loadString($string) {
        $this->_source = $string;

        return TRUE;
    }

    /**
     * Convert the source to an ordered list.
     * Each line is wrapped in <li> tags.
     * @access  public
     * @param   bool $return Return rather than print the results
     * @param   bool $funcref Reference functions to the PHP manual
     * @param   bool $blocks Whether to use code blocks around plaintext
     * @return  string    A HTML ordered list
     */
    function toList($return = FALSE, $funcref = TRUE, $blocks = TRUE) {
        // Ensure source has been loaded
        if ($this->_source == FALSE) {
            return FALSE;
        }
        // Format list
        $source = $this->toArray($funcref, $blocks);
        $out = "<ol>\n";
        foreach ($source as $i => $line) {
            $out .= "    <li>";
            // Some extra juggling for lines which are not code
            if (empty($line)) {
                $out .= '&nbsp;';
            } elseif ($blocks === TRUE && in_array($i, $this->_plaintextkeys)) {
                $out .= $line;
            } else {
                $out .= "<code>$line</code>";
            }
            $out .= "</li>\n";
        }
        $out .= "</ol>\n";
        if ($return === TRUE) {
            return $out;
        } else {
            echo $out;
        }
    }

    /**
     * Parse the loaded string into an array
     * Source is returned with the element key corresponding to the line number
     * @access  public
     * @param   bool $funcref Reference functions to the PHP manual
     * @param   bool $blocks Whether to ignore processing plaintext
     * @return  array     An array of highlighted source code
     */
    function toArray($funcref = TRUE, $blocks = FALSE) {
        // Ensure source has been loaded
        if ($this->_source == FALSE) {
            return FALSE;
        }
        // Init
        $tokens = token_get_all($this->_source);
        $manual = $this->manual;
        $span = $this->span;
        $i = 0;
        $out = array();
        $out[$i] = '';
        // Loop through each token
        foreach ($tokens as $j => $token) {
            // Single char
            if (is_string($token)) {
                // Skip token2color check for speed
                $out[$i] .= sprintf($span, $this->highlight['keyword'], htmlspecialchars($token));
                // Heredocs behave strangely
                list($tb) = isset($tokens[$j - 1]) ? $tokens[$j - 1] : FALSE;
                if ($tb === T_END_HEREDOC) {
                    $out[++$i] = '';
                }
                continue;
            }
            // Proper token
            list ($token, $value) = $token;
            // Make the value safe
            $value = htmlspecialchars($value);
            $value = str_replace($this->replace[0], $this->replace[1], $value);
            // Process
            if ($value === "\n") {
                // End this line and start the next
                $out[++$i] = '';
            } else {
                // Function linking
                if ($funcref === TRUE && $token === T_STRING) {
                    // Look ahead 1, look ahead 2, and look behind 3
                    if ((isset($tokens[$j + 1]) && $tokens[$j + 1] === '(' || isset($tokens[$j + 2]) && $tokens[$j + 2] === '(') && isset($tokens[$j - 3][0]) && $tokens[$j - 3][0] !== T_FUNCTION && function_exists($value)) {
                        // Insert the manual link
                        $value = sprintf($manual, $value, $value);
                    }
                }
                // Explode token block
                $lines = explode("\n", $value);
                foreach ($lines as $jj => $line) {
                    $line = trim($line);
                    if ($line !== '') {
                        // This next line is helpful for debugging
                        //$out[$i] .= token_name($token);
                        // Check for plaintext
                        if ($blocks === TRUE && $token === T_INLINE_HTML) {
                            $this->_plaintextkeys[] = $i;
                            $out[$i] .= $line;
                        } else {
                            $out[$i] .= sprintf($span, $this->_token2color($token), $line);
                        }
                    }
                    // Start a new line
                    if (isset($lines[$jj + 1])) {
                        $out[++$i] = '';
                    }
                }
            }
        }

        return $out;
    }

    /**
     * Assign a color based on the name of a token
     * @access  private
     * @param   int $token The token
     * @return  string  The color of the token
     */
    function _token2color($token) {
        switch ($token):
            case T_CONSTANT_ENCAPSED_STRING:
                return $this->highlight['string'];
                break;
            case T_INLINE_HTML:
                return $this->highlight['html'];
                break;
            case T_COMMENT:
            case T_DOC_COMMENT:
            case T_ML_COMMENT:
                return $this->highlight['comment'];
                break;
            case T_ABSTRACT:
            case T_ARRAY:
            case T_ARRAY_CAST:
            case T_AS:
            case T_BOOLEAN_AND:
            case T_BOOLEAN_OR:
            case T_BOOL_CAST:
            case T_BREAK:
            case T_CASE:
            case T_CATCH:
            case T_CLASS:
            case T_CLONE:
            case T_CONCAT_EQUAL:
            case T_CONTINUE:
            case T_DEFAULT:
            case T_DOUBLE_ARROW:
            case T_DOUBLE_CAST:
            case T_ECHO:
            case T_ELSE:
            case T_ELSEIF:
            case T_EMPTY:
            case T_ENDDECLARE:
            case T_ENDFOR:
            case T_ENDFOREACH:
            case T_ENDIF:
            case T_ENDSWITCH:
            case T_ENDWHILE:
            case T_END_HEREDOC:
            case T_EXIT:
            case T_EXTENDS:
            case T_FINAL:
            case T_FOREACH:
            case T_FUNCTION:
            case T_GLOBAL:
            case T_IF:
            case T_INC:
            case T_INCLUDE:
            case T_INCLUDE_ONCE:
            case T_INSTANCEOF:
            case T_INT_CAST:
            case T_ISSET:
            case T_IS_EQUAL:
            case T_IS_IDENTICAL:
            case T_IS_NOT_IDENTICAL:
            case T_IS_SMALLER_OR_EQUAL:
            case T_NEW:
            case T_OBJECT_CAST:
            case T_OBJECT_OPERATOR:
            case T_PAAMAYIM_NEKUDOTAYIM:
            case T_PRIVATE:
            case T_PROTECTED:
            case T_PUBLIC:
            case T_REQUIRE:
            case T_REQUIRE_ONCE:
            case T_RETURN:
            case T_SL:
            case T_SL_EQUAL:
            case T_SR:
            case T_SR_EQUAL:
            case T_START_HEREDOC:
            case T_STATIC:
            case T_STRING_CAST:
            case T_THROW:
            case T_TRY:
            case T_UNSET_CAST:
            case T_VAR:
            case T_WHILE:
                return $this->highlight['keyword'];
                break;
            case T_CLOSE_TAG:
            case T_OPEN_TAG:
            case T_OPEN_TAG_WITH_ECHO:
            default:
                return $this->highlight['default'];

        endswitch;
    }

    /**
     * Convert the source to formatted HTML.
     * Each line ends with <br />.
     * @access  public
     * @param   bool   $return Return rather than print the results
     * @param   bool   $linenum Display line numbers
     * @param   string $format Specify format of line numbers displayed
     * @param   bool   $funcref Reference functions to the PHP manual
     * @return  string    A HTML block of code
     */
    function toHtml($return = FALSE, $linenum = FALSE, $format = NULL, $funcref = TRUE) {
        // Ensure source has been loaded
        if ($this->_source == FALSE) {
            return FALSE;
        }
        // Line numbering
        if ($linenum === TRUE && $format === NULL) {
            $format = '<span>%02d</span> ';
        }
        // Format code
        $source = $this->toArray($funcref);
        $out = "<code>\n";
        foreach ($source as $i => $line) {
            $out .= '    ';
            if ($linenum === TRUE) {
                $out .= sprintf($format, $i);
            }
            $out .= empty($line) ? '&nbsp;' : $line;
            $out .= "<br />\n";
        }
        $out .= "</code>\n";
        if ($return === TRUE) {
            return $out;
        } else {
            echo $out;
        }
    }

    /**
     * Convert the source to formatted HTML blocks.
     * Each line ends with <br />.
     * This method ensures only PHP is between <code> blocks.
     * @access  public
     * @param   bool   $return Return rather than print the results
     * @param   bool   $linenum Display line numbers
     * @param   string $format Specify format of line numbers displayed
     * @param   bool   $reset Reset the line numbering each block
     * @param   bool   $funcref Reference functions to the PHP manual
     * @return  string    A HTML block of code
     */
    function toHtmlBlocks($return = FALSE, $linenum = FALSE, $format = NULL, $reset = TRUE, $funcref = TRUE) {
        // Ensure source has been loaded
        if ($this->_source == FALSE) {
            return FALSE;
        }
        // Default line numbering
        if ($linenum === TRUE && $format === NULL) {
            $format = '<span>%03d</span> ';
        }
        // Init
        $source = $this->toArray($funcref, TRUE);
        $out = '';
        $wasplain = TRUE;
        $k = 0;
        // Loop through each line and decide which block to use
        foreach ($source as $i => $line) {
            // Empty line
            if (empty($line)) {
                if ($wasplain === TRUE) {
                    $out .= '&nbsp;';
                } else {
                    if (in_array($i + 1, $this->_plaintextkeys)) {
                        $out .= "</code>\n";
                        // Reset line numbers
                        if ($reset === TRUE) {
                            $k = 0;
                        }
                    } else {
                        $out .= '     ';
                        // Add line number
                        if ($linenum === TRUE) {
                            $out .= sprintf($format, ++$k);
                        }
                    }
                }
                // Plain text
            } elseif (in_array($i, $this->_plaintextkeys)) {
                if ($wasplain === FALSE) {
                    $out .= "</code>\n";
                    // Reset line numbers
                    if ($reset === TRUE) {
                        $k = 0;
                    }
                }
                $wasplain = TRUE;
                $out .= str_replace('&nbsp;', ' ', $line);
                // Code
            } else {
                if ($wasplain === TRUE) {
                    $out .= "<code>\n";
                }
                $wasplain = FALSE;
                $out .= '     ';
                // Add line number
                if ($linenum === TRUE) {
                    $out .= sprintf($format, ++$k);
                }
                $out .= $line;
            }
            $out .= "<br />\n";
        }
        // Add final code tag
        if ($wasplain === FALSE) {
            $out .= "</code>\n";
        }
        // Output method
        if ($return === TRUE) {
            return $out;
        } else {
            echo $out;
        }
    }
}

