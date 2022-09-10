<?php
declare(strict_types=1);

namespace H4ck3r31\PsalmStaticPlugin;

use PhpParser\NodeTraverser;
use Psalm\Plugin\EventHandler\BeforeFileAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\BeforeFileAnalysisEvent;

class SpecialClassNameAnalysisHandler implements BeforeFileAnalysisInterface
{
    public static function beforeAnalyzeFile(BeforeFileAnalysisEvent $event): void
    {
        $filePath = $event->getStatementsSource()->getFilePath();
        $statements = $event->getCodebase()->getStatementsForFile($filePath);
        // resolves special class names (`static::class`, `self::class`, `parent::class`)
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new SpecialClassNameVisitor());
        $traverser->traverse($statements);
    }
}
