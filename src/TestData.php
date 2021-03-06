<?php

namespace Testomatio;

use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;

class TestData implements \JsonSerializable
{
    protected $name;
    protected $suites;
    protected $line;
    protected $code;
    protected $file;
    protected $tags = [];

    public function __construct(ReflectionMethod $method)
    {
        $this->suites = [$this->humanize($method->getImplementingClass()->getName())];
        $this->name = $this->humanize($method->getName());
        $this->line = $method->getStartLine();
        $this->code = $this->formatCode($method);
        $this->file = $method->getFileName();
        $this->tags = $this->fetchTags($method);

    }

    /**
     * @return mixed
     */
    public function getTags()
    {
        return $this->tags;
    }

    private function fetchTags(ReflectionMethod $method) {
        $comment = $method->getDocComment();
        $hasGroups = preg_match_all('/@group (\w+)/', $comment, $matches);
        if (!$hasGroups) {
            return [];
        }
        return $matches[1];
    }

    private function formatCode(ReflectionMethod $method)
    {
        $source = '';
        $docBlock = $method->getDocComment();
        if ($docBlock) {
            $source = $docBlock . "\n";
        }

        $fileLines = explode("\n", $method->getLocatedSource()->getSource());
        $sourceLines = array_slice($fileLines, $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 2);

        $source .= implode("\n", $sourceLines);
        return $source;
    }

    private function humanize($name)
    {
        $name = preg_replace('/([A-Z]+)([A-Z][a-z])/', '\\1 \\2', $name);
        $name = preg_replace('/([a-z\d])([A-Z])/', '\\1 \\2', $name);
        return ucfirst($name);
    }

    public function update(\Closure $closure)
    {
        $closure->call($this);
    }


    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4
     */
    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'suites' => $this->suites,
            'line' => $this->line,
            'code' => $this->code,
            'file' => $this->file,
        ];
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getSuites()
    {
        return $this->suites;
    }

    /**
     * @return mixed
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }
}