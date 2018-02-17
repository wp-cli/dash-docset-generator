<?php

namespace DashWpCli;

use Alchemy\Zippy\Zippy;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Twig_Environment;
use Twig_Loader_Filesystem;

final class Docset
{
    /** @var Command[] */
    private $commands;

    /** @var Db */
    private $db;

    /** @var string */
    public $version;

    /**
     * @param string $version
     */
    public function __construct($version)
    {
        $this->version = $version;
    }

    /**
     * @param string $resource
     * @return string|false
     */
    private function getRelativePathTo(string $resource)
    {
        $docsetPath = 'WP-CLI.docset';
        $path = "{$docsetPath}/Contents/Resources";
        switch ($resource) {
            case 'db':
                $path .= '/docSet.dsidx';
                break;
            case 'docset':
                return $docsetPath;
            case 'html':
                $path .= '/Documents';
                break;
            default:
                return false;
        }
        return $path;
    }

    public function save(string $dir)
    {
        $this->scaffold($dir);
        $this->db = new Db("{$dir}/{$this->getRelativePathTo('db')}");
        /** @var Finder $mdFiles */
        $mdFiles = (new Finder())
            ->files()
            ->name('*.md')
            ->depth(0)
            ->in(__DIR__ . "/versions/{$this->version}/commands");

        foreach ($mdFiles as $file) {
            $this->commands[] = new Command(
                $file->getBasename('.md'),
                $file->getPathname()
            );
        }
        $this->saveHtml($dir);
        $zippy = Zippy::load();
        $zippy->create(
            "{$dir}/WP-CLI.tgz", [
                'WP-CLI.docset' => "{$dir}/{$this->getRelativePathTo('docset')}"
            ]
        );
    }

    private function saveHtml(string $dir)
    {
        $twig = new Twig_Environment(
            new Twig_Loader_Filesystem(__DIR__ . '/templates')
        );
        foreach ($this->commands as $command) {
            $command->save(
                "{$dir}/{$this->getRelativePathTo('html')}",
                $this->db
            );
        }
        $commands = array_map(function (Command $command) {
            return [
                'name' => $command->name,
                'description' => $command->getDescription(),
                'relativeHref' => $command->getRelativePath()
            ];
        }, $this->commands);
        usort($commands, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        file_put_contents(
            "{$dir}/{$this->getRelativePathTo('html')}/index.html",
            $twig->render('index.html.twig', [
                'commands' => $commands,
                'css' => file_get_contents(
                    __DIR__ . '/../vendor/twbs/bootstrap/dist/css/bootstrap.min.css'
                ),
                'js' => file_get_contents(__DIR__ . '/js/add-href.js')
            ])
        );
    }

    private function scaffold(string $dir)
    {
        $docsetPath = "{$dir}/{$this->getRelativePathTo('docset')}";
        $htmlDir = "{$dir}/{$this->getRelativePathTo('html')}";

        if (file_exists($htmlDir)) {
            // rmdir() won't remove non-empty directories
            (new Filesystem())->remove($htmlDir);
        } else {
            mkdir($htmlDir, 0777, true);
            copy(__DIR__ . '/img/icon.png', "{$docsetPath}/icon.png");
            copy(__DIR__ . '/config/Info.plist', "{$docsetPath}/Contents/Info.plist");
            touch("{$dir}/{$this->getRelativePathTo('db')}");
        }
    }
}
