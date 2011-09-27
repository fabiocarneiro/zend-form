<?php

namespace Zend\Code\Scanner;

use Zend\Code\Scanner;

class DocBlockScanner implements Scanner
{
    protected $isScanned = false;
    protected $docComment = null;
    protected $shortDescription = null;
    protected $longDescription = '';
    protected $tags = array();


    public function __construct($docComment)
    {
        $this->docComment = $docComment;
    }

    public function getShortDescription()
    {
        $this->scan();
        return $this->shortDescription;
    }

    public function getLongDescription()
    {
        $this->scan();
        return $this->longDescription;
    }

    public function getTags()
    {
        $this->scan();
        return $this->tags;
    }

    protected function scan()
    {
        if ($this->isScanned) {
            return false;
        }

        $tokens = $this->tokenize();
        $tagIndex = null;

        reset($tokens);

        SCANNER_TOP:
            $token = current($tokens);

            switch ($token[0]) {

                case 'DOCBLOCK_NEWLINE':
                    if ($this->shortDescription === null) {
                        $this->shortDescription = '';
                    }
                    goto SCANNER_CONTINUE;

                case 'DOCBLOCK_TEXT':
                    if ($tagIndex !== null) {
                        $this->tags[$tagIndex]['value'] .= ($this->tags[$tagIndex]['value'] == '') ? $token[1] : ' ' . $token[1];
                    } elseif ($this->shortDescription !== null) {
                        if ($this->shortDescription === '') {
                            $this->shortDescription = $token[1];
                        } else {
                            $this->longDescription .= $token[1];
                        }
                    }
                    goto SCANNER_CONTINUE;

                case 'DOCBLOCK_TAG':
                    array_push($this->tags, array('name' => $token[1], 'value' => ''));
                    end($this->tags);
                    $tagIndex = key($this->tags);
                    goto SCANNER_CONTINUE;

                case 'DOCBLOCK_COMMENTEND':
                    goto SCANNER_END;

            }

        SCANNER_CONTINUE:
            if (next($tokens) === false) {
                goto SCANNER_END;
            }
            goto SCANNER_TOP;

        SCANNER_END:


        $this->shortDescription = rtrim($this->shortDescription);
        $this->longDescription  = rtrim($this->longDescription);
        $this->isScanned = true;
    }

