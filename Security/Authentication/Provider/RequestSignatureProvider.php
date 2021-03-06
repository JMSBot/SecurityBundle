<?php

namespace Rezzza\SecurityBundle\Security\Authentication\Provider;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\NonceExpiredException;
use Rezzza\SecurityBundle\Security\RequestSignatureToken;
use Rezzza\SecurityBundle\Security\RequestSignature\RequestSignatureBuilder;
use Rezzza\SecurityBundle\Security\Firewall\RequestSignatureEntryPoint;

/**
 * RequestSignatureProvider
 *
 * @uses AuthenticationProviderInterface
 * @author Stephane PY <py.stephane1@gmail.com>
 */
class RequestSignatureProvider implements AuthenticationProviderInterface
{
    /**
     * @var RequestSignatureBuilder
     */
    private $builder;

    /**
     * @var RequestSignatureEntryPoint
     */
    private $entryPoint;

    /**
     * @param RequestSignatureBuilder    $builder    builder
     * @param RequestSignatureEntryPoint $entryPoint entryPoint
     */
    public function __construct(RequestSignatureBuilder $builder, RequestSignatureEntryPoint $entryPoint)
    {
        $this->builder    = $builder;
        $this->entryPoint = $entryPoint;
    }

    /**
     * @param TokenInterface $token token
     *
     * @throws AuthenticationException
     * @return TokenInterface
     */
    public function authenticate(TokenInterface $token)
    {
        $this->builder->build($token, $this->entryPoint);

        if (!$this->builder->signatureEquals($token)) {
            throw new AuthenticationException('Invalid signature');
        }

        if ($this->entryPoint->get('replay_protection') && $this->builder->hasExpired($token)) {
            throw new NonceExpiredException('Signature has expired');
        }

        return $token;
    }

    /**
     * @param TokenInterface $token token
     *
     * @return boolean
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof RequestSignatureToken;
    }
}
