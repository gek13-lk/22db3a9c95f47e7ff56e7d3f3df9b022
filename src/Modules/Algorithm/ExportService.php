<?php

declare(strict_types=1);

namespace App\Modules\Algorithm;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportService
{
    public function exportXlsx(array $data)
    {
        $border = [
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];

        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();
        $row = 1;
        foreach ($data as $rows) {
            $column = 1;
            foreach ($rows as $columns) {
                $sheet->setCellValue([$column, $row], $columns);
                $sheet->getStyle([$column, $row])->applyFromArray($border);
                $column++;
            }
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'template.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        $writer->save($temp_file);

        return $temp_file;
    }

    public function exportCsv(array $data)
    {
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();
        $row = 1;
        foreach ($data as $rows) {
            $column = 1;
            foreach ($rows as $columns) {
                $sheet->setCellValue([$column, $row], $columns);
                $column++;
            }
            $row++;
        }

        $writer = new Csv($spreadsheet);
        $writer->setSheetIndex(0);
        $writer->setUseBOM(true);

        $fileName = 'template.csv';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        $writer->save($temp_file);

        return $temp_file;
    }
}