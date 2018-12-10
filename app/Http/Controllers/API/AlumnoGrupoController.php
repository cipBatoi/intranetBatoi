<?php

namespace Intranet\Http\Controllers\API;

use Intranet\Entities\Alumno_grupo;
use Intranet\Entities\Grupo;
use Illuminate\Http\Request;


class AlumnoGrupoController extends ApiBaseController
{

    protected $model = 'Alumno_grupo';

    private function alumnos($migrupo)
    {
       if (isset($migrupo->first()->codigo)) {
            $alumnos = Alumno_grupo::where('idGrupo', '=', $migrupo->first()->codigo)->get();
            foreach ($alumnos as $alumno) {
                $misAlumnos[$alumno->idAlumno] = $alumno->Alumno->apellido1 . ' ' . $alumno->Alumno->apellido2 . ', ' . $alumno->Alumno->nombre;
            }
        }
        return $misAlumnos;
        if ($send) return $this->sendResponse($misAlumnos, 'OK');
        else return $misAlumnos; 
    }
    public function show($cadena,$send=true)
    {
        $migrupo = Grupo::Qtutor($cadena)->get();
        return $this->alumnos($migrupo);
    }
    
    public function getModulo($dni,$modulo){
        $migrupo = Grupo::miGrupoModulo($dni,$modulo)->get();
        return $this->alumnos($migrupo);
    }

}