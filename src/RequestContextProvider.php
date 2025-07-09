<?php

declare(strict_types=1);

namespace Ilyes512\DumpServer;

use Illuminate\Http\Request;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\ContextProvider\ContextProviderInterface;

class RequestContextProvider implements ContextProviderInterface
{
    /**
     * The current request.
     *
     * @var \Illuminate\Http\Request|null
     */
    private $currentRequest;

    /**
     * The variable cloner.
     *
     * @var \Symfony\Component\VarDumper\Cloner\VarCloner
     */
    private $varCloner;

    /**
     * RequestContextProvider constructor.
     *
     * @return void
     */
    public function __construct(?Request $currentRequest = null)
    {
        $this->currentRequest = $currentRequest;
        $this->varCloner = new VarCloner;
        $this->varCloner->setMaxItems(0);
    }

    /**
     * Get the context.
     */
    public function getContext(): ?array
    {
        if ($this->currentRequest === null) {
            return null;
        }

        $controller = null;

        if ($route = $this->currentRequest->route()) {
            $controller = $route->controller;

            if (! $controller && ! is_string($route->action['uses'])) {
                $controller = $route->action['uses'];
            }
        }

        return [
            'uri' => $this->currentRequest->getUri(),
            'method' => $this->currentRequest->getMethod(),
            'controller' => $controller ? $this->varCloner->cloneVar(class_basename($controller)) : $this->varCloner->cloneVar(null),
            'identifier' => spl_object_hash($this->currentRequest),
        ];
    }
}
