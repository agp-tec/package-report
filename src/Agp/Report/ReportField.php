<?php


namespace Agp\Report;


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
        return $item->{$this->column->name};
    }
}
