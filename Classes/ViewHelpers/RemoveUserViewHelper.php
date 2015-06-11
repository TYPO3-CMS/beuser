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

use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Beuser\Domain\Model\BackendUser;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;

/**
 * Displays 'Delete user' link with sprite icon to remove user
 *
 * @internal
 */
class RemoveUserViewHelper extends AbstractViewHelper implements CompilableInterface {

	/**
	 * Render link with sprite icon to remove user
	 *
	 * @param \TYPO3\CMS\Beuser\Domain\Model\BackendUser $backendUser Target backendUser to switch active session to
	 * @return string
	 */
	public function render(BackendUser $backendUser) {
		return static::renderStatic(
			array(
				'backendUser' => $backendUser
			),
			$this->buildRenderChildrenClosure(),
			$this->renderingContext
		);
	}

	/**
	 * @param array $arguments
	 * @param callable $renderChildrenClosure
	 * @param RenderingContextInterface $renderingContext
	 *
	 * @return string
	 */
	static public function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {
		/** @var \TYPO3\CMS\Beuser\Domain\Model\BackendUser $backendUser */
		$backendUser = $arguments['backendUser'];
		/** @var BackendUserAuthentication $beUser */
		$beUser = $GLOBALS['BE_USER'];
		if ($backendUser->getUid() === (int)$beUser->user['uid']) {
			return '<span class="btn btn-default disabled">' . IconUtility::getSpriteIcon('empty-empty') . '</span>';
		}

		$urlParameters = [
			'cmd[be_users][' . $backendUser->getUid() . '][delete]' => 1,
			'vC' => $beUser->veriCode(),
			'prErr' => 1,
			'uPT' => 1,
			'redirect' => GeneralUtility::getIndpEnv('REQUEST_URI')
		];
		$url = BackendUtility::getModuleUrl('tce_db', $urlParameters) . BackendUtility::getUrlToken('tceAction');

		return '<a class="btn btn-default" href="' . htmlspecialchars($url) . '"  onclick="return confirm(' .
			GeneralUtility::quoteJSvalue(LocalizationUtility::translate('confirm', 'beuser', array($backendUser->getUserName()))) .
			')">' . IconUtility::getSpriteIcon('actions-edit-delete') . '</a>';
	}

}
