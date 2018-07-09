<?php

namespace Intranet\Http\Controllers;

use Illuminate\Http\Request;
use Intranet\Entities\Alumno_grupo;
use Illuminate\Support\Facades\Auth;
use Intranet\Botones\BotonBasico;
use Intranet\Botones\BotonIcon;
use Intranet\Botones\BotonImg;
use Intranet\Entities\Grupo;
use Intranet\Entities\Curso;
use Intranet\Entities\Alumno;

class Alumno_grupoController extends IntranetController
{
    protected $perfil = 'profesor';
    protected $model = 'Alumno_grupo';
    protected $gridFields = ['nameFull', 'telef1', 'telef2', 'email'];
    
    public function search(){
        $this->titulo = ['quien' => $this->search];
        return Alumno::QGrupo($this->search)->get();
    }

    protected function iniBotones()
    {
        $miGrupo = Grupo::where('tutor', '=', AuthUser()->dni)->get();
        $miGrupo = isset($miGrupo->first()->codigo) ? $miGrupo->first()->codigo : '';
        $this->panel->setBoton('grid', new BotonImg('alumno.muestra'));
        $this->panel->setBoton('grid', new BotonImg('alumno.edit', ['roles' => config('roles.rol.direccion'), 'where' => ['idGrupo', '!=', $miGrupo]]));
        $this->panel->setBoton('grid', new BotonImg('alumno.edit', ['where' => ['idGrupo', '==', $miGrupo]]));
        $this->panel->setBoton('grid', new BotonImg('alumno.carnet', ['roles' => config('roles.rol.direccion'), 'where' => ['idGrupo', '!=', $miGrupo]]));
        $this->panel->setBoton('profile', new BotonIcon('alumno.carnet', ['roles' => config('roles.rol.direccion'), 'where' => ['idGrupo', '!=', $miGrupo]]));
        $this->panel->setBoton('grid', new BotonImg('alumno.carnet', ['where' => ['idGrupo', '==', $miGrupo]]));
        $this->panel->setBoton('profile', new BotonIcon('alumno.carnet', ['where' => ['idGrupo', '==', $miGrupo]]));
        $this->panel->setBoton('grid', new BotonImg('alumno.baja', ['where' => ['idGrupo', '==', $miGrupo]]));
        $this->panel->setBoton('profile', new BotonIcon('alumno.baja', ['where' => ['idGrupo', '==', $miGrupo]]));
        $this->panel->setBoton('grid', new BotonImg('direccion.aFol', ['img' => 'fa-file-word-o','roles' => config('roles.rol.direccion')]));
        
        
        //$this->panel->setBoton('grid',new BotonImg('fct.asigna',['img'=>'fa-birthday-cake', 'roles'=>config('roles.rol.tutor'),'where' => ['idGrupo', '==', $miGrupo]]));
        $cursos = Curso::Activo()->get();
        foreach ($cursos as $curso) {
            if (($curso->aforo == 0) || ($curso->NAlumnos < $curso->aforo * config('variables.reservaAforo')))
                $this->panel->setBoton('grid', new BotonImg('alumnocurso.registerAlumno/' . $curso->id, ['text' => trans('messages.generic.register') . $curso->titulo, 'img' => 'fa-institution']));
        }
        
    }

}
