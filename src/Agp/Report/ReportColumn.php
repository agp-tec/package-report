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
    /** Dados para filtro da coluna
     * @var ReportHeader
     */
    public $header;
    /** Indica o calculo de total da coluna
     * @var ReportTotalizador
     */
    public $totalizador = null;
    /** Indica o calculo de total da coluna
     * @var ReportField
     */
    public $field;

    /**
     * ReportColumn constructor. $data contém os atributos do objeto como name, title, desc, attr
     * @param string $name
     */
    public function __construct($name = null)
    {
        $this->header = new ReportHeader($this);
        $this->filter = new ReportFilter($this);
        $this->totalizador = new ReportTotalizador($this);
        $this->field = new ReportField($this);
        $this->name = $name;
    }

    /**
     * @param string $title Titulo da coluna
     * @return ReportColumn
     */
    public function setTitle($title)
    {
        $this->header->title = $title;
        return $this;
    }

    /**
     * @param string $tipo Tipo de dado (int, string, datetime, etc)
     * @param string|array $metodo Metodo de filtro (=,>=,<=,like,between, etc) ou opcoes do choice
     * @return ReportColumn
     */
    public function setFilter($tipo, $metodo)
    {
        $this->filter->set($tipo, $metodo);
        return $this;
    }
}
