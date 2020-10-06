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
    /** Atributos html da coluna header
     * @var array
     */
    public $attr = [];
    /** Atributos html da coluna header
     * @var ReportColumn
     */
    private $column;

    public function __construct($column)
    {
        $this->column = $column;
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
