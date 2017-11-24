<?php

namespace App\Kitano\ProjectManager\Services;

use Twig_Environment;
use Twig_Loader_Array;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use App\Kitano\ProjectManager\Managers\VueManager;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class VueCli extends VueManager
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
    protected $toCopy = [];

    /** @var array */
    protected $skipExtensions = [
        'jpg',
        'jpeg',
        'gif',
        'bmp',
        'txt',
        'sh',
        'md',
    ];

    protected $results = [];

    /**
     * Iterate Vue Cli Template files
     *
     * @return array
     */
    public function make()
    {
        $this->console->write("Converting Template '{$this->template}'...");

        $templatePath = "{$this->tplPath}/{$this->template}/template";

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $templatePath,
                RecursiveDirectoryIterator::SKIP_DOTS
            )
        );

        foreach ($iterator as $this->currentFile) {
            $this->execute();
        }

        return $this->results;
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
        $copy = in_array($ext, $this->skipExtensions) || ! $this->hasMustaches($this->currentContent);

        $this->compiled['name'] = basename($this->currentFile);
        $this->compiled['path'] = $this->currentFile->getPath();

        if ($copy) {
            $this->toCopy[] = $this->currentFile;

            return $this;
        }

        $this->twiggify()
             ->render()
             ->fixLineBreaks()
             ->addResults();

        return $this;
    }

    /**
     * Populate results array
     *
     * @return $this
     */
    protected function addResults()
    {
        $this->results['create'][] = [
            'file' => $this->compiled['name'],
            'content' => $this->compiled['rendered'],
            'src' => $this->compiled['path'],
            'dest' => $this->getDestinationPath(),
        ];

        foreach ($this->toCopy as $f) {
            $p = $f->getPath();

            $this->results['copy'][] = [
                'file' => basename($f),
                'src' => $p,
                'dest' => $this->getDestinationPath($p),
            ];
        }

        return $this;
    }

    /**
     * Get file destination path
     *
     * @param null|string $path Path to file
     *
     * @return mixed
     */
    protected function getDestinationPath($path = null)
    {
        $src = $path ?? $this->compiled['path'];

        return str_replace(
            $this->tplPath.DIRECTORY_SEPARATOR.$this->template.DIRECTORY_SEPARATOR.'template',
            $this->dir.DIRECTORY_SEPARATOR.$this->projectName,
            $src
        );
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
