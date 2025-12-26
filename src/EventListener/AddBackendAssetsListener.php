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

use Contao\CoreBundle\Routing\ScopeMatcher;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;

#[AsEventListener]
class AddBackendAssetsListener
{
    public function __construct(private readonly ScopeMatcher $scopeMatcher)
    {
    }

    public function __invoke(RequestEvent $event): void
    {
        if (!$this->scopeMatcher->isBackendMainRequest($event)) {
            return;
        }

        if ('contao_system_information' === $event->getRequest()->attributes->get('_route')) {
            $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/bweinsysteminformation/js/systemInfo.js|static';
            $GLOBALS['TL_CSS'][] = 'bundles/bweinsysteminformation/css/systemInfo.css|static';
        }
    }
}
