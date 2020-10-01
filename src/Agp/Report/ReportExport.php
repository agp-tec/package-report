<?php


namespace Agp\Report;


use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Facades\Excel;

class ReportExport implements FromCollection
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

    public function __construct(Report $report)
    {
        $this->report = $report;
        $this->file = 'Report_' . date_create()->format('d-m-y_h:i:s') . '.xlsx';
    }

    public function collection()
    {
        $this->report->executaQuery(null, true);
        return $this->report->items;
    }

    public function doExport()
    {
        return Excel::download($this, $this->file);
    }
}
