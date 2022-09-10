<?php
declare(strict_types=1);

namespace H4ck3r31\PsalmStaticPlugin;

use PhpParser\ErrorHandler\Throwing;
use PhpParser\NameContext;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitorAbstract;

/**
 * Resolves and updates `resolvedName` node attribute for special class constants
 * `self::class`, `static::class` and `parent::class`
 */
class SpecialClassNameVisitor extends NodeVisitorAbstract
{
    private NameContext $nameContext;
    private ?Class_ $classContext = null;

    public function __construct()
    {
        $this->nameContext = new NameContext(new Throwing());
    }

    public function beforeTraverse(array $nodes): ?array
    {
        $this->nameContext->startNamespace();
        return null;
    }

    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof Namespace_) {
            $this->nameContext->startNamespace($node->name);
        } elseif ($node instanceof Class_ && $node->name !== null) {
            $this->classContext = $node;
            $this->addNamespacedName($node);
        } elseif ($node instanceof ClassConstFetch
            && $this->classContext !== null
            && $node->class instanceof Name
            && $node->class->isSpecialClassName()
        ) {
            $resoledName = null;
            $currentName = $node->class->getAttribute('resolvedName')
                ?? $node->class->toString();
            if ($currentName === 'parent') {
                $resoledName = $this->classContext->extends->getAttribute('resolvedName')
                    ?? $this->classContext->extends->toString();
            } elseif ($currentName === 'self' || $currentName === 'static') {
                $resoledName = $this->classContext->namespacedName->toString();
            }
            if ($resoledName !== null) {
                $node->class->setAttribute('resolvedName', $resoledName);
            }
        }
        return null;
    }

    public function leaveNode(Node $node): ?Node
    {
        if ($node instanceof Class_) {
            $this->classContext = null;
        }
        return null;
    }

    private function addNamespacedName(ClassLike $node): void {
        if ($node->namespacedName !== null) {
            return;
        }
        $node->namespacedName = Name::concat(
            $this->nameContext->getNamespace(), (string) $node->name);
    }
}
