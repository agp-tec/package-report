<?php


namespace Agp\Report;


use Illuminate\Database\Eloquent\Model;

/**
 * Class ReportField
 * @package Agp\Report
 */
class ReportField
{
    /**
     * @var
     */
    public $column;
    /** Atributos html do campo (<td>)
     * @var array|mixed
     */
    public $attr;
    /** Método f($item) a ser executado no getFieldValue do campo
     * @var \Closure
     */
    public $getAttribute = null;
    /** Método f($item) a ser executado na renderização do campo
     * @var mixed|null
     */
    public $callback;

    /**
     * ReportField constructor.
     * @param $column
     * @param array $data
     */
    public function __construct($column, $data = [])
    {
        $this->column = $column;
        $this->attr = array_key_exists('attr', $data) ? $data['attr'] : [];
        $this->callback = array_key_exists('callback', $data) ? $data['callback'] : null;
    }

    public function getFieldValue($item)
    {
        if ($item instanceof Model) {
            $value = $item->getAttribute($this->column->name);
        } else {
            $aux = (array)$item;
            if (array_key_exists($this->column->name, $aux))
                $value = $aux[$this->column->name];
            else
                $value = '???';
        }

        $a = $this->getAttribute;
        if ($a)
            return $a($value);
        return $value;
    }

    /**
     * @param $item
     * @return mixed|string
     */
    public function renderField($item)
    {
        if ($this->callback) {
            $f = $this->callback;
            return $f($item);
        }
        return $this->getFieldValue($item);
    }

    /** Retorna os atributos html
     * @return string
     */
    public function getAttrs()
    {
        $res = '';
        foreach ($this->attr as $key => $value)
            $res .= $key . "='" . $value . "' ";
        return $res;
    }
}
