<?php

namespace Org\Util;

/**
 * Parser 
 * 
 * @copyright Copyright (c) 2012 SegmentFault Team. (http://segmentfault.com)
 * @author Joyqi <joyqi@segmentfault.com>
 * @license BSD License
 */
class Parser
{
    /**
     * _whiteList 
     * 
     * @var string
     */
    private $_commonWhiteList = 'kbd|b|i|strong|em|sup|sub|br|code|del|a|hr';

    /**
     * _specialWhiteList 
     * 
     * @var mixed
     * @access private
     */
    private $_specialWhiteList = [
        'table' =>  'table|tbody|thead|tfoot|tr|td|th'
    ];

    /**
     * _footnotes
     * 
     * @var array
     */
    private $_footnotes = [];

    /**
     * _blocks
     * 
     * @var array
     */
    private $_blocks = [];

    /**
     * _current  
     * 
     * @var string
     */
    private $_current = 'normal';

    /**
     * _pos  
     * 
     * @var int
     */
    private $_pos = -1;

    /**
     * _definitions  
     * 
     * @var array
     */
    private $_definitions = [];

    /**
     * @var array
     */
    private $_hooks = [];

    /**
     * makeHtml  
     * 
     * @param mixed $text 
     * @return string
     */
    public function makeHtml($text)
    {
        $html = $this->parse($text);
        return $this->makeFootnotes($html);
    }

    /**
     * @param $type
     * @param $callback
     */
    public function hook($type, $callback)
    {
        $this->_hooks[$type][] = $callback;
    }

    /**
     * @param $html
     * @return string
     */
    private function makeFootnotes($html)
    {
        if (count($this->_footnotes) > 0) {
            $html .= '<div class="footnotes"><hr><ol>';
            $index = 1;

            while ($val = array_pop($this->_footnotes)) {
                if (is_string($val)) {
                    $val .= " <a href=\"#fnref-{$index}\" class=\"footnote-backref\">&#8617;</a>";
                } else {
                    $val[count($val) - 1] .= " <a href=\"#fnref-{$index}\" class=\"footnote-backref\">&#8617;</a>";
                    $val = count($val) > 1 ? $this->parse(implode("\n", $val)) : $this->parseInline($val[0]);
                }

                $html .= "<li id=\"fn-{$index}\">{$val}</li>";

                $index ++;
            }

            $html .= '</ol></div>';
        }

        return $html;
    }

    /**
     * parse 
     * 
     * @param string $text
     * @return string
     */
    private function parse($text)
    {
        $blocks = $this->parseBlock($text, $lines);
        $html = '';

        foreach ($blocks as $block) {
            list ($type, $start, $end, $value) = $block;
            $extract = array_slice($lines, $start, $end - $start + 1);
            $method = 'parse' . ucfirst($type);

            $extract = $this->call('before' . ucfirst($method), $extract, $value);
            $result = $this->{$method}($extract, $value);
            $result = $this->call('after' . ucfirst($method), $result, $value);

            $html .= $result;
        }
        
        return $html;
    }

    /**
     * @param $type
     * @param $value
     * @return mixed
     */
    private function call($type, $value)
    {
        if (empty($this->_hooks[$type])) {
            return $value;
        }

        $args = func_get_args();
        $args = array_slice($args, 1);

        foreach ($this->_hooks[$type] as $callback) {
            $value = call_user_func_array($callback, $args);
            $args[0] = $value;
        }

        return $value;
    }

