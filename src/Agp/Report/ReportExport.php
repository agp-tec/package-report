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
    /**
     * @var array
     */
    private $columnFormats;

    /**
     * @return array
     */
    public function getColumnFormats()
    {
        return $this->columnFormats;
    }

    /**
     * @param array $columnFormats
     */
    public function setColumnFormats(array $columnFormats)
    {
        $this->columnFormats = $columnFormats;
    }

    public function __construct(Report $report)
    {
        $this->columnFormats = [];
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
            $headers[] = $column->header->title == '' ? 'Coluna sem nome' : $column->header->title;
        }
        return $headers;
    }

    public function columnFormats(): array
    {
        return $this->columnFormats;
    }

    public function array(): array
    {
        $items = array();
        $this->report->executaQuery(null, true);
        foreach ($this->report->items as $item) {
            $data = array();
            foreach ($this->report->columns as $column)
                $data[] = $column->field->getFieldValue($item);
            $items[] = $data;
        }
        return $items;
    }
}
