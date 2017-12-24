<?php

namespace DashWpCli;

class Command
{
    /** @var \DOMDocument */
    private $domDoc;

    /** @var bool */
    private $initialized = false;

    /** @var string */
    public $name;

    /** @var string */
    private $path;

    /** @var Command[] */
    public $subCommands = [];

    /** @var string */
    public $url;

    /**
     * @param string $name Name of the command
     * @param string $url Relative URL to the command
     */
    public function __construct($name, $url)
    {
        $this->name = $name;
        $this->url = $url;
        $this->path = Constants::COMMANDS_INDEX_PATH . $url;
    }

    private function addOnlineRedirect()
    {
        $head = $this->domDoc->getElementsByTagName('head')->item(0);
        $this->domDoc->documentElement->insertBefore(
            new \DOMComment(
                ' Online page at ' .
                Constants::COMMANDS_INDEX_PUBLIC_URL .
                $this->url .
                ' '
            ),
            $head
        );
    }

    private function addTocAnchors()
    {
        foreach ($this->getSubCommandAnchors() as $anchor) {
            $commandName = preg_replace(
                '/^wp ' . $this->name . ' (.*)$/',
                '$1',
                $anchor->nodeValue
            );
            $anchor->setAttribute('name', "//apple_ref/cpp/Command/{$commandName}");
            $anchor->setAttribute('class', 'dashAnchor');
        }
    }

    /**
     * @return \DOMElement[]
     */
    private function getSubcommandAnchors()
    {
        return (new \DOMXPath($this->domDoc))->query(
            '//main[@id="main"]//h3[@id="subcommands"]/following-sibling::table/tbody/tr/td[1]/a'
        );
    }

    /**
     * @return Command[]
     */
    private function getSubcommands()
    {
        $subcommands = [];
        foreach ($this->getSubcommandAnchors() as $anchor) {
            $name = preg_replace('/^wp (.*)$/', '$1', $anchor->nodeValue);
            $url = preg_replace(
                '/^' . preg_quote(Constants::COMMANDS_INDEX_PUBLIC_URL, '/') . '(.*?)$/',
                '$1',
                $anchor->getAttribute('href')
            );
            $subcommands[] = new Command($name, $url);
        }
        return $subcommands;
    }

    public function init()
    {
        $this->domDoc = new \DOMDocument();
        @$this->domDoc->loadHTML(
            file_get_contents(Constants::COMMANDS_INDEX_PUBLIC_URL . $this->url)
        );
        $this->subCommands = $this->getSubcommands();
        $this->initialized = true;
    }

    private function removeExtraneousElements()
    {
        $xpath = new \DOMXPath($this->domDoc);
        Utils::removeElement('body/div[@id="wporg-header"]', $xpath);
        Utils::removeElement('body/div[@id="wporg-footer"]', $xpath);
        Utils::removeElement('body/div[@id="wpadminbar"]', $xpath);
        Utils::removeElement('body/div[@id="page"]/header[@id="masthead"]', $xpath);
        Utils::removeElement('body/div[@id="page"]/div[@id="content"]/div[@id="content-area"]/div[@class="breadcrumb-trail breadcrumbs"]',
            $xpath);
    }

    public function saveHtml()
    {
        if (!$this->initialized) {
            $this->init();
        }
        echo "☁️ Downloading ‘{$this->name}’ command HTML...\n";
        $this->saveCommandHtml();
        echo "✅ ‘{$this->name}’ command HTML downloaded successfully\n";
        $this->saveSubCommandHtml();
    }

    private function saveCommandHtml()
    {
        $this->addOnlineRedirect();
        $this->addTocAnchors();
        $this->removeExtraneousElements();
        $this->updateTitleElement();
        if (!file_exists($this->path)) {
            mkdir($this->path);
        }
        $this->domDoc->saveHTMLFile(
            Constants::COMMANDS_INDEX_PATH . "{$this->url}/index.html"
        );
    }

    private function saveSubCommandHtml()
    {
        foreach ($this->subCommands as $subCommand) {
            $subCommand->saveHtml();
        }
    }

    private function updateTitleElement()
    {
        $xpath = new \DOMXPath($this->domDoc);
        $title = $xpath->query('/html/head/title')->item(0);
        $title->nodeValue = preg_replace('/^wp (.*?) \|.*$/', '$1', $title->nodeValue);
    }
}