    /**
     * parseInline 
     * 
     * @param string $text 
     * @param string $whiteList
     * @return string
     */
    private function parseInline($text, $whiteList = '')
    {
        $id = 0;
        $uniqid = md5(uniqid());
        $codes = [];

        $text = $this->call('beforeParseInline', $text);

        // code
        $text = preg_replace_callback("/`(.+?)`/", function ($matches) use (&$id, &$codes, $uniqid) {
            $key = '|' . $uniqid . $id . '|';
            $codes[$key] = '<code>' . htmlspecialchars($matches[1]) . '</code>';
            $id ++;

            return $key;
        }, $text);

        // escape
        $text = preg_replace_callback("/<(\/?)([a-z0-9-]+)(\s+[^>]*)?>/i", function ($matches) use ($whiteList) {
            if (stripos($this->_commonWhiteList . '|' . $whiteList, $matches[2]) !== false) {
                return $matches[0];
            } else {
                return htmlspecialchars($matches[0]);
            }
        }, $text);

        // strong and em and some fuck
        $text = preg_replace("/(_|\*){3}(.+?)\\1{3}/", "<strong><em>\\2</em></strong>", $text);
        $text = preg_replace("/(_|\*){2}(.+?)\\1{2}/", "<strong>\\2</strong>", $text);
        $text = preg_replace("/(_|\*)(.+?)\\1/", "<em>\\2</em>", $text);
        $text = preg_replace("/<(https?:\/\/.+)>/i", "<a href=\"\\1\">\\1</a>", $text);

        // footnote
        $text = preg_replace_callback("/\[\^((?:[^\]]|\\]|\\[)+?)\]/", function ($matches) {
            $id = array_search($matches[1], $this->_footnotes);

            if (false === $id) {
                $id = count($this->_footnotes) + 1;
                $this->_footnotes[$id] = $matches[1];
            }

            return "<sup id=\"fnref-{$id}\"><a href=\"#fn-{$id}\" class=\"footnote-ref\">{$id}</a></sup>";
        }, $text);

        // image
        $text = preg_replace_callback("/!\[((?:[^\]]|\\]|\\[)+?)\]\(([^\)]+)\)/", function ($matches) {
            $escaped = $this->escapeBracket($matches[1]);
            return "<img src=\"{$matches[2]}\" alt=\"{$escaped}\" title=\"{$escaped}\">";
        }, $text);

        $text = preg_replace_callback("/!\[((?:[^\]]|\\]|\\[)+?)\]\[((?:[^\]]|\\]|\\[)+)\]/", function ($matches) {
            $escaped = $this->escapeBracket($matches[1]);
            
            if (isset($this->_definitions[$matches[2]])) {
                return "<img src=\"{$this->_definitions[$matches[2]]}\" alt=\"{$escaped}\" title=\"{$escaped}\">";
            } else {
                return $escaped;
            }
        }, $text);

        // link
        $text = preg_replace_callback("/\[((?:[^\]]|\\]|\\[)+?)\]\(([^\)]+)\)/", function ($matches) {
            $escaped = $this->escapeBracket($matches[1]);
            return "<a href=\"{$matches[2]}\">{$escaped}</a>";
        }, $text); 

        $text = preg_replace_callback("/\[((?:[^\]]|\\]|\\[)+?)\]\[((?:[^\]]|\\]|\\[)+)\]/", function ($matches) {
            $escaped = $this->escapeBracket($matches[1]);
            
            if (isset($this->_definitions[$matches[2]])) {
                return "<a href=\"{$this->_definitions[$matches[2]]}\">{$escaped}</a>";
            } else {
                return $escaped;
            }
        }, $text); 

        // autolink
        $text = preg_replace("/(^|[^\"])((http|https|ftp|mailto):[_a-z0-9-\.\/%#@\?\+=~\|\,]+)($|[^\"])/i",
            "\\1<a href=\"\\2\">\\2</a>\\4", $text);

        // release
        $text = str_replace(array_keys($codes), array_values($codes), $text);

        $text = $this->call('afterParseInline', $text);

        return $text;
    }

