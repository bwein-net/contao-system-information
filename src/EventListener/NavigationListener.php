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

use Contao\CoreBundle\Event\MenuEvent;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Adds the back end module to the "system" category of the main menu.
 *
 * Replaces the "getUserNavigation" hook, which is deprecated in Contao 6.
 */
#[AsEventListener]
class NavigationListener
{
    private const ROUTE = 'contao_system_information';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly RouterInterface $router,
        private readonly TranslatorInterface $translator,
        private readonly Security $security,
    ) {
    }

    public function __invoke(MenuEvent $event): void
    {
        $tree = $event->getTree();

        if ('mainMenu' !== $tree->getName() || !$this->security->isGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, 'system_information')) {
            return;
        }

        $categoryNode = $tree->getChild('system');

        if (null === $categoryNode) {
            return;
        }

        $label = $this->translator->trans('bwein.system_info.title');
        $isActive = self::ROUTE === $this->requestStack->getCurrentRequest()?->attributes->get('_route');

        // The core already creates a node for the BE_MOD entry (needed for the user
        // permissions), so we only point it to our route
        $node = $categoryNode->getChild('system_information') ?? $categoryNode->addChild($event->getFactory()->createItem('system_information'));

        $node
            ->setLabel($label)
            ->setUri($this->router->generate(self::ROUTE))
            ->setLinkAttribute('class', 'navigation system_information')
            ->setLinkAttribute('title', $label)
            ->setCurrent($isActive)
            ->setExtra('translation_domain', false)
        ;

        if ($isActive) {
            $categoryNode->setLinkAttribute('class', trim($categoryNode->getLinkAttribute('class').' trail'));
        }
    }
}
