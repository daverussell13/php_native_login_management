<?php

namespace Daver\MVC\Service;

use Daver\MVC\Repository\SessionRepository;
use Daver\MVC\Domain\{
  User,
  Session
};

class SessionService
{
  public static string $COOKIE_NAME = "X-SESS-ID";

  private SessionRepository $sessionRepository;
  private UserRepository $userRepository;

  public function __construct(SessionRepository $sessionRepository, UserRepository $userRepository)
  {
    $this->sessionRepository = $sessionRepository;
    $this->userRepository = $userRepository;
  }

  public function create(string $userId): Session
  {
    $session = new Session();
    $session->setId(uniqid(more_entropy: true))
            ->setUserId($userId);

    $this->sessionRepository->save($session);
    setcookie(self::$COOKIE_NAME, $session->getId(), time() + (60 * 60 * 24), "/");

    return $session;
  }

  public function destroy(): void
  {
    $sessionId = $_COOKIE[self::$COOKIE_NAME] ?? "";

    $this->sessionRepository->deleteById($sessionId);
    setcookie(self::$COOKIE_NAME, "", 1, "/");
  }

  public function current(): ?User
  {
    $sessionId = $_COOKIE[self::$COOKIE_NAME] ?? "";
    $session = $this->sessionRepository->findById($sessionId);

    if (!$session) return null;
    return $this->userRepository->findById($session->getId());
  }
}