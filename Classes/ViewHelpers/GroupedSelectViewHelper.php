<?php
namespace Neos\GoogleAnalytics\ViewHelpers;

/*
 * This file is part of the Neos.GoogleAnalytics package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\FluidAdaptor;
use Neos\FluidAdaptor\Core\ViewHelper;
use Traversable;

/**
 * An extended select view helper to handle optgroups
 *
 * The options have to be in a prepared format for this to work.
 */
class GroupedSelectViewHelper extends FluidAdaptor\ViewHelpers\Form\SelectViewHelper
{
    /**
     * Render the option tags.
     *
     * @return array an associative array of options, key will be the value of the option tag
     */
    protected function getOptions(): array
    {
        if (!is_array($this->arguments['options']) && !($this->arguments['options'] instanceof Traversable)) {
            return [];
        }

        return $this->arguments['options'];
    }

    /**
     * Render the option tags.
     *
     * @param array $options the options for the form.
     * @return string rendered tags.
     * @throws FluidAdaptor\Exception
     * @throws ViewHelper\Exception
     */
    protected function renderOptionTags($options): string
    {
        $output = '';
        if ($this->hasArgument('prependOptionLabel')) {
            $value = $this->hasArgument('prependOptionValue') ? $this->arguments['prependOptionValue'] : '';
            if ($this->hasArgument('translate')) {
                $label = $this->getTranslatedLabel($value, $this->arguments['prependOptionLabel']);
            } else {
                $label = $this->arguments['prependOptionLabel'];
            }
            $output .= $this->renderOptionTag($value, $label) . chr(10);
        }
        $output .= $this->renderOptionTagsInternal($options);

        return $output;
    }

    /**
     * @param array $options
     * @return string
     */
    protected function renderOptionTagsInternal(array $options): string
    {
        $output = '';
        foreach ($options as $suboptions) {
            if (isset($suboptions['label']) && isset($suboptions['items'])) {
                $output .= '<optgroup label="' . htmlspecialchars($suboptions['label']) . '">' . chr(10);
                $output .= $this->renderOptionTagsInternal($suboptions['items']);
                $output .= '</optgroup>' . chr(10);
            } elseif (isset($suboptions['label']) && isset($suboptions['value'])) {
                $output .= $this->renderOptionTag($suboptions['value'], $suboptions['label']) . chr(10);
            }
        }

        return $output;
    }

    /**
     * Render one option tag
     *
     * @param string $value value attribute of the option tag (will be escaped)
     * @param string $label content of the option tag (will be escaped)
     * @return string the rendered option tag
     */
    protected function renderOptionTag($value, $label): string
    {
        $output = '<option value="' . htmlspecialchars($value) . '"';
        if ($this->isSelected($value)) {
            $output .= ' selected="selected"';
        }

        $output .= '>' . htmlspecialchars($label) . '</option>';

        return $output;
    }
}
