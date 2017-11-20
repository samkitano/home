<?php

namespace App\Kitano\ProjectManager;

class VueTemplate
{
    protected $browserlist = [
        '> 1%',
        'last 2 versions',
        'not ie <= 8',
    ];
    
    /** @var array */
    protected $router = [
        'vue-router' => '^3.0.1',
    ];

    protected $sass = [
        'node-sass' => '^4.5.3',
        'sass-loader' => '^6.0.6',
    ];
    
    protected $engines = [
        'node' => '>= 4.0.0',
        'npm' => '>= 3.0.0',
    ];

    /** @var array */
    protected $eslint = [
        'babel-eslint' => '^7.1.1',
        'eslint' => '^3.19.0',
        'eslint-friendly-formatter' => '^3.0.0',
        'eslint-loader' => '^1.7.1',
        'eslint-plugin-html' => '^3.0.0',
    ];

    /** @var array */
    protected $eslintStandard = [
        'eslint-config-standard' => '^10.2.1',
        'eslint-plugin-promise' => '^3.4.0',
        'eslint-plugin-standard' => '^3.0.1',
        'eslint-plugin-node' => '^5.2.0',
    ];

    /** @var array */
    protected $eslintAirbnb = [
        'eslint-config-airbnb-base' => '^11.3.0',
        'eslint-import-resolver-webpack' => '^0.8.3',
        'eslint-plugin-import' => '^2.7.0',
    ];

    /** @var array */
    protected $unit = [
        'cross-env' => '^5.0.1',
        'karma' => '^1.4.1',
        'karma-coverage' => '^1.1.1',
        'karma-mocha' => '^1.3.0',
        'karma-phantomjs-launcher' => '^1.0.2',
        'karma-phantomjs-shim' => '^1.4.0',
        'karma-sinon-chai' => '^1.3.1',
        'karma-sourcemap-loader' => '^0.3.7',
        'karma-spec-reporter' => '0.0.31',
        'karma-webpack' => '^2.0.2',
        'mocha' => '^3.2.0',
        'chai' => '^4.1.2',
        'sinon' => '^4.0.0',
        'sinon-chai' => '^2.8.0',
        'inject-loader' => '^3.0.0',
        'babel-plugin-istanbul' => '^4.1.1',
        'phantomjs-prebuilt' => '^2.1.14',
    ];

    /** @var array */
    protected $e2e = [
        'chromedriver' => '^2.27.2',
        'cross-spawn' => '^5.0.1',
        'nightwatch' => '^0.9.12',
        'selenium-server' => '^3.0.1',
    ];

    /** @var string */
    protected $name = '';

    /** @var string */
    protected $description = '';

    /** @var string */
    protected $author = '';

    /** @var string */
    protected $version;

    /** @var string */
    protected $license = 'MIT';

    /** @var bool */
    protected $private;

    /** @var string */
    protected $template;

    /** @var array */
    protected $options;


    /** @var string */
    protected $package = '';

    /** @var string */
    protected $babelrc = '';

    /** @var string|null */
    protected $eslintrc = null;

    /** @var string|null */
    protected $eslintignore = null;

    /** @var string */
    protected $editorconfig = '';

    /** @var string */
    protected $stubsPath = '';

    /** @var string */
    protected $vueFiles = '';

    /** @var null|string */
    protected $testFiles = null;

    /** @var array */
    protected $compiled = [];


    /**
     * @param string $name
     * @param string $description
     * @param string $author
     * @param string $version
     * @param bool $private
     * @param string $template
     * @param array $options
     */
    public function __construct(
        $name,
        $description,
        $author,
        $version = '0.0.1',
        $private = true,
        $template = 'webpack',
        $options = []
    )
    {
        $this->name = $name;
        $this->description = $description;
        $this->author = $author;
        $this->version = $version;
        $this->private = $private;
        $this->template = $template;
        $this->options = $options;
        $this->stubsPath = app_path('Kitano/ProjectManager/stubs/vue');
    }

    /**
     * @return array
     */
    public function get()
    {
        switch ($this->template) {
            case 'webpack':
                $this->makeWebpack();
                break;
            case 'webpack-simple':
                $this->makeWebpackSimple();
                break;
        }

        return [
            'compiled' => $this->compiled,
            'files' => $this->vueFiles,
            'tests' => $this->testFiles,
        ];
    }


