<?php

declare(strict_types=1);

namespace CodeRhapsodie\Bundle\ConnectorGemini\Installer;

use Ibexa\Installer\Provisioner\AbstractMigrationProvisioner;

final class InstallerProvisioner extends AbstractMigrationProvisioner
{
    protected function getMigrationFiles(): array
    {
        return [
            '2025_06_09_08_44_action_configuration.yaml' => 'action_configurations.yaml',
        ];
    }

    protected function getMigrationDirectory(): string
    {
        return '@CodeRhapsodie/Bundle/ConnectorGemini/Resources/migrations';
    }
}
