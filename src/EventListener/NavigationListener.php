<?php

declare(strict_types=1);

/*
 * This file is part of System Information Bundle for Contao Open Source CMS.
 *
 * (c) eikona-media.de
 * (c) bwein.net
 *
 * @license MIT
 */

namespace Bwein\SystemInformation\EventListener;

use Contao\BackendUser;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Hook("getUserNavigation")
 */
class NavigationListener
{
    private RequestStack $requestStack;
    private RouterInterface $router;
    private TranslatorInterface $translator;
    private TokenStorageInterface $tokenStorage;

    public function __construct(RequestStack $requestStack, RouterInterface $router, TranslatorInterface $translator, TokenStorageInterface $tokenStorage)
    {
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->translator = $translator;
        $this->tokenStorage = $tokenStorage;
    }

    public function __invoke(array $modules, bool $showAll): array
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return $modules;
        }

        $token = $this->tokenStorage->getToken();

        if (null === $token) {
            throw new \RuntimeException('No token provided');
        }

        $user = $token->getUser();

        if (!$user instanceof BackendUser || $user->hasAccess('system_information', 'modules')) {
            $modules['system']['modules']['system_information'] = [
                'label' => $this->translator->trans('bwein.system_info.title'),
                'title' => '',
                'class' => 'navigation system_information',
                'href' => $this->router->generate('contao_system_information'),
                'isActive' => 'contao_system_information' === $request->attributes->get('_route'),
            ];
        }

        return $modules;
    }
}
