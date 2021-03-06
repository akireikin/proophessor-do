<?php
/**
 * This file is part of prooph/proophessor-do.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Prooph\ProophessorDo\Model\User\Handler;

use Prooph\ProophessorDo\Model\User\Command\RegisterUser;
use Prooph\ProophessorDo\Model\User\Exception\UserAlreadyExists;
use Prooph\ProophessorDo\Model\User\Exception\UserNotFound;
use Prooph\ProophessorDo\Model\User\Service\ChecksUniqueUsersEmailAddress;
use Prooph\ProophessorDo\Model\User\User;
use Prooph\ProophessorDo\Model\User\UserCollection;

/**
 * Class RegisterUserHandler
 *
 * @package Prooph\ProophessorDo\Model\User\Handler
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class RegisterUserHandler
{
    /**
     * @var UserCollection
     */
    private $userCollection;

    /**
     * @var ChecksUniqueUsersEmailAddress
     */
    private $checksUniqueUsersEmailAddress;

    /**
     * @param UserCollection $userCollection
     * @param ChecksUniqueUsersEmailAddress $checksUniqueUsersEmailAddress
     */
    public function __construct(
        UserCollection $userCollection,
        ChecksUniqueUsersEmailAddress $checksUniqueUsersEmailAddress
    ) {
        $this->userCollection = $userCollection;
        $this->checksUniqueUsersEmailAddress = $checksUniqueUsersEmailAddress;
    }

    /**
     * @param RegisterUser $command
     */
    public function __invoke(RegisterUser $command)
    {
        $userId = call_user_func($this->checksUniqueUsersEmailAddress, $command->emailAddress());

        if ($userId) {
            if (!$user = $this->userCollection->get($userId)) {
                throw UserNotFound::withUserId($userId);
            }

            $user->registerAgain($command->name());

            return;
        }

        if ($user = $this->userCollection->get($command->userId())) {
            throw UserAlreadyExists::withUserId($command->userId());
        }

        $user = User::registerWithData($command->userId(), $command->name(), $command->emailAddress());

        $this->userCollection->add($user);
    }
}
