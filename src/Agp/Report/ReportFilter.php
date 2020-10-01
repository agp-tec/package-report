<?php


namespace Agp\Report;


class ReportFilter
{
    /** Tipo do campo (int, string, date, etc)
     * @var string
     */
    public $tipo;
    /** Metodo de filtro (=, between, like)
     * @var string
     */
    public $metodo;
    /** Direção do orderby atual
     * @var string
     */
    public $order;
    /**
     * @var ReportColumn
     */
    private $column;

    /**
     * ReportFilter constructor.
     * @param $column
     */
    public function __construct($column)
    {
        $this->column = $column;
    }

    /** Renderiza input
     * @return string
     */
    public function renderInput()
    {
        $inputName = 'query[' . $this->column->name . ']';
        switch ($this->tipo) {
            case 'int':
                return '<input class="form-control" type="number" name="' . $inputName . '" value="' . request()->input('query.' . $this->column->name) . '">';
            case 'bool':
                return '<input class="form-control" type="checkbox" name="' . $inputName . '" value="' . request()->input('query.' . $this->column->name) . '">';
            case 'date':
            case 'datetime':
                $inputValue = request()->input('query.' . $this->column->name);
                if ($this->metodo == 'between') {
                    return '<div class="input-daterange input-group datepicker">
                            <input type="text" class="form-control" name="' . $inputName . '[start]" value="' . ($inputValue ? $inputValue['start'] : '') . '">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="la la-ellipsis-h"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control" name="query[' . $this->column->name . '][end]" value="' . ($inputValue ? $inputValue['end'] : '') . '">
                        </div>';
//                    return '<input class="form-control" type="text" name="' . $inputName . '[start]" value="' . ($inputValue ? $inputValue['start'] : '') . '">' .
//                        '<input class="form-control" type="text" name="query[' . $this->column->name . '][end]" value="' . ($inputValue ? $inputValue['end'] : '') . '">';
                }
                return '<input class="form-control" type="text" name="' . $inputName . '" value="' . request()->input('query.' . $this->column->name) . '">';
            default:
                return '<input class="form-control" type="text" name="' . $inputName . '" value="' . request()->input('query.' . $this->column->name) . '">';
        }
    }

    /**
     * @param string $tipo
     * @param string $metodo
     */
    public function set(string $tipo, string $metodo)
    {
        $this->tipo = $tipo;
        $this->metodo = $metodo;
    }

    public function getOrderByUrl($params)
    {
        $params['order'][$this->column->name] = $this->order == 'asc' ? 'desc' : 'asc';
        return http_build_query($params);
    }
}
