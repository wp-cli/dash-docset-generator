<?php

namespace DashWpCli;

use Parsedown;
use Symfony\Component\Finder\Finder;
use Twig_Environment;
use Twig_Loader_Filesystem;

class Command
{
    /** @var string Command body HTML */
    private $bodyHtml;

    /** @var string */
    public $name;

    /** @var Command[] */
    private $subcommands = [];

    /** @va  Twig_Environment */
    private $twig;

    /**
     * @param string $name Command name
     * @param string $file Path to the command Markdown file
     */
    public function __construct(string $name, string $file)
    {
        $parsedown = new Parsedown();

        $this->name = $name;
        $this->bodyHtml = Filter::html(
            $parsedown->setBreaksEnabled(true)->text(file_get_contents($file))
        );
        $this->twig = new Twig_Environment(new Twig_Loader_Filesystem(__DIR__));
        $this->addSubcommands(\dirname($file) . '/' . $this->getShortName());
    }

    private function addSubcommands(string $dir)
    {
        if (file_exists($dir)) {
            /** @var Finder $subcommandFiles */
            $subcommandFiles = (new Finder())
                ->files()
                ->name('*.md')
                ->depth(0)
                ->in($dir);
            foreach ($subcommandFiles as $file) {
                $this->subcommands[] = new Command(
                    $this->name . ' ' . $file->getBasename('.md'),
                    $file->getPathname()
                );
            }
        }
    }

    private function getBodyHtml()
    {
        return preg_replace(
            '/^(.*)<h3>GLOBAL PARAMETERS<\/h3>.*$/s',
            '$1',
            $this->bodyHtml
        );
    }

    private function getCss(): string
    {
        $css = file_get_contents(
            __DIR__ . '/../vendor/twbs/bootstrap/dist/css/bootstrap.min.css'
        );
        $css .= '.card { margin-bottom: 1rem }';
        $css .= '.card-body > pre { margin-bottom: 0 }';
        return $css;
    }

    public function getDescription(): string
    {
        if (preg_match('/<\/h1>.*?<p>(.*?)<\/p>/s', $this->bodyHtml, $matches)) {
            return $matches[1];
        }
        return '';
    }

    private function getGlobalParametersHtml(): string
    {
        $pattern = '/^.*(<h3>GLOBAL PARAMETERS<\/h3>.*)$/s';
        if (preg_match($pattern, $this->bodyHtml)) {
            return preg_replace($pattern, '$1', $this->bodyHtml);
        }
        return '';
    }

    private function getShortName(): string
    {
        return ($pos = strrpos($this->name, ' '))
            ? substr($this->name, $pos + 1)
            : $this->name;
    }

    private function getSubcommandHtml(string $dir): string
    {
        if ($this->subcommands) {
            $commands = array_map(function (Command $command) use ($dir) {
                return [
                    'name' => $command->name,
                    'description' => $command->getDescription(),
                    'href' => "{$dir}/{$this->getShortName()}/{$command->getShortName()}.html"
                ];
            }, $this->subcommands);
            return $this->twig->render('subcommand-table.html.twig', [
                'commands' => $commands
            ]);
        }
        return '';
    }

    public function save(string $dir, Db $db)
    {
        $this->saveHtml($dir);
        $this->saveToDb($db);
    }

    private function saveHtml(string $dir)
    {
        $css = $this->getCss();
        $fallbackUrl = Constants::COMMANDS_DOCS_URL .
            str_replace(' ', '/', $this->name);

        if (!is_dir($dir)) {
            mkdir($dir);
        }
        file_put_contents(
            "{$dir}/{$this->getShortName()}.html",
            $this->twig->render('command.html.twig', [
                'fallbackUrl' => $fallbackUrl,
                'title' => "wp {$this->name}",
                'css' => $css,
                'bodyHtml' => $this->getBodyHtml(),
                'subcommandHtml' => $this->getSubcommandHtml($dir),
                'globalParametersHtml' => $this->getGlobalParametersHtml(),
            ])
        );
        foreach ($this->subcommands as $command) {
            $command->saveHtml("{$dir}/{$this->getShortName()}");
        }
    }

    private function saveToDb(Db $db)
    {
        if (substr_count($this->name, ' ') + 1 > 1) {
            $dir = \dirname(str_replace(' ', '/', $this->name));
            $path = "{$dir}/{$this->getShortName()}.html";
        } else {
            $path = "{$this->getShortName()}.html";
        }
        $db->createCommand($this->name, $path);
        foreach ($this->subcommands as $subcommand) {
            $subcommand->saveToDb($db);
        }
    }
}