    /**
     * parseBlock 
     * 
     * @param string $text 
     * @param array $lines
     * @return array
     */
    private function parseBlock($text, &$lines)
    {
        $lines = explode("\n", $text);
        $this->_blocks = [];
        $this->_current = '';
        $this->_pos = -1;
        $special = implode("|", array_keys($this->_specialWhiteList));
        $emptyCount = 0;

        // analyze by line
        foreach ($lines as $key => $line) {
            // code block is special
            if (preg_match("/^(~|`){3,}([^`~]*)$/i", $line, $matches)) {
                if ($this->isBlock('code')) {
                    $this->setBlock($key)
                        ->endBlock();
                } else {
                    $this->startBlock('code', $key, $matches[2]);
                }

                continue;
            } else if ($this->isBlock('code')) {
                $this->setBlock($key);
                continue;
            }

            // html block is special too
            if (preg_match("/^\s*<({$special})(\s+[^>]*)?>/i", $line, $matches)) {
                $tag = strtolower($matches[1]);
                if (!$this->isBlock('html', $tag) && !$this->isBlock('pre')) {
                    $this->startBlock('html', $key, $tag);
                }

                continue;
            } else if (preg_match("/<\/({$special})>\s*$/i", $line, $matches)) {
                $tag = strtolower($matches[1]);

                if ($this->isBlock('html', $tag)) {
                    $this->setBlock($key)
                        ->endBlock();
                }

                continue;
            } else if ($this->isBlock('html')) {
                $this->setBlock($key);
                continue;
            }

            switch (true) {
                // list
                case preg_match("/^(\s*)((?:[0-9a-z]\.?)|\-|\+|\*)\s+/", $line, $matches):
                    $space = strlen($matches[1]);
                    $emptyCount = 0;

                    // opened
                    if ($this->isBlock('list')) {
                        $this->setBlock($key, $space);
                    } else {
                        $this->startBlock('list', $key, $space);
                    }
                    break;

                // footnote
                case preg_match("/^\[\^((?:[^\]]|\\]|\\[)+?)\]:/", $line, $matches):
                    $space = strlen($matches[0]) - 1;
                    $this->startBlock('footnote', $key, [$space, $matches[1]]);
                    break;

                // definition
                case preg_match("/^\s*\[((?:[^\]]|\\]|\\[)+?)\]:\s*(.+)$/", $line, $matches):
                    $this->_definitions[$matches[1]] = $matches[2];
                    $this->startBlock('definition', $key)
                        ->endBlock();
                    break;

                // pre
                case preg_match("/^ {4,}/", $line):
                    if ($this->isBlock('pre')) {
                        $this->setBlock($key);
                    } else if ($this->isBlock('normal')) {
                        $this->startBlock('pre', $key);
                    }
                    break;

                // table
                case preg_match("/^((?:(?:(?:[ :]*\-[ :]*)+(?:\||\+))|(?:(?:\||\+)(?:[ :]*\-[ :]*)+)|(?:(?:[ :]*\-[ :]*)+(?:\||\+)(?:[ :]*\-[ :]*)+))+)$/", $line, $matches):
                    if ($this->isBlock('normal')) {
                        $block = $this->getBlock();
                        $head = false;

                        if (empty($block) ||
                            $block[0] != 'normal' ||
                            preg_match("/^\s*$/", $lines[$block[2]])) {
                            $this->startBlock('table', $key);
                        } else {
                            $head = true;
                            $this->backBlock(1, 'table');
                        }

                        if ($matches[1][0] == '|') {
                            $matches[1] = substr($matches[1], 1);

                            if ($matches[1][strlen($matches[1]) - 1] == '|') {
                                $matches[1] = substr($matches[1], 0, -1);
                            }
                        }

                        $rows = preg_split("/(\+|\|)/", $matches[1]);
                        $aligns = [];
                        foreach ($rows as $row) {
                            $align = 'none';

                            if (preg_match("/^\s*(:?)\-+(:?)\s*$/", $row, $matches)) {
                                if (!empty($matches[1]) && !empty($matches[2])) {
                                    $align = 'center';
                                } else if (!empty($matches[1])) {
                                    $align = 'left';
                                } else if (!empty($matches[2])) {
                                    $align = 'right';
                                }
                            }

                            $aligns[] = $align;
                        }

                        $this->setBlock($key, [$head, $aligns]);
                    }
                    break;

                // single heading
                case preg_match("/^(#+)(.*)$/", $line, $matches):
                    $num = min(strlen($matches[1]), 6);
                    $this->startBlock('sh', $key, $num)
                        ->endBlock();
                    break;

                // multi heading
                case preg_match("/^\s*((=|-){2,})\s*$/", $line, $matches)
                    && ($this->getBlock() && !preg_match("/^\s*$/", $lines[$this->getBlock()[2]])):    // check if last line isn't empty
                    if ($this->isBlock('normal')) {
                        $this->backBlock(1, 'mh', $matches[1][0] == '=' ? 1 : 2)
                            ->setBlock($key)
                            ->endBlock();
                    } else {
                        $this->startBlock('normal', $key);
                    }
                    break;

                // block quote
                case preg_match("/^>/", $line):
                    if ($this->isBlock('quote')) {
                        $this->setBlock($key);
                    } else {
                        $this->startBlock('quote', $key);
                    }
                    break;

                // hr
                case preg_match("/^[-\*]{3,}\s*$/", $line):
                    $this->startBlock('hr', $key)
                        ->endBlock();
                    break;

                // normal
                default:
                    if ($this->isBlock('list')) {
                        preg_match("/^(\s*)/", $line, $matches);

                        if (strlen($line) == strlen($matches[1])) { // empty line
                            if ($emptyCount > 0) {
                                $this->startBlock('normal', $key);
                            } else {
                                $this->setBlock($key);
                            }

                            $emptyCount ++;
                        } else if (strlen($matches[1]) >= $this->getBlock()[3] && $emptyCount == 0) {
                            $this->setBlock($key);
                        } else {
                            $this->startBlock('normal', $key);
                        }
                    } else if ($this->isBlock('footnote')) {
                        preg_match("/^(\s*)/", $line, $matches);

                        if (strlen($matches[1]) >= $this->getBlock()[3][0]) {
                            $this->setBlock($key);
                        } else {
                            $this->startBlock('normal', $key);
                        }
                    } else if ($this->isBlock('table')) {
                        if (false !== strpos($line, '|')) {
                            $this->setBlock($key);
                        } else {
                            $this->startBlock('normal', $key);
                        }
                    } else {
                        $block = $this->getBlock();

                        if (empty($block) || $block[0] != 'normal') {
                            $this->startBlock('normal', $key);
                        } else {
                            $this->setBlock($key);
                        }
                    }
                    break;
            }
        }

        return $this->optimizeBlocks($this->_blocks, $lines);
    }

