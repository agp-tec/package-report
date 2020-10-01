<?php


namespace Agp\Report;


use Closure;

class ReportRow
{
    /** Atributor html do campo
     * @var array
     */
    protected $fieldAttr = [];
    /** atributos html da linha
     * @var array
     */
    protected $rowAttr = [];
    /** Metodo callback
     * @var Closure
     */
    protected $callback;

    /**
     * @param string $key
     * @param $value
     * @return $this
     */
    public function set($key, $value)
    {
        $this->$key = $value;
        return $this;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->$key;
    }

}
