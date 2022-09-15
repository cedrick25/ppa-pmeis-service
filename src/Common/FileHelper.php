<?php

namespace App\Common;

class FileHelper
{
    public function arrayToCSV(array $items): array
    {
        $data = [];

        $isFirstIteration = true;
        foreach ($items as $item) {
            $actionDetails = json_decode($item['action_details'], true);

            if ($isFirstIteration) {
                $data[] = $this->getHeaders($item, $actionDetails);

                $isFirstIteration = false;
            }

            $data[] = $this->getValues($item, $actionDetails);
        }

        return $data;
    }

    private function getHeaders(array $item, array $actionDetails): array
    {
        $headers = [];

        foreach ($item as $key=>$value) {
            $headers[] = $key;
        }

        foreach ($actionDetails as $key=>$value) {
            $headers[] = $key;
        }

        return $headers;
    }

    private function getValues(array $item, array $actionDetails): array
    {
        $values = [];

        foreach ($item as $value) {
            $values[] = $value;
        }

        foreach ($actionDetails as $key=>$value) {
            $values[] = $value;
        }

        return $values;
    }
}