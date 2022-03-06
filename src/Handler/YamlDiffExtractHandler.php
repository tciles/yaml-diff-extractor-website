<?php

declare(strict_types=1);

namespace App\Handler;

use SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class YamlDiffExtractHandler
{
    /**
     * @var string
     */
    private string $storageDir;

    /**
     * @param string $storageDir
     */
    public function __construct(string $storageDir)
    {
        $this->storageDir = $storageDir;

        if (!is_dir($storageDir)) {
            mkdir($storageDir);
        }
    }

    /**
     * @param string $sourceA
     * @param string $sourceB
     * @return SplFileInfo
     */
    public function extract(string $sourceA, string $sourceB): SplFileInfo
    {
        $sourceAValues = Yaml::parseFile($sourceA);
        $sourceBValues = Yaml::parseFile($sourceB);

        $diff = [];
        self::compareArrayValues($sourceAValues, $sourceBValues, $diff);
        self::compareArrayValues($sourceBValues, $sourceAValues, $diff);

        unset($sourceValues, $extraValues);

        $filename = $this->storageDir . DIRECTORY_SEPARATOR . md5((string) microtime(true)) . '-diff.yaml';
        file_put_contents($filename, Yaml::dump($diff, 16));
        unset($diff);

        return new SplFileInfo($filename);
    }

    /**
     * @param array $arrayOne
     * @param array $arrayTwo
     * @param array $diff
     */
    private function compareArrayValues(array $arrayOne, array $arrayTwo, array &$diff = []): void
    {
        foreach ($arrayOne as $key => $val) {
            if (!isset($arrayTwo[$key])) {
                $diff[$key] = $val;
                continue;
            }

            if (is_array($val) &&
                $this->getHash($val) !== $this->getHash($arrayTwo[$key])
            ) {
                if (!isset($diff[$key])) {
                    $diff[$key] = [];
                }

                $this->compareArrayValues($val, $arrayTwo[$key], $diff[$key]);
                continue;
            }

            if ($val === $arrayTwo[$key]) {
                continue;
            }

            $diff[$key] = $val;
        }
    }

    /**
     * @param array $values
     * @return string
     */
    private function getHash(array $values): string
    {
        return md5(serialize($values));
    }
}