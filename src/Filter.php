<?php

namespace DashWpCli;

class Filter
{
    public static function html(string $string): string
    {
        // Wrap <h1> contents in <code>
        $string = preg_replace(
            '/<h1>(.*?)<\/h1>/',
            '<h1><code>$1</code></h1>',
            $string
        );
        $string = str_replace('<table>', '<table class="table">', $string);
        // Put code example in card
        $string = preg_replace(
            '/<pre><code>(.*?)<\/code><\/pre>/ms',
            '<div class="card"><div class="card-body">$0</div></div>',
            $string
        );
        // Put command options in <dl>
        $string = preg_replace(
            '/<p>(.*?)<br ?\/?>\n:((.|\n)*?)<\/p>/m',
            '<dt>$1</dt><dd>$2</dd>',
            $string
        );
        $string = preg_replace('/<dt>(.*)<\/dd>/ms', '<dl>$0</dl>', $string);
        // Add line breaks
        $string = preg_replace('/(<(d[dt])>.*?)\n(.*?<\/\1>)/', '$1<br>$2', $string);
        return $string;
    }
}