    /**
     * Create a webpack-simple template
     */
    protected function makeWebpackSimple()
    {
        $this->compiled = [
            'package.json' => $this->getBasePackage(),
            '.editorconfig' => $this->getStub('.editorconfig'),
        ];

        $this->vueFiles = $this->getVueFiles();
    }
    
    /**
     * Create a webpack template
     */
    protected function makeWebpack()
    {
        $this->compiled = [
            'package.json' => $this->getBasePackage(),
            '.babelrc' => $this->getStub('.babelrc'),
            '.eslintrc.js' => $this->getEslintStub(),
            '.eslintignore' => $this->hasOption('eslint') ? $this->getStub('.eslintignore') : null,
            '.editorconfig' => $this->getStub('.editorconfig'),
        ];

        $this->vueFiles = $this->getVueFiles();
        $this->testFiles = $this->getTestFiles();
    }

    /**
     * @return array
     */
    protected function getBasePackage()
    {
        return json_encode([
            'name' => $this->name,
            'description' => $this->description,
            'author' => $this->author,
            'version' => $this->version,
            'license' => $this->license,
            'private' => $this->private,
            'scripts' => $this->addScripts(),
            'dependencies' => $this->getDependencies(),
            'devDependencies' => $this->getDependencies(true),
            'engines' => $this->engines,
            'browserslist' => $this->browserlist,
        ], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get path for vue files to copy
     *
     * @return string
     */
    protected function getVueFiles()
    {
        if ($this->template === 'webpack-simple') {
            return $this->stubsPath.DIRECTORY_SEPARATOR.$this->template;
        }

        if ($this->options['standalone']) {
            return $this->stubsPath.DIRECTORY_SEPARATOR.$this->template.DIRECTORY_SEPARATOR.'standalone';
        }

        return $this->stubsPath.DIRECTORY_SEPARATOR.$this->template.DIRECTORY_SEPARATOR.'runtime';
    }

    /**
     * Get path for test files to copy
     *
     * @return string|null
     */
    protected function getTestFiles()
    {
        $path = $this->stubsPath.DIRECTORY_SEPARATOR.$this->template.DIRECTORY_SEPARATOR.'tests';

        if ($this->hasUnitTests() && $this->hasE2Etests()) {
            return $path.DIRECTORY_SEPARATOR.'e2e_unit';
        }

        if ($this->hasUnitTests()) {
            return $path.DIRECTORY_SEPARATOR.'unit';
        }

        if ($this->hasE2Etests()) {
            return $path.DIRECTORY_SEPARATOR.'e2e';
        }
        
        return null;
    }

    /**
     * Get stub for eslint
     *
     * @return string|null
     */
    protected function getEslintStub()
    {
        if (! $this->hasOption('eslint')) {
            return null;
        }

        if ($this->hasOption('eslintAirbnb')) {
            return $this->getStub('airbnb.eslintrc.js');
        }

        if ($this->hasOption('eslintStandard')) {
            return $this->getStub('standard.eslintrc.js');
        }

        return $this->getStub('none.eslintrc.js');
    }

    /**
     * Add package.json dependencies
     *
     * @param bool $dev
     *
     * @return array
     */
    protected function getDependencies($dev = false)
    {
        $options = $dev ? $this->options['devDependencies'] : $this->options['dependencies'];
        $deps = [];

        if ($this->template === 'webpack') {
            $deps = $dev ? $this->getWepackDevDependencies() : $this->getWebpackDependencies();
        }

        if ($this->template === 'webpack-simple') {
            $deps = $dev ? $this->getWepackSimpleDevDependencies() : $this->getWebpackDependencies();
        }

        foreach ($options as $option) {
            $deps = array_merge($deps, $this->$option);
        }

        ksort($deps);

        return $deps;
    }

    /**
     * @return array
     */
    protected function getWebpackDependencies()
    {
        return [
            'vue' => '^2.4.4',
        ];
    }

    /**
     * @return array
     */
    protected function getWepackSimpleDevDependencies()
    {
        return [
            'babel-core' => '^6.26.0',
            'babel-loader' => '^7.1.2',
            'babel-preset-env' => '^1.6.0',
            'babel-preset-stage-3' => '^6.24.1',
            'cross-env' => '^5.0.5',
            'css-loader' => '^0.28.7',
            'file-loader' => '^1.1.4',
            'vue-loader' => '^13.0.5',
            'vue-template-compiler' => '^2.4.4',
            'webpack' => '^3.6.0',
            'webpack-dev-server' => '^2.9.1',
        ];
    }

    /**
     * @return array
     */
    protected function getWepackDevDependencies()
    {
        return [
            'autoprefixer' => '^7.1.2',
            'babel-core' => '^6.22.1',
            'babel-loader' => '^7.1.1',
            'babel-plugin-transform-runtime' => '^6.22.0',
            'babel-preset-env' => '^1.3.2',
            'babel-preset-stage-2' => '^6.22.0',
            'babel-register' => '^6.22.0',
            'chalk' => '^2.0.1',
            'connect-history-api-fallback' => '^1.3.0',
            'copy-webpack-plugin' => '^4.0.1',
            'css-loader' => '^0.28.0',
            'eventsource-polyfill' => '^0.9.6',
            'express' => '^4.14.1',
            'extract-text-webpack-plugin' => '^3.0.0',
            'file-loader' => '^1.1.4',
            'friendly-errors-webpack-plugin' => '^1.6.1',
            'html-webpack-plugin' => '^2.30.1',
            'http-proxy-middleware' => '^0.17.3',
            'semver' => '^5.3.0',
            'shelljs' => '^0.7.6',
            'opn' => '^5.1.0',
            'optimize-css-assets-webpack-plugin' => '^3.2.0',
            'ora' => '^1.2.0',
            'rimraf' => '^2.6.0',
            'url-loader' => '^0.5.8',
            'vue-loader' => '^13.3.0',
            'vue-style-loader' => '^3.0.1',
            'vue-template-compiler' => '^2.5.2',
            'portfinder' => '^1.0.13',
            'webpack' => '^3.6.0',
            'webpack-bundle-analyzer' => '^2.9.0',
            'webpack-dev-middleware' => '^1.12.0',
            'webpack-hot-middleware' => '^2.18.2',
            'webpack-merge' => '^4.1.0'
        ];
    }

    /**
     * Add package.json scripts
     *
     * @return array
     */
    protected function addScripts()
    {
        $scripts = [
            'dev' => 'node build/dev-server.js',
            'start' => 'npm run dev',
            'build' => 'node build/build.js',
        ];

        if ($this->hasEslint()) {
            $scripts = array_merge(
                $scripts,
                ['lint' => 'eslint --ext .js,.vue src test/unit/specs test/e2e/specs']
            );
        }

        if ($this->hasUnitTests()) {
            $scripts = array_merge(
                $scripts,
                ['unit' => 'cross-env BABEL_ENV=test karma start test/unit/karma.conf.js --single-run']
            );
        }

        if ($this->hasE2Etests()) {
            $scripts = array_merge(
                $scripts,
                ['e2e' => 'node test/e2e/runner.js']
            );
        }

        if ($this->hasUnitTests() && $this->hasE2Etests()) {
            $scripts = array_merge(
                $scripts,
                ['test' => 'npm run unit && npm run e2e']
            );
        }

        return $scripts;
    }

    /**
     * @return bool
     */
    protected function hasEslint()
    {
        return $this->hasDevDeps() && in_array('eslint', $this->options['devDependencies']);
    }

    /**
     * @return bool
     */
    protected function hasUnitTests()
    {
        return $this->hasDevDeps() && in_array('unit', $this->options['devDependencies']);
    }

    /**
     * @return bool
     */
    protected function hasE2Etests()
    {
        return $this->hasDevDeps() && in_array('e2e', $this->options['devDependencies']);
    }

    /**
     * @return bool
     */
    protected function hasDevDeps()
    {
        return isset($this->options['devDependencies']);
    }

    /**
     * @param string $option
     *
     * @return bool
     */
    protected function hasOption($option)
    {
        return $this->hasDevDeps() && in_array($option, $this->options['devDependencies']);
    }

    /**
     * Get a stub content
     *
     * @param string $stubName
     *
     * @return string
     */
    protected function getStub($stubName)
    {
        $file = $this->stubsPath.DIRECTORY_SEPARATOR.$stubName;

        return file_get_contents($file);
    }
}
