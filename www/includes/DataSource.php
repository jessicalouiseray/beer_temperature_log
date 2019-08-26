<?php
declare(strict_types=1);

/**
 * @file DataSource.php
 */

/**
 * Read logged data into a structure.
 */
class DataSource
{

    private $logData;
    private $totalLines;
    private $structuredData = [];

    public function __construct($logData)
    {
        $this->logData = $logData;
        $this->totalLines = count($logData);

    }

    private function structureData()
    {
        foreach ($this->logData as $item) {
            $this->setStructuredData(str_replace("\r\n", '',
              explode(',', $item)));
        }
    }

    public function getStructuredData(): array
    {
        return $this->structuredData;
    }

    public function setStructuredData($structuredData): void
    {
        $this->structuredData[] = $structuredData;
    }

}
