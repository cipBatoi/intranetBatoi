<?php

namespace Intranet\Http\Controllers;

use Intranet\Botones\BotonImg;
use Intranet\Botones\BotonBasico;
use Intranet\Entities\Grupo;
use Intranet\Entities\AlumnoFctAval;
use DB;
use Styde\Html\Facades\Alert;
use Intranet\Botones\Panel;
use Intranet\Entities\Documento;
use Illuminate\Support\Facades\Session;

/**
 * Class PanelFctAvalController
 * @package Intranet\Http\Controllers
 */
class PanelFctAvalController extends IntranetController
{

    /**
     * @var string
     */
    protected $perfil = 'profesor';
    /**
     * @var string
     */
    protected $model = 'AlumnoFctAval';
    /**
     * @var array
     */
    protected $gridFields = ['Nombre', 'Qualificacio', 'Projecte', 'periode','hasta'];
    /**
     * @var bool
     */
    protected $profile = false;

    /**
     * @return \Illuminate\Support\Collection|mixed
     */
    public function search()
    {
        $nombres = AlumnoFctAval::select('idAlumno')->distinct()->misFcts()->esAval()->get()->toArray();
        $todas = collect();
        foreach ($nombres as $nombre){
            $todas->push(AlumnoFctAval::misFcts()->esAval()->where('idAlumno',$nombre['idAlumno'])->orderBy('idAlumno')->first());
        }
        return $todas;
        
    }


    /**
     *
     */
    protected function iniBotones()
    {
        Session::put('redirect', 'PanelFctAvalController@index');
        $this->panel->setPestana('Resum', false, 'profile.resumenfct');
        $this->setQualityB();
        $this->setActaB();
        $this->panel->setBoton('grid', new BotonImg('fct.apte', ['img' => 'fa-hand-o-up', 'where' => ['calificacion', '!=', '1', 'actas', '==', 0, 'asociacion', '==', 1]]));
        $this->panel->setBoton('grid', new BotonImg('fct.noApte', ['img' => 'fa-hand-o-down', 'where' => ['calProyecto', '<', '5', 'calificacion', '!=', '0', 'actas', '==', 0, 'asociacion', '==', 1]]));
        $this->setProjectB();
        $this->panel->setBoton('grid', new BotonImg('fct.empresa', ['img' => 'fa-square-o', 'roles' => config('roles.rol.tutor'),
            'where' => ['insercion', '==', '0','asociacion','==',1]]));
        $this->panel->setBoton('grid', new BotonImg('fct.empresa', ['img' => 'fa-check-square-o', 'roles' => config('roles.rol.tutor'),
            'where' => ['insercion', '==', '1','asociacion','==',1]]));

    }


    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function apte($id)
    {
        $fct = AlumnoFctAval::find($id);
        $fct->calificacion = 1;
        $fct->save();

        return back();
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function noApte($id)
    {
        $fct = AlumnoFctAval::find($id);
        $fct->calificacion = 0;
        $fct->calProyecto = null;
        $fct->save();

        return back();
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function noProyecto($id)
    {
        $fct = AlumnoFctAval::find($id);
        $fct->calProyecto = 0;
        $fct->save();

        return back();
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function nuevoProyecto($id)
    {
        $fct = AlumnoFctAval::find($id);
        $fct->calProyecto = null;
        $fct->actas = 1;
        $fct->save();

        return back();
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function empresa($id){
       $fct = AlumnoFctAval::find($id);
       $fct->insercion = $fct->insercion?0:1;
       $fct->save();
       return $this->redirect();
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function demanarActa()
    {
        $grupo = Grupo::QTutor()->first();

        if ($grupo->acta_pendiente) {
            Alert::message("L'acta pendent esta en procés", 'info');
            return back();
        }

        if ($this->lookForStudents($grupo->proyecto)) {
            $grupo->acta_pendiente = 1;
            $grupo->save();
            avisa(config('contacto.jefeEstudios2'), "Acta pendent grup $grupo->nombre", config('contacto.host.web')."/direccion/$grupo->codigo/acta");
            Alert::message('Acta demanada', 'info');
            return back();
        }

        Alert::message('No tens nous alumnes per ser avaluats', 'warning');
        return back();
    }

    /**
     * @param $projectNeeded
     * @return bool
     */
    private function lookForStudents($projectNeeded){
        $found = false;
        foreach (AlumnoFctAval::MisFcts()->NoAval()->get() as $fct) {
            if ($projectNeeded)
                if (isset($fct->calProyecto)) {
                    $fct->actas = 3;
                    $fct->save();
                    $found = true;
                }
            else
                if (isset($fct->calificacion)) {
                    $fct->actas = 3;
                    $fct->save();
                    $found = true;
                }
        }
        return $found;
    }

    /**
     *
     */
    private function setQualityB(): void
    {
        $find = Documento::where('propietario', AuthUser()->FullName)->where('tipoDocumento', 'Qualitat')
                                    ->where('curso', Curso())->first();
        if (!$find)
            $this->panel->setBoton('index', new BotonBasico("fct.upload", ['class' => 'btn-info', 'roles' => config('roles.rol.tutor')]));
        else
            $this->panel->setBoton('index', new BotonBasico("documento.$find->id.edit", ['class' => 'btn-info', 'roles' => config('roles.rol.tutor')]));
    }

    /**
     *
     */
    private function setActaB(): void
    {
        if (Grupo::QTutor()->first() && Grupo::QTutor()->first()->acta_pendiente == false)
            $this->panel->setBoton('index', new BotonBasico("fct.acta", ['class' => 'btn-info', 'roles' => config('roles.rol.tutor')]));
        else
            Alert::message("L'acta pendent esta en procés", 'info');
    }

    /**
     *
     */
    private function setProjectB(): void
    {
        if (Grupo::QTutor()->first() && Grupo::QTutor()->first()->proyecto) {
            $this->panel->setBoton('grid', new BotonImg('fct.proyecto', ['img' => 'fa-file', 'roles' => config('roles.rol.tutor'),
                'where' => ['calProyecto', '<', '1', 'actas', '<', 2]]));
            $this->panel->setBoton('grid', new BotonImg('fct.noProyecto', ['img' => 'fa-toggle-off', 'roles' => config('roles.rol.tutor'),
                'where' => ['calProyecto', '<', '0', 'actas', '<', 2]]));
            $this->panel->setBoton('grid', new BotonImg('fct.nuevoProyecto', ['img' => 'fa-toggle-on', 'roles' => config('roles.rol.tutor'),
                'where' => ['calProyecto', '<', '5', 'calProyecto', '>=', 0, 'actas', '==', 2]]));
            $this->panel->setBoton('grid', new BotonImg('fct.modificaNota', ['img' => 'fa-edit', 'roles' => config('roles.rol.tutor'),
                'where' => ['calProyecto', '>=', 0, 'actas', '<', 2]]));
        }
    }

} 