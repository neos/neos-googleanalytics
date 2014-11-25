<?php
namespace TYPO3\Neos\GoogleAnalytics\ViewHelpers;

/*                                                                            *
 * This script belongs to the TYPO3 Flow package "TYPO3.Neos.GoogleAnalytics" *
 *                                                                            *
 * It is free software; you can redistribute it and/or modify it under        *
 * the terms of the GNU General Public License, either version 3 of the       *
 * License, or (at your option) any later version.                            *
 *                                                                            *
 * The TYPO3 project - inspiring people to share!                             *
 *                                                                            */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Fluid;
use TYPO3\Fluid\Core\ViewHelper;

/**
 * An extended select view helper to handle optgroups
 *
 * The options have to be in a prepared format for this to work.
 */
class GroupedSelectViewHelper extends Fluid\ViewHelpers\Form\SelectViewHelper {

	/**
	 * Render the option tags.
	 *
	 * @return array an associative array of options, key will be the value of the option tag
	 * @throws ViewHelper\Exception
	 */
	protected function getOptions() {
		if (!is_array($this->arguments['options']) && !($this->arguments['options'] instanceof \Traversable)) {
			return array();
		}
		return $this->arguments['options'];
	}

	/**
	 * Render the option tags.
	 *
	 * @param array $options the options for the form.
	 * @return string rendered tags.
	 */
	protected function renderOptionTags($options) {
		$output = '';
		if ($this->hasArgument('prependOptionLabel')) {
			$value = $this->hasArgument('prependOptionValue') ? $this->arguments['prependOptionValue'] : '';
			if ($this->hasArgument('translate')) {
				$label = $this->getTranslatedLabel($value, $this->arguments['prependOptionLabel']);
			} else {
				$label = $this->arguments['prependOptionLabel'];
			}
			$output .= $this->renderOptionTag($value, $label, FALSE) . chr(10);
		}
		$output .= $this->renderOptionTagsInternal($options);
		return $output;
	}

	/**
	 * @param array $options
	 * @return string
	 */
	protected function renderOptionTagsInternal($options) {
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
	protected function renderOptionTag($value, $label) {
		$output = '<option value="' . htmlspecialchars($value) . '"';
		if ($this->isSelected($value)) {
			$output .= ' selected="selected"';
		}

		$output .= '>' . htmlspecialchars($label) . '</option>';

		return $output;
	}

}
