<?php
namespace TYPO3\CMS\Beuser\ViewHelpers;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Displays a section in backend module template, similar to \TYPO3\CMS\Backend\Template\DocumentTemplate::section()
 *
 * @author Felix Kopp <felix-source@phorax.com>
 * @internal
 */
class SectionViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Display section with title and content
	 *
	 * @param string $title
	 * @param bool $collapsible
	 *
	 * @return string
	 * @see \TYPO3\CMS\Backend\Template\DocumentTemplate::section()
	 */
	public function render($title, $collapsible = FALSE) {
		if ($collapsible) {
			$uniqueId = 'section_' . md5((microtime() . rand()));
			return '<h3 class="collapsibleSection"><a href="#" onClick="$(\'' . $uniqueId . '\').toggle(); return false;">' . $title . '</a></h3>' . '<div id="' . $uniqueId . '" class="collapsibleSection">' . $this->renderChildren() . '</div>';
		}
		return '<h2>' . $title . '</h2>' . $this->renderChildren();
	}

}
