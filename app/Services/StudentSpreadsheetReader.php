<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class StudentSpreadsheetReader
{
    public function read(UploadedFile $file): array
    {
        return strtolower($file->getClientOriginalExtension()) === 'csv'
            ? $this->readCsv($file->getRealPath())
            : $this->readXlsx($file->getRealPath());
    }

    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'rb');
        if (! $handle) {
            throw new RuntimeException('Không thể đọc tệp đã tải lên.');
        }

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = array_map(fn ($value) => trim((string) $value), $row);
        }
        fclose($handle);
        if (isset($rows[0][0])) {
            $rows[0][0] = preg_replace('/^\xEF\xBB\xBF/', '', $rows[0][0]);
        }

        return $rows;
    }

    private function readXlsx(string $path): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Máy chủ chưa bật PHP Zip để đọc XLSX. Hãy dùng tệp CSV.');
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Tệp XLSX không hợp lệ hoặc đã bị hỏng.');
        }

        $sharedStrings = [];
        if (($xml = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
            $shared = simplexml_load_string($xml);
            foreach ($shared->si as $item) {
                $texts = $item->xpath('.//t');
                $sharedStrings[] = implode('', array_map(fn ($text) => (string) $text, $texts));
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        if ($sheetXml === false) {
            throw new RuntimeException('Không tìm thấy trang tính đầu tiên trong tệp XLSX.');
        }

        $sheet = simplexml_load_string($sheetXml);
        $rows = [];
        foreach ($sheet->sheetData->row as $row) {
            $values = [];
            foreach ($row->c as $cell) {
                $column = $this->columnIndex((string) $cell['r']);
                $type = (string) $cell['t'];
                $value = (string) $cell->v;
                if ($type === 's') {
                    $value = $sharedStrings[(int) $value] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = implode('', array_map(fn (SimpleXMLElement $text) => (string) $text, $cell->xpath('.//t')));
                }
                $values[$column] = trim($value);
            }
            if ($values !== []) {
                $rows[] = array_map(fn ($i) => $values[$i] ?? '', range(0, max(array_keys($values))));
            }
        }

        return $rows;
    }

    private function columnIndex(string $reference): int
    {
        preg_match('/^[A-Z]+/', strtoupper($reference), $matches);
        $index = 0;
        foreach (str_split($matches[0] ?? 'A') as $letter) {
            $index = ($index * 26) + ord($letter) - 64;
        }

        return $index - 1;
    }
}
