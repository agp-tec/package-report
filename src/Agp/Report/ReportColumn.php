<?php


namespace Agp\Report;

/**
 * Class ReportColumn
 * Contem os atributos das colunas
 * @package App\Helper
 */
class ReportColumn
{
    /** Nome da coluna
     * @var
     */
    public $name;
    /** Dados para filtro da coluna
     * @var ReportFilter
     */
    public $filter;
    /** Titulo da coluna
     * @var
     */
    public $title;
    /** Descrição da coluna
     * @var
     */
    public $desc;
    /** Atributos html da coluna header
     * @var array
     */
    public $attr = [];
    /** Indica o calculo de total da coluna
     * @var ReportTotalizador
     */
    public $totalizador = null;

    /**
     * ReportColumn constructor. $data contém os atributos do objeto como name, title, desc, attr
     * @param $data
     */
    public function __construct($data)
    {
        $this->filter = new ReportFilter($this);
        if (is_array($data))
            foreach ($data as $key => $value)
                $this->$key = $value;
    }
}
