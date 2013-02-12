<?php
namespace Aura\View\Helper;

class EscapeJs extends Escape
{
    /**
     * 
     * A regular expression character class considered "safe" by the escaper.
     * 
     * @var string
     * 
     */
    protected $safe = 'a-z0-9,._';
    
    /**
     * 
     * Callback method to replace an unsafe character with an escaped one.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string An escaped character.
     * 
     */
    protected function replace(array $matches)
    {
        // get the character
        $chr = $matches[0];
        
        // is it UTF-8 ?
        if (strlen($chr) == 1) {
            // yes
            return sprintf('\\x%02X', ord($chr));
        } else {
            // no
            $chr = $this->convert($chr);
            return sprintf('\\u%04s', strtoupper(bin2hex($chr)));
        }
    }
}