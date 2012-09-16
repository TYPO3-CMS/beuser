<?php
namespace TYPO3\CMS\Beuser\Domain\Repository;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Felix Kopp <felix-source@phorax.com>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * Repository for Tx_Beuser_Domain_Model_BackendUser
 *
 * @author Felix Kopp <felix-source@phorax.com>
 * @package TYPO3
 * @subpackage beuser
 */
class BackendUserRepository extends \TYPO3\CMS\Extbase\Domain\Repository\BackendUserGroupRepository {

	/**
	 * Finds Backend Users on a given list of uids
	 *
	 * @param array $uidList
	 * @return Tx_Extbase_Persistence_QueryResult<Tx_Beuser_Domain_Model_BackendUser>
	 */
	public function findByUidList($uidList) {
		$query = $this->createQuery();
		return $query->matching($query->in('uid', $uidList))->execute();
	}

	/**
	 * Find Backend Users matching to Demand object properties
	 *
	 * @param \TYPO3\CMS\Beuser\Domain\Model\Demand $demand
	 * @return Tx_Extbase_Persistence_QueryResult<Tx_Beuser_Domain_Model_BackendUser>
	 */
	public function findDemanded(\TYPO3\CMS\Beuser\Domain\Model\Demand $demand) {
		$constraints = array();
		$query = $this->createQuery();
		// Find invisible as well, but not deleted
		$constraints[] = $query->equals('deleted', 0);
		$query->setOrderings(array('userName' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING));
		// Username
		if ($demand->getUserName() !== '') {
			$constraints[] = $query->like('userName', '%' . $demand->getUserName() . '%');
		}
		// Only display admin users
		if ($demand->getUserType() == \TYPO3\CMS\Beuser\Domain\Model\Demand::USERTYPE_ADMINONLY) {
			$constraints[] = $query->equals('admin', 1);
		}
		// Only display non-admin users
		if ($demand->getUserType() == \TYPO3\CMS\Beuser\Domain\Model\Demand::USERTYPE_USERONLY) {
			$constraints[] = $query->equals('admin', 0);
		}
		// Only display active users
		if ($demand->getStatus() == \TYPO3\CMS\Beuser\Domain\Model\Demand::STATUS_ACTIVE) {
			$constraints[] = $query->equals('disable', 0);
		}
		// Only display in-active users
		if ($demand->getStatus() == \TYPO3\CMS\Beuser\Domain\Model\Demand::STATUS_INACTIVE) {
			$constraints[] = $query->logicalOr($query->equals('disable', 1));
		}
		// Not logged in before
		if ($demand->getLogins() == \TYPO3\CMS\Beuser\Domain\Model\Demand::LOGIN_NONE) {
			$constraints[] = $query->equals('lastlogin', 0);
		}
		// At least one login
		if ($demand->getLogins() == \TYPO3\CMS\Beuser\Domain\Model\Demand::LOGIN_SOME) {
			$constraints[] = $query->logicalNot($query->equals('lastlogin', 0));
		}
		// In backend user group
		// @TODO: Refactor for real n:m relations
		if ($demand->getBackendUserGroup()) {
			$constraints[] = $query->logicalOr($query->equals('usergroup', $demand->getBackendUserGroup()->getUid()), $query->like('usergroup', $demand->getBackendUserGroup()->getUid() . ',%'), $query->like('usergroup', '%,' . $demand->getBackendUserGroup()->getUid()), $query->like('usergroup', '%,' . $demand->getBackendUserGroup()->getUid() . ',%'));
			$query->contains('usergroup', $demand->getBackendUserGroup());
		}
		$query->matching($query->logicalAnd($constraints));
		return $query->execute();
	}

	/**
	 * Find Backend Users currently online
	 *
	 * @return Tx_Extbase_Persistence_QueryResult<Tx_Beuser_Domain_Model_BackendUser>
	 */
	public function findOnline() {
		$uids = array();
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('DISTINCT ses_userid', 'be_sessions', '');
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$uids[] = $row['ses_userid'];
		}
		$query = $this->createQuery();
		$query->matching($query->in('uid', $uids));
		return $query->execute();
	}

	/**
	 * Overwrite createQuery to don't respect enable fields
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryInterface
	 */
	public function createQuery() {
		$query = parent::createQuery();
		$query->getQuerySettings()->setRespectEnableFields(FALSE);
		return $query;
	}

}


?>