<?php


namespace Agp\Report;


use Illuminate\Database\Eloquent\Model;

class ReportField
{
    public $column;
    public $attr;
    public $callback;

    public function __construct($column, $data)
    {
        $this->column = $column;
        $this->attr = array_key_exists('attr', $data) ? $data['attr'] : [];
        $this->callback = array_key_exists('callback', $data) ? $data['callback'] : null;
    }

    public function renderField($item)
    {
        if ($this->callback) {
            $f = $this->callback;
            return $f($item);
        }
        if ($item instanceof Model)
            return $item->getAttribute($this->column->name);
        $aux = (array)$item;
        if (array_key_exists($this->column->name, $aux))
            return $aux[$this->column->name];
        return '???';
    }
}
