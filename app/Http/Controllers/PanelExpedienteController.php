<?php

namespace Intranet\Http\Controllers;
use Intranet\Entities\Expediente;
use Intranet\Entities\TipoExpediente;

/**
 * Class PanelExpedienteController
 * @package Intranet\Http\Controllers
 */
class PanelExpedienteController extends BaseController
{
    use traitPanel;

    /**
     * @var array
     */
    protected $gridFields = ['id', 'nomAlum', 'fecha', 'Xtipo', 'Xmodulo', 'situacion'];
    /**
     * @var string
     */
    protected $perfil = 'profesor';
    /**
     * @var string
     */
    protected $model = 'Expediente';
    /**
     * @var string
     */
    protected $orden = 'fecha';
    /**
     * @var array
     */
    protected $parametresVista = ['before' => [] , 'modal' => ['explicacion']];


    /**
     *
     */
    protected function iniBotones()
    {
        $this->panel->setBotonera([], ['delete', 'edit']);
        $this->panel->setBothBoton('expediente.gestor',['img' => 'fa-eye', 'where'=>['idDocumento','!=',null]]);
        $this->setAuthBotonera();
    }

    /**
     * @return mixed
     */
    protected function search()
    {
        return Expediente::whereIn('tipo', hazArray(TipoExpediente::where('orientacion',0)->get(), 'id'))->get();
    }
    

}
