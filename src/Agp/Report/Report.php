<?php


namespace Agp\Report;


use Closure;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class Report
{

    /** Colunas do relatório
     * @var Collection
     */
    public $columns;
    /** Resultado da query
     * @var LengthAwarePaginator
     */
    public $items;
    /** Metodo que executa o select
     * @return Builder
     * @var Closure|Builder
     */
    protected $queryBuilder;
    /** Metodo que executa a busca generica $f($builder, $genericSearch)
     * @return Builder
     * @var Closure|Builder
     */
    protected $queryGenericSearch;

    /** Nome do arquivo blade
     * @var string
     */
    protected $view;

    /**
     * @var ReportExport
     */
    protected $reportExport;

    /** Nome do report
     * @var string
     */
    protected $name;

    /** Contem os parametros da requisicao do report
     * @var array|null
     */
    protected $request;

    /**
     * Report constructor.
     * @param string $name Nome do report
     */
    public function __construct($name = '')
    {
        $this->name = $name;
        $this->name == '' ? $this->request = request()->all() : $this->request = request()->get($this->name);
        $this->view = config('report.view');
        if (!$this->view)
            $this->view = 'Report::report';
        $this->columns = new Collection();
        $this->reportExport = new ReportExport($this);
        $this->queryBuilder = null;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /** Retorna a chave $key da requisicao
     * @param string|null $key Chave
     * @param string|array|null $defaultValue Valor caso inexistente
     * @return string|array|null
     */
    public function getRequestKey($key, $defaultValue = null)
    {
        if (!$key)
            return $this->request;
        if (!$this->request)
            return $defaultValue;
        if (array_key_exists($key, $this->request))
            return $this->request[$key];
        return $defaultValue;
    }

    /** Adiciona ou atualiza $value da chave $key da requisicao
     * @param string $key
     * @param string|array $value
     */
    public function setRequestKey($key, $value)
    {
        $this->request[$key] = $value;
    }

    /**
     * @return string
     */
    public function getInputGenericSearch()
    {
        return '<input class="form-control" type="text" name="' . $this->name . '[genericSearch]"
                           value="' . $this->getRequestKey('genericSearch') . '" placeholder="Pesquisar...">';
    }

    /**
     * @return string
     */
    public function getDownloadLink()
    {
        $query = $this->request;
        $query['export'] = '1';
        if ($this->name != '')
            return http_build_query([
                $this->name => $query
            ]);
        return http_build_query($query);
    }

    /** Adiciona uma coluna
     * @param ReportColumn|string $data
     * @return ReportColumn
     */
    public function addColumn($data)
    {
        if ($data instanceof ReportColumn) {
            $this->columns->add($data);
            $data->alias = pow($this->columns->count(), 3);
            return $data;
        }
        if (is_string($data)) {
            $column = new ReportColumn($this, $data);
            $this->columns->add($column);
            $column->alias = pow($this->columns->count(), 3);
            return $column;
        }
        throw new Exception('Invalid type');
    }

    /** Executa query do relatorio
     * @param Builder|null $builder
     * @param bool $export Indica se query deve ser paginada para web ou sem paginacao para export
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|LengthAwarePaginator|\Illuminate\Support\Collection
     * @throws Exception
     */
    public function executaQuery($builder = null, $export = false)
    {
        $query = $this->queryBuilder;
        $builder = $query();
        if ($this->getRequestKey('clear'))
            request()->merge([
                $this->name => null,
            ]);
        $builder = $this->montaSelects($builder);
        $builder = $this->montaWhere($builder);
        $builder = $this->montaOrder($builder);
        if ($export)
            $this->items = $builder->get();
        else
            $this->items = $builder
                ->paginate($this->getRequestKey('per_page', 10))
                ->appends([$this->name => $this->request])
                ->fragment($this->name);
        return $this->items;
    }

    /** Retorna a coluna identificada pelo nome
     * @param $name
     * @return ReportColumn|null
     * @throws Exception
     */
    public function getColumnByName($name)
    {
        foreach ($this->columns as $item)
            if ($item->name == $name)
                return $item;
        throw new Exception('Unknow column ' . $name);
    }

    /** Retorna a coluna identificada pelo alias
     * @param $alias
     * @return ReportColumn|null
     * @throws Exception
     */
    public function getColumnByAlias($alias)
    {
        foreach ($this->columns as $item)
            if ($item->alias == $alias)
                return $item;

        //Tenta pegar coluna pelo nome, se não achou pelo alias
        return $this->getColumnByName($alias);
    }

    /** Cria colunas com base no atributo fillables da entidade
     * @param Model|array $model Entidade
     */
    public function setModel($model)
    {
        if ($model instanceof Model) {
            foreach ($model->getFillable() as $item) {
                $this->columns->add(new ReportColumn($this, [
                    'name' => $model->getTable() . '.' . $item,
                    'title' => ucwords(str_replace('_', ' ', strtolower($item))),
                ]));
            }
        } elseif (is_array($model)) {
            foreach ($model as $item) {
                $this->setModel($item);
            }
        }
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|View
     * @throws Exception
     */
    public function build()
    {
        if (!$this->queryBuilder)
            throw new Exception('Method queryBuilder not implemented.');

        $this->clearTotalizadores();

        $this->executaQuery();

        foreach ($this->items as $item)
            foreach ($this->columns as $column)
                $column->totalizador->append($item);

        return $this->view();
    }

    /** Retorna resultado da query para retorno via API
     * @return Builder|\Illuminate\Support\Collection|null
     * @throws Exception
     */
    public function buildForAPI()
    {
        if (!$this->queryBuilder)
            throw new Exception('Method queryBuilder not implemented.');

        $this->clearTotalizadores();

        return $this->executaQuery();
    }

    /**
     * Limpa os totalizadores
     */
    protected function clearTotalizadores()
    {
        foreach ($this->columns as $column)
            if ($column->totalizador)
                $column->totalizador->clear();
    }

    /**
     * @return View
     */
    protected function view()
    {
        $report = $this;
        return view($this->view, compact('report'));
    }

    /** Retorna os links do paginator
     * @return \Illuminate\Contracts\Support\Htmlable
     */
    public function links()
    {
        return $this->items->links();
    }

    /** Retorna se possui parametro indicando que deve fazer download do arquivo
     * @return bool
     */
    public function toExport()
    {
        return ($this->getRequestKey('export') != null);
    }

    /** Retorna resposta com arquivo para download
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        return $this->reportExport->doExport();
    }

    /**
     * @param $query
     * @return array
     */
    protected function addDefaultFilter($query)
    {
        foreach ($this->columns as $column) {
            if ($column->defaultFilter != null) {
                if (!$query)
                    $query = array();
                if (array_key_exists($column->alias, $query))
                    continue;
                $query[$column->alias] = $column->defaultFilter;
            }
        }
        return $query;
    }

    /**
     * @param Builder $builder
     * @return Builder
     * @throws Exception
     */
    protected function montaWhere($builder)
    {
        if ($this->getRequestKey('genericSearch')) {
            $f = $this->queryGenericSearch;
            if ($f)
                return $f($builder, $this->getRequestKey('genericSearch'));
        }
        $query = $this->getRequestKey('query');
        $query = $this->addDefaultFilter($query);
        if ($query) {
            foreach ($query as $key => $value) {
                if ($value != null) {
                    $column = $this->getColumnByAlias($key);
                    $column->filter->data = $value;
                    if ($column->filter->tipo == 'choice') {
                        $builder = $builder->where(DB::raw($column->name), '=', $value);
                        continue;
                    }
                    switch ($column->filter->metodo) {
                        case '=':
                        case '>':
                        case '<':
                        case '<=':
                        case '>=':
                        case '!=':
                        case '<>':
                            $builder = $builder->where(DB::raw($column->name), $column->filter->metodo, $value);
                            break;
                        case 'like':
                            $builder = $builder->where(DB::raw($column->name), 'LIKE', '%' . $value . '%');
                            break;
                        case 'between':
                            if (array_key_exists('start', $value) && array_key_exists('end', $value) &&
                                $value['start'] && $value['end']) {
                                if ($column->filter->tipo == 'date') {
                                    $value['start'] = DateTime::createFromFormat('d/m/Y', $value['start'])->setTime(0, 0, 0, 0);
                                    $value['end'] = DateTime::createFromFormat('d/m/Y', $value['end'])->setTime(23, 59, 59, 999);
                                    $builder = $builder->whereBetween(DB::raw($column->name), $value);
                                } else {
                                    $value['start'] = DateTime::createFromFormat('d/m/Y H:i:s', $value['start']);
                                    $value['end'] = DateTime::createFromFormat('d/m/Y H:i:s', $value['end']);
                                    $builder = $builder->whereBetween(DB::raw($column->name), $value);
                                }
                            } else
                                unset($query[$key]);
                            break;
                    }
                } else
                    unset($query[$key]);
            }
            $this->setRequestKey('query', $query);
        }
        return $builder;
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    protected function montaSelects($builder)
    {
        $selects = array();
        if ($builder->getQuery()->joins) {
            //Possui joins
            if ($builder->getModel()) {
                $selects[] = $builder->getModel()->getTable() . '.' . $builder->getModel()->getKeyName() . ' as ' . $builder->getModel()->getKeyName();
                foreach ($builder->getModel()->getFillable() as $field) {
                    $aux = false;
                    foreach ($this->columns as $column) {
                        if (($column->name == $field) && ($column->raw != '')) {
                            $selects[] = $column->raw;
                            $aux = true;
                            break;
                        }
                    }
                    if (!$aux)
                        $selects[] = $builder->getModel()->getTable() . '.' . $field . ' as ' . $field;
                }
            }
            foreach ($builder->getQuery()->joins as $join) {
                foreach ($this->columns as $column) {
                    if (str_contains($column->name, $join->table . '.')) {
                        $selects[] =
                            $column->raw ? $column->raw :
                                ($column->name . ' as ' . $column->name);
                    }
                }
            }
        } else {
            //Possui apenas 1 tabela ou possui with
        }

        //Adiciona colunas raw que não tenham sido adicionados anteriormente
        foreach ($this->columns as $column)
            if (($column->raw != '') && (!in_array($column->raw, $selects)))
                $selects[] = $column->raw;

        if (count($selects) > 0)
            return $builder->select($selects);
        return $builder;
    }

    /**
     * @param Builder $builder
     * @return Builder
     * @throws Exception
     */
    protected function montaOrder($builder)
    {
        $httpParam = true;
        $order = $this->getRequestKey('order');
        if (!$order) {
            $order = $this->getDefaultOrder();
            $httpParam = false;
        }
        if ($order) {
            foreach ($order as $key => $value) {
                if ($value) {
                    $column = $this->getColumnByAlias($key);
                    $column->filter->order = $value;
                    $builder = $builder->orderBy($column->name, $value);
                } else
                    unset($order[$key]);
            }
            if ($httpParam)
                $this->setRequestKey('order', $order);
        }
        return $builder;
    }

    /** Retorna array com default order by
     * @return false|array
     */
    public function getDefaultOrder()
    {
        $res = array();
        foreach ($this->columns as $column) {
            if (strtoupper($column->orderby) == 'ASC') {
                $res[$column->alias] = 'ASC';
            } elseif (strtoupper($column->orderby) == 'DESC') {
                $res[$column->alias] = 'DESC';
            }
        }
        if (count($res) > 0)
            return $res;
        return false;
    }

    public function setExcelColumnFormats($formats)
    {
        throw new \Exception('Metodo extinto. Use \$this->addColumn(\"...\")->setExcelColumnFormat(...)');
    }
}
