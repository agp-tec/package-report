<?php


namespace Agp\Report;


class ReportHeader
{
    /** Titulo da coluna
     * @var string
     */
    public $title;
    /** Descrição da coluna
     * @var string
     */
    public $desc;
    /** Tipo da coluna em arquivo Excel
     * @var string
     */
    public $excelColumnFormat;
    /** Atributos html da coluna header
     * @var array
     */
    public $attr = [];
    /** Atributos html da coluna header
     * @var ReportColumn
     */
    private $column;
    /** Define se coluna é action. Não é exportado para excel se true
     * @var bool
     */
    private $isAction;

    /**
     * @param bool $isAction
     */
    public function setIsAction(bool $isAction): void
    {
        $this->isAction = $isAction;
    }

    /**
     * @return bool
     */
    public function isAction(): bool
    {
        return $this->isAction;
    }

    public function __construct($column)
    {
        $this->column = $column;
        $this->isAction = false;
    }

    /** Retorna os atributos html
     * @return string
     */
    public function getAttrs()
    {
        $res = '';
        foreach ($this->attr as $key => $value)
            $res .= $key . '="' . $value . '"';
        return $res;
    }
}