    /**
     * @param array $blocks
     * @param array $lines
     * @return array
     */
    private function optimizeBlocks(array $blocks, array $lines)
    {
        $blocks = $this->call('beforeOptimizeBlocks', $blocks, $lines);

        foreach ($blocks as $key => &$block) {
            $prevBlock = isset($blocks[$key - 1]) ? $blocks[$key - 1] : NULL;
            $nextBlock = isset($blocks[$key + 1]) ? $blocks[$key + 1] : NULL;

            list ($type, $from, $to) = $block;

            if ('pre' == $type) {
                $isEmpty = true;

                for ($i = $from; $i <= $to; $i ++) {
                    $line = $lines[$i];
                    if (!preg_match("/^\s*$/", $line)) {
                        $isEmpty = false;
                        break;
                    }
                }

                if ($isEmpty) {
                    $block[0] = $type = 'normal';
                }
            }

            if ('normal' == $type) {
                // one sigle empty line
                if ($from == $to && preg_match("/^\s*$/", $lines[$from])
                    && !empty($prevBlock) && !empty($nextBlock)) {
                    if ($prevBlock[0] == 'list' && $nextBlock[0] == 'list') {
                        // combine 3 blocks
                        $blocks[$key - 1] = ['list', $prevBlock[1], $nextBlock[2], NULL];
                        array_splice($blocks, $key, 2);
                    }
                }
            }
        }

        return $this->call('afterOptimizeBlocks', $blocks, $lines);
    }

    /**
     * parseCode 
     * 
     * @param array $lines 
     * @param string $lang 
     * @return string
     */
    private function parseCode(array $lines, $lang)
    {
        $lang = trim($lang);
        $lines = array_slice($lines, 1, -1);

        return '<pre><code' . (!empty($lang) ? " class=\"{$lang}\"" : '') . '>'
            . htmlspecialchars(implode("\n", $lines)) . '</code></pre>';
    }

    /**
     * parsePre  
     * 
     * @param array $lines 
     * @return string
     */
    private function parsePre(array $lines)
    {
        foreach ($lines as &$line) {
            $line = htmlspecialchars(substr($line, 4));
        }

        return '<pre><code>' . implode("\n", $lines) . '</code></pre>';
    }

    /**
     * parseSh  
     * 
     * @param array $lines 
     * @param int $num 
     * @return string
     */
    private function parseSh(array $lines, $num)
    {
        $line = $this->parseInline(trim($lines[0], '# '));
        return "<h{$num}>{$line}</h{$num}>";
    }

