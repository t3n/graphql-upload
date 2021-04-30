<?php

declare(strict_types=1);

namespace t3n\GraphQL\Upload\Http;

use Neos\Flow\Http\Helper\UploadedFilesHelper;
use Neos\Utility\Arrays;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GraphQLUploadMiddleware implements MiddlewareInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->isGraphQLRequest($request) && strpos($request->getHeader('Content-Type')[0], 'multipart/form-data') === 0) {
            $request = $this->parseUploadedFiles($request);
        }
        return $handler->handle($request);
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

    protected function isGraphQLRequest(ServerRequestInterface $request): bool
    {
        $routingMatchResults = $request->getAttribute('routingResults') ?? [];
        $package = $routingMatchResults['@package'] ?? null;
        $controller = $routingMatchResults['@controller'] ?? null;
        $action = $routingMatchResults['@action'] ?? null;

        return $package === 't3n.GraphQL' &&
            $controller === 'GraphQL' &&
            $action === 'query';
    }
}
