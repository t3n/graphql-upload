<?php

declare(strict_types=1);

namespace t3n\GraphQL\Upload\Mvc;

use Neos\Flow\Http\Component\ComponentContext;
use Neos\Flow\Http\Component\ComponentInterface;
use Neos\Flow\Http\Helper\UploadedFilesHelper;
use Neos\Flow\Mvc\Routing\RoutingComponent;
use Neos\Utility\Arrays;
use Psr\Http\Message\ServerRequestInterface;

class GraphQLUploadComponent implements ComponentInterface
{
    public function handle(ComponentContext $componentContext): void
    {
        if ($this->isGraphQLRequest($componentContext)) {
            $httpRequest = $componentContext->getHttpRequest();

            if (strpos($httpRequest->getHeader('Content-Type')[0], 'multipart/form-data') === 0) {
                $httpRequest = $this->parseUploadedFiles($httpRequest);
                $componentContext->replaceHttpRequest($httpRequest);
            }
        }
    }

    protected function parseUploadedFiles(ServerRequestInterface $request): ServerRequestInterface
    {
        $arguments = $request->getParsedBody();
        if (! is_array($arguments) || ! isset($arguments['map'])) {
            throw new \Exception('The request must define a "map"');
        }

        $map = json_decode($arguments['map'], true);
        $result = json_decode($arguments['operations'], true);

        if (isset($result['operationName'])) {
            $result['operation'] = $result['operationName'];
            unset($result['operationName']);
        }

        foreach ($map as $fileKey => $locations) {
            foreach ($locations as $location) {
                $uploadedFile = UploadedFilesHelper::upcastUploadedFiles([$request->getUploadedFiles()[$fileKey]], []);
                $result = Arrays::setValueByPath($result, $location, $uploadedFile[0]);
            }
        }

        $request = $request->withAddedHeader('Content-Type', 'application/json');
        $request = $request->withParsedBody($result);

        return $request;
    }

    protected function isGraphQLRequest(ComponentContext $componentContext): bool
    {
        $routingMatchResults = $componentContext->getParameter(RoutingComponent::class, 'matchResults') ?? [];
        $package = $routingMatchResults['@package'] ?? null;
        $controller = $routingMatchResults['@controller'] ?? null;
        $action = $routingMatchResults['@action'] ?? null;

        return $package === 't3n.GraphQL' &&
            $controller === 'GraphQL' &&
            $action === 'query';
    }
}
