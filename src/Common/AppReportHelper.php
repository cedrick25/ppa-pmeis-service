<?php

namespace App\Common;

class AppReportHelper
{

    public function buildCoordinate(string $coordinate, int $lastFilledOutCellY): string
    {
        if (str_contains($coordinate, ':')) {
            $coordinate = explode(':', $coordinate);

            return $this->getRowAndColumn($coordinate[0], $lastFilledOutCellY) . ':' . $this->getRowAndColumn($coordinate[1], $lastFilledOutCellY);
        }

        return $this->getRowAndColumn($coordinate, $lastFilledOutCellY);
    }

    /**
     * @param string $coordinate
     * @param int $lastFilledOutCellY
     * @return string
     */
    private function getRowAndColumn(string $coordinate, int $lastFilledOutCellY): string
    {
        $coordinate = explode('_', $coordinate);

        return $coordinate[0] . $lastFilledOutCellY + intval($coordinate[1]);
    }

}