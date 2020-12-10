<?php

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

namespace TYPO3\CMS\Beuser\Domain\Repository;

use TYPO3\CMS\Beuser\Domain\Model\BackendUser;
use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Core\Session\Backend\HashableSessionBackendInterface;
use TYPO3\CMS\Core\Session\Backend\SessionBackendInterface;
use TYPO3\CMS\Core\Session\SessionManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @internal This class is a TYPO3 Backend implementation and is not considered part of the Public TYPO3 API.
 */
class BackendUserSessionRepository
{
    protected SessionBackendInterface $sessionBackend;

    public function __construct()
    {
        $this->sessionBackend = GeneralUtility::makeInstance(SessionManager::class)->getSessionBackend('BE');
    }

    /**
     * Find all active sessions for all backend users
     *
     * @return array
     */
    public function findAllActive(): array
    {
        $allSessions = $this->sessionBackend->getAll();

        // Map array to correct keys
        $allSessions = array_map(
            function ($session) {
                return [
                    'id' => $session['ses_id'], // this is the hashed sessionId
                    'ip' => $session['ses_iplock'],
                    'timestamp' => $session['ses_tstamp'],
                    'ses_userid' => $session['ses_userid']
                ];
            },
            $allSessions
        );

        // Sort by timestamp
        usort($allSessions, function ($session1, $session2) {
            return $session1['timestamp'] <=> $session2['timestamp'];
        });

        return $allSessions;
    }

    /**
     * Find Sessions for specific BackendUser
     *
     * @param BackendUser $backendUser
     * @return array
     */
    public function findByBackendUser(BackendUser $backendUser)
    {
        $allActive = $this->findAllActive();

        return array_filter(
            $allActive,
            function ($session) use ($backendUser) {
                return (int)$session['ses_userid'] === $backendUser->getUid();
            }
        );
    }

    /**
     * Update current session to move back to the original user.
     *
     * @param AbstractUserAuthentication $userObject
     */
    public function switchBackToOriginalUser(AbstractUserAuthentication $userObject): void
    {
        $sessionObject = $userObject->getSession();
        $originalUser = (int)$sessionObject->get('backuserid');
        $sessionObject->set('backuserid', null);
        $sessionRecord = $sessionObject->toArray();
        $sessionRecord['ses_userid'] = $originalUser;
        $this->sessionBackend->update($sessionObject->getIdentifier(), $sessionRecord);
        // We must regenerate the internal session so the new ses_userid is present in the userObject
        $userObject->enforceNewSessionId();
    }

    /**
     * Update current session to move to the target user. This is done
     * by setting the target user id as ses_userid and storing the current
     * user in backuserid to restore the session record later on.
     *
     * @param AbstractUserAuthentication $userObject
     * @param int $targetUserId
     */
    public function switchToUser(AbstractUserAuthentication $userObject, int $targetUserId): void
    {
        $sessionObject = $userObject->getSession();
        $sessionObject->set('backuserid', (int)$userObject->user['uid']);
        $sessionRecord = $sessionObject->toArray();
        $sessionRecord['ses_userid'] = $targetUserId;
        $this->sessionBackend->update($sessionObject->getIdentifier(), $sessionRecord);
        // We must regenerate the internal session so the new ses_userid is present in the userObject
        $userObject->enforceNewSessionId();
    }

    public function getPersistedSessionIdentifier(AbstractUserAuthentication $userObject): string
    {
        $currentSessionId = $userObject->getSession()->getIdentifier();
        if ($this->sessionBackend instanceof HashableSessionBackendInterface) {
            $currentSessionId = $this->sessionBackend->hash($currentSessionId);
        }
        return $currentSessionId;
    }

    public function terminateSessionByIdentifier(string $sessionIdentifier): string
    {
        return $this->sessionBackend->remove($sessionIdentifier);
    }
}