    /**
     * parseMh 
     * 
     * @param array $lines 
     * @param int $num 
     * @return string
     */
    private function parseMh(array $lines, $num)
    {
        $line = $this->parseInline(trim($lines[0], '# '));
        return "<h{$num}>{$line}</h{$num}>";
    }

    /**
     * parseQuote 
     * 
     * @param array $lines 
     * @return string
     */
    private function parseQuote(array $lines)
    {
        foreach ($lines as &$line) {
            $line = preg_replace("/^> ?/", '', $line);
        }

        return '<blockquote>' . $this->parse(implode("\n", $lines)) . '</blockquote>';
    }

    /**
     * parseList 
     * 
     * @param array $lines 
     * @return string
     */
    private function parseList(array $lines)
    {
        $html = '';
        $minSpace = 99999;
        $rows = [];

        // count levels
        foreach ($lines as $key => $line) {
            if (preg_match("/^(\s*)((?:[0-9a-z]\.?)|\-|\+|\*)(\s+)(.*)$/", $line, $matches)) {
                $space = strlen($matches[1]);
                $type = false !== strpos('+-*', $matches[2]) ? 'ul' : 'ol';
                $minSpace = min($space, $minSpace);

                $rows[] = [$space, $type, $line, $matches[4]];
            } else {
                $rows[] = $line;
            }
        }

        $found = false;
        $secondMinSpace = 99999;
        foreach ($rows as $row) {
            if (is_array($row) && $row[0] != $minSpace) {
                $secondMinSpace = min($secondMinSpace, $row[0]);
                $found = true;
            }
        }
        $secondMinSpace = $found ?: $minSpace;

        $lastType = '';
        $leftLines = [];

        foreach ($rows as $row) {
            if (is_array($row)) {
                list ($space, $type, $line, $text) = $row;

                if ($space != $minSpace) {
                    $leftLines[] = preg_replace("/^\s{" . $secondMinSpace . "}/", '', $line);
                } else {
                    if ($lastType != $type) {
                        if (!empty($lastType)) {
                            $html .= "</{$lastType}>";
                        }

                        $html .= "<{$type}>";
                    }

                    if (!empty($leftLines)) {
                        $html .= "<li>" . $this->parse(implode("\n", $leftLines)) . "</li>";
                    }

                    $leftLines = [$text];
                    $lastType = $type;
                }
            } else {
                $leftLines[] = preg_replace("/^\s{" . $secondMinSpace . "}/", '', $row);
            }
        }

        if (!empty($leftLines)) {
            $html .= "<li>" . $this->parse(implode("\n", $leftLines)) . "</li></{$lastType}>";
        }

        return $html;
    }

    /**
     * @param array $lines
     * @param array $value
     * @return string
     */
    private function parseTable(array $lines, array $value)
    {
        list ($head, $aligns) = $value;
        $ignore = $head ? 1 : 0;

        $html = '<table>';
        $body = NULL;

        foreach ($lines as $key => $line) {
            if ($key == $ignore) {
                $head = false;
                $body = true;
                continue;
            }

            if ($line[0] == '|') {
                $line = substr($line, 1);
                if ($line[strlen($line) - 1] == '|') {
                    $line = substr($line, 0, -1);
                }
            }

            $line = preg_replace("/^(\|?)(.*?)\\1$/", "\\2", $line);
            $rows = array_map('trim', explode('|', $line));
            $columns = [];
            $last = -1;

            foreach ($rows as $row) {
                if (strlen($row) > 0) {
                    $last ++;
                    $columns[$last] = [1, $row];
                } else if (isset($columns[$last])) {
                    $columns[$last][0] ++;
                }
            }

            if ($head) {
                $html .= '<thead>';
            } else if ($body) {
                $html .= '<tbody>';
            }

            $html .= '<tr>';

            foreach ($columns as $key => $column) {
                list ($num, $text) = $column;
                $tag = $head ? 'th' : 'td';

                $html .= "<{$tag}";
                if ($num > 1) {
                    $html .= " colspan=\"{$num}\"";
                }

                if (isset($aligns[$key]) && $aligns[$key] != 'none') {
                    $html .= " align=\"{$aligns[$key]}\"";
                }

                $html .= '>' . $this->parseInline($text) . "</{$tag}>";
            }

            $html .= '</tr>';

            if ($head) {
                $html .= '</thead>';
            } else if ($body) {
                $body = false;
            }
        }

        if ($body !== NULL) {
            $html .= '</tbody>';
        }

        $html .= '</table>';
        return $html;
    }

