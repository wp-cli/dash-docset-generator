<?php

namespace DashWpCli;

final class Db
{
    /** @var \SQLite3 */
    private $db;

    /** @var string */
    private $dbFilePath = __DIR__ . '/../WP-CLI.docset/Contents/Resources/docSet.dsidx';

    /** @var string */
    private $initSql = <<< SQL
DROP TABLE IF EXISTS searchIndex;
CREATE TABLE searchIndex(id INTEGER PRIMARY KEY, name TEXT, type TEXT, path TEXT);
CREATE UNIQUE INDEX anchor ON searchIndex(name, type, path);
SQL;

    public function init()
    {
        $this->db = new \SQLite3($this->dbFilePath);
        $this->db->exec($this->initSql);
    }

    /**
     * @param Command $command
     */
    private function insertCommand($command)
    {
        $this->db->exec(
            $this->insertCommandSql(
                $command->name,
                "commands/{$command->url}index.html"
            )
        );
        $this->insertSubcommands($command);
    }

    /**
     * @param Command[] $commands
     */
    public function insertCommands($commands)
    {
        foreach ($commands as $command) {
            $this->insertCommand($command);
        }
    }

    /**
     * @param string $name The name of the entry
     * @param string $path The relative path towards the documentation file you want Dash to display for this entry.
     * @return string
     */
    private function insertCommandSql($name, $path)
    {
        return <<< SQL
INSERT OR IGNORE INTO searchIndex(name, type, path) VALUES ('{$name}', 'Command', '{$path}');
SQL;
    }

    private function insertSubcommands($command)
    {
        foreach ($command->subCommands as $subCommand) {
            $this->db->exec(
                $this->insertCommandSql(
                    $subCommand->name,
                    "commands/{$subCommand->url}index.html"
                )
            );
            $this->insertSubcommands($subCommand);
        }
    }
}
