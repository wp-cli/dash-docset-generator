<?php

namespace DashWpCli;

final class Utils
{
    /**
     * @param string $selector XPath selector
     * @param \DOMXPath $xpath
     */
    public static function removeElement($selector, $xpath)
    {
        $element = $xpath->query($selector)->item(0);
        $element->parentNode->removeChild($element);
    }
}
