<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @package Aura.View
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\View\Helper;

/**
 * 
 * Helper for `<select>` tag with `<option>` and `<optgroup>` tags.
 * 
 * @package Aura.View
 * 
 */
class Select extends AbstractHelper
{
    /**
     * 
     * The stack of options and optgroups.
     * 
     * @param array
     * 
     */
    protected $stack = [];
    
    /**
     * 
     * Attributes for the select tag.
     * 
     * @param array
     * 
     */
    protected $attr = [];
    
    /**
     * 
     * Is there an optgroup in the stack?
     * 
     * @param bool
     * 
     */
    protected $optgroup = false;
    
    /**
     * 
     * The currently-selected option value(s).
     * 
     * @param array
     * 
     */
    protected $selected = [];
    
    /**
     * 
     * The HTML being built.
     * 
     * @param string
     * 
     */
    protected $html = '';
    
    /**
     * 
     * The current indent level for options.
     * 
     * @param int
     * 
     */
    protected $optlevel = 1;
    
    /**
     * 
     * Returns this object, or a fully-built <select> tag with options.
     * 
     * @param array $attr Attributes for the select tag.
     * 
     * @param array $opts Options for the select tag.
     * 
     * @param mixed $selected The value of the currently selected option(s).
     * 
     * @return string|self The select tag HTML if $opts are passed, or this
     * object if not.
     * 
     */
    public function __invoke(
        array $attr,
        array $opts = null,
        $selected = null)
    {
        // reset the object
        $this->stack    = [];
        $this->optgroup = false;
        $this->selected = [];
        $this->html     = '';
        $this->attr     = $attr;
        $this->optlevel = 1;
        
        // do we have options?
        if ($opts !== null) {
            // yes, generate and return the HTML
            $this->options($opts);
            $this->selected($selected);
            return $this->get();
        } else {
            // no, return this object for further manipulation
            return $this;
        }
    }
    
    /**
     * 
     * Add one option value and label to the stack.
     * 
     * @param string $value The option value.
     * 
     * @param string $label The option label.
     * 
     * @param array $attr Attributes for the option.
     * 
     * @return self
     * 
     */
    public function option($value, $label, array $attr = [])
    {
        $this->stack[] = ['buildOption', $value, $label, $attr];
        return $this;
    }
    
    /**
     * 
     * Adds multiple option values and labels to the stack.
     * 
     * @param array $opts An array where the keys are the option values and
     * the values are the option labels.
     * 
     * @param array $attr Attributes to be applied to every option.
     * 
     * @return self
     * 
     */
    public function options(array $opts, array $attr = [])
    {
        foreach ($opts as $value => $label) {
            $this->option($value, $label, $attr);
        }
        return $this;
    }
    
    /**
     * 
     * Adds an optgroup to the stack; ends any previous optgroup.
     * 
     * @param string $label The optgroup label.
     * 
     * @param array $attr Attributes for the optgroup.
     * 
     * @return self
     * 
     */
    public function optgroup($label, array $attr = [])
    {
        if ($this->optgroup) {
            $this->stack[] = ['endOptgroup'];
        }
        $this->stack[] = ['beginOptgroup', $label, $attr];
        $this->optgroup = true;
        return $this;
    }
    
    /**
     * 
     * Sets the value(s) of the currently selected option(s).
     * 
     * @param mixed $selected The selected value(s)
     * 
     * @return self
     * 
     */
    public function selected($selected)
    {
        $this->selected = (array) $selected;
        return $this;
    }
    
    /**
     * 
     * Returns the HTML for the select tag with its options and optgroups.
     * 
     * @return string
     * 
     */
    public function get()
    {
        // if this is a multiple select, the name needs to end in "[]"
        $append_brackets = isset($this->attr['multiple'])
                        && $this->attr['multiple']
                        && isset($this->attr['name'])
                        && substr($this->attr['name'], -2) != '[]';
        if ($append_brackets) {
            $this->attr['name'] .= '[]';
        }
        
        // build basic html with attributes
        $attr = $this->attr($this->attr);
        $this->html = $this->indent(0, "<select {$attr}>");
        
        // process the option stack
        foreach ($this->stack as $info) {
            $method = array_shift($info);
            $this->$method($info);
        }
        
        // end any open optgroup tag
        if ($this->optgroup) {
            $this->endOptgroup();
        }
        
        // close the tag, indent, and done.
        $this->html .= $this->indent(0, '</select>');
        return $this->html;
    }
    
    /**
     * 
     * Build one option.
     * 
     * @param array $info Information about the option.
     * 
     * @return void
     * 
     */
    protected function buildOption(array $info)
    {
        list($value, $label, $attr) = $info;
        
        // set the option value into the attr
        $attr['value'] = $value;
        
        // is the value selected?
        unset($attr['selected']);
        if (in_array($value, $this->selected)) {
            $attr['selected'] = 'selected';
        }
        
        // build attributes and return option tag with label text
        $attr = $this->attr($attr);
        $this->html .= $this->indent($this->optlevel, "<option {$attr}>$label</option>");
    }
    
    /**
     * 
     * Begin an optgroup.
     * 
     * @param array $info Information about the optgroup.
     * 
     * @return void
     * 
     */
    protected function beginOptgroup($info)
    {
        list($label, $attr) = $info;
        $attr['label'] = $label;
        $attr = $this->attr($attr);
        $this->html .= $this->indent(1, "<optgroup {$attr}>");
        $this->optlevel += 1;
    }
    
    /**
     * 
     * End an optgroup.
     * 
     * @return void
     * 
     */
    protected function endOptgroup()
    {
        $this->html .= $this->indent(1, "</optgroup>");
        $this->optlevel -= 1;
    }
}
