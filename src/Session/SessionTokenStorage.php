<?php

namespace SA\Form\Session;

use Illuminate\Session\Store;
use Symfony\Component\Security\Csrf\Exception\TokenNotFoundException;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

/**
 * Class SessionStorage
 * @package SA\Form\Session
 */
class SessionTokenStorage implements TokenStorageInterface
{
    private const TOKEN_ID = '_token';

    /**
     * @var Store
     */
    private $store;

    /**
     * SessionStorage constructor.
     * @param Store $store
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    /**
     * @inheritDoc
     */
    public function getToken(string $tokenId)
    {
        if (!$this->store->isStarted()) {
            $this->store->start();
        }

        if (!$this->store->has(self::TOKEN_ID)) {
            throw new TokenNotFoundException('The CSRF token with ID ' . self::TOKEN_ID . ' does not exist.');
        }

        return (string) $this->store->get(self::TOKEN_ID);
    }

    /**
     * @inheritDoc
     */
    public function setToken(string $tokenId, string $token)
    {
        if (!$this->store->isStarted()) {
            $this->store->start();
        }

        $this->store->put(self::TOKEN_ID, $token);
    }

    /**
     * @inheritDoc
     */
    public function removeToken(string $tokenId)
    {
        if (!$this->store->isStarted()) {
            $this->store->start();
        }

        return (string) $this->store->pull(self::TOKEN_ID, null);
    }

    /**
     * @inheritDoc
     */
    public function hasToken(string $tokenId)
    {
        if (!$this->store->isStarted()) {
            $this->store->start();
        }

        return $this->store->has(self::TOKEN_ID);
    }
}
