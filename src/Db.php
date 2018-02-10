<?php

namespace DashWpCli;

use SQLite3;

final class Db
{
    /** @var SQLite3 */
    private $db;

    public function __construct(string $file)
    {
        $initSql = <<< SQLite
DROP TABLE IF EXISTS searchIndex;
CREATE TABLE searchIndex(id INTEGER PRIMARY KEY, name TEXT, type TEXT, path TEXT);
CREATE UNIQUE INDEX anchor ON searchIndex(name, type, path);
SQLite;

        $this->db = new SQLite3($file);
        $this->db->exec($initSql);
    }

    public function createCommand(string $name, string $path)
    {
        $this->db->exec(self::insertCommandSql($name, $path));
    }

    /**
     * @param string $name The name of the entry
     * @param string $path Path to the HTML file, relative to `Documents/`
     * @return string
     */
    private static function insertCommandSql(string $name, string $path): string
    {
        return <<< SQLite
INSERT OR IGNORE INTO searchIndex(name, type, path) VALUES ('{$name}', 'Command', '{$path}');
SQLite;
    }
}
