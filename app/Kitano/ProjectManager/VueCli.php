<?php

namespace App\Kitano\ProjectManager;

use Twig_Environment;
use Twig_Loader_Array;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class VueCli
{
    /** Regex for finding mustaches */
    const MUSTACHE_PATTERN = '/{{(.*?)}}/';

    /** @var array */
    protected $compiled = [];

    /** @var string */
    protected $currentContent = '';

    /** @var \RecursiveIteratorIterator */
    protected $currentFile;

    /** @var array */
    protected $options;

    /** @var string */
    protected $template;

    /** @var array */
    protected $toCopy = [];

    /** @var array */
    protected $rejectExtensions = [
        'jpg',
        'jpeg',
        'gif',
        'bmp',
        'txt',
        'sh',
        'md',
    ];

    /**
     * @param string $template
     * @param array  $options
     */
    public function __construct($template, $options = [])
    {
        $this->template = $template;
        $this->options = $options;
    }

    /**
     * Iterate Vue Cli Template files
     */
    public function build()
    {
        $templatePath = public_path("downloads/vuejs-templates/{$this->template}/template");
        $dir_iterator = new RecursiveDirectoryIterator($templatePath, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($dir_iterator);

        foreach ($iterator as $this->currentFile) {
            $this->execute();
        }

        // TODO: return
    }

    /**
     * Build Twig Template
     *
     * @return $this
     */
    protected function execute()
    {
        $fc = file_get_contents($this->currentFile);

        $this->currentContent = str_replace(PHP_EOL, '_NEW_LINE_', $fc);

        $ext = $this->currentFile->getExtension();
        $copy = in_array($ext, $this->rejectExtensions) || ! $this->hasMustaches($this->currentContent);

        if ($copy) {
            $this->toCopy[] = $this->currentFile;

            return $this;
        }

        $this->compiled['name'] = basename($this->currentFile);
        $this->compiled['path'] = $this->currentFile->getPath();
        $this->compiled['raw'] = $this->currentContent;

        $this->twiggify()
             ->render()
             ->fixLineBreaks()
             ->createFiles();

        return $this;
    }

    /**
     * @TODO: implement metod
     *
     * @return $this
     */
    protected function createFiles()
    {
        return $this;
    }

    /**
     * Put line breaks in place
     *
     * @return $this
     */
    protected function fixLineBreaks()
    {
        $newLined = str_replace('_NEW_LINE_', PHP_EOL, $this->compiled['rendered']);
        $this->compiled['rendered'] = $this->removeIndentedBlanks($newLined);

        return $this;
    }

    /**
     * Remove indented new lines
     *
     * @param string $content
     *
     * @return string
     */
    protected function removeIndentedBlanks($content)
    {
        $e = explode(PHP_EOL, $content);
        $n = [];

        foreach ($e as $line) {
            if (preg_match('/^\s+$/', $line)) {
                continue;
            }

            $n[] = $line;
        }

        return implode(PHP_EOL, $n);
    }

    /**
     * Render Twig Template
     */
    protected function render()
    {
        $loader = new Twig_Loader_Array([
            $this->compiled['name'] => $this->compiled['twig'],
        ]);

        $twig = new Twig_Environment($loader);

        $this->compiled['rendered'] = $twig->render($this->compiled['name'], $this->options);

        return $this;
    }

    /**
     * Convert Handlebars to Twig syntax
     *
     * @return $this
     */
    protected function twiggify()
    {
        $handler = new MustacheHandler($this->currentContent);
        $this->compiled['twig'] = $handler->twiggify();

        return $this;
    }

    /**
     * Check if string contains any mustaches
     *
     * @param string $str Subject
     *
     * @return bool
     */
    protected function hasMustaches($str)
    {
        return strpos($str, MustacheHandler::OPEN_MUSTACHE) !== false;
    }

    /**
     * Get template meta
     *
     * @param string $template
     *
     * @return string JSON
     * @throws FileNotFoundException
     */
    public static function getMeta($template)
    {
        $metajs = public_path("downloads/vuejs-templates/{$template}/meta.js");
        $metajson = public_path("downloads/vuejs-templates/{$template}/meta.json");

        if (! file_exists($metajs) && ! file_exists($metajson)) {
            throw new FileNotFoundException("Template {$template} not found.");
        }

        return jsonDecodeMetaFile(file_get_contents(file_exists($metajson) ? $metajson : $metajs));
    }
}
