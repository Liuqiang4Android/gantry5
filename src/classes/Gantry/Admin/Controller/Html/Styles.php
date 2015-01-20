<?php
namespace Gantry\Admin\Controller\Html;

use Gantry\Component\Config\Blueprints;
use Gantry\Component\Config\CompiledBlueprints;
use Gantry\Component\Config\ConfigFileFinder;
use Gantry\Component\Controller\HtmlController;
use Gantry\Component\File\CompiledYamlFile;
use Gantry\Component\Filesystem\Folder;
use Gantry\Framework\Base\Gantry;
use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;

class Styles extends HtmlController
{

    protected $httpVerbs = [
        'GET' => [
            '/'              => 'index',
            '/blocks'        => 'undefined',
            '/blocks/*'      => 'display',
            '/blocks/*/**'   => 'formfield',
        ],
        'POST' => [
            '/'         => 'forbidden',
            '/blocks'   => 'forbidden',
            '/blocks/*' => 'save'
        ],
        'PUT' => [
            '/'         => 'forbidden',
            '/blocks'   => 'forbidden',
            '/blocks/*' => 'save'
        ],
        'PATCH' => [
            '/'         => 'forbidden',
            '/blocks'   => 'forbidden',
            '/blocks/*' => 'save'
        ],
        'DELETE' => [
            '/'         => 'forbidden',
            '/blocks'   => 'forbidden',
            '/blocks/*' => 'reset'
        ]
    ];

    public function index()
    {
        $this->params['blocks'] = $this->container['blocks']->group();

        return $this->container['admin.theme']->render('@gantry-admin/pages/styles/styles.html.twig', $this->params);
    }

    public function display($id)
    {
        $style = $this->container['blocks']->get($id);
        $blueprints = new Blueprints($style);
        $prefix = 'blocks.' . $id;

        $this->params += [
            'block' => $blueprints,
            'data' =>  Gantry::instance()['config']->get($prefix),
            'id' => $id,
            'parent' => 'styles',
            'route' => 'styles.' . $prefix,
            'skip' => ['enabled']
        ];

        return $this->container['admin.theme']->render('@gantry-admin/pages/styles/item.html.twig', $this->params);
    }

    public function formfield($id)
    {
        $path = func_get_args();

        $style = $this->container['blocks']->get($id);

        // Load blueprints.
        $blueprints = new Blueprints($style);

        list($fields, $path, $value) = $blueprints->resolve(array_slice($path, 1), '/');

        if (!$fields) {
            throw new \RuntimeException('Page Not Found', 404);
        }

        // Get the prefix.
        $prefix = "blocks.{$id}." . implode('.', $path);
        if ($value !== null) {
            $parent = $fields;
            $fields = ['fields' => $fields['fields']];
            $prefix .= '.' . $value;
        }
        array_pop($path);

        $this->params = [
                'blueprints' => $fields,
                'data' =>  $this->container['config']->get($prefix),
                'parent' => $path ? "styles/blocks/{$id}/" . implode('/', $path) : "styles/blocks/{$id}",
                'route' => 'styles.' . $prefix
            ] + $this->params;

        if (isset($parent['key'])) {
            $this->params['key'] = $parent['key'];
        }

        return $this->container['admin.theme']->render('@gantry-admin/pages/styles/field.html.twig', $this->params);
    }

    public function save($id)
    {
        $this->params += [
            'data' => $_POST,
        ];

        return $this->display($id);
    }

    public function reset($id)
    {
        $this->params += [
            'data' => [],
        ];

        return $this->display($id);
    }
}
