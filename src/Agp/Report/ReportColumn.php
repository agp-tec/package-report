<?php


namespace Agp\Report;

use Closure;

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
    /** Visibilidade da coluna
     * @var bool
     */
    public $visible = true;
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
     * @var string
     */
    public $alias;
    /**
     * @var string
     */
    public $orderby;
    /** Valor bruto do select (sum, count, date_format, etc). Caso utilize, precisa conter alias para nome da coluna. Ex: sum(valor) as soma
     * @var string
     */
    public $raw;

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
     * @param string $asc_desc
     * @return ReportColumn
     */
    public function setDefaultOrderBy($asc_desc)
    {
        $this->orderby = $asc_desc;
        return $this;
    }

    /**
     * @param string $raw Valor bruto do select (sum, count, date_format, etc)
     * @return ReportColumn
     */
    public function setRaw($raw)
    {
        $this->raw = $raw;
        return $this;
    }

    /**
     * @param bool $visible
     * @return ReportColumn
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;
        return $this;
    }

    /**
     * @param string|Closure $actions
     * @return ReportColumn
     */
    public function setActions($actions)
    {
        $this->field->setActions($actions);
        $this->header->setIsAction(true);
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

    /**
     * @param Closure $closure
     * @return ReportColumn
     */
    public function setFieldCallback(Closure $closure)
    {
        $this->field->callback = $closure;
        return $this;
    }

    /**
     * @param Closure $closure
     * @return ReportColumn
     */
    public function setFieldAttribute(Closure $closure)
    {
        $this->field->getAttribute = $closure;
        return $this;
    }

    /**
     * @param array $attrs
     * @return ReportColumn
     */
    public function setHeaderAttrs($attrs)
    {
        $this->header->attr = $attrs;
        return $this;
    }

    /**
     * @param array $attrs
     * @return ReportColumn
     */
    public function setFieldAttrs($attrs)
    {
        $this->field->attr = $attrs;
        return $this;
    }
}
