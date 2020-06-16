<?php

declare(strict_types=1);

namespace t3n\GraphQL\Upload\Resolver\Type;

use GraphQL\Error\Error;
use GraphQL\Language\AST\Node;
use Neos\Http\Factories\FlowUploadedFile;
use t3n\GraphQL\ResolverInterface;

class UploadResolver implements ResolverInterface
{
    public function parseValue(FlowUploadedFile $file): FlowUploadedFile
    {
        return $file;
    }

    /**
     * @param mixed $value
     *
     * @throws Error
     */
    public function serialize($value): void
    {
        throw new Error('`Upload` cannot be serialized');
    }

    /**
     * @throws Error
     */
    public function parseLiteral(Node $valueNode): void
    {
        throw new Error('`Upload` cannot be hardcoded in query');
    }
}