    protected function tokenize()
    {
        static $CONTEXT_INSIDE_DOCBLOCK = 0x01;
        static $CONTEXT_INSIDE_ASTERISK = 0x02;

        $context = 0x00;
        $stream = $this->docComment;
        $streamIndex = null;
        $tokens = array();
        $tokenIndex = null;
        $currentChar = null;
        $currentWord = null;
        $currentLine = null;

        /*
        if (function_exists('docblock_tokenize')) {
            $tokens = array();
            foreach (docblock_tokenize($this->docComment) as $token) {
                $token[0] = docblock_token_name($token[0]);
                $tokens[] = $token;
            }
            return $tokens;
        }
        */

        $MACRO_STREAM_ADVANCE_CHAR = function ($positionsForward = 1) use (&$stream, &$streamIndex, &$currentChar, &$currentWord, &$currentLine) {
            $positionsForward = ($positionsForward > 0) ? $positionsForward : 1;
            $streamIndex = ($streamIndex === null) ? 0 : $streamIndex+$positionsForward;
            if (!isset($stream[$streamIndex])) {
                $currentChar = false;
                return false;
            }
            $currentChar = $stream[$streamIndex];
            $matches = array();
            $currentLine = (preg_match('#(.*)\n#', $stream, $matches, null, $streamIndex) === 1) ? $matches[1] : substr($stream, $streamIndex);
            if ($currentChar === ' ') {
                $currentWord = (preg_match('#( +)#', $currentLine, $matches) === 1) ? $matches[1] : $currentLine;
            } else {
                $currentWord = (($matches = strpos($currentLine, ' ')) !== false) ? substr($currentLine, 0, $matches) : $currentLine;
            }
            return $currentChar;
        };
        $MACRO_STREAM_ADVANCE_WORD = function () use (&$currentWord, &$MACRO_STREAM_ADVANCE_CHAR) {
            return $MACRO_STREAM_ADVANCE_CHAR(strlen($currentWord));
        };
        $MACRO_STREAM_ADVANCE_LINE = function () use (&$currentLine, &$MACRO_STREAM_ADVANCE_CHAR) {
            return $MACRO_STREAM_ADVANCE_CHAR(strlen($currentLine));
        };
        $MACRO_TOKEN_ADVANCE = function () use (&$tokenIndex, &$tokens) {
            $tokenIndex = ($tokenIndex === null) ? 0 : $tokenIndex+1;
            $tokens[$tokenIndex] = array('DOCBLOCK_UNKNOWN', '');
        };
        $MACRO_TOKEN_SET_TYPE = function ($type) use (&$tokenIndex, &$tokens) {
            $tokens[$tokenIndex][0] = $type;
        };
        $MACRO_TOKEN_APPEND_CHAR = function () use (&$currentChar, &$tokens, &$tokenIndex) {
            $tokens[$tokenIndex][1] .= $currentChar;
        };
        $MACRO_TOKEN_APPEND_WORD = function () use (&$currentWord, &$tokens, &$tokenIndex) {
            $tokens[$tokenIndex][1] .= $currentWord;
        };
        $MACRO_TOKEN_APPEND_LINE = function () use (&$currentLine, &$tokens, &$tokenIndex) {
            $tokens[$tokenIndex][1] .= $currentLine;
        };

        $MACRO_STREAM_ADVANCE_CHAR();
        $MACRO_TOKEN_ADVANCE();

        TOKENIZER_TOP:

            if ($context === 0x00 && $currentChar === '/' && $currentWord === '/**') {
                $MACRO_TOKEN_SET_TYPE('DOCBLOCK_COMMENTSTART');
                $MACRO_TOKEN_APPEND_WORD();
                $MACRO_TOKEN_ADVANCE();
                $context |= $CONTEXT_INSIDE_DOCBLOCK;
                $context |= $CONTEXT_INSIDE_ASTERISK;
                if ($MACRO_STREAM_ADVANCE_WORD() === false) {
                    goto TOKENIZER_END;
                }
                goto TOKENIZER_TOP;
            }

            if ($context & $CONTEXT_INSIDE_DOCBLOCK && $currentWord === '*/') {
                $MACRO_TOKEN_SET_TYPE('DOCBLOCK_COMMENTEND');
                $MACRO_TOKEN_APPEND_WORD();
                $MACRO_TOKEN_ADVANCE();
                $context &= ~$CONTEXT_INSIDE_DOCBLOCK;
                if ($MACRO_STREAM_ADVANCE_WORD() === false) {
                    goto TOKENIZER_END;
                }
                goto TOKENIZER_TOP;
            }

            if ($currentChar === ' ') {
                $MACRO_TOKEN_SET_TYPE('DOCBLOCK_WHITESPACE');
                $MACRO_TOKEN_APPEND_WORD();
                $MACRO_TOKEN_ADVANCE();
                if ($MACRO_STREAM_ADVANCE_WORD() === false) {
                    goto TOKENIZER_END;
                }
                goto TOKENIZER_TOP;
            }

            if ($currentChar === '*') {
                if (($context & $CONTEXT_INSIDE_DOCBLOCK) && ($context & $CONTEXT_INSIDE_ASTERISK)) {
                    $MACRO_TOKEN_SET_TYPE('DOCBLOCK_TEXT');
                } else {
                    $MACRO_TOKEN_SET_TYPE('DOCBLOCK_ASTERISK');
                    $context |= $CONTEXT_INSIDE_ASTERISK;
                }
                $MACRO_TOKEN_APPEND_CHAR();
                $MACRO_TOKEN_ADVANCE();
                if ($MACRO_STREAM_ADVANCE_CHAR() === false) {
                    goto TOKENIZER_END;
                }
                goto TOKENIZER_TOP;
            }

            if ($currentChar === '@') {
                $MACRO_TOKEN_SET_TYPE('DOCBLOCK_TAG');
                $MACRO_TOKEN_APPEND_WORD();
                $MACRO_TOKEN_ADVANCE();
                if ($MACRO_STREAM_ADVANCE_WORD() === false) {
                    goto TOKENIZER_END;
                }
                goto TOKENIZER_TOP;
            }

            if ($currentChar === "\n") {
                $MACRO_TOKEN_SET_TYPE('DOCBLOCK_NEWLINE');
                $MACRO_TOKEN_APPEND_CHAR();
                $MACRO_TOKEN_ADVANCE();
                $context &= ~$CONTEXT_INSIDE_ASTERISK;
                if ($MACRO_STREAM_ADVANCE_CHAR() === false) {
                    goto TOKENIZER_END;
                }
                goto TOKENIZER_TOP;
            }

            $MACRO_TOKEN_SET_TYPE('DOCBLOCK_TEXT');
            $MACRO_TOKEN_APPEND_LINE();
            $MACRO_TOKEN_ADVANCE();
            if ($MACRO_STREAM_ADVANCE_LINE() === false) {
                goto TOKENIZER_END;
            }
            goto TOKENIZER_TOP;

        TOKENIZER_CONTINUE:

            if ($MACRO_STREAM_ADVANCE_CHAR() === false) {
                goto TOKENIZER_END;
            }
            goto TOKENIZER_TOP;

        TOKENIZER_END:

            array_pop($tokens);

        return $tokens;
    }
}
