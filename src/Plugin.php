<?php
declare(strict_types=1);

namespace H4ck3r31\PsalmStaticPlugin;

use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Plugin\RegistrationInterface;
use SimpleXMLElement;

class Plugin implements PluginEntryPointInterface
{
    public function __invoke(RegistrationInterface $registration, ?SimpleXMLElement $config = null): void
    {
        class_exists(SpecialClassNameAnalysisHandler::class);
        $registration->registerHooksFromClass(SpecialClassNameAnalysisHandler::class);
    }
}
