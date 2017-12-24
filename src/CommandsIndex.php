<?php

namespace DashWpCli;

final class CommandsIndex
{
    /** @var Command[] */
    public $commands;

    /** @var \DOMDocument */
    private $domDoc;

    /** @var bool */
    private $initialized = false;

    private function addOnlineRedirect()
    {
        $head = $this->domDoc->getElementsByTagName('head')->item(0);
        $this->domDoc->documentElement->insertBefore(
            new \DOMComment(
                ' Online page at ' .
                Constants::COMMANDS_INDEX_PUBLIC_URL .
                ' '
            ),
            $head
        );
    }

    private function addTocAnchors()
    {
        foreach ($this->getCommandAnchors() as $anchor) {
            $commandName = preg_replace('/^wp (.*)$/', '$1', $anchor->nodeValue);
            $anchor->setAttribute('name', "//apple_ref/cpp/Command/{$commandName}");
            $anchor->setAttribute('class', 'dashAnchor');
        }
    }

    /**
     * @return \DOMElement[]
     */
    private function getCommandAnchors()
    {
        return (new \DOMXPath($this->domDoc))->query(
            '//main[@id="main"]/table/tbody/tr/td[1]/a'
        );
    }

    /**
     * @return Command[]
     */
    public function getCommands()
    {
        $commands = [];
        foreach ($this->getCommandAnchors() as $anchor) {
            $name = preg_replace('/^wp (.*)$/', '$1', $anchor->nodeValue);
            $url = preg_replace(
                '/^' . preg_quote(Constants::COMMANDS_INDEX_PUBLIC_URL, '/') . '(.*?)$/',
                '$1',
                $anchor->getAttribute('href')
            );
            $commands[] = new Command($name, $url);
        }
        return $commands;
    }

    public function init()
    {
        $this->domDoc = new \DOMDocument();
        @$this->domDoc->loadHTML(
            file_get_contents(Constants::COMMANDS_INDEX_PUBLIC_URL)
        );
        $this->commands = $this->getCommands();
        $this->initialized = true;
    }

    private function removeExtraneousElements()
    {
        $xpath = new \DOMXPath($this->domDoc);
        Utils::removeElement('body/div[@id="wporg-header"]', $xpath);
        Utils::removeElement('body/div[@id="wporg-footer"]', $xpath);
        Utils::removeElement('body/div[@id="wpadminbar"]', $xpath);
    }

    public function saveHtml()
    {
        if (!$this->initialized) {
            $this->init();
        }
        echo "☁️ Downloading command index HTML...\n";
        $this->saveIndexHtml();
        echo "✅ Command index HTML downloaded successfully\n";
        $this->saveCommandsHtml();
    }

    private function saveCommandsHtml()
    {
        foreach ($this->commands as $command) {
            $command->saveHtml();
        }
    }

    private function saveIndexHtml()
    {
        $this->addOnlineRedirect();
        $this->addTocAnchors();
        $this->removeExtraneousElements();
        $this->updateTitleElement();
        if (!file_exists(Constants::COMMANDS_INDEX_PATH)) {
            mkdir(Constants::COMMANDS_INDEX_PATH);
        }
        $this->domDoc->saveHTMLFile(
            Constants::COMMANDS_INDEX_PATH . 'index.html'
        );
    }

    private function updateTitleElement()
    {
        $xpath = new \DOMXPath($this->domDoc);
        $title = $xpath->query('/html/head/title')->item(0);
        $title->nodeValue = preg_replace('/^(.*?) \|.*$/', '$1', $title->nodeValue);
    }
}
