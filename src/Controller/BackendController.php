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

namespace Bwein\SystemInformation\Controller;

use Bwein\SystemInformation\Service\SystemInformationService;
use Contao\CoreBundle\Controller\AbstractBackendController;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\Message;
use Contao\System;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class BackendController extends AbstractBackendController
{
    public function __construct(
        private readonly SystemInformationService $systemInformationService,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('%contao.backend.route_prefix%/system_information', name: 'contao_system_information', defaults: ['_scope' => 'backend'])]
    public function indexAction(): Response
    {
        $this->checkPermissions();
        $parameters = [
            'title' => $this->translator->trans('bwein.system_info.title'),
            'headline' => $this->translator->trans('bwein.system_info.title'),
            'backUrl' => System::getReferer(),
            'messages' => Message::generate(),
            'systemLoadInfo' => $this->systemInformationService->getSystemLoadInfo(),
            'phpInfo' => $this->systemInformationService->getPHPInfo(),
            'osInfo' => $this->systemInformationService->getOSInfo(),
            'hostInfo' => $this->systemInformationService->getHostInfo(),
            'hardwareInfo' => $this->systemInformationService->getHardwareInfo(),
            'databaseInfo' => $this->systemInformationService->getDatabaseInfo(),
            'virtualizationInfo' => $this->systemInformationService->getVirtualizationInfo(),
        ];

        return $this->render('@BweinSystemInformation/index.html.twig', $parameters);
    }

    #[Route('%contao.backend.route_prefix%/system_information/system_load', name: 'contao_system_information.system_load', defaults: ['_scope' => 'backend'])]
    public function getSystemLoadAction(): Response
    {
        $this->checkPermissions();
        $systemLoadInfo = $this->systemInformationService->getSystemLoadInfo();

        return new JsonResponse([
            'now' => $systemLoadInfo->getLast1Minute(),
            '5min' => $systemLoadInfo->getLast5Minutes(),
            '15min' => $systemLoadInfo->getLast15Minutes(),
            'factor' => $systemLoadInfo->getFactor(),
        ]);
    }

    private function checkPermissions(): void
    {
        if (!$this->isGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, 'system_information')) {
            throw new AccessDeniedException('Not enough permissions to access system information.');
        }
    }
}
