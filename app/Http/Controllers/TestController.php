<?php

namespace App\Http\Controllers;

use App\Kitano\ProjectManager\Managers\VueManager;
use App\Kitano\ProjectManager\VueTemplate;
use App\Kitano\ProjectManager\Services\VueCli;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index()
    {
        $metaFile = public_path(env('VUE_TEMPLATES')).DIRECTORY_SEPARATOR.'pwa'.DIRECTORY_SEPARATOR.'meta.js';

        $meta = file_get_contents($metaFile);

        dd(VueManager::decodeMeta($meta));
    }
/*    public function index()
    {
        $r = new Request();

        $r->template = 'webpack';
        $r->options = [
            'name' => 'Sammy', 'router'=>true, 'unit'=>true, 'runner' => 'karma',
        ];
        //dd(VueCli::getMeta('webpack'));
        $v = new VueCli($r);
        $v->make();
    }*/

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
