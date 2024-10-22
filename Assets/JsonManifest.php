<?php

namespace Roots\Sage\Assets;

/**
 * Class JsonManifest
 * @package Roots\Sage
 * @author QWp6t
 */
class JsonManifest implements ManifestInterface
{
    /** @var array */
    public $manifest;

    /** @var string */
    public $dist;

    /**
     * JsonManifest constructor
     *
     * @param string $manifestPath Local filesystem path to JSON-encoded manifest
     * @param string $distUri Remote URI to assets root
     */
    public function __construct(string $manifestPath, string $distUri)
    {
        $this->manifest = [];
        $this->dist     = $distUri;

        if (file_exists($manifestPath)) {
            $this->manifest = json_decode(file_get_contents($manifestPath), true);
        }
    }

    /** @inheritdoc */
    public function get(string $asset): string
    {
        return $this->manifest[$asset] ?? $asset;
    }

    /** @inheritdoc */
    public function getUri(string $asset): string
    {
        return "{$this->dist}/{$this->get($asset)}";
    }
}
