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

namespace Bwein\SystemInformation\Service;

use Bwein\SystemInformation\Service\InfoObjects\DatabaseInfo;
use Bwein\SystemInformation\Service\InfoObjects\HardwareInfo;
use Bwein\SystemInformation\Service\InfoObjects\HostInfo;
use Bwein\SystemInformation\Service\InfoObjects\OSInfo;
use Bwein\SystemInformation\Service\InfoObjects\PHPInfo;
use Bwein\SystemInformation\Service\InfoObjects\SystemLoadInfo;
use Bwein\SystemInformation\Service\InfoObjects\VirtualizationInfo;

/**
 * Class SystemInformationService.
 */
class SystemInformationService
{
    private SystemLoadInfo $systemLoadInfo;
    private HostInfo $hostInfo;
    private DatabaseInfo $databaseInfo;
    private PHPInfo $PHPInfo;
    private OSInfo $OSInfo;
    private HardwareInfo $hardwareInfo;
    private VirtualizationInfo $virtualizationInfo;

    public function __construct(SystemLoadInfo $systemLoadInfo, HostInfo $hostInfo, DatabaseInfo $databaseInfo, PHPInfo $PHPInfo, OSInfo $OSInfo, HardwareInfo $hardwareInfo, VirtualizationInfo $virtualizationInfo)
    {
        $this->systemLoadInfo = $systemLoadInfo;
        $this->hostInfo = $hostInfo;
        $this->databaseInfo = $databaseInfo;
        $this->PHPInfo = $PHPInfo;
        $this->OSInfo = $OSInfo;
        $this->hardwareInfo = $hardwareInfo;
        $this->virtualizationInfo = $virtualizationInfo;
    }

    public function getSystemLoadInfo(): SystemLoadInfo
    {
        return $this->systemLoadInfo->init();
    }

    public function getHostInfo(): HostInfo
    {
        return $this->hostInfo->init();
    }

    public function getDatabaseInfo(): DatabaseInfo
    {
        return $this->databaseInfo->init();
    }

    public function getPHPInfo(): PHPInfo
    {
        return $this->PHPInfo->init();
    }

    public function getOSInfo(): OSInfo
    {
        return $this->OSInfo->init();
    }

    public function getHardwareInfo(): HardwareInfo
    {
        return $this->hardwareInfo->init();
    }

    public function getVirtualizationInfo(): VirtualizationInfo
    {
        return $this->virtualizationInfo->init();
    }
}
