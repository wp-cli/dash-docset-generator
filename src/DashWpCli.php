<?php

namespace DashWpCli;

final class DashWpCli
{
    public static function buildDocs()
    {
        $commandsIndex = new CommandsIndex();
        echo "☁️ Downloading all HTML files...\n";
        $commandsIndex->saveHtml();
        echo "✅ All HTML files downloaded successfully\n";

        echo "🥞 Creating database...\n";
        $commands = $commandsIndex->commands;
        $db = new Db();
        $db->init();
        $db->insertCommands($commands);
        echo "✅ Database created successfully\n";
    }
}