    /**
     * parseHr 
     * 
     * @return string
     */
    private function parseHr()
    {
        return '<hr>';
    }

    /**
     * parseNormal  
     * 
     * @param array $lines 
     * @return string
     */
    private function parseNormal(array $lines)
    {
        foreach ($lines as &$line) {
            $line = $this->parseInline($line);
        }

        $str = trim(implode("\n", $lines));
        $str = preg_replace("/(\n\s*){2,}/", "</p><p>", $str);
        $str = preg_replace("/\n/", "<br>", $str);

        return empty($str) ? '' : "<p>{$str}</p>";
    }

    /**
     * parseFootnote 
     * 
     * @param array $lines 
     * @param array $value 
     * @return string
     */
    private function parseFootnote(array $lines, array $value)
    {
        list($space, $note) = $value;
        $index = array_search($note, $this->_footnotes);

        if (false !== $index) {
            $lines[0] = preg_replace("/^\[\^((?:[^\]]|\\]|\\[)+?)\]:/", '', $lines[0]);
            $this->_footnotes[$index] = $lines;
        }

        return '';
    }

    /**
     * parseDefine  
     * 
     * @return string
     */
    private function parseDefinition()
    {
        return '';
    }

    /**
     * parseHtml 
     * 
     * @param array $lines 
     * @param string $type
     * @return string
     */
    private function parseHtml(array $lines, $type)
    {
        foreach ($lines as &$line) {
            $line = $this->parseInline($line, 
                isset($this->_specialWhiteList[$type]) ? $this->_specialWhiteList[$type] : '');
        }

        return implode("\n", $lines);
    }

    /**
     * @param $str
     * @return mixed
     */
    private function escapeBracket($str)
    {
        return str_replace(['[', ']'], ['[', ']'], $str);
    }

    /**
     * startBlock  
     * 
     * @param mixed $type 
     * @param mixed $start
     * @param mixed $value 
     * @return $this
     */
    private function startBlock($type, $start, $value = NULL)
    {
        $this->_pos ++;
        $this->_current = $type;

        $this->_blocks[$this->_pos] = [$type, $start, $start, $value];
        
        return $this;
    }

    /**
     * endBlock  
     * 
     * @return $this
     */
    private function endBlock()
    {
        $this->_current = 'normal';
        return $this;
    }

    /**
     * isBlock  
     * 
     * @param mixed $type 
     * @param mixed $value
     * @return bool
     */
    private function isBlock($type, $value = NULL)
    {
        return $this->_current == $type 
            && (NULL === $value ? true : $this->_blocks[$this->_pos][3] == $value);
    }

    /**
     * getBlock  
     * 
     * @return array
     */
    private function getBlock()
    {
        return isset($this->_blocks[$this->_pos]) ? $this->_blocks[$this->_pos] : NULL;
    }

    /**
     * setBlock  
     * 
     * @param mixed $to 
     * @param mixed $value 
     * @return $this
     */
    private function setBlock($to = NULL, $value = NULL)
    {
        if (NULL !== $to) {
            $this->_blocks[$this->_pos][2] = $to;
        }

        if (NULL !== $value) {
            $this->_blocks[$this->_pos][3] = $value;
        }
        
        return $this;
    }

    /**
     * backBlock 
     * 
     * @param mixed $step 
     * @param mixed $type 
     * @param mixed $value 
     * @return $this
     */
    private function backBlock($step, $type, $value = NULL)
    {
        if ($this->_pos < 0) {
            return $this->startBlock($type, 0, $value);
        }

        $last = $this->_blocks[$this->_pos][2];
        $this->_blocks[$this->_pos][2] = $last - $step;

        if ($this->_blocks[$this->_pos][1] <= $this->_blocks[$this->_pos][2]) {
            $this->_pos ++;
        }

        $this->_current = $type;
        $this->_blocks[$this->_pos] = [$type, $last - $step + 1, $last, $value];

        return $this;
    }
}

