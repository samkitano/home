<?php

namespace App\Http\Controllers;

use App\Kitano\ProjectManager\Managers\VueManager;
use App\Kitano\ProjectManager\VueTemplate;
use App\Kitano\ProjectManager\Services\VueCli;
use Illuminate\Http\Request;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use App\Kitano\ProjectManager\Traits\HandlesTemplates;

class TestController extends Controller
{
    protected $options;

/*    public function index()
    {
        $metaFile = public_path(env('VUE_TEMPLATES')).DIRECTORY_SEPARATOR.'simple'.DIRECTORY_SEPARATOR.'meta.json';

        $meta = file_get_contents($metaFile);

        $filters = VueManager::decodeMeta($meta);
dd($filters);
        $a = $filters['config/test.env.js'];
        $b = $filters['.eslintignore'];

        $this->options['unit'] = false;
        $this->options['e2e'] = false;
        $this->options['runner'] = 'jest';


        $filter = "!unit && runner === 'jest'";
        $exploded = explode(' ', $filter);
        $expression = 'return ';

        foreach ($exploded as $item) {
            if (preg_match('/[a-zA-Z]/', substr($item, 0, 1))) {
                $expression .= '($this->options["'.$item.'"] ?? false)';
            } else {
                if (substr($item, 0, 1) === '(' && substr($item, 1, 1) !== '!') {
                    $expression .= '(($this->options["' . ltrim($item, '(') . '"] ?? false)';
                } elseif (substr($item, 0, 1) === '(' && substr($item, 1, 1) === '!') {
                    $expression .= '((! $this->options["' . ltrim($item, '(') . '"] ?? false)';
                } elseif (substr($item, 0, 1) === '!') {
                    if (strlen($item) > 1) {
                        // only condition will be negated
                        $expression .= '(! $this->options["' . ltrim($item, '!') . '"] ?? true)';
                    } else {
                        // whole expression will be negated
                        $expression .= '! ';
                    }
                } else {
                    $expression .= ' '.$item.' ';
                }
            }
        }

        $expression = trim($expression).';';
dd($expression);
$r = eval($expression);
dd($r);

        $filter = substr($filterString, 0, strlen($filterString) - 1);
dump($filterString);
dump($filter);
dd($filter);
        //$f = glob(public_path('downloads/vuejs-templates').'/**');
dd($f);
    }*/
    public function index()
    {
//        $f = public_path(env('VUE_TEMPLATES')).DIRECTORY_SEPARATOR.'webpack/template/test/unit';
//        $i = new RecursiveIteratorIterator(
//            new RecursiveDirectoryIterator(
//                $f,
//                RecursiveDirectoryIterator::SKIP_DOTS
//            )
//        );
//
//        foreach ($i as $file) {
//            $results[] = $file->getRealPath();
//        }
//
//        dd($results);
//        $r = new Request();
        $metaFile = public_path(env('TEMPLATES')).DIRECTORY_SEPARATOR.'webpack-simple'.DIRECTORY_SEPARATOR.'meta.json';

        $meta = file_get_contents($metaFile);

        $filters = HandlesTemplates::decodeMeta($meta, true);dd($filters);
        $r->template = 'webpack';
        $r->options = [
            'name' => 'Sammy',
            'router' => false
        ];
        $r->meta = $filters;
        //dd(VueCli::getMeta('webpack'));
        $v = new VueCli($r);
        $v->make();
    }

    /*public function index()
        {
            $content = file_get_contents(public_path("downloads/vuejs-templates/webpack/template/package.json"));
            $mustaches = getMustaches($content);
    //dump($mustaches);

            for($i = 0; $i < count($mustaches); $i++) {
                $current = $mustaches[$i];
                if (isInPlaceMustache($current[0]) || isClosingMustache($current[0])) {
                    continue;
                }
                $nextIdx = $i + 1;
                $next = isset($mustaches[$nextIdx]) ? $mustaches[$nextIdx] : null;

                $cPos = $current[1];
                $nTag = isset($next) ? $next[0] : null;
                $nPos = isset($next) ? $next[1] : null;
    //dump($i.'= '.$cPos.' - '.$nPos);
                $between = stringBetweenPositions($content, $cPos, $nPos).$nTag;

                if (hasNestedMustaches($between)) {

                }
    dump($between);

            }
            die();

        }*/

    /*public function index()
    {
        $content = file_get_contents(base_path('DEL.txt'));
        dd(nestedMustache($content));
    }*/

//    public function index()
//    {
//        $options['dependencies'] = ['router'];
//        $options['devDependencies'] = ['eslint', 'eslintStandard', 'e2e'];
//        $options['standalone'] = true;
//
//        $n = new VueTemplate('samy', 'a test', 'Sam Kitano', '1.0.0', true, 'webpack', $options);
//        $g = $n->get();
//
//        return '.babelrc:<pre>'
//                .$g['.babelrc']
//                .'</pre>package.json:<pre>'
//                .$g['package.json']
//                .'</pre>
//                .eslintrc.js:<pre>'
//                .$g['.eslintrc.js']
//                .'</pre>
//                .eslintignore:<pre>'
//                .$g['.eslintignore']
//                .'</pre>.editorconfig:<pre>'
//                .$g['.editorconfig']
//                .'</pre>
//                .files:<pre>'
//                .$g['files']
//                .'</pre>
//                .tests:<pre>'
//                .$g['tests']
//                .'</pre>'
//            ;
//    }
}
