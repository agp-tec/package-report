<?php


namespace Agp\Report;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ReportTotalizador
 * Contem as colunas para realizar o totalizador
 * @package App\Helper
 */
class ReportTotalizador
{
    /** Metodo de totalização = 'count','sum','avg'
     * @var string
     */
    public $metodo;
    /** Resultado da totalização
     * @var float|int
     */
    private $valor;
    /** Variavel auxiliar contadoa para realizar avg
     * @var int
     */
    private $count;

    /**
     * ReportTotalizador constructor.
     * @param ReportColumn $column
     */
    public function __construct($column)
    {
        $this->column = $column;
        $this->metodo = '';
        $this->valor = 0;
        $this->count = 0;
    }

    /**
     * @return float|int
     */
    public function getValor()
    {
        if ($this->metodo == 'avg')
            return $this->valor / $this->count;
        return $this->valor;
    }

    /**
     * Limpa valores do totalizador
     */
    public function clear()
    {
        $this->valor = 0;
        $this->count = 0;
    }

    /** Realiza totalização do item
     * @param $item
     */
    public function append($item)
    {
        $this->count++;
        switch ($this->metodo) {
            case 'count':
                $this->valor += 1;
                break;
            case 'avg':
            case 'sum':
                if ($item instanceof Model)
                    $this->valor += $item->getAttribute($this->column->name);
                else {
                    $aux = (array)$item;
                    if (array_key_exists($this->column->name, $aux))
                        $this->valor += $aux[$this->column->name];
                    break;
                }
        }
    }
}
