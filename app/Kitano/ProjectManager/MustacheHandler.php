<?php

namespace App\Kitano\ProjectManager;


class MustacheHandler
{
    /** Regex for extracting mustaches */
    const MUSTACHE_PATTERN = '/{{(.*?)}}/';

    const OPEN_MUSTACHE = '{{';
    const CLOSE_MUSTACHE = '}}';

    const POUND = '#';
    const SLASH = '/';

    /**
     * Handlebars directives
     */
    const IF_EQ = '#if_eq';
    const IF_OR = '#if_or';
    const UNLESS = '#unless';
    const UNLESS_EQ = '#unless_eq';
    const IF_ELSE = 'else';
    const END_EQ = '/if_eq';
    const END_OR = '/if_or';

    /**
     * Handlebars Tag types
     */
    const TAG_IF_EQ = 0;
    const TAG_IF_OR = 1;
    const TAG_UNLESS = 2;
    const TAG_UNLESS_EQ = 3;
    const TAG_ELSE = 4;
    const TAG_END_EQ = 5;
    const TAG_END_OR = 6;
    const TAG_IF_TRUE = 7;

    /**
     * Handlebars Tag types (grouped)
     */
    const TYPE_BLOCK = 0;
    const TYPE_ENDIF = 1;
    const TYPE_ELSE = 2;
    const TYPE_LITERAL = 3;

    /** @var string */
    protected $content;


    /** @var string */
    protected $mustache = '';

    /** @var string */
    protected $mustacheHead ='';

    /** @var array */
    protected $mustacheAttrs = [];
    
    /** @var array */
    protected $blockTypes = [
        self::IF_EQ => self::TYPE_BLOCK,
        self::IF_OR => self::TYPE_BLOCK,
        self::UNLESS => self::TYPE_BLOCK,
        self::UNLESS_EQ => self::TYPE_BLOCK,
        self::IF_ELSE => self::TYPE_ELSE,
        self::END_EQ => self::TYPE_ENDIF,
        self::END_OR => self::TYPE_ENDIF,
    ];

    /** @var array */
    protected $handleBars = [
        self::IF_EQ => self::TAG_IF_EQ,
        self::IF_OR => self::TAG_IF_OR,
        self::IF_ELSE => self::TAG_ELSE,
        self::UNLESS => self::TAG_UNLESS,
        self::UNLESS_EQ => self::TAG_UNLESS_EQ,
        self::END_EQ => self::TAG_END_EQ,
        self::END_OR => self::TAG_END_OR,
    ];

    /** @var array */
    protected $translations  = [
        self::TAG_IF_TRUE => '{%% if %s %%}',
        self::TAG_IF_EQ => '{%% if %s == %s %%}',
        self::TAG_UNLESS_EQ => '{%% if %s != %s %%}',
        self::TAG_UNLESS => '{%% if not %s %%}',
        self::TAG_ELSE => '{% else %}',
        self::TAG_IF_OR => '{%% if %s or %s %%}',
        self::TAG_END_EQ => '{% endif %}',
        self::TAG_END_OR => '{% endif %}',
    ];


    /**
     * @param string $content
     */
    public function __construct($content)
    {
        $this->content = $content;
    }
    
    /**
     * Convert Handlebars to Twig Syntax.
     *
     * @return string
     */
    public function twiggify()
    {
        if (! $this->contentHasMustaches()) {
            return $this->content;
        }

        foreach ($this->fetchMustaches() as $this->mustache) {
            $this->content = $this->translateMustache();
        }

        return $this->content;
    }

    /**
     * Convert currently iterating mustache
     *
     * @return string
     */
    protected function translateMustache() {
        $type = $this->getTagType();

        switch ($type) {
            case self::TYPE_BLOCK:
                return $this->replaceBlock();
            case self::TYPE_ENDIF:
                return $this->replaceEndblock();
            case self::TYPE_ELSE:
                return $this->replaceElseBlock();
            case self::TYPE_LITERAL:
                return $this->content; // no need for replacements
            default:
                return $this->content;
        }
    }

    /**
     * Translate block mustache
     *
     * @return mixed
     */
    protected function replaceBlock()
    {
        return str_replace(
            $this->mustache,
            vsprintf($this->translations[$this->getBlockType()], $this->mustacheAttrs),
            $this->content
        );
    }

    /**
     * Translate else block
     *
     * @return mixed
     */
    protected function replaceElseBlock()
    {
        return str_replace($this->mustache, $this->translations[self::TAG_ELSE], $this->content);
    }

    /**
     * Translate end block
     *
     * @return mixed
     */
    protected function replaceEndBlock()
    {
        return str_replace($this->mustache, $this->translations[self::TAG_END_EQ], $this->content);
    }

    /**
     * Determine block type
     *
     * @return int
     */
    protected function getBlockType()
    {
        $e = explode(' ', $this->getMustacheDirective($this->mustache));
        $directive = str_replace(self::POUND, '', $e[0]);

        $this->mustacheHead = $directive;
        $this->mustacheAttrs = count($e) < 2
            ? [$directive]
            : array_slice($e, 1, 2);

        return isset($this->handleBars[$e[0]])
                    ? $this->handleBars[$e[0]]
                    : self::TAG_IF_TRUE;
    }

    /**
     * Determine Mustache Type
     *
     * @return string
     */
    protected function getTagType() {
        $inner = $this->getMustacheDirective();

        if (substr($inner, 0, 1) === self::SLASH) {
            return self::TYPE_ENDIF;
        }

        $space = strpos($inner, ' ');

        if ($space === 0) {
            return self::TYPE_LITERAL;
        }

        $inner = $space ? substr($inner, 0, $space) : $inner;

        return isset($this->blockTypes[$inner]) ? $this->blockTypes[$inner] : self::TYPE_BLOCK;
    }

    /**
     * Get all mustaches from gurrent line
     *
     * @return array
     */
    protected function fetchMustaches()
    {
        if (preg_match_all(self::MUSTACHE_PATTERN, $this->content, $m)) {
            return $m[0];
        }

        return [];
    }

    /**
     * Check if current content contains mustaches
     *
     * @return bool
     */
    protected function contentHasMustaches()
    {
        return strpos($this->content, self::OPEN_MUSTACHE) !== false;
    }

    /**
     * Get inner mustache
     *
     * @return string
     */
    protected function getMustacheDirective()
    {
        return str_replace([self::OPEN_MUSTACHE, self::CLOSE_MUSTACHE], '', $this->mustache);
    }
}
