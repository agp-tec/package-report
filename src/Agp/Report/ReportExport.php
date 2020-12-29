<?php


namespace Agp\Report;


use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

class ReportExport implements FromArray, WithHeadings, WithColumnFormatting
{
    use Exportable;

    /**
     * @var Report
     */
    private $report;
    /**
     * @var string
     */
    private $file;

    /** Retorna a coluna no modo Excel (A, B, AB, AC, AD...)
     * @return string
     */
    private function getNameFromNumber($num)
    {
        $numeric = $num % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval($num / 26);
        if ($num2 > 0) {
            return $this->getNameFromNumber($num2 - 1) . $letter;
        } else {
            return $letter;
        }
    }

    public function __construct(Report $report)
    {
        $this->report = $report;
        $this->file = 'Report_' . date_create()->format('d-m-y_h:i:s') . '.xlsx';
    }

    public function doExport()
    {
        return Excel::download($this, $this->file);
    }

    public function headings(): array
    {
        $headers = [];
        foreach ($this->report->columns as $column) {
            if (!$column->header->isAction())
                $headers[] = $column->header->title == '' ? 'Coluna sem nome' : $column->header->title;
        }
        return $headers;
    }

    public function columnFormats(): array
    {
        $columns = array();
        foreach ($this->report->columns as $key => $column) {
            if ($column->header->excelColumnFormat)
                $columns[$this->getNameFromNumber($key)] = $column->header->excelColumnFormat;
        }
        return $columns;
    }

    public function array(): array
    {
        $items = array();
        $this->report->executaQuery(null, true);
        foreach ($this->report->items as $item) {
            $data = array();
            foreach ($this->report->columns as $column)
                if (!$column->header->isAction())
                    $data[] = $column->field->getFieldValue($item);
            $items[] = $data;
        }
        return $items;
    }
}
